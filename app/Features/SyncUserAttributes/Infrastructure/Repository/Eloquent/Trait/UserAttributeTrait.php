<?php

namespace App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Trait;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\UserAttributeQueueRepository;

trait UserAttributeTrait
{
    public function unSentRecordsCount(): int
    {
        $attributeQueueRepository = new UserAttributeQueueRepository;

        return $attributeQueueRepository->getUnsentRecordsSize();
    }
}
