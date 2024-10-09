<?php

namespace App\Features\SyncUserAttributes\Application\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAttributesBatchReady implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(

    ) {}
}
