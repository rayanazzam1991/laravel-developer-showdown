<?php

namespace App\Features\SyncUserAttributes\Listeners;

use App\Features\SyncUserAttributes\Events\UserAttributesBatchSending;
use App\Features\SyncUserAttributes\Service\SyncUserAttributesWithProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;


class UserAttributesBatchSendingListener implements ShouldQueue
{

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create the event listener.
     */
    public function __construct(
        private readonly SyncUserAttributesWithProvider $attributesWithProvider,
    )
    {
    }

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
        Log::info("Failed Sync", [$exception->getMessage()]);
    }


}
