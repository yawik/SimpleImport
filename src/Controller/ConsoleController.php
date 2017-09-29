<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Controller;

use SimpleImport\Repository\Crawler as CrawlerRepository;
use SimpleImport\CrawlerProcessor\Manager as CrawlerProcessors;
use SimpleImport\Options\ModuleOptions;
use Zend\Mvc\Console\Controller\AbstractConsoleController;
use Zend\Console\ColorInterface;
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
     * @var ModuleOptions
     */
    private $moduleOptions;
    
    /**
     * @var callable
     */
    private $progressBarFactory;
    
    /**
     * @param CrawlerRepository $crawlerRepository
     * @param ModuleOptions $moduleOptions
     * @param callable $progressBarFactory
     */
    public function __construct(
        CrawlerRepository $crawlerRepository,
        CrawlerProcessors $crawlerProcessors,
        ModuleOptions $moduleOptions,
        callable $progressBarFactory)
    {
        $this->crawlerRepository = $crawlerRepository;
        $this->crawlerProcessors = $crawlerProcessors;
        $this->moduleOptions = $moduleOptions;
        $this->progressBarFactory = $progressBarFactory;
    }
    
    public function importAction()
    {
        $limit = abs($this->params('limit')) ?: 2;
        $delay = new DateTime();
        $delay->modify(sprintf('-%d minute', $this->moduleOptions->getImportRunDelay()));
        $console = $this->getConsole();
        $crawlers = $this->crawlerRepository->getCrawlersToImport($delay, $limit);
        $count = count($crawlers);
        
        if (0 === $count) {
            return $console->writeLine('There is currently no crawler to process.', ColorInterface::YELLOW);
        }
        
        /** @var \Core\Console\ProgressBar $progressBar */
        $progressBar = call_user_func($this->progressBarFactory, $count);
        $i = 0;
        
        foreach ($crawlers as $crawler) {
            $processor = $this->crawlerProcessors->get($crawler->getType());
            $result = $processor->execute($crawler);
            $crawler->setDateLastRun(new DateTime());
            
            $console->writeLine(sprintf(
                'The crawler with name(ID) "%s(%s)" finished with the following result:',
                $crawler->getName(),
                $crawler->getId()
            ), ColorInterface::GREEN);
            
            $console->writeLine(sprintf(
                'Inserted = %d, Updated = %d, Removed = %d',
                $result->getNumberOfInserted(),
                $result->getNumberOfUpdated(),
                $result->getNumberOfRemoved()
            ), ColorInterface::LIGHT_YELLOW);
            
            $progressBar->update(++$i);
        }
        
        $progressBar->finish();
    }
    
    public function addCrawlerAction()
    {
        /** @var \SimpleImport\Entity\Crawler $crawler */
        $crawler = $this->crawlerRepository->create([
            'name' => $this->params('name'),
            'feedUri' => $this->params('feed-uri'),
            'type' => $this->params('type'),
        ]);
        $this->crawlerRepository->store($crawler);
        
        return sprintf('A new crawler with ID "%s" has been successfully created.' . PHP_EOL, $crawler->getId());
    }
    
    /**
     * @return callable
     */
    public function getProgressBarFactory()
    {
        return $this->progressBarFactory;
    }
}
