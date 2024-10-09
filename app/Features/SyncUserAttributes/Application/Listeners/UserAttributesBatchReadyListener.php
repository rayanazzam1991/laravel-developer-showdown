<?php

namespace App\Features\SyncUserAttributes\Application\Listeners;

use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInterface;

class UserAttributesBatchReadyListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        public SyncUserAttributesInterface $attributesWithProvider,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserAttributesBatchReady $event): void
    {
        $this->attributesWithProvider->prepareDataAndTriggerSyncProcess();
    }
}
