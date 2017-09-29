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
use Core\Entity\ModificationDateAwareEntityTrait;

/**
 * @ODM\EmbeddedDocument
 */
class Item
{
    
    use ModificationDateAwareEntityTrait;
    
    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $id;
    
    /**
     * @var array
     * @ODM\Hash
     */
    private $data;
    
    /**
     * @var DateTime
     * @ODM\Field(type="tz_date")
     */
    private $dateDeleted;
    
    /**
     * @param string $id
     * @param array $data
     */
    public function __construct($id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return Item
     */
    public function setData($data)
    {
        $this->data = $data;
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
    public function setDateDeleted(DateTime $dateDeleted)
    {
        $this->dateDeleted = $dateDeleted;
        return $this;
    }
}
