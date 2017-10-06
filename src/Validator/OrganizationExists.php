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
use Organizations\Repository\Organization as OrganizationRepository;

class OrganizationExists extends AbstractValidator
{
    
    /**
     * @var string
     */
    const NOT_EXIST = 'notExist';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_EXIST => "Organization with ID '%value%' does not exist",
    ];
    
    /**
     * @var OrganizationRepository
     */
    private $repository;
    
    /**
     * @param OrganizationRepository $repository
     */
    public function __construct(OrganizationRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
    
    /**
     * {@inheritDoc}
     * @see \Zend\Validator\ValidatorInterface::isValid()
     */
    public function isValid($value)
    {
        $this->setValue($value);
        
        if (!$this->repository->find($value)) {
            $this->error(self::NOT_EXIST);
            return false;
        }
        
        return true;
    }
}