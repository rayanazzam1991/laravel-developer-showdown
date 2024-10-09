<?php

namespace App\Console\Commands;

use App\Features\SyncUserAttributes\Infrastructure\Repository\Eloquent\Model\User;
use Illuminate\Console\Command;

class UpdateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update {email}';

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
        /** @var User|null $user */
        $user = User::whereEmail($email)->first();

        // Check if user exists
        if (! $user) {
            $this->error("User with email {$email} not found.");

            return 1;
        }

        $firstName = $this->ask('What is the new first name? as the old is', $user->first_name);
        $lastName = $this->ask('What is the new last name? as the old is', $user->last_name);
        $timeZone = $this->ask('What is the new time zone? as the old is', $user->time_zone);

        // Ensure that these variables are always strings
        $firstName = is_string($firstName) ? $firstName : $user->first_name;
        $lastName = is_string($lastName) ? $lastName : $user->last_name;
        $timeZone = is_string($timeZone) ? $timeZone : $user->time_zone;

        // Update the fields
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->time_zone = $timeZone;

        // Save the user
        $user->save();

        // Provide feedback
        $this->info("User {$user->email} has been updated successfully.");

        return 0;
    }
}
