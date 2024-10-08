<?php

namespace App\Features\SyncUserAttributes\Events;

use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAttributesBatchReady
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public UserAttributeQueue $userAttributeQueue
    )
    {}
}
