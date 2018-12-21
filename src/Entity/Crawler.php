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
use Core\Entity\MetaDataProviderInterface;
use Core\Entity\MetaDataProviderTrait;
use DateTime;
use InvalidArgumentException;
use LogicException;
use Doctrine\Common\Collections\Collection;
use Core\Entity\Collection\ArrayCollection;
use Organizations\Entity\Organization;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="simpleimport.crawler", repositoryClass="\SimpleImport\Repository\Crawler")
 * @ODM\HasLifecycleCallbacks
 */
class Crawler extends AbstractIdentifiableEntity implements CrawlerInterface, MetaDataProviderInterface
{
    use MetaDataProviderTrait;

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
     * @var Organization
     * @ODM\ReferenceOne(targetDocument="\Organizations\Entity\Organization", storeAs="id")
     */
    private $organization;
    
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
     * Number of minutes the next import run will be proceeded again
     * 
     * @var int
     * @ODM\Field(type="int")
     */
    private $runDelay;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateLastRun;
    
    /**
     * @var JobOptions|mixed
     * @ODM\EmbedOne
     */
    private $options;
    
    /**
     * @var Collection
     * @ODM\ReferenceMany(targetDocument="Item", mappedBy="crawler", cascade={"persist","remove"})
     */
    private $items;

    /**
     * @ODM\PrePersist
     * @ODM\PreUpdate
     */
    public function setItemsMetaData()
    {
        $documentIds = [];

        foreach ($this->items as $item) {
            $documentIds[] = $item->getDocumentId();
        }

        $this->setMetaData('documentIds', $documentIds);
    }

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
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param Organization $organization
     * @return Crawler
     */
    public function setOrganization(Organization $organization)
    {
        $this->organization = $organization;
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
        if (!in_array($type, self::validTypes())) {
            throw new InvalidArgumentException(sprintf('Invalid type: "%s"', $type));
        }
        
        $this->type = $type;
        return $this;
    }
    
    /**
     * @return array
     */
    public static function validTypes()
    {
        return [self::TYPE_JOB];
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
     * @return int
     */
    public function getRunDelay()
    {
        return $this->runDelay;
    }

    /**
     * @param int $runDelay
     * @return Crawler
     */
    public function setRunDelay($runDelay)
    {
        $this->runDelay = $runDelay;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateLastRun()
    {
        return $this->dateLastRun;
    }

    public function canRun()
    {
        $lastRunDate = $this->getDateLastRun();
        $lastRun = $lastRunDate->getTimestamp();
        $timezone = $lastRunDate->getTimezone();
        $now = (new \DateTime('now', $timezone))->getTimestamp();

        return $lastRun < $now - 60 * $this->getRunDelay();
    }

    /**
     * @return array|Item[]
     */
    public function getItems()
    {
        return $this->items()->toArray();
    }

    public function getItemsCollection()
    {
        return $this->items();
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

        $item->setCrawler($this);
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
     * @return JobOptions
     */
    public function getOptions()
    {
        if (!isset($this->options)) {
            if (!$this->type) {
                throw new LogicException('The options class cannot be resolved because the type is not set');
            }
            
            $map = [
                self::TYPE_JOB => JobOptions::class
            ];
            
            if (!isset($map[$this->type])) {
                throw new LogicException(sprintf('The options class resolving failed for the type: "%s"', $this->type));
            }
            
            $class = $map[$this->type];
            $this->options = new $class();
        }
        
        return $this->options;
    }
    
    /**
     * @param array $array
     * @throws InvalidArgumentException
     * @return Crawler
     */
    public function setOptionsFromArray(array $array)
    {
        $options = $this->getOptions();
        
        foreach ($array as $key => $value) {
            $setter = "set{$key}";
            
            if (!is_callable([$options, $setter])) {
                throw new InvalidArgumentException(sprintf('Invalid option key: "%s"', $key));
            }
            
            $options->$setter($value);
        }
        
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
