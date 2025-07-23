<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestSessionTracking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:session-tracking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the session tracking functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Session Tracking System...');

        // Check if sessions table exists and has the right structure
        $this->info('1. Checking database structure...');

        try {
            $sessions = Session::all();
            $this->info('✓ Sessions table is accessible');
            $this->info("   Found {$sessions->count()} existing sessions");
        } catch (\Exception $e) {
            $this->error('✗ Error accessing sessions table: ' . $e->getMessage());
            return 1;
        }

        // Check if we have any users
        $this->info('2. Checking for test users...');
        $user = User::first();

        if (!$user) {
            $this->warn('No users found. Creating a test user...');
            $user = User::create([
                'email' => 'test@example.com',
                'username' => 'testuser',
                'password' => Hash::make('password'),
                'first_name' => 'Test',
                'last_name' => 'User',
                'user_type' => 'CREATOR',
                'accepted_terms' => true,
                'status' => 'ACTIVE',
                'date_registered' => now(),
            ]);
            $this->info('✓ Test user created');
        } else {
            $this->info('✓ Found existing user: ' . $user->email);
        }

        // Test session creation
        $this->info('3. Testing session creation...');

        $testToken = 'test_token_' . time();
        $tokenHash = hash('sha256', $testToken);

        $session = Session::create([
            'user_id' => $user->id,
            'token_hash' => $tokenHash,
            'device' => 'Test Device',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'last_active' => now(),
        ]);

        if ($session) {
            $this->info('✓ Session created successfully');
            $this->info("   Session ID: {$session->id}");
            $this->info("   User ID: {$session->user_id}");
            $this->info("   Device: {$session->device}");
        } else {
            $this->error('✗ Failed to create session');
            return 1;
        }

        // Test session relationships
        $this->info('4. Testing session relationships...');

        $userSessions = $user->sessions;
        $this->info("✓ User has {$userSessions->count()} sessions");

        // Test session methods
        $this->info('5. Testing session methods...');

        $formattedTime = $session->formatted_last_active;
        $this->info("✓ Formatted last active: {$formattedTime}");

        // Clean up test data
        $this->info('6. Cleaning up test data...');

        if ($user->email === 'test@example.com') {
            $user->sessions()->delete();
            $user->delete();
            $this->info('✓ Test user and sessions cleaned up');
        } else {
            $session->delete();
            $this->info('✓ Test session cleaned up');
        }

        $this->info('Session tracking test completed successfully!');
        return 0;
    }
}
