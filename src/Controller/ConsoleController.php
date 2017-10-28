<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Controller;

use SimpleImport\Entity\Crawler;
use SimpleImport\Repository\Crawler as CrawlerRepository;
use SimpleImport\CrawlerProcessor\Manager as CrawlerProcessors;
use SimpleImport\CrawlerProcessor\Result;
use SimpleImport\Options\ModuleOptions;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Console\ColorInterface;
use Zend\Log\LoggerInterface;
use Zend\InputFilter\InputFilterInterface;
use DateTime;

class ConsoleController extends AbstractConsoleController
{
    
    /**
     * @var CrawlerRepository
     */
    private $crawlerRepository;
    
    /**
     * @var CrawlerProcessors
     */
    private $crawlerProcessors;
    
    /**
     * @var InputFilterInterface
     */
    private $crawlerInputFilter;
    
    /**
     * @var ModuleOptions
     */
    private $moduleOptions;
    
    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @var Result
     */
    private $resultPrototype;
    
    /**
     * @param CrawlerRepository $crawlerRepository
     * @param CrawlerProcessors $crawlerProcessors
     * @param InputFilterInterface $crawlerInputFilter
     * @param ModuleOptions $moduleOptions
     * @param LoggerInterface $logger
     * @param Result $resultPrototype
     */
    public function __construct(
        CrawlerRepository $crawlerRepository,
        CrawlerProcessors $crawlerProcessors,
        InputFilterInterface $crawlerInputFilter,
        ModuleOptions $moduleOptions,
        LoggerInterface $logger,
        Result $resultPrototype)
    {
        $this->crawlerRepository = $crawlerRepository;
        $this->crawlerProcessors = $crawlerProcessors;
        $this->crawlerInputFilter = $crawlerInputFilter;
        $this->moduleOptions = $moduleOptions;
        $this->logger = $logger;
        $this->resultPrototype = $resultPrototype;
    }
    
    /**
     * Imports data via available crawlers
     */
    public function importAction()
    {
        $limit = abs($this->params('limit')) ?: 3;
        $console = $this->getConsole();
        $crawlers = $this->crawlerRepository->getCrawlersToImport($limit);
        
        if (0 === count($crawlers)) {
            $console->writeLine('There is currently no crawler to process.', ColorInterface::YELLOW);
            return;
        }
        
        $documentManager = $this->crawlerRepository->getDocumentManager();
        
        foreach ($crawlers as $crawler) {
            /** @var \SimpleImport\CrawlerProcessor\ProcessorInterface $processor */
            $processor = $this->crawlerProcessors->get($crawler->getType());
            
            $console->writeLine(sprintf(
                'The crawler with the name (ID) "%s (%s)" has started its job:',
                $crawler->getName(),
                $crawler->getId())
            );
            
            $result = clone $this->resultPrototype;
            $processor->execute($crawler, $result, $this->logger);
            $crawler->setDateLastRun(new DateTime());
            $documentManager->flush();
            
            $console->writeLine(sprintf(
                'The crawler with the name (ID) "%s (%s)" has finished with the following result:',
                $crawler->getName(),
                $crawler->getId()
            ), ColorInterface::GREEN);
            
            $console->writeLine(sprintf(
                'To process = %d, Inserted = %d, Updated = %d, Deleted = %d, Invalid = %d, Unchanged = %d',
                $result->getToProcess(),
                $result->getInserted(),
                $result->getUpdated(),
                $result->getDeleted(),
                $result->getInvalid(),
                $result->getUnchanged()
            ), ColorInterface::LIGHT_YELLOW);
        }
        
        $console->writeLine('The import task has finished (see simple-import.log for possible problems).', ColorInterface::GRAY);
    }
    
    /**
     * Adds a new crawler.
     */
    public function addCrawlerAction()
    {
        $this->crawlerInputFilter->setData([
            'name' => $this->params('name'),
            'organization' => $this->params('organization'),
            'feedUri' => $this->params('feed-uri'),
            'runDelay' => $this->params('runDelay', $this->moduleOptions->getImportRunDelay()),
            'type' => $this->params('type', Crawler::TYPE_JOB),
            'options' => [
                'initialState' => $this->params('jobInitialState')
            ]
        ]);
        $console = $this->getConsole();
        
        if (!$this->crawlerInputFilter->isValid()) {
            $console->writeLine('Invalid parameters!', ColorInterface::RED);
            foreach ($this->crawlerInputFilter->getMessages() as $name => $messages) {
                $console->writeLine(sprintf(' - %s: %s', $name, implode(', ', $messages)), ColorInterface::LIGHT_YELLOW);
            }
            return;
        }
        
        /** @var Crawler $crawler */
        $data = $this->crawlerInputFilter->getValues();
        $crawler = $this->crawlerRepository->create($data)
            ->setOptionsFromArray($data['options']);
        $this->crawlerRepository->store($crawler);
        
        $console->writeLine(sprintf(
            'A new crawler with the ID "%s" has been successfully added.',
            $crawler->getId()
        ), ColorInterface::GREEN);
    }
}
