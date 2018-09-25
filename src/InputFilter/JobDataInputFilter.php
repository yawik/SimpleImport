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
use Zend\InputFilter\OptionalInputFilter;

class JobDataInputFilter extends InputFilter
{

    /**
     * @param array $availableClassifications
     */
    public function __construct(array $availableClassifications)
    {
        $this->add([
            'name' => 'id',
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'title',
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'location',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'company',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'reference',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'contactEmail',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ],
            'validators' => [
                [
                    'name' => 'EmailAddress'
                ]
            ]
        ])->add([
            'name' => 'language',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ]
        ])->add([
            'name' => 'link',
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
            'name' => 'datePublishStart',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ],
            'validators' => [
                [
                    'name' => 'Date',
                    'options' => [
                        'format' => 'd.m.Y'
                    ]
                ]
            ]
        ])->add([
            'name' => 'datePublishEnd',
            'required' => false,
            'filters' => [
                [
                    'name' => 'StringTrim'
                ]
            ],
            'validators' => [
                [
                    'name' => 'Date',
                    'options' => [
                        'format' => 'd.m.Y'
                    ]
                ]
            ]
        ])->add([
            'name' => 'logoRef',
            'required' => false,
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
            'name' => 'linkApply',
            'required' => false,
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
            'name' => 'classifications',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Callback',
                    'options' => [
                        'callback' => function ($classifications) use ($availableClassifications)
                        {
                            $return = [];
                            
                            foreach ($availableClassifications as $availableClassification) {
                                $return[$availableClassification] = isset($classifications[$availableClassification])
                                    ? (array)$classifications[$availableClassification]
                                    : [];
                            }
                            
                            return $return;
                        }
                    ]
                ]
            ]
        ])->add([
            'type' => OptionalInputFilter::class,
            'html' => [
                'required' => false,
                'allowEmpty' => false,
                'filters' => [ [ 'name' => 'StringTrim' ] ],
            ],
            'requirements' => [
                'required' => false,
                'allowEmpty' => false,
                'filters' => [ [ 'name' => 'StringTrim' ] ],
            ],
            'benefits' => [
                'required' => false,
                'allowEmpty' => false,
                'filters' => [ [ 'name' => 'StringTrim' ] ],
            ],
            'tasks' => [
                'required' => false,
                'allowEmpty' => false,
                'filters' => [ [ 'name' => 'StringTrim' ] ],
            ],
        ], 'templateValues');
    }
}
