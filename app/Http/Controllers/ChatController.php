<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {}

    public function getConversations(Request $request)
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);

        $conversations = $this->chatService->getConversationsForUser($user, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'conversations' => $conversations->items(),
                'pagination' => [
                    'current_page' => $conversations->currentPage(),
                    'last_page' => $conversations->lastPage(),
                    'per_page' => $conversations->perPage(),
                    'total' => $conversations->total(),
                ],
            ],
        ]);
    }

    public function getMessages(Request $request, string $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is participant
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'conversation' => 'You are not a participant in this conversation.',
            ]);
        }

        $perPage = $request->get('per_page', 50);
        $beforeMessageId = $request->get('before_message_id');

        $messages = $this->chatService->getMessagesForConversation($conversation, $perPage, $beforeMessageId);

        // Mark conversation as read
        $this->chatService->markConversationAsRead($conversation, $user);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->items(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                ],
            ],
        ]);
    }

    public function sendMessage(Request $request, string $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is participant
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'conversation' => 'You are not a participant in this conversation.',
            ]);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
            'message_type' => 'sometimes|string|in:text,image,file,system',
            'attachments' => 'sometimes|array',
            'reply_to_id' => 'sometimes|string|exists:messages,id',
        ]);

        $message = $this->chatService->sendMessage(
            $conversation,
            $user,
            $request->content,
            $request->get('message_type', 'text'),
            $request->get('attachments', []),
            $request->get('reply_to_id')
        );

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function createDirectConversation(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'user_id' => 'required|integer|exists:users,id|different:' . $user->id,
        ]);

        $otherUser = User::findOrFail($request->user_id);

        $conversation = $this->chatService->createDirectConversation($user, $otherUser);

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    public function createGroupConversation(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $users->push($user); // Add current user

        $conversation = $this->chatService->createGroupConversation($users->all(), $request->name);

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    public function editMessage(Request $request, string $messageId)
    {
        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        // Check if user is the sender
        if ($message->sender_id !== $user->id) {
            throw ValidationException::withMessages([
                'message' => 'You can only edit your own messages.',
            ]);
        }

        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $message = $this->chatService->editMessage($message, $request->content);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function deleteMessage(string $messageId)
    {
        $user = Auth::user();
        $message = Message::findOrFail($messageId);

        // Check if user is the sender
        if ($message->sender_id !== $user->id) {
            throw ValidationException::withMessages([
                'message' => 'You can only delete your own messages.',
            ]);
        }

        $this->chatService->deleteMessage($message);

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.',
        ]);
    }

    public function markAsRead(string $conversationId)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($conversationId);

        // Check if user is participant
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'conversation' => 'You are not a participant in this conversation.',
            ]);
        }

        $this->chatService->markConversationAsRead($conversation, $user);

        return response()->json([
            'success' => true,
            'message' => 'Conversation marked as read.',
        ]);
    }

    /**
     * Fetch or create a project chat conversation by projectId.
     * POST /chat/conversations/project
     * Body: { project_id: int, name?: string, user_ids?: int[] }
     */
    public function getOrCreateProjectConversation(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'name' => 'sometimes|string|max:255',
            'user_ids' => 'sometimes|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);
        $projectId = $request->input('project_id');
        $name = $request->input('name');
        $userIds = $request->input('user_ids', []);

        // Try to find an existing project conversation
        $conversation = \App\Models\Conversation::where('type', 'project')
            ->where('project_id', $projectId)
            ->first();

        if ($conversation) {
            // Optionally, add the current user as a participant if not already
            if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
                $conversation->participants()->attach($user->id, ['role' => 'member']);
            }
            return response()->json([
                'success' => true,
                'data' => $conversation->load('participants'),
            ]);
        }

        // Gather participants: current user + any provided user_ids
        $participants = [$user];
        if (!empty($userIds)) {
            $otherUsers = \App\Models\User::whereIn('id', $userIds)->get();
            foreach ($otherUsers as $otherUser) {
                if ($otherUser->id !== $user->id) {
                    $participants[] = $otherUser;
                }
            }
        }

        // Create the project conversation
        $conversation = $this->chatService->createProjectConversation($participants, $projectId, $name);
        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }
}
