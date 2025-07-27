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

        $mailMessage = (new MailMessage)
            ->subject($title);

        if ($this->type === NotificationType::NEW_PROJECT_UPDATE) {
            $projectTitle = $this->data['project_title'] ?? null;
            $updateType = $this->data['update_type'] ?? null;
            $updateBody = $this->data['update_body'] ?? null;
            if ($projectTitle) {
                $mailMessage->line('Project: ' . $projectTitle);
            }
            if ($updateType) {
                $mailMessage->line('Update Type: ' . $updateType);
            }
            if ($updateBody) {
                $mailMessage->line('Update Details:');
                $mailMessage->line($updateBody);
            }
        } else if ($this->type === NotificationType::NEW_PROJECT_INVITE) {
            $projectTitle = $this->data['project_title'] ?? null;
            $inviteMessage = $this->data['message'] ?? null;
            $mailMessage->line('You have been invited to join a new project.');
            if ($projectTitle) {
                $mailMessage->line('Project: ' . $projectTitle);
            }
            if ($inviteMessage) {
                $mailMessage->line('Invitation Message:');
                $mailMessage->line($inviteMessage);
            }
        } else if ($this->type === NotificationType::NEW_PROPOSAL) {
            $projectTitle = $this->data['project_title'] ?? null;
            $proposalId = $this->data['proposal_id'] ?? null;
            $mailMessage->line('You have received a new proposal.');
            if ($projectTitle) {
                $mailMessage->line('Project: ' . $projectTitle);
            }
            if ($proposalId) {
                $mailMessage->line('Proposal ID: ' . $proposalId);
            }
        } else if ($this->type === NotificationType::NEW_PROPOSAL_COMMENT) {
            $projectTitle = $this->data['project_title'] ?? null;
            $proposalId = $this->data['proposal_id'] ?? null;
            $commentBody = $this->data['comment_body'] ?? null;
            $mailMessage->line('There is a new comment on your proposal.');
            if ($projectTitle) {
                $mailMessage->line('Project: ' . $projectTitle);
            }
            if ($proposalId) {
                $mailMessage->line('Proposal ID: ' . $proposalId);
            }
            if ($commentBody) {
                $mailMessage->line('Comment:');
                $mailMessage->line($commentBody);
            }
        } else if ($this->type === NotificationType::PROJECT_PERMISSION_UPDATED) {
            $project = $this->data['project_title'] ?? $this->data['project_id'] ?? '';
            $permission = $this->data['permission'] ?? '';
            $mailMessage->line('Your project permission has been updated.');
            if ($project) {
                $mailMessage->line('Project: ' . $project);
            }
            if ($permission) {
                $mailMessage->line('New Permission: ' . $permission);
            }
        } else {
            $mailMessage->line($message);
        }

        return $mailMessage
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
            NotificationType::PROJECT_PERMISSION_UPDATED => 'Project Permission Updated',
            NotificationType::NEW_PROJECT_INVITE => 'You have a new project invitation',
            NotificationType::NEW_PROPOSAL => 'You have received a new proposal',
            NotificationType::NEW_PROPOSAL_COMMENT => 'New comment on your proposal',
            NotificationType::NEW_PROJECT_UPDATE => 'Project Update',
        };
    }

    private function getMessage(): string
    {
        if ($this->type === NotificationType::NEW_PROJECT_UPDATE) {
            $projectTitle = $this->data['project_title'] ?? '';
            $updateType = $this->data['update_type'] ?? '';
            $updateBody = $this->data['update_body'] ?? '';
            $msg = 'Project update';
            if ($projectTitle) {
                $msg .= ' for "' . $projectTitle . '"';
            }
            if ($updateType) {
                $msg .= ' [' . $updateType . ']';
            }
            if ($updateBody) {
                $msg .= ': ' . $updateBody;
            }
            return $msg;
        }
        if ($this->type === NotificationType::NEW_PROJECT_INVITE) {
            $project = $this->data['project_title'] ?? $this->data['project_id'] ?? '';
            $msg = 'You have been invited to a new project';
            if ($project) {
                $msg .= ': ' . $project;
            }
            return $msg;
        }
        if ($this->type === NotificationType::NEW_PROPOSAL) {
            $project = $this->data['project_title'] ?? $this->data['project_id'] ?? '';
            $msg = 'You have received a new proposal';
            if ($project) {
                $msg .= ' for project: ' . $project;
            }
            return $msg;
        }
        if ($this->type === NotificationType::NEW_PROPOSAL_COMMENT) {
            $proposal = $this->data['proposal_id'] ?? '';
            $msg = 'There is a new comment on your proposal';
            if ($proposal) {
                $msg .= ' (Proposal ID: ' . $proposal . ')';
            }
            return $msg;
        }
        if ($this->type === NotificationType::PROJECT_UPDATE) {
            $projectTitle = $this->data['project_title'] ?? '';
            $updateType = $this->data['update_type'] ?? '';
            $updateBody = $this->data['update_body'] ?? '';
            $msg = 'Project update';
            if ($projectTitle) {
                $msg .= ' for "' . $projectTitle . '"';
            }
            if ($updateType) {
                $msg .= ' [' . $updateType . ']';
            }
            if ($updateBody) {
                $msg .= ': ' . $updateBody;
            }
            return $msg;
        }
        if ($this->type === NotificationType::PROJECT_PERMISSION_UPDATED) {
            $project = $this->data['project_title'] ?? $this->data['project_id'] ?? '';
            $permission = $this->data['permission'] ?? '';
            $msg = 'Your project permission has been updated';
            if ($project) {
                $msg .= ' for project: ' . $project;
            }
            if ($permission) {
                $msg .= ' (New permission: ' . $permission . ')';
            }
            return $msg;
        }
        if ($this->type === NotificationType::TEAM_INVITATION_ACCEPTED) {
            $userName = $this->data['user_name'] ?? '';
            $teamName = $this->data['team_name'] ?? '';
            $role = $this->data['role'] ?? '';
            $msg = 'Team invitation accepted';
            if ($userName) {
                $msg .= ' by ' . $userName;
            }
            if ($teamName) {
                $msg .= ' for team: ' . $teamName;
            }
            if ($role) {
                $msg .= ' (Role: ' . $role . ')';
            }
            return $msg;
        }
        if ($this->type === NotificationType::TEAM_INVITATION_DECLINED) {
            $userName = $this->data['user_name'] ?? '';
            $teamName = $this->data['team_name'] ?? '';
            $msg = 'Team invitation declined';
            if ($userName) {
                $msg .= ' by ' . $userName;
            }
            if ($teamName) {
                $msg .= ' for team: ' . $teamName;
            }
            return $msg;
        }
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
            NotificationType::TEAM_INVITATION_ACCEPTED => 'A team member has accepted your invitation.',
            NotificationType::TEAM_INVITATION_DECLINED => 'A team member has declined your invitation.',
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
            NotificationType::PROJECT_PERMISSION_UPDATED => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::NEW_PROJECT_INVITE => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::NEW_PROPOSAL => $baseUrl . '/proposals/' . ($this->data['proposal_id'] ?? ''),
            NotificationType::NEW_PROPOSAL_COMMENT => $baseUrl . '/proposals/' . ($this->data['proposal_id'] ?? ''),
            NotificationType::NEW_PROJECT_UPDATE => $baseUrl . '/projects/' . ($this->data['project_id'] ?? ''),
            NotificationType::TEAM_INVITATION_ACCEPTED => $baseUrl . '/admin/ambassadors',
            NotificationType::TEAM_INVITATION_DECLINED => $baseUrl . '/admin/ambassadors',
        };
    }
}
