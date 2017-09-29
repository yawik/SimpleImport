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
use Zend\Mvc\Controller\AbstractActionController;


class ConsoleController extends AbstractActionController
{
    
    /**
     * @var CrawlerRepository
     */
    private $crawlerRepository;
    
    /**
     * @var callable
     */
    private $progressBarFactory;
    
    /**
     * @param CrawlerRepository $crawlerRepository
     * @param callable $progressBarFactory
     */
    public function __construct(CrawlerRepository $crawlerRepository, callable $progressBarFactory)
    {
        $this->crawlerRepository = $crawlerRepository;
        $this->progressBarFactory = $progressBarFactory;
    }
    
    public function importAction()
    {
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
