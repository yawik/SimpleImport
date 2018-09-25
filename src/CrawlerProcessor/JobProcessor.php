<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\CrawlerProcessor;

use SimpleImport\Entity\Crawler;
use SimpleImport\DataFetch\JsonFetch;
use SimpleImport\DataFetch\PlainTextFetch;
use Zend\Json\Json;
use Zend\Log\LoggerInterface;
use SimpleImport\Entity\Item;
use Jobs\Repository\Job as JobRepository;
use Jobs\Entity\StatusInterface as JobStatusInterface;
use Zend\Hydrator\HydrationInterface;
use Zend\InputFilter\InputFilterInterface;
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
     * @param JsonFetch $jsonFetch
     * @param PlainTextFetch $plainTextFetch
     * @param JobRepository $jobRepository
     * @param HydrationInterface $jobHydrator
     * @param InputFilterInterface $dataInputFilter
     */
    public function __construct(
        JsonFetch $jsonFetch,
        PlainTextFetch $plainTextFetch,
        JobRepository $jobRepository,
        HydrationInterface $jobHydrator,
        InputFilterInterface $dataInputFilter)
    {
        $this->jsonFetch = $jsonFetch;
        $this->plainTextFetch = $plainTextFetch;
        $this->jobRepository = $jobRepository;
        $this->jobHydrator = $jobHydrator;
        $this->dataInputFilter = $dataInputFilter;
    }
    
    /**
     * {@inheritDoc}
     * @see \SimpleImport\CrawlerProcessor\ProcessorInterface::execute()
     */
    public function execute(Crawler $crawler, Result $result, LoggerInterface $logger)
    {
        try {
            $data = $this->jsonFetch->fetch($crawler->getFeedUri());
        } catch (RuntimeException $e) {
            $logger->err(sprintf('Fetching remote data failed, reason: "%s"', $e->getMessage()));
            return;
        }
        
        if (!is_array($data) || !isset($data['jobs']) || !is_array($data['jobs'])) {
            $logger->err('Invalid data, a jobs key is missing or invalid');
            return;
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
                if ($importData != $item->getImportData()) {
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
                        $job->setStatus(JobStatusInterface::WAITING_FOR_APPROVAL);
                        $this->jobHydrator->hydrate($item->getImportData(), $job);
                        $result->incrementUpdated();
                    }
                } else {
                    // the realated job does not exists
                    $logger->err(sprintf('Job with ID "%s" does not exists', $item->getDocumentId()));
                }
            } else {
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
                } else {
                    try {
                        $plainText = $this->plainTextFetch->fetch($importData['link']);
                    } catch (RuntimeException $e) {
                        $logger->err(sprintf(
                            'Cannot fetch HTML digest for a job, import ID: "%s", link: "%s", reason: "%s"',
                            $item->getImportId(),
                            $importData['link'],
                            $e->getMessage())
                        );
                    
                        $result->incrementInvalid();
                        continue;
                    }
                }
                
                // create a new job
                $job = $this->jobRepository->create(null);
                $job->setOrganization($crawler->getOrganization());
                $job->setStatus($crawler->getOptions()->getInitialState());
                if (false !== $plainText) { $job->setMetaData('plainText', $plainText); }
                $this->jobHydrator->hydrate($importData, $job);
                $this->jobRepository->store($job);
                $item->setDocumentId($job->getId());
                $result->incrementInserted();
            }
            
            $item->setDateSynced(new DateTime());
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
}
