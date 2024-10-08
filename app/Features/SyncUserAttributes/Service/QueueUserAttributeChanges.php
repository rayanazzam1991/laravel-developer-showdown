<?php

namespace App\Features\SyncUserAttributes\Service;

use App\Features\SyncUserAttributes\Entity\UserAttributesQueueEntity;
use App\Features\SyncUserAttributes\Mappers\QueueUserAttributeChangeMapper;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;

readonly class QueueUserAttributeChanges
{

    public function __construct(
        private UserAttributeQueueRepository $attributeQueueRepository
    )
    {}

    public function storeChangedUserAttributes(UserAttributesQueueEntity $entity): void
    {
        $mapper = new QueueUserAttributeChangeMapper($this->attributeQueueRepository);
        $this->attributeQueueRepository->store($mapper->toModelArray($entity));
    }


}
