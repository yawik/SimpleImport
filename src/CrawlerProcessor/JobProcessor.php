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
use SimpleImport\RemoteFetch\JsonRemoteFetch;
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
     * @var JsonRemoteFetch
     */
    private $jsonFetch;
    
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
     * @param JsonRemoteFetch $jsonFetch
     * @param JobRepository $jobRepository
     * @param HydrationInterface $jobHydrator
     * @param InputFilterInterface $dataInputFilter
     */
    public function __construct(
        JsonRemoteFetch $jsonFetch,
        JobRepository $jobRepository,
        HydrationInterface $jobHydrator,
        InputFilterInterface $dataInputFilter)
    {
        $this->jsonFetch = $jsonFetch;
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
        
        $this->trackChanges($crawler, $result, $logger, $data);
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
                $logger->err(sprintf('Invalid import data: "%s"', implode(', ', $messages)), $importData);
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
                // create a new job
                $job = $this->jobRepository->create(null, true);
                $job->setOrganization($crawler->getOrganization());
                $job->setStatus($crawler->getOptions()->getInitialState());
                $this->jobHydrator->hydrate($item->getImportData(), $job);
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
