# Chat System Setup Guide

## Environment Configuration

### Backend (.env)

Add these variables to your Laravel `.env` file:

```env
# Broadcasting Configuration
BROADCAST_DRIVER=pusher

# Pusher Configuration
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Frontend URL for notifications
FRONTEND_URL=http://localhost:3000
```

### Frontend (.env.local)

Create a `.env.local` file in your Next.js app root:

```env
# Pusher Configuration
NEXT_PUBLIC_PUSHER_KEY=your_pusher_app_key
NEXT_PUBLIC_PUSHER_CLUSTER=mt1
NEXT_PUBLIC_API_URL=http://localhost:8000
```

## Pusher Setup

1. **Create a Pusher Account**:

   - Go to [pusher.com](https://pusher.com)
   - Sign up for a free account
   - Create a new Channels app

2. **Get Your Credentials**:

   - Copy your App ID, Key, and Secret from the Pusher dashboard
   - Update your environment variables with these values

3. **Configure App Settings**:
   - Enable client events in your Pusher app settings
   - Set up authentication endpoint (already configured in Laravel)

## Database Setup

Run the migrations to create the chat tables:

```bash
cd smia-api
php artisan migrate
```

## Testing the Chat

1. **Start the Backend**:

   ```bash
   cd smia-api
   php artisan serve
   ```

2. **Start the Frontend**:

   ```bash
   cd smia-app
   npm run dev
   ```

3. **Test Chat Features**:
   - Navigate to `/chat` in your browser
   - Create conversations between users
   - Send messages and test real-time updates

## Features Included

- ✅ Real-time messaging with WebSocket support
- ✅ Direct and group conversations
- ✅ Message editing and deletion
- ✅ Read receipts and unread counts
- ✅ File attachments support
- ✅ Search conversations
- ✅ Responsive design
- ✅ Multi-language support (English/French)
- ✅ Integration with existing auth system
- ✅ Notification system integration

## API Endpoints

### Conversations

- `GET /api/chat/conversations` - Get user conversations
- `POST /api/chat/conversations/direct` - Create direct conversation
- `POST /api/chat/conversations/group` - Create group conversation

### Messages

- `GET /api/chat/conversations/{id}/messages` - Get conversation messages
- `POST /api/chat/conversations/{id}/messages` - Send message
- `PUT /api/chat/messages/{id}` - Edit message
- `DELETE /api/chat/messages/{id}` - Delete message
- `PUT /api/chat/conversations/{id}/read` - Mark as read

## Troubleshooting

### Common Issues

1. **WebSocket Connection Failed**:

   - Check Pusher credentials in environment variables
   - Verify Pusher app is active and configured correctly
   - Check browser console for connection errors

2. **Messages Not Sending**:

   - Verify API endpoints are accessible
   - Check authentication token is valid
   - Review Laravel logs for errors

3. **Real-time Updates Not Working**:
   - Ensure broadcasting is enabled in Laravel
   - Check Pusher channel authentication
   - Verify frontend is subscribed to correct channels

### Debug Mode

Enable debug mode to see detailed logs:

```env
# Backend
APP_DEBUG=true

# Frontend
NEXT_PUBLIC_DEBUG=true
```

## Security Considerations

- All chat endpoints require authentication
- Users can only access conversations they're participants in
- Message editing/deletion is restricted to message authors
- WebSocket channels are authenticated via Laravel's broadcasting system

## Performance Optimization

- Messages are paginated (50 per page by default)
- Conversations are cached and updated efficiently
- Real-time updates use efficient WebSocket connections
- Database queries are optimized with proper indexing
