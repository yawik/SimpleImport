<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Entity;

use Core\Entity\AbstractIdentifiableEntity;
use DateTime;
use InvalidArgumentException;
use Doctrine\Common\Collections\Collection;
use Core\Entity\Collection\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="simpleimport.crawler", repositoryClass="\SimpleImport\Repository\Crawler")
 */
class Crawler extends AbstractIdentifiableEntity
{
    
    /**
     * @var string
     */
    const TYPE_JOB = 'job';
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $name;
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $type;
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $feedUri;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateLastRun;
    
    /**
     * @var Collection
     * @ODM\EmbedMany(targetDocument="Item", strategy="set")
     */
    private $items;
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Crawler
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Crawler
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getFeedUri()
    {
        return $this->feedUri;
    }

    /**
     * @param string $feedUri
     * @return Crawler
     */
    public function setFeedUri($feedUri)
    {
        $this->feedUri = $feedUri;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateLastRun()
    {
        return $this->dateLastRun;
    }

    /**
     * @return array|Item[]
     */
    public function getItems()
    {
        return $this->items()->toArray();
    }

    /**
     * @return array|Item[]
     */
    public function getItemsToSync()
    {
        return $this->items()->filter(function (Item $item)
        {
            return !$item->isSynced();
        })->toArray();
    }

    /**
     * @param string $importId
     * @return Item|null
     */
    public function getItem($importId)
    {
        $items = $this->items();
        
        return isset($items[$importId]) ? $items[$importId] : null;
    }
    
    /**
     * @param Item $item
     * @throws InvalidArgumentException
     */
    public function addItem(Item $item)
    {
        $importId = $item->getImportId();
        $items = $this->items();
      
        if (isset($items[$importId])) {
            throw new InvalidArgumentException(sprintf('Item with import ID "%s" already exists', $importId));
        }
        
        $items[$importId] = $item;
    }

    /**
     * @param DateTime $dateLastRun
     * @return Crawler
     */
    public function setDateLastRun(DateTime $dateLastRun)
    {
        $this->dateLastRun = $dateLastRun;
        return $this;
    }
    
    /**
     * @return Collection
     */
    private function items()
    {
        if (!isset($this->items)) {
            $this->items = new ArrayCollection();
        }
        
        return $this->items;
    }
}
