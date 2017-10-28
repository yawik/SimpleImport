<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\CrawlerProcessor;

use SimpleImport\Factory\ProgressBarFactory;
use Core\Console\ProgressBar;

class Result
{
    
    /**
     * @var int
     */
    private $toProcess;
    
    /**
     * @var int
     */
    private $inserted;
    
    /**
     * @var int
     */
    private $updated;
    
    /**
     * @var int
     */
    private $deleted;
    
    /**
     * @var int
     */
    private $invalid;
    
    /**
     * @var int
     */
    private $unchanged;
    
    /**
     * @var ProgressBarFactory
     */
    private $progressBarFactory;
    
    /**
     * @var ProgressBar
     */
    private $progressBar;
    
    /**
     * @param ProgressBarFactory $progressBarFactory
     */
    public function __construct(ProgressBarFactory $progressBarFactory)
    {
        $this->progressBarFactory = $progressBarFactory;
        $this->toProcess = 0;
        $this->inserted = 0;
        $this->updated = 0;
        $this->deleted = 0;
        $this->invalid = 0;
        $this->unchanged = 0;
    }
    
    /**
     * @return int
     */
    public function getToProcess()
    {
        return $this->toProcess;
    }

    /**
     * @param number $toProcess
     * @return Result
     */
    public function setToProcess($toProcess)
    {
        $this->toProcess = $toProcess;
        $this->progressBar = $this->progressBarFactory->factory($toProcess);
        return $this;
    }

    /**
     * @return int
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementInserted($increment = 1)
    {
        $this->inserted += $increment;
        return $this->updateProgressBar($increment);
    }

    /**
     * @return int
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementUpdated($increment = 1)
    {
        $this->updated += $increment;
        return $this->updateProgressBar($increment);
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementDeleted($increment = 1)
    {
        $this->deleted += $increment;
        return $this->updateProgressBar($increment);
    }

    /**
     * @return int
     */
    public function getInvalid()
    {
        return $this->invalid;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementInvalid($increment = 1)
    {
        $this->invalid += $increment;
        return $this->updateProgressBar($increment);
    }
    
    /**
     * @return int
     */
    public function getUnchanged()
    {
        return $this->unchanged;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementUnchanged($increment = 1)
    {
        $this->unchanged += $increment;
        return $this->updateProgressBar($increment);
    }
    
    /**
     * @param int $increment
     * @return Result
     */
    private function updateProgressBar($increment)
    {
        if (isset($this->progressBar)) {
            $this->progressBar->next($increment);
        }
        
        return $this;
    }
}
