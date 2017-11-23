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

use CoreTestUtils\TestCase\TestSetterGetterTrait;
use CoreTestUtils\TestCase\SetupTargetTrait;
use SimpleImport\Entity\Item;
use DateTime;

/**
 * @coversDefaultClass \SimpleImport\Entity\Item
 *
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    use TestSetterGetterTrait;
    use SetupTargetTrait;

    /**
     * @var array
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
}
