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
use Laminas\Log\LoggerInterface;

interface ProcessorInterface
{
    
    /**
     * @param Crawler $crawler
     * @param Result $result
     * @param LoggerInterface $logger
     */
    public function execute(Crawler $crawler, Result $result, LoggerInterface $logger);
}
