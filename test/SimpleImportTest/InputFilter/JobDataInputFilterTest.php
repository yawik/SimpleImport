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

use Cross\TestUtils\TestCase\ContainerDoubleTrait;
use Cross\TestUtils\TestCase\CreateProphecyTrait;
use PHPUnit\Framework\TestCase;

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use Prophecy\Argument;
use SimpleImport\InputFilter\JobDataInputFilter;
use Laminas\Filter\FilterPluginManager;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\AbstractValidator;

/**
 * @coversDefaultClass \SimpleImport\InputFilter\JobDataInputFilter
 */
class JobDataInputFilterTest extends TestCase
{
    use TestInheritanceTrait, SetupTargetTrait, ContainerDoubleTrait, CreateProphecyTrait;

    /**
     * @var JobDataInputFilter
     */
    private $target = [
        JobDataInputFilter::class,
        [
            'professions',
            'industries',
            'employmentTypes',
        ]
    ];

    /**
     * @see TestInheritanceTrait
     *
     * @var array
     */
    private $inheritance = [InputFilter::class];

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        $this->setupTarget();

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

        $filterMock = new \SimpleImport\Filter\MapClassificationsFilter(
            [
                'industries' => [
                    'test' => 'MAPPED',
                ],
            ]
        );

        $filterPluginManager = $this->createContainerDouble(
            [
                \SimpleImport\Filter\MapClassificationsFilter::class => $filterMock
            ],
            [
                'target' => FilterPluginManager::class
            ]
        );

        $this->target->getFactory()->getDefaultFilterChain()->setPluginManager($filterPluginManager);
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
        $target = new class extends JobDataInputFilter
        {
            public function __construct() {}
            public function initTest($classifications) {
                parent::__construct($classifications);
            }

        };

        $filterMock = new \SimpleImport\Filter\MapClassificationsFilter(
            [
                'industries' => [
                    'test' => 'MAPPED',
                ],
            ]
        );
        $filterMockFactory = new class ($filterMock) {
            private $filterMock;
            public function __construct($filterMock) {
                $this->filterMock = $filterMock;
            }
            public function __invoke($container, $name, $options)
            {
                return $this->filterMock;
            }
        };

        $filterPluginManager = $target->getFactory()->getDefaultFilterChain()->getPluginManager();
        $filterPluginManager->setFactory(\SimpleImport\Filter\MapClassificationsFilter::class, $filterMockFactory);

        $target->initTest(['industries']);

        $classifications = [
            'unknownClassification' => [
                'firstUnknown'
            ],
            'industries' => [
                'firstIndustry',
                'secondIndustry',
                'test',
                'Test',
                'tEst',
            ],
        ];
        $mappedExpected = [
            'firstIndustry',
            'secondIndustry',
            'MAPPED',
        ];
        $target->setData([
            'classifications' => $classifications
        ]);

        $filtered = $target->getValue('classifications');
        $this->assertArrayNotHasKey('unknownClassification', $filtered, 'Unknown classifications should be stripped off');
        $this->assertArrayHasKey('industries', $filtered, 'Filtered value should always contain known classifications');
        $this->assertSame($mappedExpected, $filtered['industries'], 'Filtered value should contain its passed value');
    }
}
