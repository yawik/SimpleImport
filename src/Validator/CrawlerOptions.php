<?php
/**
 * @filesource
 * @copyright (c) 2013 - 2017 Cross Solution (http://cross-solution.de)
 * @license MIT
 * @author Miroslav Fedeleš <miroslav.fedeles@gmail.com>
 * @since 0.30
 */
namespace SimpleImport\Validator;

use Zend\Validator\AbstractValidator;
use LogicException;
use SimpleImport\Entity\Crawler;
use Jobs\Entity\Status;

class CrawlerOptions extends AbstractValidator
{
    
    /**
     * @var string
     */
    const INVALID_INITIAL_STATE = 'invalidInitialState';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_INITIAL_STATE => "Invalid initial state. Possible values are: %validStates%.",
    ];
    
    /**
     * @var array
     */
    protected $messageVariables = [
        'validStates' => 'validStates',
    ];
    
    /**
     * @var string
     */
    protected $validStates;
    
    /**
     * {@inheritDoc}
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);
        
        if (!isset($context['type'])) {
            throw new LogicException('There is no type key in the context');
        }
        
        switch ($context['type']) {
            case Crawler::TYPE_JOB:
                if (isset($value['initialState'])) {
                    $states = Status::getStates();
                    if (!in_array($value['initialState'], $states)) {
                        $this->validStates = implode(', ', $states);
                        $this->error(self::INVALID_INITIAL_STATE);
                        return false;
                    }
                }
            break;
        }
        
        return true;
    }
}