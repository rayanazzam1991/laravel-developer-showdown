<?php

namespace App\Features\SyncUserAttributes\Domain\Entity;

use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use Spatie\LaravelData\Data;

class UserAttributesQueueEntity extends Data
{
    private int $id;

    private string $payload;

    private int $status;

    private int $retryCount;

    public function __construct(string $payload, int $status = QueueDataStatusEnum::UN_SENT->value, int $retryCount = 0)
    {

        $this->payload = $payload;
        $this->status = $status;
        $this->retryCount = $retryCount;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function setRetryCount(int $retryCount): void
    {
        $this->retryCount = $retryCount;
    }

    public function markAsSent(): void
    {
        $this->status = QueueDataStatusEnum::SENT->value;
    }

    public function markAsFailed(): void
    {
        $this->status = QueueDataStatusEnum::FAILED->value;
    }

    public function incrementRetryCount(): void
    {
        $this->retryCount++;
    }

    public function hasExceededRetries(): bool
    {
        return $this->retryCount >= 3;
    }
}
