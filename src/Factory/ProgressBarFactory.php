<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Factory;

use Core\Console\ProgressBar;

class ProgressBarFactory
{
    /**
     * @param int $count
     * @return \Zend\ProgressBar\ProgressBar
     */
    public function factory($count)
    {
        return new ProgressBar($count);
    }
}