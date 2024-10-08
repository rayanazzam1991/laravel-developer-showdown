<?php

namespace App\Features\SyncUserAttributes\Listeners;

use App\Features\SyncUserAttributes\Events\UserAttributesChanged;
use App\Features\SyncUserAttributes\Mappers\QueueUserAttributeChangeMapper;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;
use App\Features\SyncUserAttributes\Service\QueueUserAttributeChanges;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserAttributesChangedListener
{

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public ?string $connection = 'sync';
    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly QueueUserAttributeChanges    $attributeChangesService,
        private readonly UserAttributeQueueRepository $attributeQueueRepository,
    )
    {}

    /**
     * Handle the event.
     */
    public function handle(UserAttributesChanged $event): void
    {
        $mapper = new QueueUserAttributeChangeMapper($this->attributeQueueRepository);
        $entity = $mapper->toEntity($event->user);
        $this->attributeChangesService->storeChangedUserAttributes($entity);
    }
}
