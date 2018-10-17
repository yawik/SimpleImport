<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2018 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace SimpleImport\Filter;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Zend\Filter\FilterInterface;

/**
 * Filter to load an entity by its id.
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class IdToEntity implements FilterInterface
{
    /**
     * @var DocumentRepository
     */
    private $repository;

    /**
     * Custom value to return if no entity is found.
     *
     * @var mixed
     */
    private $notFoundValue;

    /**
     * IdToEntity constructor.
     *
     * @param DocumentRepository $repository
     */
    public function __construct(DocumentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Allows direct invokation.
     *
     * Proxies to {@łink filter()}
     *
     * @param mixed $value
     *
     * @return mixed|null
     * @see filter
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * @param mixed $notFoundValue
     *
     * @return self
     */
    public function setNotFoundValue($notFoundValue)
    {
        $this->notFoundValue = $notFoundValue;

        return $this;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    private function getNotFoundValue($value)
    {
        return $this->notFoundValue ?: $value;
    }

    /**
     * Filters an id to an entity instance.
     *
     * If empty($value) is true, returns null.
     *
     * The id can be given as a string or as an instance of \MongoId.
     * If an entity is passed, and it is of the type managed by {@link repository}, it is
     * returned as is.
     *
     * If no entity with the given Id is found, the {@link notFoundValue} is returned
     * which defaults to $value
     *
     * If $value is not an entity that {@link repository} manages, and $value is not a string
     * or an instance of \MongoId, an exception is thrown.
     *
     * @param mixed $value
     *
     * @return mixed|null|object
     * @throws \InvalidArgumentException
     */
    public function filter($value)
    {
        if (empty($value)) { return null; }

        if (is_string($value) || $value instanceOf \MongoId) {
            return $this->repository->find($value) ?: $this->getNotFoundValue($value);
        }

        if (!is_a($value, $this->repository->getDocumentName())) {
            throw new \InvalidArgumentException(sprintf(
                'Value must either be a string or an instance of \MongoId or %s',
                $this->repository->getDocumentName())
            );
        }

        return $value;
    }

}
