<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ambassador;
use App\Models\TeamMember;
use Illuminate\Support\Facades\DB;

class MigrateTeamMembersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting team members migration...');

        // Check if team_members column still exists
        $columns = DB::select("SHOW COLUMNS FROM ambassadors LIKE 'team_members'");

        if (empty($columns)) {
            $this->command->info('team_members column has already been removed. Checking for existing team members...');

            // Check if there are any existing team members
            $existingTeamMembers = DB::table('ambassador_team_members')->count();

            if ($existingTeamMembers > 0) {
                $this->command->info("Found {$existingTeamMembers} existing team members. Migration already completed.");
                return;
            }

            $this->command->info('No existing team members found. Creating sample data...');
            $this->createSampleTeamMembers();
            return;
        }

        // Get all ambassadors that have team_members data
        $ambassadors = DB::table('ambassadors')
            ->whereNotNull('team_members')
            ->where('team_members', '!=', '[]')
            ->where('team_members', '!=', 'null')
            ->get();

        $this->command->info("Found {$ambassadors->count()} ambassadors with team members to migrate.");

        foreach ($ambassadors as $ambassador) {
            $teamMembers = json_decode($ambassador->team_members, true);

            if (is_array($teamMembers)) {
                foreach ($teamMembers as $index => $userId) {
                    // Skip if user doesn't exist
                    $userExists = DB::table('users')->where('id', $userId)->exists();
                    if (!$userExists) {
                        $this->command->warn("User with ID {$userId} not found, skipping...");
                        continue;
                    }

                    // Check if team member already exists
                    $existingMember = DB::table('ambassador_team_members')
                        ->where('ambassador_id', $ambassador->id)
                        ->where('user_id', $userId)
                        ->exists();

                    if (!$existingMember) {
                        // Create team member record
                        DB::table('ambassador_team_members')->insert([
                            'ambassador_id' => $ambassador->id,
                            'user_id' => $userId,
                            'role' => null, // Default role
                            'is_primary' => $index === 0, // First member is primary
                            'joined_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        $this->command->info("Migrated team member {$userId} for ambassador {$ambassador->id}");
                    } else {
                        $this->command->info("Team member {$userId} already exists for ambassador {$ambassador->id}, skipping...");
                    }
                }
            }
        }

        $this->command->info('Team members migration completed!');
    }

    private function createSampleTeamMembers(): void
    {
        // Get some ambassadors to create sample team members for
        $ambassadors = DB::table('ambassadors')->limit(5)->get();
        $users = DB::table('users')->where('user_type', '!=', 'AMBASSADOR')->limit(10)->get();

        if ($ambassadors->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No ambassadors or users found for sample data creation.');
            return;
        }

        foreach ($ambassadors as $ambassador) {
            // Create 1-3 team members for each ambassador
            $numMembers = rand(1, 3);
            $selectedUsers = $users->random($numMembers);

            foreach ($selectedUsers as $index => $user) {
                DB::table('ambassador_team_members')->insert([
                    'ambassador_id' => $ambassador->id,
                    'user_id' => $user->id,
                    'role' => $this->getRandomRole(),
                    'is_primary' => $index === 0, // First member is primary
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("Created sample team member {$user->id} for ambassador {$ambassador->id}");
            }
        }

        $this->command->info('Sample team members created successfully!');
    }

    private function getRandomRole(): string
    {
        $roles = [
            'Photographer',
            'Videographer',
            'Editor',
            'Assistant',
            'Producer',
            'Director',
            'Sound Engineer',
            'Lighting Technician',
        ];

        return $roles[array_rand($roles)];
    }
}
