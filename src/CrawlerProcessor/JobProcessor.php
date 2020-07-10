<?php

/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImport\CrawlerProcessor;

use Jobs\Entity\Job;
use SimpleImport\Entity\Crawler;
use SimpleImport\DataFetch\JsonFetch;
use SimpleImport\DataFetch\PlainTextFetch;
use SimpleImport\Queue\GuessLanguageJob;
use SlmQueue\Controller\Plugin\QueuePlugin;
use Laminas\Json\Json;
use Laminas\Log\LoggerInterface;
use SimpleImport\Entity\Item;
use Jobs\Repository\Job as JobRepository;
use Jobs\Entity\StatusInterface as JobStatusInterface;
use Laminas\Hydrator\HydrationInterface;
use Laminas\InputFilter\InputFilterInterface;
use Symfony\Component\Filesystem\Filesystem;
use DateTime;
use RuntimeException;

class JobProcessor implements ProcessorInterface
{
    
    /**
     * @var JsonFetch
     */
    private $jsonFetch;
    
    /**
     * @var PlainTextFetch
     */
    private $plainTextFetch;
    
    /**
     * @var JobRepository
     */
    private $jobRepository;
    
    /**
     * @var HydrationInterface
     */
    private $jobHydrator;
    
    /**
     * @var InputFilterInterface
     */
    private $dataInputFilter;

    /**
     * @var \SlmQueue\Controller\Plugin\QueuePlugin
     */
    private $queuePlugin;

    /**
     * @var string
     */
    private $lockDir;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $currentLockFile;

