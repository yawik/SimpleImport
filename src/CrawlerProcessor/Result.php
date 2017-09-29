<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\CrawlerProcessor;

class Result
{
    
    /**
     * @var int
     */
    private $numberOfInserted;
    
    /**
     * @var int
     */
    private $numberOfUpdated;
    
    /**
     * @var int
     */
    private $numberOfRemoved;
    
    /**
     * @param int $numberOfInserted
     * @param int $numberOfUpdated
     * @param int $numberOfRemoved
     */
    public function __construct($numberOfInserted, $numberOfUpdated, $numberOfRemoved)
    {
        $this->numberOfInserted = $numberOfInserted;
        $this->numberOfUpdated = $numberOfUpdated;
        $this->numberOfRemoved = $numberOfRemoved;
    }
    
    /**
     * @return int
     */
    public function getNumberOfInserted()
    {
        return $this->numberOfInserted;
    }

    /**
     * @return int
     */
    public function getNumberOfUpdated()
    {
        return $this->numberOfUpdated;
    }

    /**
     * @return int
     */
    public function getNumberOfRemoved()
    {
        return $this->numberOfRemoved;
    }
}
