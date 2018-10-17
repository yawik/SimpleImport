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
use SimpleImport\Entity\Crawler;
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
    private $target = [
        CrawlerInputFilter::class,
        'args' => false,
        'mock' => ['add'],
    ];

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

    /**
     * @covers ::init()
     */
    public function testInitiatesCorrectInputs()
    {
        $this->target->expects($this->exactly(6))->method('add')
            ->withConsecutive(
                [[
                   'name' => 'name',
                   'filters' => [
                       [
                           'name' => 'StringTrim'
                       ]
                   ]
                ]],
                [[
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
                ]],
                [[
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
                ]],
                [[
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
                ]],
                [[
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
                ]],
                [[
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
                ]]
            )
            ->will($this->returnSelf());

        $this->target->init();


    }
}
