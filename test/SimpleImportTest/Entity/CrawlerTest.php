<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImportTest\Entity;

use CoreTestUtils\TestCase\TestInheritanceTrait;
use CoreTestUtils\TestCase\TestUsesTraitsTrait;
use CoreTestUtils\TestCase\TestSetterGetterTrait;
use SimpleImport\Entity\Crawler;
use Organization\Entity\Organization;

/**
 * Tests for Item
 *
 * @covers \SimpleImport\Entity\Item
 * @coversDefaultClass \SimpleImport\Entity\Item
 *
 * @author Carsten Bleek <bleek@cross-solution.de>
 * @group  Orders
 * @group  Orders.Entity
 */
class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait, TestSetterGetterTrait;//, TestUsesTraitsTrait;

    /**
     * The "Class under Test"
     *
     * @var Item
     */
    private $target = Crawler::class;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [ 'SimpleImport\Entity\CrawlerInterface' ];

    /**
     * @see TestUsesTraitsTrait
     *
     * @var array
     */
    //private $traits = [ 'Core\Entity\EntityTrait' ];

    /**
     * @see TestSetterGetterTrait
     *
     * @var array
     */
    private $properties = [
        [ 'name', 'example-crawler' ],
        [ 'organization', '@Organizations\Entity\Organization' ],
        [ 'type', 'job' ],
        [ 'feedUri', 'http://ftp.yawik.org/example.json' ],
        [ 'runDelay', 10 ],
        [ 'DateLastRun', '@DateTime' ],
    ];
}
