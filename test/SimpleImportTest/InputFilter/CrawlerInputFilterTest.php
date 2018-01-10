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

namespace SimpleImportTest\Hydrator;

use CoreTestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorPluginManager;
use Zend\Validator\AbstractValidator;

/**
 * @coversDefaultClass \SimpleImport\InputFilter\CrawlerInputFilter
 */
class CrawlerInputFilterTest extends \PHPUnit_Framework_TestCase
{
    use TestInheritanceTrait;

    /**
     * @var CrawlerInputFilter
     */
    private $target = CrawlerInputFilter::class;

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [InputFilter::class];

    /**
     * {@inheritDoc}
     * @see \PHPUnit_Framework_TestCase::setUp()
     */
    protected function setUp()
    {
        $this->setupTargetInstance();

        $validatorMock = $this->getMockBuilder(AbstractValidator::class)
            ->getMock();

        $validatorPluginManager = $this->getMockBuilder(ValidatorPluginManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorPluginManager->method('get')
            ->will($this->returnValue($validatorMock));

        $this->target->getFactory()
            ->getDefaultValidatorChain()
            ->setPluginManager($validatorPluginManager);
    }

    /**
     * @covers ::init()
     */
    public function testHasInputFilters()
    {
        $this->target->init();

        $this->assertTrue($this->target->has('name'));
        $this->assertTrue($this->target->has('organization'));
        $this->assertTrue($this->target->has('feedUri'));
        $this->assertTrue($this->target->has('runDelay'));
        $this->assertTrue($this->target->has('type'));
        $this->assertTrue($this->target->has('options'));
    }

    /**
     * @covers ::init()
     */
    public function testOptionsFilter()
    {

        $this->target->init();

        $options = [
            'someKey' => 'someValue'
        ];
        $this->target->setData([
            'options' => array_merge($options, [
                'emptyValue' => null
            ])
        ]);
        $this->assertSame($options, $this->target->getValue('options'));
    }
}
