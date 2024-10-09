<?php

namespace App\Console\Commands;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use Illuminate\Console\Command;

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
        // Ask for the number of times to trigger changes
        $times = $this->ask('How many times do you want to trigger changes', '2000');

        // Check if the input is numeric and greater than 0
        if (! is_numeric($times) || intval($times) <= 0) {
            $this->error('The number of times must be a positive integer.');

            return 1; // Indicate an error
        }

        $users = User::all(); // Fetch all users

        foreach ($users as $user) {
            for ($i = 0; $i < intval($times); $i++) {
                // Update the fields
                $user->first_name = fake()->firstName();
                $user->last_name = fake()->lastName();
                $user->time_zone = fake()->timezone();
                // Save the user
                $user->save();
            }
        }

        // Provide feedback
        $this->info('User attributes have been synced successfully.');

        return 0; // Indicate success
    }
}
