<?php

/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace SimpleImport\Filter;

use DateInterval;
use DateTime;
use Laminas\Filter\FilterInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class ShufflePublishDateFilter implements FilterInterface
{
    private $range;

    public function __construct(int $range = 0)
    {
        $this->range = $range;
    }

    public function filter($value)
    {
        if (!$value instanceof DateTime) {
            $value = new DateTime($value ?? 'now');
        }

        if (!$this->range) {
            return $value;
        }

        $shuffle = rand(0, $this->range);
        return $value->sub(new DateInterval('PT' . $shuffle . 'S'));
    }
}
