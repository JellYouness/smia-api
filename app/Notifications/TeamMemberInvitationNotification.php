<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\TeamMemberInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TeamMemberInvitation $invitation
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $ambassador = $this->invitation->ambassador;
        $ambassadorUser = $ambassador->user;
        $role = $this->invitation->role;
        $isPrimary = $this->invitation->is_primary;

        $mailMessage = (new MailMessage)
            ->subject('Team Member Invitation - ' . ($ambassador->teamName ?? 'SMIA Ambassador Team'))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited to join a team as a team member.');

        if ($ambassadorUser) {
            $mailMessage->line('Invited by: ' . $ambassadorUser->name);
        }

        if ($ambassador->teamName) {
            $mailMessage->line('Team: ' . $ambassador->teamName);
        }

        if ($role) {
            $mailMessage->line('Role: ' . $role);
        }

        if ($isPrimary) {
            $mailMessage->line('This is a primary team member position.');
        }

        $mailMessage->line('Please click the button below to accept or decline this invitation.')
            ->action('Respond to Invitation', $this->getActionUrl())
            ->line('This invitation will expire on ' . $this->invitation->expires_at->format('F j, Y \a\t g:i A'))
            ->line('If you have any questions, please contact the team leader.');

        return $mailMessage;
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => NotificationType::PROJECT_INVITE,
            'title' => 'Team Member Invitation',
            'message' => 'You have been invited to join a team as a team member.',
            'data' => [
                'invitation_id' => $this->invitation->id,
                'ambassador_id' => $this->invitation->ambassador_id,
                'ambassador_name' => $this->invitation->ambassador->teamName ?? 'SMIA Ambassador Team',
                'role' => $this->invitation->role,
                'is_primary' => $this->invitation->is_primary,
                'action_url' => $this->getActionUrl(),
            ],
        ];
    }

    private function getActionUrl(): string
    {
        $baseUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return $baseUrl . '/team-invitations/' . $this->invitation->token;
    }
}
