<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Factory;

use PHPUnit\Framework\TestCase;

use SimpleImport\Factory\ProgressBarFactory;
use Core\Console\ProgressBar;

/**
 * @coversDefaultClass \SimpleImport\Factory\ProgressBarFactory
 */
class ProgressBarFactoryTest extends TestCase
{

    /**
     * @covers ::factory
     */
    public function testInvoke()
    {
        $progressBar = (new ProgressBarFactory())->factory(123, 'preventOutput');
        $this->assertInstanceOf(ProgressBar::class, $progressBar);
    }
}
