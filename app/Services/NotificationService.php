<?php

namespace App\Services;

use App\Enums\NotificationType;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Support\Str;

class NotificationService
{
    public function send(
        User $user,
        NotificationType $type,
        array $data,
        array $channels = ['in_app']
    ): void {
        // Load user profile if not already loaded
        if (!$user->relationLoaded('profile')) {
            $user->load('profile');
        }

        // Get notification preferences with defaults
        $preferences = $user->profile?->notification_preferences ?? [
            'in_app' => true,
            'email' => true,
            'sms' => false,
            'push' => false,
        ];

        // Create in-app notification
        if (in_array('in_app', $channels) && ($preferences['in_app'] ?? true)) {
            $this->createInAppNotification($user, $type, $data);
        }

        // Send email notification
        if (in_array('email', $channels) && ($preferences['email'] ?? true)) {
            $this->sendEmailNotification($user, $type, $data);
        }

        // Send SMS notification
        if (in_array('sms', $channels) && ($preferences['sms'] ?? false)) {
            $this->sendSmsNotification($user, $type, $data);
        }

        // Send push notification
        if (in_array('push', $channels) && ($preferences['push'] ?? false)) {
            $this->sendPushNotification($user, $type, $data);
        }
    }

    public function sendToMultiple(
        array $users,
        NotificationType $type,
        array $data,
        array $channels = ['in_app']
    ): void {
        foreach ($users as $user) {
            $this->send($user, $type, $data, $channels);
        }
    }

    private function createInAppNotification(User $user, NotificationType $type, array $data): void
    {
        DatabaseNotification::create([
            'id' => Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $data,
        ]);
    }

    private function sendEmailNotification(User $user, NotificationType $type, array $data): void
    {
        $user->notify(new InAppNotification($type, $data));
    }

    private function sendSmsNotification(User $user, NotificationType $type, array $data): void
    {
        // TODO: Implement SMS notification logic
        // This would integrate with services like Twilio, Vonage, etc.
    }

    private function sendPushNotification(User $user, NotificationType $type, array $data): void
    {
        // TODO: Implement push notification logic
        // This would integrate with services like Firebase Cloud Messaging, OneSignal, etc.
    }

    public function getNotificationTitle(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::PROJECT_INVITE => 'Project Invitation',
            NotificationType::PROJECT_UPDATE => 'Project Update',
            NotificationType::PROJECT_COMPLETED => 'Project Completed',
            NotificationType::PROPOSAL_RECEIVED => 'New Proposal',
            NotificationType::PROPOSAL_ACCEPTED => 'Proposal Accepted',
            NotificationType::PROPOSAL_REJECTED => 'Proposal Rejected',
            NotificationType::MESSAGE_RECEIVED => 'New Message',
            NotificationType::SYSTEM_ANNOUNCEMENT => 'System Announcement',
            NotificationType::PROFILE_UPDATE => 'Profile Update',
            NotificationType::PAYMENT_RECEIVED => 'Payment Received',
            NotificationType::PAYMENT_SENT => 'Payment Sent',
            NotificationType::ACCOUNT_VERIFIED => 'Account Verified',
            NotificationType::APPLICATION_SUBMITTED => 'Application Submitted',
            NotificationType::APPLICATION_APPROVED => 'Application Approved',
            NotificationType::APPLICATION_REJECTED => 'Application Update',
            NotificationType::APPLICATION_PENDING => 'Application Status Update',
            NotificationType::WELCOME => 'Welcome',
            NotificationType::REMINDER => 'Reminder',
            NotificationType::SECURITY_ALERT => 'Security Alert',
            NotificationType::TEAM_INVITATION_ACCEPTED => 'Team Invitation Accepted',
            NotificationType::TEAM_INVITATION_DECLINED => 'Team Invitation Declined',
        };
    }

    public function getNotificationIcon(NotificationType $type): string
    {
        return match ($type) {
            NotificationType::PROJECT_INVITE => 'project',
            NotificationType::PROJECT_UPDATE => 'update',
            NotificationType::PROJECT_COMPLETED => 'check_circle',
            NotificationType::PROPOSAL_RECEIVED => 'description',
            NotificationType::PROPOSAL_ACCEPTED => 'thumb_up',
            NotificationType::PROPOSAL_REJECTED => 'thumb_down',
            NotificationType::MESSAGE_RECEIVED => 'message',
            NotificationType::SYSTEM_ANNOUNCEMENT => 'announcement',
            NotificationType::PROFILE_UPDATE => 'person',
            NotificationType::PAYMENT_RECEIVED => 'payment',
            NotificationType::PAYMENT_SENT => 'send',
            NotificationType::ACCOUNT_VERIFIED => 'verified',
            NotificationType::APPLICATION_SUBMITTED => 'description',
            NotificationType::APPLICATION_APPROVED => 'check_circle',
            NotificationType::APPLICATION_REJECTED => 'cancel',
            NotificationType::APPLICATION_PENDING => 'schedule',
            NotificationType::WELCOME => 'celebration',
            NotificationType::REMINDER => 'schedule',
            NotificationType::SECURITY_ALERT => 'security',
            NotificationType::TEAM_INVITATION_ACCEPTED => 'check_circle',
            NotificationType::TEAM_INVITATION_DECLINED => 'cancel',
        };
    }
}
