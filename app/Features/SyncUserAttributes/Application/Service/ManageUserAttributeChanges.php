<?php

namespace App\Features\SyncUserAttributes\Application\Service;

use App\Features\SyncUserAttributes\Application\Mappers\QueueUserAttributeChangeMapper;
use App\Features\SyncUserAttributes\Domain\Entity\UserAttributesQueueEntity;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\UserAttributeQueueRepository;

readonly class ManageUserAttributeChanges
{
    public function __construct(
        private UserAttributeQueueRepository $attributeQueueRepository
    ) {}

    public function storeChangedUserAttributes(UserAttributesQueueEntity $entity): void
    {
        /**
         * @var array{
         *   paylod:string,
         *   status:int,
         *   retry_count:int
         *  } $arrayDataToSave
         */
        $arrayDataToSave = QueueUserAttributeChangeMapper::toModelArray($entity);
        $this->attributeQueueRepository->store($arrayDataToSave);
    }

    public function removeSyncedUserAttributes(): void
    {

        $this->attributeQueueRepository->removeSentRecords();
    }
}
