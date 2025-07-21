<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private NotificationType $type,
        private array $data
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $title = $this->getTitle();
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject($title)
            ->line($message)
            ->action('View Details', $this->getActionUrl())
            ->line('Thank you for using our platform!');
    }

    private function getTitle(): string
    {
        return match ($this->type) {
            NotificationType::PROJECT_INVITE => 'Project Invitation',
            NotificationType::PROJECT_UPDATE => 'Project Update',
            NotificationType::PROJECT_COMPLETED => 'Project Completed',
            NotificationType::PROPOSAL_RECEIVED => 'New Proposal Received',
            NotificationType::PROPOSAL_ACCEPTED => 'Proposal Accepted',
            NotificationType::PROPOSAL_REJECTED => 'Proposal Update',
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
            NotificationType::WELCOME => 'Welcome to SMIA',
            NotificationType::REMINDER => 'Reminder',
            NotificationType::SECURITY_ALERT => 'Security Alert',
        };
    }

    private function getMessage(): string
    {
        return $this->data['message'] ?? match ($this->type) {
            NotificationType::PROJECT_INVITE => 'You have been invited to join a project.',
            NotificationType::PROJECT_UPDATE => 'A project you are involved with has been updated.',
            NotificationType::PROJECT_COMPLETED => 'A project has been completed successfully.',
            NotificationType::PROPOSAL_RECEIVED => 'You have received a new proposal.',
            NotificationType::PROPOSAL_ACCEPTED => 'Your proposal has been accepted.',
            NotificationType::PROPOSAL_REJECTED => 'Your proposal status has been updated.',
            NotificationType::MESSAGE_RECEIVED => 'You have received a new message.',
            NotificationType::SYSTEM_ANNOUNCEMENT => 'There is an important system announcement.',
            NotificationType::PROFILE_UPDATE => 'Your profile has been updated.',
            NotificationType::PAYMENT_RECEIVED => 'You have received a payment.',
            NotificationType::PAYMENT_SENT => 'A payment has been sent.',
            NotificationType::ACCOUNT_VERIFIED => 'Your account has been verified.',
            NotificationType::APPLICATION_SUBMITTED => 'Your application has been submitted successfully and is under review.',
            NotificationType::APPLICATION_APPROVED => 'Congratulations! Your application has been approved.',
            NotificationType::APPLICATION_REJECTED => 'Your application status has been updated. Please check your dashboard for details.',
            NotificationType::APPLICATION_PENDING => 'Your application is currently under review. We will notify you once a decision has been made.',
            NotificationType::WELCOME => 'Welcome to SMIA! We are excited to have you on board.',
            NotificationType::REMINDER => 'This is a reminder for an upcoming event.',
            NotificationType::SECURITY_ALERT => 'There is a security alert for your account.',
        };
    }

    private function getActionUrl(): string
    {
        $baseUrl = env('FRONTEND_URL', 'http://localhost:3000');

        return match ($this->type) {
            NotificationType::PROJECT_INVITE => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::PROJECT_UPDATE => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::PROJECT_COMPLETED => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::PROPOSAL_RECEIVED => $baseUrl . '/proposals/' . ($this->data['proposal_id'] ?? ''),
            NotificationType::PROPOSAL_ACCEPTED => $baseUrl . '/proposals/' . ($this->data['proposal_id'] ?? ''),
            NotificationType::PROPOSAL_REJECTED => $baseUrl . '/proposals/' . ($this->data['proposal_id'] ?? ''),
            NotificationType::MESSAGE_RECEIVED => $baseUrl . '/messages',
            NotificationType::SYSTEM_ANNOUNCEMENT => $baseUrl . '/announcements',
            NotificationType::PROFILE_UPDATE => $baseUrl . '/profile',
            NotificationType::PAYMENT_RECEIVED => $baseUrl . '/payments',
            NotificationType::PAYMENT_SENT => $baseUrl . '/payments',
            NotificationType::ACCOUNT_VERIFIED => $baseUrl . '/profile',
            NotificationType::APPLICATION_SUBMITTED => $baseUrl . '/dashboard',
            NotificationType::APPLICATION_APPROVED => $baseUrl . '/dashboard',
            NotificationType::APPLICATION_REJECTED => $baseUrl . '/dashboard',
            NotificationType::APPLICATION_PENDING => $baseUrl . '/dashboard',
            NotificationType::WELCOME => $baseUrl . '/dashboard',
            NotificationType::REMINDER => $baseUrl . '/dashboard',
            NotificationType::SECURITY_ALERT => $baseUrl . '/security',
        };
    }
}