    /**
     * @param JsonFetch $jsonFetch
     * @param PlainTextFetch $plainTextFetch
     * @param JobRepository $jobRepository
     * @param HydrationInterface $jobHydrator
     * @param InputFilterInterface $dataInputFilter
     * @param Filesystem $filesystem
     * @param string $lockDir
     */
    public function __construct(
        JsonFetch $jsonFetch,
        PlainTextFetch $plainTextFetch,
        JobRepository $jobRepository,
        HydrationInterface $jobHydrator,
        InputFilterInterface $dataInputFilter,
        Filesystem $filesystem,
        $lockDir
    )
    {
        $this->jsonFetch = $jsonFetch;
        $this->plainTextFetch = $plainTextFetch;
        $this->jobRepository = $jobRepository;
        $this->jobHydrator = $jobHydrator;
        $this->dataInputFilter = $dataInputFilter;
        $this->fs = $filesystem;

        if(!is_dir($lockDir) || !is_writable($lockDir)){
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $lockDir
            ));
        }
        $this->lockDir = $lockDir;
    }

    /**
     * @param QueuePlugin $queuePlugin
     *
     * @return self
     */
    public function setQueuePlugin(QueuePlugin $queuePlugin)
    {
        $this->queuePlugin = $queuePlugin;
        $queuePlugin('simpleimport');

        return $this;
    }

    /**
     * {@inheritDoc}
     * @throws
     */
    public function execute(
        Crawler $crawler,
        Result $result,
        LoggerInterface $logger,
        $force = false
    )
    {   $fs = $this->fs;
        $lockFile = $this->lockDir.'/'.$crawler->getId().'.lck';

        clearstatcache(true,$lockFile);

        // check if existing crawler already running
        if($fs->exists($lockFile) && !$force){
            $message = sprintf(
                'Crawler "%s (%s)" already running, with pid: "%s"',
                $crawler->getName(),
                $crawler->getId(),
                file_get_contents($lockFile)
            );
            $logger->err($message);
            throw new RuntimeException($message);
        }

        try {
            file_put_contents($lockFile,getmypid(),LOCK_EX);
            $fs->touch($lockFile);
            $this->currentLockFile = $lockFile;
            $this->doExecute($crawler, $result, $logger);
            $fs->remove($lockFile);
        } catch (\Exception $e) {
            $message = sprintf('Fetching remote data failed, reason: "%s"', $e->getMessage());
            $logger->err($message);
            $fs->remove($lockFile);
            throw new RuntimeException($message);
        }
    }

    /**
     * @param Crawler $crawler
     * @param Result $result
     * @param LoggerInterface $logger
     * @throws \Doctrine\ODM\MongoDB\LockException when syncChanges failed
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException  when syncChanges failed
     * @throws \Exception when trackChanges failed
     */
    private function doExecute(Crawler $crawler, Result $result, LoggerInterface $logger)
    {
        $data = $this->jsonFetch->fetch($crawler->getFeedUri());
        if (!is_array($data) || !isset($data['jobs']) || !is_array($data['jobs'])) {
            throw new RuntimeException('Invalid data format, a jobs key is missing or invalid');
        }
        $result->setToProcess(count($data['jobs']));
        $this->trackChanges($crawler, $result, $logger, $data['jobs']);
        $this->syncChanges($crawler, $result, $logger);
    }

    /**
     * @param Crawler $crawler
     * @param Result $result
     * @param LoggerInterface $logger
     * @param array $data
     * @throws \Exception
     */
    private function trackChanges(Crawler $crawler, Result $result, LoggerInterface $logger, array $data)
    {
        $importIds = [];
        
        foreach ($data as $importData) {
            $this->dataInputFilter->setData($importData);
            
            if (!$this->dataInputFilter->isValid()) {
                $result->incrementInvalid();
                $messages = $this->formatMessages($this->dataInputFilter->getMessages());
                $logger->err(sprintf('Invalid import data: "%s"', Json::encode($messages)), $importData);
                continue;
            }
            
            $importData = $this->dataInputFilter->getValues();
            $importId = $importData['id'];
            $importIds[$importId] = true;
            $item = $crawler->getItem($importId);
            
            if ($item) {
                // check if the item has changed
                if ($importData != $item->getImportData() || $item->getDateDeleted()) {
                    // mark the item modified
                    $item->setImportData($importData)
                        ->setDateModified(new DateTime())
                        ->setDateDeleted(null);
                } else {
                    $result->incrementUnchanged();
                }
            } else {
                // create a new item
                $crawler->addItem(new Item($importId, $importData));
            }
        }
        
        // check for deleted items
        foreach ($crawler->getItems() as $item) {
            if (!isset($importIds[$item->getImportId()]) && !$item->getDateDeleted()) {
                $item->setDateDeleted(new DateTime());
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @param Result $result
     * @param LoggerInterface $logger
     * @throws \Doctrine\ODM\MongoDB\LockException
     * @throws \Doctrine\ODM\MongoDB\Mapping\MappingException
     */
    private function syncChanges(Crawler $crawler, Result $result, LoggerInterface $logger)
    {
        foreach ($crawler->getItemsToSync() as $item) {
            if ($item->getDocumentId()) {
                /** @var \Jobs\Entity\Job $job */
                $job = $this->jobRepository->find($item->getDocumentId());
                
                if ($job) {
                    if ($item->getDateDeleted()) {
                        // expire the job
                        $job->setStatus(JobStatusInterface::EXPIRED);
                        $result->incrementDeleted();
                    } else {
                        // update the job
                        $job->setStatus($crawler->getOptions()->getRecoverState());
                        $this->jobHydrator->hydrate($item->getImportData(), $job);
                        $this->guessLanguage($job);
                        $result->incrementUpdated();
                    }
                } else {
                    // the realated job does not exists
                    $logger->err(sprintf('Job with ID "%s" does not exists', $item->getDocumentId()));
                }
            }
            else {
                $importData = $item->getImportData();

                if (array_key_exists('templateValues', $importData)
                    && (array_key_exists('description', $importData['templateValues'])
                        || array_key_exists('tasks', $importData['templateValues'])
                        || array_key_exists('requirements', $importData['templateValues'])
                        || array_key_exists('benefits', $importData['templateValues'])
                        || array_key_exists('html', $importData['templateValues'])
                    )
                ) {
                    $plainText = false;
                }
                else {
                    try {
                        $plainText = $this->plainTextFetch->fetch($importData['link']);
                    } catch (RuntimeException $e) {
                        $logger->warn(sprintf(
                            'Cannot fetch HTML digest for a job, import ID: "%s", link: "%s", reason: "%s"',
                            $item->getImportId(),
                            $importData['link'],
                            $e->getMessage())
                        );
                        $plainText = false;
                    }
                }
                
                // create a new job
                /* @var Job $job */
                $job = $this->jobRepository->create(null);
                $job->setOrganization($crawler->getOrganization());
                $job->setStatus($crawler->getOptions()->getInitialState());
                if (false !== $plainText) { $job->setMetaData('plainText', $plainText); }

                $this->jobHydrator->hydrate($importData, $job);
                try{
                    $this->jobRepository->store($job);
                }catch (\Exception $exception){
                    if(!$job->getId()){
                        $crawler->getItemsCollection()->remove($item->getId());
                    }
                }

                if($job->getId()){
                    $this->guessLanguage($job);
                    $item->setDocumentId($job->getId());
                    $result->incrementInserted();
                }
            }

            if($item->getDocumentId()){
                // only set date synced when document id is defined
                $item->setDateSynced(new DateTime());
            }
        }
    }
    
    /**
     * @param array $messages
     * @return array
     */
    private function formatMessages(array $messages)
    {
        $formatted = [];
        
        foreach ($messages as $name => $message) {
            $formatted[] = sprintf('%s: "%s"', $name, implode(', ', $message));
        }
        
        return $formatted;
    }

    private function guessLanguage(Job $job)
    {
        if (!$this->queuePlugin || $job->getLanguage()) { return; }

        $this->queuePlugin->push(GuessLanguageJob::class, ['jobId' => $job->getId()]);
    }
}
