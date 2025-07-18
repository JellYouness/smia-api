# Notification System Documentation

## Overview

The SMIA notification system provides a comprehensive solution for sending and managing notifications across multiple channels (in-app, email, SMS, push) with user preference management.

## Features

- **Multi-channel notifications**: In-app, email, SMS, and push notifications
- **User preferences**: Users can control which notification types they receive
- **Real-time updates**: Unread count updates automatically
- **Rich notification types**: 15+ predefined notification types
- **Internationalization**: Support for multiple languages
- **Pagination and filtering**: Efficient notification management
- **Test commands**: Easy testing and development tools

## Architecture

### Backend (Laravel)

#### Models

- `Notification`: Stores in-app notifications
- `User`: Extended with notification relationships and methods

#### Services

- `NotificationService`: Core service for sending notifications
- Handles multiple channels and user preferences

#### Controllers

- `NotificationController`: API endpoints for notification management

#### Enums

- `NotificationType`: Defines all notification types

### Frontend (Next.js)

#### Components

- `NotificationItem`: Individual notification display
- `NotificationDropdown`: Topbar notification dropdown
- `NotificationProvider`: Global notification context

#### Hooks

- `useNotifications`: Fetch and manage notifications
- `useUnreadCount`: Real-time unread count
- `useMarkAsRead`: Mark notifications as read
- `useDeleteNotification`: Delete notifications

## Database Schema

### notifications table

```sql
CREATE TABLE notifications (
    id UUID PRIMARY KEY,
    type VARCHAR NOT NULL,
    notifiable_type VARCHAR NOT NULL,
    notifiable_id BIGINT NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### users table (existing)

- `notification_email`: Boolean for email preferences
- `notification_sms`: Boolean for SMS preferences
- `notification_push`: Boolean for push preferences
- `notification_in_app`: Boolean for in-app preferences

## API Endpoints

### GET /api/notifications

Get user notifications with filtering and pagination.

**Query Parameters:**

- `type`: Filter by notification type
- `read`: Filter by read status (true/false)
- `per_page`: Number of notifications per page
- `page`: Page number

**Response:**

```json
{
  "success": true,
  "data": {
    "notifications": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 20,
      "total": 100
    },
    "unread_count": 15
  }
}
```

### GET /api/notifications/{id}

Get a specific notification.

### PATCH /api/notifications/{id}/read

Mark a notification as read.

### PATCH /api/notifications/mark-all-read

Mark all notifications as read.

### DELETE /api/notifications/{id}

Delete a notification.

### GET /api/notifications/unread-count

Get unread notification count.

### GET /api/notifications/types

Get available notification types.

## Usage Examples

### Sending Notifications

```php
use App\Services\NotificationService;
use App\Enums\NotificationType;

// Send to single user
$notificationService->send(
    $user,
    NotificationType::PROJECT_INVITE,
    [
        'title' => 'Project Invitation',
        'message' => 'You have been invited to join Project XYZ',
        'project_id' => 123,
        'inviter_name' => 'John Doe'
    ],
    ['in_app', 'email']
);

// Send to multiple users
$notificationService->sendToMultiple(
    $users,
    NotificationType::SYSTEM_ANNOUNCEMENT,
    [
        'title' => 'System Maintenance',
        'message' => 'System will be down for maintenance on Sunday'
    ],
    ['in_app', 'email']
);
```

### Frontend Usage

```typescript
import { useNotifications, useUnreadCount } from '@modules/notifications/hooks/useNotifications';

// Get notifications with filters
const { data, isLoading } = useNotifications({
  type: NotificationType.PROJECT_INVITE,
  read: false,
  perPage: 20,
});

// Get unread count
const { data: unreadData } = useUnreadCount();

// Mark as read
const markAsRead = useMarkAsRead();
markAsRead.mutate(notificationId);
```

## Notification Types

| Type                  | Description         | Icon         | Color   |
| --------------------- | ------------------- | ------------ | ------- |
| `project_invite`      | Project invitation  | project      | primary |
| `project_update`      | Project update      | update       | primary |
| `project_completed`   | Project completed   | check_circle | success |
| `proposal_received`   | New proposal        | description  | primary |
| `proposal_accepted`   | Proposal accepted   | thumb_up     | success |
| `proposal_rejected`   | Proposal rejected   | thumb_down   | error   |
| `message_received`    | New message         | message      | primary |
| `system_announcement` | System announcement | announcement | warning |
| `profile_update`      | Profile update      | person       | default |
| `payment_received`    | Payment received    | payment      | success |
| `payment_sent`        | Payment sent        | send         | default |
| `account_verified`    | Account verified    | verified     | success |
| `welcome`             | Welcome message     | celebration  | warning |
| `reminder`            | Reminder            | schedule     | default |
| `security_alert`      | Security alert      | security     | error   |

## Testing

### Artisan Commands

```bash
# Send test notification to first user
php artisan notification:test

# Send test notification to specific user
php artisan notification:test 1

# Send specific notification type
php artisan notification:test 1 project_invite

# Send with custom message
php artisan notification:test 1 welcome --message="Welcome to our platform!"
```

### Available Types for Testing

- project_invite
- project_update
- project_completed
- proposal_received
- proposal_accepted
- proposal_rejected
- message_received
- system_announcement
- profile_update
- payment_received
- payment_sent
- account_verified
- welcome
- reminder
- security_alert

## Configuration

### Environment Variables

```env
# Frontend URL for email notifications
FRONTEND_URL=http://localhost:3000

# Email configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### User Preferences

Users can manage their notification preferences through:

- User settings page
- API endpoint: `PUT /api/user/settings`

## Future Enhancements

### Planned Features

- **WebSocket support**: Real-time notification delivery
- **Push notifications**: Firebase Cloud Messaging integration
- **SMS notifications**: Twilio/Vonage integration
- **Notification templates**: Rich HTML email templates
- **Notification scheduling**: Delayed notification sending
- **Notification analytics**: Track notification engagement

### Integration Points

- **Project system**: Automatic notifications for project events
- **Messaging system**: Real-time message notifications
- **Payment system**: Payment status notifications
- **Security system**: Security alert notifications

## Troubleshooting

### Common Issues

1. **Notifications not appearing**

   - Check user notification preferences
   - Verify database migration ran successfully
   - Check notification service configuration

2. **Email notifications not sending**

   - Verify mail configuration in `.env`
   - Check mail logs in `storage/logs/laravel.log`
   - Test with `php artisan notification:test`

3. **Frontend not updating**
   - Check React Query cache invalidation
   - Verify API endpoints are accessible
   - Check browser console for errors

### Debug Commands

```bash
# Check notification count
php artisan tinker
>>> App\Models\User::first()->getUnreadNotificationsCount()

# List all notifications for user
php artisan tinker
>>> App\Models\User::first()->notifications()->get()
```

## Contributing

When adding new notification types:

1. Add to `NotificationType` enum
2. Update `NotificationService` methods
3. Add translations to language files
4. Update documentation
5. Add test cases

## Support

For issues and questions:

1. Check this documentation
2. Review the code examples
3. Test with the provided commands
4. Check the logs for errors
