<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        // Get some users to create conversations with
        $users = User::take(10)->get();

        if ($users->count() < 2) {
            $this->command->error('Need at least 2 users to create conversations. Please run UserSeeder first.');
            return;
        }

        $this->command->info('Creating chat conversations and messages...');

        // Create direct conversations
        $this->createDirectConversations($users);

        // Create group conversations
        $this->createGroupConversations($users);

        // Create project conversations
        $this->createProjectConversations($users);

        $this->command->info('Chat seeding completed!');
    }

    private function createDirectConversations($users): void
    {
        $this->command->info('Creating direct conversations...');

        // Create direct conversations between pairs of users
        for ($i = 0; $i < min(5, $users->count() - 1); $i++) {
            $user1 = $users[$i];
            $user2 = $users[$i + 1];

            $conversation = Conversation::create([
                'id' => Str::uuid(),
                'type' => 'direct',
            ]);

            // Add participants
            $conversation->participants()->attach([
                $user1->id => ['role' => 'member'],
                $user2->id => ['role' => 'member'],
            ]);

            // Create messages for this conversation
            $this->createMessagesForConversation($conversation, [$user1, $user2], 'direct');
        }
    }

    private function createGroupConversations($users): void
    {
        $this->command->info('Creating group conversations...');

        $groupNames = [
            'Project Team Alpha',
            'Marketing Team',
            'Development Team',
            'Design Team',
            'Support Team'
        ];

        foreach ($groupNames as $index => $groupName) {
            if ($index >= 3) break; // Limit to 3 groups

            // Select 3-5 random users for each group
            $participants = $users->random(rand(3, min(5, $users->count())))->all();

            $conversation = Conversation::create([
                'id' => Str::uuid(),
                'type' => 'group',
                'name' => $groupName,
            ]);

            // Add participants
            $participantsData = [];
            foreach ($participants as $participant) {
                $participantsData[$participant->id] = ['role' => 'member'];
            }
            $conversation->participants()->attach($participantsData);

            // Create messages for this conversation
            $this->createMessagesForConversation($conversation, $participants, 'group');
        }
    }

    private function createProjectConversations($users): void
    {
        $this->command->info('Creating project conversations...');

        $projectNames = [
            'Website Redesign',
            'Mobile App Development',
            'Brand Identity Project'
        ];

        foreach ($projectNames as $index => $projectName) {
            if ($index >= 2) break; // Limit to 2 project chats

            // Select 2-4 random users for each project
            $participants = $users->random(rand(2, min(4, $users->count())))->all();

            $conversation = Conversation::create([
                'id' => Str::uuid(),
                'type' => 'project',
                'name' => $projectName,
                'project_id' => $index + 1, // Mock project ID
            ]);

            // Add participants
            $participantsData = [];
            foreach ($participants as $participant) {
                $participantsData[$participant->id] = ['role' => 'member'];
            }
            $conversation->participants()->attach($participantsData);

            // Create messages for this conversation
            $this->createMessagesForConversation($conversation, $participants, 'project');
        }
    }

    private function createMessagesForConversation($conversation, $participants, $type): void
    {
        $messageCount = rand(5, 15); // 5-15 messages per conversation

        $sampleMessages = $this->getSampleMessages($type);

        for ($i = 0; $i < $messageCount; $i++) {
            $sender = $participants[array_rand($participants)];
            $messageText = $sampleMessages[array_rand($sampleMessages)];

            // Add some variety to messages
            $messageText = $this->personalizeMessage($messageText, $sender->firstName);

            $message = Message::create([
                'id' => Str::uuid(),
                'conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'content' => $messageText,
                'message_type' => 'text',
                'created_at' => now()->subMinutes(rand(1, 1440)), // Random time in last 24 hours
                'updated_at' => now()->subMinutes(rand(1, 1440)),
            ]);

            // Randomly mark some messages as edited
            if (rand(1, 10) === 1) { // 10% chance
                $message->update([
                    'edited_at' => $message->created_at->addMinutes(rand(1, 5)),
                ]);
            }
        }

        // Update conversation's updated_at to reflect latest message
        $latestMessage = $conversation->messages()->latest()->first();
        if ($latestMessage) {
            $conversation->update(['updated_at' => $latestMessage->created_at]);
        }

        // Randomly mark some conversations as read for some participants
        foreach ($participants as $participant) {
            if (rand(1, 3) === 1) { // 33% chance
                $conversation->participants()->updateExistingPivot($participant->id, [
                    'last_read_at' => now()->subMinutes(rand(1, 60)),
                ]);
            }
        }
    }

    private function getSampleMessages($type): array
    {
        $baseMessages = [
            'Hello! How are you doing?',
            'Thanks for the update!',
            'That sounds great!',
            'I\'ll get back to you soon.',
            'Can you send me the details?',
            'Perfect, that works for me.',
            'Let me check on that.',
            'I\'m available for a call tomorrow.',
            'Great work on this!',
            'Do you have any questions?',
            'I\'ll review this and let you know.',
            'Thanks for your help!',
            'That\'s exactly what I needed.',
            'I\'ll keep you posted.',
            'Looking forward to working together!',
        ];

        switch ($type) {
            case 'direct':
                return array_merge($baseMessages, [
                    'Hey! How\'s it going?',
                    'Did you see the latest updates?',
                    'We should catch up soon.',
                    'Thanks for reaching out!',
                    'I\'ll be in touch later.',
                ]);

            case 'group':
                return array_merge($baseMessages, [
                    'Team, I have an update for everyone.',
                    'Great job everyone!',
                    'Who\'s available for the meeting?',
                    'Let\'s coordinate on this.',
                    'I\'ll share the agenda shortly.',
                    'Everyone please review the latest changes.',
                    'Thanks team for your hard work!',
                ]);

            case 'project':
                return array_merge($baseMessages, [
                    'Project update: We\'re on track.',
                    'I\'ve completed the first phase.',
                    'Need feedback on the latest iteration.',
                    'Timeline looks good so far.',
                    'Let\'s discuss the next steps.',
                    'I\'ve uploaded the latest files.',
                    'Project milestone reached!',
                    'Need input on this decision.',
                ]);

            default:
                return $baseMessages;
        }
    }

    private function personalizeMessage($message, $firstName): string
    {
        $replacements = [
            'Hello!' => "Hi {$firstName}!",
            'Hey!' => "Hey {$firstName}!",
            'Thanks!' => "Thanks {$firstName}!",
            'Great work!' => "Great work {$firstName}!",
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }
}
