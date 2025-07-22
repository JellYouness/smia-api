<?php

namespace App\Enums;

enum NotificationType: string
{
    case PROJECT_INVITE = 'project_invite';
    case PROJECT_UPDATE = 'project_update';
    case PROJECT_COMPLETED = 'project_completed';
    case PROPOSAL_RECEIVED = 'proposal_received';
    case PROPOSAL_ACCEPTED = 'proposal_accepted';
    case PROPOSAL_REJECTED = 'proposal_rejected';
    case MESSAGE_RECEIVED = 'message_received';
    case SYSTEM_ANNOUNCEMENT = 'system_announcement';
    case PROFILE_UPDATE = 'profile_update';
    case PAYMENT_RECEIVED = 'payment_received';
    case PAYMENT_SENT = 'payment_sent';
    case ACCOUNT_VERIFIED = 'account_verified';
    case APPLICATION_SUBMITTED = 'application_submitted';
    case APPLICATION_APPROVED = 'application_approved';
    case APPLICATION_REJECTED = 'application_rejected';
    case APPLICATION_PENDING = 'application_pending';
    case WELCOME = 'welcome';
    case REMINDER = 'reminder';
    case SECURITY_ALERT = 'security_alert';
    case PROJECT_PERMISSION_UPDATED = 'project_permission_updated';
    case NEW_PROJECT_INVITE = 'new_project_invite';
    case NEW_PROPOSAL = 'new_proposal';
    case NEW_PROPOSAL_COMMENT = 'new_proposal_comment';
    case NEW_PROJECT_UPDATE = 'new_project_update';
}
