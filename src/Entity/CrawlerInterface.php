<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Entity;

use Core\Entity\Collection\ArrayCollection;
use Organizations\Entity\Organization;
use DateTime;

/**
 * @ODM\Document(collection="simpleimport.crawler", repositoryClass="\SimpleImport\Repository\Crawler")
 */
interface CrawlerInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return Crawler
     */
    public function setName($name);
    
    /**
     * @return Organization
     */
    public function getOrganization();

    /**
     * @param Organization $organization
     * @return Crawler
     */
    public function setOrganization(Organization $organization);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return Crawler
     */
    public function setType($type);
    
    /**
     * @return array
     */
    public static function validTypes();

    /**
     * @return string
     */
    public function getFeedUri();

    /**
     * @param string $feedUri
     * @return Crawler
     */
    public function setFeedUri($feedUri);

    /**
     * @return int
     */
    public function getRunDelay();

    /**
     * @param int $runDelay
     * @return Crawler
     */
    public function setRunDelay($runDelay);

    /**
     * @return DateTime
     */
    public function getDateLastRun();

    /**
     * @return array|Item[]
     */
    public function getItems();

    /**
     * @return array|Item[]
     */
    public function getItemsToSync();

    /**
     * @param string $importId
     * @return Item|null
     */
    public function getItem($importId);
    
    /**
     * @param Item $item
     * @throws InvalidArgumentException
     */
    public function addItem(Item $item);

    /**
     * @param DateTime $dateLastRun
     * @return Crawler
     */
    public function setDateLastRun(DateTime $dateLastRun);
    
    /**
     * @return JobOptions
     */
    public function getOptions();
    
    /**
     * @param array $array
     * @throws InvalidArgumentException
     * @return Crawler
     */
    public function setOptionsFromArray(array $array);

}
