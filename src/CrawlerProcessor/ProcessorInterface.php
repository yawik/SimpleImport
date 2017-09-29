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

interface ProcessorInterface
{
    
    /**
     * @param Crawler $crawler
     * @return Result
     */
    public function execute(Crawler $crawler);
}
