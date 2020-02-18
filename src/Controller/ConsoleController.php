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
use Laminas\Mvc\Console\Controller\AbstractConsoleController;
use Laminas\Console\ColorInterface;
use Laminas\Log\LoggerInterface;
use Laminas\InputFilter\InputFilterInterface;
use DateTime;
use Laminas\Mvc\Console\View\ViewModel;

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
        $name  = $this->params('name');
        $id    = $this->params('id');

        $console = $this->getConsole();

        if ($name || $id) {
            /* @var \SimpleImport\Entity\Crawler $crawler */
            $findMethod = 'find' . ($id ? '' : 'OneByName');
            $name = $id ?: $name;
            $crawler = $this->crawlerRepository->$findMethod($name);

            if (!$crawler) {
                $console->writeLine(sprintf('There is no crawler with %s "%s"', $id ? 'id' : 'name', $name), ColorInterface::RED);
                return new ViewModel([], ['errorLevel' => 1]);
            }

            if (!$crawler->canRun()) {
                $console->writeLine(sprintf('Crawler "%s" (%s) is still delayed.', $crawler->getName(), $crawler->getId()), ColorInterface::YELLOW);
                return new ViewModel([], ['errorLevel' => 2]);
            }

            $crawlers = [$crawler];
        } else {

            $crawlers = $this->crawlerRepository->getCrawlersToImport($limit);
        
            if (0 === count($crawlers)) {
                $console->writeLine('There is currently no crawler to process.', ColorInterface::YELLOW);
                return;
            }
        }

        $documentManager = $this->crawlerRepository->getDocumentManager();
        $console = $this->getConsole();

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
                'initialState' => $this->params('jobInitialState'),
                'recoverState' => $this->params('jobRecoverState'),
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
