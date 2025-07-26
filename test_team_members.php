<?php

require_once 'vendor/autoload.php';

use App\Models\Ambassador;
use App\Models\TeamMember;

// Test 1: Check if TeamMember model works
echo "Test 1: TeamMember count: " . TeamMember::count() . "\n";

// Test 2: Check if Ambassador with team members works
$ambassador = Ambassador::with('teamMembers.user.profile')->find(1);
if ($ambassador) {
    echo "Test 2: Ambassador 1 has " . $ambassador->teamMembers->count() . " team members\n";

    foreach ($ambassador->teamMembers as $member) {
        echo "  - Team member: " . ($member->user->name ?? "User " . $member->user_id) .
            " (Role: " . ($member->role ?? "None") .
            ", Primary: " . ($member->is_primary ? "Yes" : "No") . ")\n";
    }
} else {
    echo "Test 2: No ambassador found with ID 1\n";
}

// Test 3: Check if the relationship query works
echo "Test 3: Testing relationship query...\n";
try {
    $teamMembers = TeamMember::with('user.profile')->get();
    echo "  Successfully loaded " . $teamMembers->count() . " team members with user data\n";
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

echo "All tests completed!\n";
