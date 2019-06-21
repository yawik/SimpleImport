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

use PHPUnit\Framework\TestCase;

use CoreTestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\InputFilter\JobDataInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\ValidatorPluginManager;
use Zend\Validator\AbstractValidator;

/**
 * @coversDefaultClass \SimpleImport\InputFilter\JobDataInputFilter
 */
class JobDataInputFilterTest extends TestCase
{
    use TestInheritanceTrait;

    /**
     * @var JobDataInputFilter
     */
    private $target = [JobDataInputFilter::class, [[
        'professions',
        'industries',
        'employmentTypes',
    ]]];

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [InputFilter::class];

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
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
     * @covers ::__construct()
     */
    public function testHasInputFilters()
    {
        $this->assertTrue($this->target->has('id'));
        $this->assertTrue($this->target->has('title'));
        $this->assertTrue($this->target->has('location'));
        $this->assertTrue($this->target->has('company'));
        $this->assertTrue($this->target->has('reference'));
        $this->assertTrue($this->target->has('contactEmail'));
        $this->assertTrue($this->target->has('language'));
        $this->assertTrue($this->target->has('link'));
        $this->assertTrue($this->target->has('datePublishStart'));
        $this->assertTrue($this->target->has('datePublishEnd'));
        $this->assertTrue($this->target->has('logoRef'));
        $this->assertTrue($this->target->has('linkApply'));
        $this->assertTrue($this->target->has('classifications'));
    }

    /**
     * @covers ::__construct()
     */
    public function testClassificationsFilter()
    {
        $classifications = [
            'unknownClassification' => [
                'firstUnknown'
            ],
            'industries' => [
                'firstIndustry',
                'secondIndustry',
            ],
        ];
        $this->target->setData([
            'classifications' => $classifications
        ]);

        $filtered = $this->target->getValue('classifications');
        $this->assertArrayNotHasKey('unknownClassification', $filtered, 'Unknown classifications should be stripped off');
        $this->assertArrayHasKey('professions', $filtered, 'Filtered value should always contain known classifications');
        $this->assertArrayHasKey('employmentTypes', $filtered, 'Filtered value should always contain known classifications');
        $this->assertArrayHasKey('industries', $filtered, 'Filtered value should always contain known classifications');
        $this->assertSame($classifications['industries'], $filtered['industries'], 'Filtered value should contain its passed value');
    }
}
