<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function createDirectConversation(User $user1, User $user2): Conversation
    {
        // Check if conversation already exists
        $existingConversation = $this->findDirectConversation($user1, $user2);
        if ($existingConversation) {
            return $existingConversation;
        }

        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'type' => 'direct',
        ]);

        // Add participants
        $conversation->participants()->attach([
            $user1->id => ['role' => 'member'],
            $user2->id => ['role' => 'member'],
        ]);

        return $conversation->load('participants');
    }

    public function createGroupConversation(array $users, string $name): Conversation
    {
        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'type' => 'group',
            'name' => $name,
        ]);

        // Add participants
        $participants = [];
        foreach ($users as $user) {
            $participants[$user->id] = ['role' => 'member'];
        }
        $conversation->participants()->attach($participants);

        return $conversation->load('participants');
    }

    public function createProjectConversation(array $users, int $projectId, string $name = null): Conversation
    {
        $conversation = Conversation::create([
            'id' => Str::uuid(),
            'type' => 'project',
            'name' => $name,
            'project_id' => $projectId,
        ]);

        // Add participants
        $participants = [];
        foreach ($users as $user) {
            $participants[$user->id] = ['role' => 'member'];
        }
        $conversation->participants()->attach($participants);

        return $conversation->load('participants');
    }

    public function sendMessage(Conversation $conversation, User $sender, string $content, string $messageType = 'text', array $attachments = [], ?string $replyToId = null): Message
    {
        $message = Message::create([
            'id' => Str::uuid(),
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $content,
            'message_type' => $messageType,
            'attachments' => $attachments,
            'reply_to_id' => $replyToId,
        ]);

        // Update conversation's updated_at timestamp to reflect the latest message
        $conversation->timestamps = false;
        $conversation->updated_at = now();
        $conversation->save();
        $conversation->timestamps = true;

        // Mark conversation as read for sender
        $conversation->markAsReadForUser($sender);

        // Send notifications to other participants
        $this->notifyParticipants($conversation, $sender, $message);

        // Broadcast the message
        $this->broadcastMessage($conversation, $message);

        return $message->load(['sender', 'replyTo']);
    }

    public function getConversationsForUser(User $user, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $conversations = $user->conversations()
            ->with(['participants', 'latestMessage.sender'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'asc')
            ->paginate($perPage);

        // Add unread count for each conversation
        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $conversation->unreadCount = $conversation->getUnreadCountForUser($user);
            return $conversation;
        });

        return $conversations;
    }

    public function getMessagesForConversation(Conversation $conversation, int $perPage = 50, ?string $beforeMessageId = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $conversation->messages()
            ->with(['sender', 'replyTo'])
            ->orderBy('created_at', 'desc');

        if ($beforeMessageId) {
            $beforeMessage = Message::find($beforeMessageId);
            if ($beforeMessage) {
                $query->where('created_at', '<', $beforeMessage->created_at);
            }
        }

        return $query->paginate($perPage);
    }

    public function markConversationAsRead(Conversation $conversation, User $user): void
    {
        $conversation->markAsReadForUser($user);

        // Broadcast user-specific event for real-time unread count updates
        broadcast(new \App\Events\MessageRead($conversation, $user));
    }

    public function addParticipantToConversation(Conversation $conversation, User $user, string $role = 'member'): void
    {
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            $conversation->participants()->attach($user->id, ['role' => $role]);
        }
    }

    public function removeParticipantFromConversation(Conversation $conversation, User $user): void
    {
        $conversation->participants()->detach($user->id);
    }

    public function editMessage(Message $message, string $newContent): Message
    {
        $message->update([
            'content' => $newContent,
            'edited_at' => now(),
        ]);

        // Update conversation's updated_at timestamp
        $message->conversation->timestamps = false;
        $message->conversation->updated_at = now();
        $message->conversation->save();
        $message->conversation->timestamps = true;

        // Broadcast the edited message
        $this->broadcastMessageEdit($message->conversation, $message);

        return $message->load(['sender', 'replyTo']);
    }

    public function deleteMessage(Message $message): void
    {
        $conversation = $message->conversation;
        $message->delete();

        // Update conversation's updated_at timestamp to the latest remaining message
        $latestMessage = $conversation->messages()->latest()->first();
        $conversation->timestamps = false;
        if ($latestMessage) {
            $conversation->updated_at = $latestMessage->created_at;
        } else {
            $conversation->updated_at = now();
        }
        $conversation->save();
        $conversation->timestamps = true;

        // Broadcast the deleted message
        $this->broadcastMessageDelete($conversation, $message);
    }

    private function findDirectConversation(User $user1, User $user2): ?Conversation
    {
        return Conversation::where('type', 'direct')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();
    }

    private function notifyParticipants(Conversation $conversation, User $sender, Message $message): void
    {
        $participants = $conversation->participants()
            ->where('user_id', '!=', $sender->id)
            ->get();

        foreach ($participants as $participant) {
            // Send notification
            $notificationData = [
                'conversation_id' => $conversation->id,
                'conversation_name' => $conversation->name ?? 'Direct Message',
                'sender_name' => $sender->firstName . ' ' . $sender->lastName,
                'message_preview' => substr($message->content, 0, 100),
                'message_id' => $message->id,
            ];

            // Debug: Log the notification data
            \Log::info('Sending message notification', [
                'participant_id' => $participant->id,
                'sender_id' => $sender->id,
                'sender_name' => $notificationData['sender_name'],
                'sender_first_name' => $sender->firstName,
                'sender_last_name' => $sender->lastName,
                'sender_attributes' => $sender->getAttributes(),
            ]);

            // $this->notificationService->send(
            //     $participant,
            //     NotificationType::MESSAGE_RECEIVED,
            //     $notificationData,
            //     ['in_app', 'email']
            // );

            // Broadcast user-specific event for real-time unread count updates
            broadcast(new \App\Events\MessageReceived($conversation, $message, $participant));
        }
    }

    private function broadcastMessage(Conversation $conversation, Message $message): void
    {
        // Broadcast to conversation channel
        broadcast(new \App\Events\MessageSent($conversation, $message))
            ->toOthers();
    }

    private function broadcastMessageEdit(Conversation $conversation, Message $message): void
    {
        broadcast(new \App\Events\MessageEdited($conversation, $message))
            ->toOthers();
    }

    private function broadcastMessageDelete(Conversation $conversation, Message $message): void
    {
        broadcast(new \App\Events\MessageDeleted($conversation, $message))
            ->toOthers();
    }
}
