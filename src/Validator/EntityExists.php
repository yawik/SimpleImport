<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */

/** */
namespace SimpleImport\Validator;

use Zend\Validator\AbstractValidator;
use Organizations\Repository\Organization as OrganizationRepository;

/**
 * Validates, if the value is an object of a specific type.
 *
 * This validator is meant to be used with {@link \SimpleImport\Filter\IdToEntity} in an InputFilter.
 * It does not make much sense outside of this context.
 *
 * The filter loads an entity or returns the given value (which should be an entity id).
 * So, if anything else than an object of the set type is given to this validator, it assumes
 * that the entity could not be loaded and thus, does not exist.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class EntityExists extends AbstractValidator
{
    
    /**
     * @var string
     */
    const NOT_EXIST = 'notExist';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_EXIST => "%entityClass% with ID '%value%' does not exist",
    ];

    /**
     * @var array
     */
    protected $messageVariables = [
        'entityClass' => 'entityClass',
    ];

    /**
     * @var string
     */
    protected $entityClass;
    
    /**
     * @param OrganizationRepository $repository
     */
    public function __construct($entityOrOptions = null)
    {
        if (null !== $entityOrOptions && !is_array($entityOrOptions)) {
            $entityOrOptions = [ 'entityClass' => $entityOrOptions ];
        }

        parent::__construct($entityOrOptions);
    }

    /**
     * @param string|object $entityClass
     *
     * @return self
     * @throws \InvalidArgumentException if anything else than a string or an object is passed.
     */
    public function setEntityClass($entityClass)
    {
        if (is_object($entityClass)) {
            $entityClass = get_class($entityClass);
        }

        if (!is_string($entityClass)) {
            throw new \InvalidArgumentException('Entity class must be given as string or object');
        }

        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @param object|mixed $value
     *
     * @returns bool
     * @throws \RuntimeException if no entity class is set prior to the call.
     */
    public function isValid($value)
    {
        if (!$this->entityClass) {
            throw new \RuntimeException('An entity class must be set prior to use this validator.');
        }

        if (!$value instanceOf $this->entityClass) {
            if (is_object($value)) {
                $value = method_exists($value, '__toString') ? (string) $value : '[object]';
            }
            $this->setValue($value);
            $this->error(self::NOT_EXIST);

            return false;
        }
        
        return true;
    }
}
