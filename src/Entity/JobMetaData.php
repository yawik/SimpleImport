<?php

/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

declare(strict_types=1);

namespace SimpleImport\Entity;

use Jobs\Entity\JobInterface;
use Laminas\Json\Json;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class JobMetaData
{
    public const KEY = 'simpleimport-extra';

    private $data = null;

    public static function fromJob(JobInterface $job): self
    {
        $data = $job->getMetaData(self::KEY) ?? [];
        return new self($data);
    }

    public static function fromJson(string $json): self
    {
        $data = Json::decode($json, Json::TYPE_ARRAY) ?: [];
        return new self($data);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function storeIn(JobInterface $job): void
    {
        $job->setMetaData(self::KEY, $this->data);
    }
}
