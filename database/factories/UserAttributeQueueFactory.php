<?php

namespace Database\Factories;

use App\Features\SyncUserAttributes\Enum\QueueDataStatusEnum;
use App\Features\SyncUserAttributes\Enum\SyncApiParametersEnum;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAttributeQueue>
 */
class UserAttributeQueueFactory extends Factory
{
    protected $model = UserAttributeQueue::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'payload' => json_encode(['test']),
            'status' => QueueDataStatusEnum::UN_SENT->value,
            'retry_count' => 0
        ];
    }
}
