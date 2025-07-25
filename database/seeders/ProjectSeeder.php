<?php

namespace Database\Seeders;

use App\Enums\CREATOR_PROJECT_PERMISSION;
use App\Enums\CREATOR_PROJECT_STATUS;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Client;
use App\Models\Ambassador;
use App\Models\Creator;
use App\Models\User;
use App\Enums\PROJECT_STATUS;
use App\Models\ProjectCreator;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get a client, ambassador, and creator
        $client = Client::first();
        $ambassador = Ambassador::first();
        $creator = Creator::first();

        // Project 1: with client and creator
        $project1 = Project::create([
            'title' => 'Client Project Example',
            'description' => 'A project created by a client with a linked creator.',
            'status' => PROJECT_STATUS::DRAFT,
            'client_id' => $client?->id,
            'creator_id' => $creator?->id,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'budget' => 1000.00,
            'is_public' => true,
        ]);
        if ($project1 && $creator) {
            ProjectCreator::create([
              'project_id' => $project1->id,
              'creator_id' => $creator->id,
              'permission' => null,
              'status' => CREATOR_PROJECT_STATUS::CONFIRMED,
            ]);
        }

        // Project 2: with ambassador, no creators
        $project2 = Project::create([
            'title' => 'Ambassador Project Example',
            'description' => 'A project created by an ambassador with no creators yet.',
            'status' => PROJECT_STATUS::DRAFT,
            'ambassador_id' => $ambassador?->id,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'budget' => 2000.00,
            'is_public' => true,
        ]);
    }
}
