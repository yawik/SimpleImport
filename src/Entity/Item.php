<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Entity;

use DateTime;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\EmbeddedDocument
 */
class Item
{
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $importId;
    
    /**
     * @var array
     * @ODM\Hash
     */
    private $importData;
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $documentId;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateCreated;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateModified;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateDeleted;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateSynced;
    
    /**
     * @param string $importId
     * @param array $importData
     */
    public function __construct($importId, array $importData)
    {
        $this->importId = $importId;
        $this->importData = $importData;
        $this->dateCreated = $this->dateModified = new DateTime();
    }

    /**
     * @return string
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * @return array
     */
    public function getImportData()
    {
        return $this->importData;
    }

    /**
     * @param array $importData
     * @return Item
     */
    public function setImportData($importData)
    {
        $this->importData = $importData;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentId()
    {
        return $this->documentId;
    }

    /**
     * @param string $documentId
     * @return Item
     */
    public function setDocumentId($documentId)
    {
        $this->documentId = $documentId;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return DateTime
     */
    public function getDateModified()
    {
        return $this->dateModified;
    }

    /**
     * @param DateTime $dateModified
     * @return Item
     */
    public function setDateModified(DateTime $dateModified)
    {
        $this->dateModified = $dateModified;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateDeleted()
    {
        return $this->dateDeleted;
    }

    /**
     * @param DateTime $dateDeleted
     * @return Item
     */
    public function setDateDeleted(DateTime $dateDeleted = null)
    {
        $this->dateDeleted = $dateDeleted;
        return $this;
    }
    
    /**
     * @return DateTime
     */
    public function getDateSynced()
    {
        return $this->dateSynced;
    }

    /**
     * @param DateTime $dateSynced
     * @return Item
     */
    public function setDateSynced($dateSynced)
    {
        $this->dateSynced = $dateSynced;
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isSynced()
    {
        if (!$this->dateSynced) {
            return false;
        }
        
        if ($this->dateDeleted) {
            return $this->dateSynced >= $this->dateDeleted;
        }
        
        return $this->dateSynced >= $this->dateModified;
    }
}
