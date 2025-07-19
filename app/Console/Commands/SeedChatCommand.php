<?php

namespace App\Console\Commands;

use Database\Seeders\ChatSeeder;
use Illuminate\Console\Command;

class SeedChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:seed {--users=10 : Number of users to use for seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with chat conversations and messages for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting chat seeding...');

        // Check if users exist
        $userCount = \App\Models\User::count();
        if ($userCount < 2) {
            $this->error('Need at least 2 users to create conversations. Please run UserSeeder first.');
            $this->info('You can run: php artisan db:seed --class=UserSeeder');
            return 1;
        }

        $this->info("Found {$userCount} users. Proceeding with chat seeding...");

        // Run the chat seeder
        $seeder = new ChatSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->info('Chat seeding completed successfully!');
        $this->info('You can now test the chat functionality at /chat');

        return 0;
    }
}
