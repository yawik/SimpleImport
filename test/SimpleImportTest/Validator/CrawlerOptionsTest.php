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

namespace SimpleImportTest\Validator;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestDefaultAttributesTrait;
use Jobs\Entity\StatusInterface;
use SimpleImport\Validator\CrawlerOptions;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Zend\Validator\AbstractValidator;
use SimpleImport\Entity\Crawler;
use Jobs\Entity\Status;

/**
 * @coversDefaultClass \SimpleImport\Validator\CrawlerOptions
 */
class CrawlerOptionsTest extends \PHPUnit_Framework_TestCase
{

    use TestInheritanceTrait, TestDefaultAttributesTrait, SetupTargetTrait;

    /**
     * @var CrawlerOptions
     */
    private $target = CrawlerOptions::class;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [AbstractValidator::class];

    /**
     * @see TestDefaultAttributesTrait
     * @var array
     */
    private $defaultAttributes = [
        'messageTemplates' => [
            CrawlerOptions::INVALID_INITIAL_STATE => "Invalid initial state. Possible values are: %validStates%.",
            CrawlerOptions::INVALID_RECOVER_STATE => "Invalid recover state. Possible values are: %validStates%.",
        ],
        'messageVariables' => [ 'validStates' => 'validStates' ],
    ];

    /**
     * @covers ::isValid()
     * @expectedException LogicException
     * @expectedExceptionMessage There is no type key in the context
     */
    public function testIsValidWithInvalidContext()
    {
        $this->target->isValid('some value');
    }

    /**
     * @covers ::isValid()
     */
    public function testIsValidWithUnknownType()
    {
        $isValid = $this->target->isValid('some value', ['type' => 'unkn0wn']);
        $this->assertTrue($isValid);
    }

    public function provideIsValidTestData()
    {
        return [
            [['initialState' => 'invalid'], false, [CrawlerOptions::INVALID_INITIAL_STATE]],
            [['initialState' => StatusInterface::ACTIVE], true],
            [['recoverState' => 'invalid'], false, [CrawlerOptions::INVALID_RECOVER_STATE]],
            [['recoverState' => StatusInterface::ACTIVE], true],
            [['initialState' => StatusInterface::ACTIVE, 'recoverState' => 'invalid'], false, [CrawlerOptions::INVALID_RECOVER_STATE]],
            [['initialState' => 'invalid', 'recoverState' => StatusInterface::ACTIVE], false, [CrawlerOptions::INVALID_INITIAL_STATE]],
            [['initialState' => 'invalid', 'recoverState' => 'invalid'], false, [CrawlerOptions::INVALID_INITIAL_STATE, CrawlerOptions::INVALID_RECOVER_STATE]],
            [['initialState' => StatusInterface::ACTIVE, 'recoverState' => StatusInterface::ACTIVE], true],
        ];
    }

    /**
     * @dataProvider provideIsValidTestData
     *
     * @param      $value
     * @param      $expect
     * @param null $messageKeys
     */
    public function testIsValid($value, $expect, $messageKeys = null)
    {
        $actual = $this->target->isValid($value, ['type' => Crawler::TYPE_JOB]);

        $this->assertEquals($actual, $expect, CrawlerOptions::class . '::isValid returns wrong boolean value.');

        if ($messageKeys) {
            $messages = $this->target->getMessages();
            foreach ($messageKeys as $key) {
                $this->assertArrayHasKey($key, $messages);
            }
        }
    }
}
