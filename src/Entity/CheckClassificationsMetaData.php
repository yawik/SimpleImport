<?php declare(strict_types=1);
/**
 * YAWIK SimpleImport
 *
 * @filesource
 * @copyright 2019 CROSS Solution <https://www.cross-solution.de>
 * @license MIT
 */

namespace SimpleImport\Entity;

use DateTime;
use Jobs\Entity\JobInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * TODO: write tests
 */
class CheckClassificationsMetaData
{
    const KEY = 'si-cc-';
    const UNCHECKED = 'unchecked';
    const QUEUED    = 'queued';
    const CHECKED   = 'checked';

    /** @var array */
    private $messages = [];

    /** @var string */
    private $status = self::UNCHECKED;

    public static function fromJob(JobInterface $job, string $category)
    {
        $meta = $job->getMetaData(self::KEY . $category) ?? [];
        return new static($category, $meta);
    }

    public function __construct(string $category, array $data = [])
    {
        $this->status = $data['status'] ?? self::UNCHECKED;
        $this->messages = $data['messages'] ?? [];
        $this->category = $category;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function addMessage(string $message): void
    {
        $time = (new DateTime())->format('Y-m-d H:i:s');
        array_push($this->messages, "$time: $message");
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status, ?string $message = null): void
    {
        $this->status = $status;
        if ($message) {
            $this->addMessage($message);
        }
    }

    public function queued(?string $message = null): void
    {
        $this->setStatus(self::QUEUED, $message);
    }

    public function checked($message = null): void
    {
        $this->setStatus(self::CHECKED, $message);
    }

    public function isChecked(): bool
    {
        return $this->status == self::CHECKED;
    }

    public function isQueued(): bool
    {
        return $this->status == self::QUEUED;
    }

    public function isUnchecked(): bool
    {
        return $this->status == self::UNCHECKED;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'messages' => $this->messages,
        ];
    }

    public function storeIn(JobInterface $job): void
    {
        $meta = $this->toArray();
        $job->setMetaData(self::KEY . $this->category, $meta);
    }
}
