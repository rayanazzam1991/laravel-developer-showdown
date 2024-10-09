<?php

namespace App\Features\SyncUserAttributes\Application\Events;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAttributesChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public User $user
    ) {}
}
