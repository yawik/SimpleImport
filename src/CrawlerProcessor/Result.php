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
    
    public function __construct()
    {
        $this->inserted = 0;
        $this->updated = 0;
        $this->deleted = 0;
        $this->invalid = 0;
    }
    
    /**
     * @return int
     */
    public function getInserted()
    {
        return $this->inserted;
    }

    /**
     * @param int $inserted
     * @return Result
     */
    public function setInserted($inserted)
    {
        $this->inserted = $inserted;
        return $this;
    }

    /**
     * @param int $increment
     * @return Result
     */
    public function incrementInserted($increment = 1)
    {
        $this->inserted += $increment;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param int $updated
     * @return Result
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }
    
    /**
     * @param int $increment
     * @return Result
     */
    public function incrementUpdated($increment = 1)
    {
        $this->updated += $increment;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return Result
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }
    
    /**
     * @param int $increment
     * @return Result
     */
    public function incrementDeleted($increment = 1)
    {
        $this->deleted += $increment;
        return $this;
    }

    /**
     * @return int
     */
    public function getInvalid()
    {
        return $this->invalid;
    }

    /**
     * @param int $invalid
     * @return Result
     */
    public function setInvalid($invalid)
    {
        $this->invalid = $invalid;
        return $this;
    }
    
    /**
     * @param int $increment
     * @return Result
     */
    public function incrementInvalid($increment = 1)
    {
        $this->invalid += $increment;
        return $this;
    }
}
