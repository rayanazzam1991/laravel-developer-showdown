<?php

namespace App\Console\Commands;

use App\Features\SyncUserAttributes\Events\UserAttributesBatchReady;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\User;
use App\Features\SyncUserAttributes\Repository\Eloquent\Model\UserAttributeQueue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SendBatchUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:batch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all users randomly';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
//        Artisan::call('db:seed', [
//            '--class' => 'UserAttributeQueueSeeder'
//        ]);
        $times = $this->ask('How many times you want to trigger changes', 2000);

        $users = User::query()->get();
        for ($i = 0; $i < $times; $i++) {
            foreach ($users as $user) {
                // Update the fields
                $user->first_name = fake()->firstName();
                $user->last_name = fake()->lastName();
                $user->time_zone = fake()->timezone();
                // Save the user
                $user->save();
            }
        }


        // Provide feedback
        $this->info("Users attributes has been synced successfully.");

        return 0;
    }
}
