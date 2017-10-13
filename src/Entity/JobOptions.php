<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Jobs\Entity\StatusInterface as JobStatusInterface;

/**
 * @ODM\EmbeddedDocument
 */
class JobOptions
{
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $initialState;
    
    public function __construct()
    {
        $this->initialState = JobStatusInterface::ACTIVE;
    }
    
    /**
     * @return string
     */
    public function getInitialState()
    {
        return $this->initialState;
    }

    /**
     * @param string $initialState
     * @return JobOptions
     */
    public function setInitialState($initialState)
    {
        $this->initialState = $initialState;
        return $this;
    }


}
