<?php

namespace App\Features\SyncUserAttributes\Application\Listeners;

use App\Features\SyncUserAttributes\Application\Events\UserAttributesBatchSending;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInteractor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserAttributesBatchSendingListener implements ShouldQueue
{
    /**
     * The number of times the queued listener may be attempted.
     */
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly SyncUserAttributesInteractor $attributesWithProvider,
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserAttributesBatchSending $event): void
    {
        $this->attributesWithProvider->syncReadyData();
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserAttributesBatchSending $event, Throwable $exception): void
    {
        Log::info('Failed Sync', [$exception->getMessage()]);
    }
}
