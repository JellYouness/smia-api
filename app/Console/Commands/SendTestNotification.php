<?php

namespace App\Console\Commands;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendTestNotification extends Command
{
    protected $signature = 'notification:test {user_id?} {type?} {--message=}';
    protected $description = 'Send a test notification to a user';

    public function __construct(
        private NotificationService $notificationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $userId = $this->argument('user_id');
        $type = $this->argument('type');
        $message = $this->option('message');

        // Get user
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error("No users found in the database.");
                return 1;
            }
            $this->info("Using first user: {$user->email}");
        }

        // Get notification type
        if ($type) {
            try {
                $notificationType = NotificationType::from($type);
            } catch (\ValueError $e) {
                $this->error("Invalid notification type: {$type}");
                $this->info("Available types: " . implode(', ', array_column(NotificationType::cases(), 'value')));
                return 1;
            }
        } else {
            $notificationType = NotificationType::WELCOME;
            $this->info("Using default type: {$notificationType->value}");
        }

        // Prepare notification data
        $data = [
            'title' => $this->notificationService->getNotificationTitle($notificationType),
            'message' => $message ?? "This is a test {$notificationType->value} notification.",
            'timestamp' => now()->toISOString(),
            'test' => true,
        ];

        // Add specific data for team invitation notifications
        if ($notificationType === NotificationType::TEAM_INVITATION_ACCEPTED) {
            $data = array_merge($data, [
                'user_name' => 'Test User',
                'team_name' => 'Test Team',
                'role' => 'Member',
                'is_primary' => false,
            ]);
        } elseif ($notificationType === NotificationType::TEAM_INVITATION_DECLINED) {
            $data = array_merge($data, [
                'user_name' => 'Test User',
                'team_name' => 'Test Team',
                'role' => 'Member',
                'is_primary' => false,
            ]);
        }

        // Send notification
        try {
            $this->notificationService->send($user, $notificationType, $data, ['in_app', 'email']);
            $this->info("Test notification sent successfully to {$user->email}");
            $this->info("Type: {$notificationType->value}");
            $this->info("Message: {$data['message']}");
        } catch (\Exception $e) {
            $this->error("Failed to send notification: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
