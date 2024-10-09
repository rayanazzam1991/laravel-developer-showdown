<?php

namespace App\Features\SyncUserAttributes\Application\Mappers;

use App\Features\SyncUserAttributes\Domain\Entity\UserAttributesQueueEntity;
use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;

readonly class QueueUserAttributeChangeMapper
{
    public function __construct() {}

    /**
     * @return array<string, string|int>
     */
    public static function toModelArray(UserAttributesQueueEntity $entity): array
    {
        return [
            'payload' => $entity->getPayload(),
            'status' => $entity->getStatus(),
            'retry_count' => $entity->getRetryCount(),
        ];
    }

    public static function toEntity(User $user): UserAttributesQueueEntity
    {
        $payload = [
            'email' => $user->email,
        ];

        if ($user->isDirty('first_name') || $user->isDirty('last_name')) {
            $name = $user->userName;
            $payload = array_merge($payload, ['name' => $name]);
        }

        if ($user->isDirty('time_zone')) {
            $timeZone = $user->time_zone;
            $payload = array_merge($payload, ['time_zone' => $timeZone]);
        }

        $encodedPayload = json_encode($payload);

        if ($encodedPayload === false) {
            throw new \RuntimeException('Failed to encode payload as JSON.');
        }

        return new UserAttributesQueueEntity(
            payload: $encodedPayload,
            status: QueueDataStatusEnum::UN_SENT->value,
            retryCount: 0
        );
    }

    public static function toEntityUpdate(UserAttributeQueue $model): UserAttributesQueueEntity
    {
        if (! is_string($model->payload)) {
            throw new \InvalidArgumentException('Payload must be a string.');
        }

        return new UserAttributesQueueEntity(
            payload: $model->payload,
            status: QueueDataStatusEnum::UN_SENT->value,
            retryCount: 0
        );
    }
}
