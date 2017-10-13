<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions
{

    /**
     * Default delay when to proceed another import run in minutes
     *
     * @var int
     */
    private $importRunDelay = 1440;

    /**
     * @return int
     */
    public function getImportRunDelay()
    {
        return $this->importRunDelay;
    }

    /**
     * @param int $importRunDelay
     * @return ModuleOptions
     */
    public function setImportRunDelay($importRunDelay)
    {
        $this->importRunDelay = $importRunDelay;
        return $this;
    }
}