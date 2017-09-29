<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\CrawlerProcessor;

use Zend\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{

	/**
	 * @var bool
	 */
	protected $autoAddInvokableClass = false;

	/**
	 * @var string
	 */
	protected $instanceOf = ProcessorInterface::class;
}