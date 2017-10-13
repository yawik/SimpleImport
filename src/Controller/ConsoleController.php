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
     * @param CrawlerRepository $crawlerRepository
     * @param CrawlerProcessors $crawlerProcessors
     * @param ModuleOptions $moduleOptions
     * @param LoggerInterface $logger
     */
    public function __construct(
        CrawlerRepository $crawlerRepository,
        CrawlerProcessors $crawlerProcessors,
        InputFilterInterface $crawlerInputFilter,
        ModuleOptions $moduleOptions,
        LoggerInterface $logger)
    {
        $this->crawlerRepository = $crawlerRepository;
        $this->crawlerProcessors = $crawlerProcessors;
        $this->crawlerInputFilter = $crawlerInputFilter;
        $this->moduleOptions = $moduleOptions;
        $this->logger = $logger;
    }
    
    /**
     * Imports data via available crawlers
     */
    public function importAction()
    {
        $limit = abs($this->params('limit')) ?: 2;
        $delay = new DateTime();
        $delay->modify(sprintf('-%d minute', $this->moduleOptions->getImportRunDelay()));
        $console = $this->getConsole();
        $crawlers = $this->crawlerRepository->getCrawlersToImport($delay, $limit);
        
        if (0 === count($crawlers)) {
            $console->writeLine('There is currently no crawler to process.', ColorInterface::YELLOW);
            return;
        }
        
        $documentManager = $this->crawlerRepository->getDocumentManager();
        
        foreach ($crawlers as $crawler) {
            /** @var \SimpleImport\CrawlerProcessor\ProcessorInterface $processor */
            $processor = $this->crawlerProcessors->get($crawler->getType());
            $result = new Result();
            $processor->execute($crawler, $result, $this->logger);
            $crawler->setDateLastRun(new DateTime());
            $documentManager->flush();
            
            $console->writeLine(sprintf(
                'The crawler with the name (ID) "%s (%s)" finished with the following result:',
                $crawler->getName(),
                $crawler->getId()
            ), ColorInterface::GREEN);
            
            $console->writeLine(sprintf(
                'Inserted = %d, Updated = %d, Deleted = %d, Invalid = %d',
                $result->getInserted(),
                $result->getUpdated(),
                $result->getDeleted(),
                $result->getInvalid()
            ), ColorInterface::LIGHT_YELLOW);
        }
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
        $crawler = $this->crawlerRepository->create($data);
        $crawler->setOptionsFromArray($data['options']);
        $this->crawlerRepository->store($crawler);
        
        $console->writeLine(sprintf(
            'A new crawler with the ID "%s" has been successfully added.',
            $crawler->getId()
        ), ColorInterface::GREEN);
    }
}
