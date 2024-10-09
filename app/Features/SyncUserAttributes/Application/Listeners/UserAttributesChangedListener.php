<?php

namespace App\Features\SyncUserAttributes\Application\Listeners;

use App\Features\SyncUserAttributes\Application\Events\UserAttributesChanged;
use App\Features\SyncUserAttributes\Application\Mappers\QueueUserAttributeChangeMapper;
use App\Features\SyncUserAttributes\Application\Service\ManageUserAttributeChanges;

class UserAttributesChangedListener
{
    /**
     * The name of the connection the job should be sent to.
     */
    public ?string $connection = 'sync';

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly ManageUserAttributeChanges $attributeChangesService,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserAttributesChanged $event): void
    {
        $entity = QueueUserAttributeChangeMapper::toEntity($event->user);
        $this->attributeChangesService->storeChangedUserAttributes($entity);
    }
}
