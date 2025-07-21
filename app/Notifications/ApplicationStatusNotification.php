<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $applicationType, // 'creator' or 'ambassador'
        private string $status, // 'approved', 'rejected', 'pending'
        private ?string $reviewNotes = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $title = $this->getTitle();
        $message = $this->getMessage();

        $mailMessage = (new MailMessage)
            ->subject($title)
            ->line($message);

        if ($this->reviewNotes) {
            $mailMessage->line('Review Notes: ' . $this->reviewNotes);
        }

        return $mailMessage
            ->action('View Dashboard', $this->getActionUrl())
            ->line('Thank you for your interest in joining our platform!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->getNotificationType(),
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'data' => [
                'application_type' => $this->applicationType,
                'status' => $this->status,
                'review_notes' => $this->reviewNotes,
                'action_url' => $this->getActionUrl(),
            ],
        ];
    }

    private function getNotificationType(): string
    {
        return match ($this->status) {
            'submitted' => NotificationType::APPLICATION_SUBMITTED->value,
            'approved' => NotificationType::APPLICATION_APPROVED->value,
            'rejected' => NotificationType::APPLICATION_REJECTED->value,
            'pending' => NotificationType::APPLICATION_PENDING->value,
            default => NotificationType::APPLICATION_PENDING->value,
        };
    }

    private function getTitle(): string
    {
        $type = ucfirst($this->applicationType);

        return match ($this->status) {
            'submitted' => "Your {$type} Application Has Been Submitted",
            'approved' => "Your {$type} Application Has Been Approved!",
            'rejected' => "Your {$type} Application Status Update",
            'pending' => "Your {$type} Application is Under Review",
            default => "Your {$type} Application Status Update",
        };
    }

    private function getMessage(): string
    {
        $type = $this->applicationType;

        return match ($this->status) {
            'submitted' => "Thank you for submitting your {$type} application! We have received your application and our team will review it shortly. You will receive an email notification once the review is complete.",
            'approved' => "Congratulations! Your {$type} application has been approved. You can now start using all the features available to {$type}s on our platform.",
            'rejected' => "Your {$type} application status has been updated. Please review the feedback provided and feel free to submit a new application if you believe you meet the requirements.",
            'pending' => "Your {$type} application is currently under review by our team. We will notify you once a decision has been made. This process typically takes 2-3 business days.",
            default => "Your {$type} application status has been updated. Please check your dashboard for more details.",
        };
    }

    private function getActionUrl(): string
    {
        $baseUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return $baseUrl . '/dashboard';
    }
}
