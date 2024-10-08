<?php

namespace Database\Seeders;

use App\Features\SyncUserAttributes\Repository\Eloquent\Model\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        User::factory(20)->create();
    }
}
