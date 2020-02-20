<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Options;

use OutOfBoundsException;
use Laminas\Stdlib\AbstractOptions;
use function array_change_key_case;
use function in_array;

/**
 * Provides classifications mapping.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class MapClassificationsOptions extends AbstractOptions
{
    /**
     * Map for professions.
     * ```
     * [ 'crawled name' => 'mapped name' ]
     * ```
     *
     * @var array
     */
    private $professions = [];

    /**
     * Map for industries
     *
     * @var array
     */
    private $industries = [];

    /**
     * Map for employmentTypes
     *
     * @var array
     */
    private $employmentTypes = [];

    /**
     * Set map for professions.
     *
     * @param array $professions
     */
    public function setProfessions(array $professions): void
    {
        $this->professions = $professions;
    }

    /**
     * Get map for professions
     *
     * @return array
     */
    public function getProfessions(): array
    {
        return $this->professions;
    }

    /**
     * Get map for industries
     *
     * @return array
     */
    public function getIndustries(): array
    {
        return $this->industries;
    }

    /**
     * Set map for industries
     *
     * @param array $industries
     */
    public function setIndustries(array $industries): void
    {
        $this->industries = $industries;
    }

    /**
     * Get map for employmentTypes
     *
     * @return array
     */
    public function getEmploymentTypes(): array
    {
        return $this->employmentTypes;
    }

    /**
     * Set map for employmentTypes
     *
     * @param array $employmentTypes
     */
    public function setEmploymentTypes(array $employmentTypes): void
    {
        $this->employmentTypes = $employmentTypes;
    }

    /**
     * Get a normalized map
     *
     * The keys of the map array will be lowercased, allowing
     * for case insensitive mapping.
     *
     * @param string $type 'industries', 'professions' or 'employmentTypes'
     * @return array
     * @throws OutOfBoundsException when an unknown type is given
     */
    public function getMap(string $type): array
    {
        if (!in_array($type, ['professions', 'industries', 'employmentTypes'])) {
            throw new OutOfBoundsException('Unknown map type "' . $type . '"');
        }

        return array_change_key_case($this->{"get$type"}(), CASE_LOWER);
    }

    /**
     * Get an array of normalized maps.
     *
     * @param string[] $types an array with valid map types.
     * @return array
     * @throws OutOfBoundsException
     * @uses getMap()
     */
    public function getMaps(array $types): array
    {
        $maps = [];

        foreach ($types as $type) {
            $maps[$type] = $this->getMap($type);
        }

        return $maps;
    }
}
