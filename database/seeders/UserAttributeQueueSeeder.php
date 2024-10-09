<?php

namespace Database\Seeders;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Database\Seeder;

class UserAttributeQueueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserAttributeQueue::factory(4000)->create();
    }
}
