<?php

namespace App\Features\SyncUserAttributes\Listeners;

use App\Features\SyncUserAttributes\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Mappers\QueueUserAttributeChangeMapper;
use App\Features\SyncUserAttributes\Repository\UserAttributeQueueRepository;
use App\Features\SyncUserAttributes\Service\SyncUserAttributesWithProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class UserAttributesBatchReadyListener

{

    /**
     * Create the event listener.
     */
    public function __construct(
        private SyncUserAttributesWithProvider $attributesWithProvider,
    )
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UserAttributesBatchReady $event): void
    {
        $this->attributesWithProvider->prepareDataAndTriggerSyncProcess();
    }


}
