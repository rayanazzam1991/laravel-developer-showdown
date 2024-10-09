<?php

namespace App\Console\Commands;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use Illuminate\Console\Command;

class RandomUpdateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update-random {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user details like first name, last name, and time zone by email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Get the email from the argument
        $email = $this->argument('email');

        // Find the user by email
        $user = User::whereEmail($email)->first();

        // Check if user exists
        if (! $user) {
            $this->error("User with email {$email} not found.");

            return 1;
        }

        // Update the fields
        $user->first_name = fake()->firstName();
        $user->last_name = fake()->lastName();
        $user->time_zone = fake()->timezone();

        // Save the user
        $user->save();

        // Provide feedback
        $this->info("User {$user->email} has been updated successfully.");

        return 0;
    }
}
