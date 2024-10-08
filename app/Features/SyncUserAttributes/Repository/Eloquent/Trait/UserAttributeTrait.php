<?php

namespace App\Features\SyncUserAttributes\Repository\Eloquent\Trait;

use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;

trait UserAttributeTrait
{



    public function unSentRecordsCount(): int
    {
        $attributeQueueRepository = new UserAttributeQueueRepository();
        return $attributeQueueRepository->getUnsentRecordsSize();
    }
}
