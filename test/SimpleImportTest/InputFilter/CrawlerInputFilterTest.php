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

use Cross\TestUtils\TestCase\SetupTargetTrait;
use Cross\TestUtils\TestCase\TestInheritanceTrait;
use SimpleImport\Entity\Crawler;
use SimpleImport\InputFilter\CrawlerInputFilter;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\ValidatorPluginManager;
use Laminas\Validator\AbstractValidator;

/**
 * @coversDefaultClass \SimpleImport\InputFilter\CrawlerInputFilter
 */
class CrawlerInputFilterTest extends TestCase
{
    use TestInheritanceTrait, SetupTargetTrait;

    /**
     * @var CrawlerInputFilter
     */
    private $target = [
        'create' => [
            ['for' => 'testInheritance', 'reflection' => CrawlerInputFilter::class ]
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

    public function initTarget()
    {
        return new class extends CrawlerInputFilter
        {
            public $addArgs = [];
            public function add($input, $name = null)
            {
                $this->addArgs[] = $input;
                return $this;
            }
        };
    }

    /**
     * @covers ::init()
     */
    public function testInitiatesCorrectInputs()
    {
        $expects =
                [
                    [
                       'name' => 'name',
                       'filters' => [
                           [
                               'name' => 'StringTrim'
                           ]
                       ]
                    ],
                    [
                        'name' => 'organization',
                        'filters' => [
                            [
                                'name' => \SimpleImport\Filter\IdToEntity::class,
                                'options' => [
                                    'document' => 'Organizations',
                                ],
                            ],
                        ],
                        'validators' => [
                            [
                                'name' => \SimpleImport\Validator\EntityExists::class,
                                'options' => [
                                    'entityClass' => \Organizations\Entity\Organization::class,
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'feedUri',
                        'filters' => [
                            [
                                'name' => 'StringTrim'
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'Uri',
                                'options' => [
                                    'allowRelative' => false
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'runDelay',
                        'required' => false,
                        'validators' => [
                            [
                                'name' => 'Digits',
                            ],
                            [
                                'name' => 'GreaterThan',
                                'options' => [
                                    'min' => 0,
                                    'inclusive' => true
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'type',
                        'filters' => [
                            [
                                'name' => 'StringTrim'
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => 'InArray',
                                'options' => [
                                    'haystack' => Crawler::validTypes()
                                ]
                            ]
                        ]
                    ],
                    [
                        'name' => 'options',
                        'required' => false,
                        'filters' => [
                            [
                                'name' => 'Callback',
                                'options' => [
                                    'callback' => 'array_filter'
                                ]
                            ]
                        ],
                        'validators' => [
                            [
                                'name' => \SimpleImport\Validator\CrawlerOptions::class,
                            ]
                        ]
                    ]
                ];


        $this->target->init();

        static::assertEquals($expects, $this->target->addArgs);
    }
}
