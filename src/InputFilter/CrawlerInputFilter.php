<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\InputFilter;

use Zend\InputFilter\InputFilter;
use SimpleImport\Entity\Crawler;
use SimpleImport\Validator\CrawlerOptions;

class CrawlerInputFilter extends InputFilter
{

    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\BaseInputFilter::init()
     */
    public function init()
    {
        $this->add([
            'name' => 'name',
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'organization',
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ],
            'validators' => [
                [
                    'name' => 'SimpleImportOrganizationExists',
                ]
            ]
        ])->add([
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
        ])->add([
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
        ])->add([
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
        ])->add([
            'name' => 'options',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Callback',
                    'options' => [
                        'callback' => function ($array)
                        {
                            return array_filter((array)$array, function ($value)
                            {
                                return !is_null($value);
                            });
                        }
                    ]
                ]
            ],
            'validators' => [
                [
                    'name' => CrawlerOptions::class,
                ]
            ]
        ]);
    }
}