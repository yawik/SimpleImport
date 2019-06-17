<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */

namespace SimpleImportTest\Entity;

use Cross\TestUtils\TestCase\TestSetterAndGetterTrait;
use Cross\TestUtils\TestCase\SetupTargetTrait;
use SimpleImport\Entity\Item;
use DateTime;

/**
 * @coversDefaultClass \SimpleImport\Entity\Item
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    use TestSetterAndGetterTrait;
    use SetupTargetTrait;

    /**
     * @var Item
     */
    private $target = [Item::class, 'id1', []];

    /**
     * @return array
     */
    public function setterAndGetterData()
    {
        return [
            [ 'importId', [
                'value' => 'id1',
                'setter' => false,
            ]],
            [ 'importData', [
                'value' => ['key' => 'value'],
            ]],
            [ 'documentId', 'documentId1'],
            [ 'dateCreated', [
                'value_object' => 'DateTime',
                'assert' => function($g, $return) {
                    static::assertInstanceOf(DateTime::class, $return);
                },
                'setter' => false,
            ]],
            [ 'dateModified', ['value_object' => 'DateTime'] ],
            [ 'dateDeleted', ['value_object' => 'DateTime'] ],
            [ 'dateSynced', ['value_object' => 'DateTime'] ],
        ];
    }

    /**
     * @covers ::isSynced()
     */
    public function testIsSynced()
    {
        $this->assertFalse($this->target->isSynced(), 'False should be returned if dateSynced is not set');

        $this->target->setDateSynced(new DateTime('-1 hour'));
        $this->target->setDateModified(new DateTime());
        $this->target->setDateDeleted(new DateTime());
        $this->assertFalse($this->target->isSynced(), 'False should be returned if dateSynced is less than dateDeleted');

        $this->target->setDateSynced(new DateTime('+1 hour'));
        $this->target->setDateModified(new DateTime());
        $this->target->setDateDeleted(new DateTime());
        $this->assertTrue($this->target->isSynced(), 'True should be returned if dateSynced is greater than or equals dateDeleted');

        $this->target->setDateSynced(new DateTime('-1 hour'));
        $this->target->setDateModified(new DateTime());
        $this->target->setDateDeleted(null);
        $this->assertFalse($this->target->isSynced(), 'False should be returned if dateSynced is less than dateModified');

        $this->target->setDateSynced(new DateTime('+1 hour'));
        $this->target->setDateModified(new DateTime());
        $this->target->setDateDeleted(null);
        $this->assertTrue($this->target->isSynced(), 'True should be returned if dateSynced is greater than or equals dateModified');
    }
}
