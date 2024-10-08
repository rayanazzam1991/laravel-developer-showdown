<?php

namespace App\Features\SyncUserAttributes\Mappers;

use App\Features\SyncUserAttributes\Entity\UserAttributesQueueEntity;
use App\Features\SyncUserAttributes\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\User;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;

readonly class QueueUserAttributeChangeMapper
{

    public function __construct(
        private UserAttributeQueueRepository $attributeQueueRepository
    )
    {}

    public function toModelArray(UserAttributesQueueEntity $entity): array
    {
        $model['payload'] = $entity->getPayload();
        $model['status'] = $entity->getStatus();
        $model['retry_count'] = $entity->getRetryCount();

        return $model;
    }

    public function toEntity(User $user): UserAttributesQueueEntity
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
        return new UserAttributesQueueEntity(
            payload: json_encode($payload),
            status: QueueDataStatusEnum::UN_SENT->value,
            retryCount: 0
        );
    }

    public function toEntityUpdate(UserAttributeQueue $model): UserAttributesQueueEntity
    {
        return new UserAttributesQueueEntity(
            payload: $model->payload,
            status: QueueDataStatusEnum::UN_SENT->value,
            retryCount: 0
        );
    }
}
