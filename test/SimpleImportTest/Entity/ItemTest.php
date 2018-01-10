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

use CoreTestUtils\TestCase\TestSetterGetterTrait;
use CoreTestUtils\TestCase\SetupTargetTrait;
use SimpleImport\Entity\Item;
use DateTime;

/**
 * @coversDefaultClass \SimpleImport\Entity\Item
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    use TestSetterGetterTrait;
    use SetupTargetTrait;

    /**
     * @var Item
     */
    private $target = [Item::class, ['id1', []]];

    /**
     * @return array
     */
    public function propertiesProvider()
    {
        return [
            [ 'importId', [
                'value' => 'id1',
                'ignore_setter' => true,
            ]],
            [ 'importData', [
                'value' => ['key' => 'value'],
            ]],
            [ 'documentId', 'documentId1'],
            [ 'dateCreated', [
                'value' => '@DateTime',
                'getter_assert' => function($g, $return) {
                    $this->assertInstanceOf(DateTime::class, $return);
                },
                'ignore_setter' => true,
            ]],
            [ 'dateModified', '@DateTime' ],
            [ 'dateDeleted', '@DateTime' ],
            [ 'dateSynced', '@DateTime' ],
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
