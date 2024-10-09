<?php

use App\Features\SyncUserAttributes\Application\Events\UserAttributesChanged;
use App\Features\SyncUserAttributes\Domain\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Support\Facades\Event;

describe('Test user change attributes', function () {

    it('fires the UserAttributesChanged event on user update', function () {

        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Event::fake([
            UserAttributesChanged::class,
        ]);

        $user->update(['first_name' => 'Jane']);

        // Assert the event was dispatched
        Event::assertDispatched(UserAttributesChanged::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });

    });
    it('stores user attribute changes in the queue', function () {
        $user = User::factory()->create([
            'email' => 'JohnDoe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user->update(['first_name' => 'Jane']);

        $this->assertDatabaseHas(UserAttributeQueue::class, [
            'status' => QueueDataStatusEnum::UN_SENT->value,
        ]);
        $this->assertDatabaseHas(UserAttributeQueue::class, [
            'payload->name' => 'Jane Doe',
            'payload->email' => 'JohnDoe@example.com',
        ]);
    });
});
