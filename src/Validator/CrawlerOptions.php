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
     * @var string
     */
    const INVALID_RECOVER_STATE = 'invalidRecoverState';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_INITIAL_STATE => "Invalid initial state. Possible values are: %validStates%.",
        self::INVALID_RECOVER_STATE => "Invalid recover state. Possible values are: %validStates%.",
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

        $isValid = true;
        
        switch ($context['type']) {
            case Crawler::TYPE_JOB:
                $states = Status::getStates();
                $this->validStates = join(', ', $states);

                if (isset($value['initialState']) && !in_array($value['initialState'], $states)) {
                   $this->error(self::INVALID_INITIAL_STATE);
                   $isValid = false;
                }
                if (isset($value['recoverState']) && !in_array($value['recoverState'], $states)) {
                    $this->error(self::INVALID_RECOVER_STATE);
                    $isValid = false;
                }

            break;
        }
        
        return $isValid;
    }
}
