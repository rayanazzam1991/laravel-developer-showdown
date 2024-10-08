<?php

namespace App\Features\SyncUserAttributes\Events;

use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAttributesBatchSending
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
    )
    {}

}
