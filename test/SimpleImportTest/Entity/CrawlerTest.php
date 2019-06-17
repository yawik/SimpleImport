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

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use SimpleImport\Entity\Crawler;
use SimpleImport\Entity\Item;
use SimpleImport\Entity\JobOptions;
use InvalidArgumentException;
use DateTime;
use ReflectionClass;

/**
 * @coversDefaultClass \SimpleImport\Entity\Crawler
 *
 * @author Carsten Bleek <bleek@cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 */
class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait, TestSetterAndGetterTrait, SetupTargetTrait;

    /**
     * The "Class under Test"
     *
     * @var Crawler
     */
    private $target = Crawler::class;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [ 'SimpleImport\Entity\CrawlerInterface' ];

    /**
     * @see TestSetterGetterTrait
     *
     * @var array
     */
    private $setterAndGetter = [
        [ 'name', 'example-crawler' ],
        [ 'organization', ['value_object' => 'Organizations\Entity\Organization'] ],
        [ 'type', 'job' ],
        [ 'feedUri', 'http://ftp.yawik.org/example.json' ],
        [ 'runDelay', 10 ],
        [ 'DateLastRun', ['value_object' => 'DateTime'] ],
    ];

    /**
     * @covers ::setType()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid type
     */
    public function testSetTypeInvalid()
    {
        $this->target->setType('inV4lid');
    }

    /**
     * @covers ::getItems()
     * @covers ::addItem()
     * @covers ::items()
     */
    public function testGetItems()
    {
        $this->assertSame([], $this->target->getItems());

        $importId = 'importId1';
        $item = new Item($importId, []);
        $this->target->addItem($item);
        $this->assertSame([$item], $this->target->getItems());
    }

    /**
     * @covers ::getItemsToSync()
     * @covers ::addItem()
     * @covers ::items()
     */
    public function testGetItemsToSync()
    {
        $this->assertSame([], $this->target->getItemsToSync());

        $nonSyncedImportId = 'importId1';
        $nonSyncedItem = new Item($nonSyncedImportId, []);
        $this->target->addItem($nonSyncedItem);
        $this->assertSame([$nonSyncedItem], $this->target->getItemsToSync());

        $syncedImportId = 'importId2';
        $syncedItem = new Item($syncedImportId, []);
        $syncedItem->setDateSynced(new DateTime());
        $this->target->addItem($syncedItem);
        $this->assertSame([$nonSyncedItem], $this->target->getItemsToSync(),
            'Synchronized items may not be returned');
    }

    /**
     * @covers ::addItem()
     * @covers ::getItem()
     * @covers ::items()
     */
    public function testAddItem()
    {
        $importId = 'importId1';
        $item = new Item($importId, []);
        $this->target->addItem($item);
        $this->assertSame($item, $this->target->getItem($importId));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already exists');
        $this->target->addItem($item);
    }

    /**
     * @covers ::getOptions()
     * @expectedException LogicException
     * @expectedExceptionMessage The options class cannot be resolved
     */
    public function testGetOptionsWithoutTypeSet()
    {
        $this->target->getOptions();
    }

    /**
     * @covers ::getOptions()
     * @expectedException LogicException
     * @expectedExceptionMessage The options class resolving failed
     */
    public function testGetOptionsWithInvalidTypeSet()
    {
        $reflectionClass = new ReflectionClass($this->target);
        $reflectionProperty = $reflectionClass->getProperty('type');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->target, 'inv4lid');

        $this->target->getOptions();
    }

    /**
     * @covers ::getOptions()
     */
    public function testGetOptionsWithValidTypeSet()
    {
        $this->target->setType(Crawler::TYPE_JOB);
        $options = $this->target->getOptions();

        $this->assertInstanceOf(JobOptions::class, $options);
        $this->assertSame($options, $this->target->getOptions(), 'Repetitive calls should return the same instance');
    }

    /**
     * @covers ::setOptionsFromArray()
     */
    public function testSetOptionsFromArray()
    {
        $this->target->setType(Crawler::TYPE_JOB);
        $optionsArray = ['initialState' => 'stateValue'];

        $this->target->setOptionsFromArray($optionsArray);
        $options = $this->target->getOptions();
        $this->assertSame($optionsArray['initialState'], $options->getInitialState());
    }

    /**
     * @covers ::setOptionsFromArray()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid option key
     */
    public function testSetOptionsFromArrayWithInvalidKey()
    {
        $this->target->setType(Crawler::TYPE_JOB);
        $this->target->setOptionsFromArray(['inv4lid' => 'someValue']);
    }

    public function testCanRun()
    {
        $this->target->setRunDelay(10000);
        $this->target->setDateLastRun(new \DateTime());

        $this->assertFalse($this->target->canRun());

        $this->target->setRunDelay(100);
        $this->target->setDateLastRun(new \DateTime('yesterday'));

        $this->assertTrue($this->target->canRun());
    }
}
