<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Ambassador;
use App\Models\Creator;
use App\Models\Client;
use App\Models\Notification;
use App\Enums\PROJECT_STATUS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminDashboardController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        try {
            // Get user counts
            $totalUsers = User::count();
            $totalCreators = User::where('user_type', 'CREATOR')->count();
            $totalClients = User::where('user_type', 'CLIENT')->count();
            $totalAmbassadors = User::where('user_type', 'AMBASSADOR')->count();

            // Get project statistics
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', PROJECT_STATUS::IN_PROGRESS)->count();
            $completedProjects = Project::where('status', PROJECT_STATUS::COMPLETED)->count();

            // Get pending applications
            $pendingAmbassadorApplications = Ambassador::where('application_status', 'PENDING')->count();
            $pendingCreatorApplications = Creator::where('verification_status', 'PENDING')->count();
            $pendingApplications = $pendingAmbassadorApplications + $pendingCreatorApplications;

            // Get recent activity (last 10 activities)
            $recentActivity = $this->getRecentActivity();

            // Get system health metrics
            $systemHealth = $this->getSystemHealth();

            return response()->json([
                'success' => true,
                'data' => [
                    'totalUsers' => $totalUsers,
                    'totalCreators' => $totalCreators,
                    'totalClients' => $totalClients,
                    'totalAmbassadors' => $totalAmbassadors,
                    'totalProjects' => $totalProjects,
                    'pendingApplications' => $pendingApplications,
                    'activeProjects' => $activeProjects,
                    'completedProjects' => $completedProjects,
                    'recentActivity' => $recentActivity,
                    'systemHealth' => $systemHealth,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AdminDashboardController::getDashboardStats: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => [__('common.unexpected_error')],
            ], 500);
        }
    }

    private function getRecentActivity()
    {
        $activities = [];

        try {
            // Get recent user registrations
            $recentUsers = User::with('profile')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentUsers as $user) {
                $activities[] = [
                    'id' => 'user_' . $user->id,
                    'type' => 'user_registration',
                    'description' => 'New ' . strtolower($user->user_type) . ' registered',
                    'timestamp' => $user->created_at->toISOString(),
                    'user' => [
                        'id' => $user->id,
                        'firstName' => $user->first_name,
                        'lastName' => $user->last_name,
                        'email' => $user->email,
                    ],
                ];
            }

            // Get recent projects
            $recentProjects = Project::with(['client.user', 'ambassador.user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentProjects as $project) {
                $activities[] = [
                    'id' => 'project_' . $project->id,
                    'type' => 'project_created',
                    'description' => 'New project created: ' . $project->title,
                    'timestamp' => $project->created_at->toISOString(),
                    'user' => $project->client && $project->client->user ? [
                        'id' => $project->client->user->id,
                        'firstName' => $project->client->user->first_name,
                        'lastName' => $project->client->user->last_name,
                        'email' => $project->client->user->email,
                    ] : null,
                ];
            }

            // Get recent ambassador applications
            $recentAmbassadorApplications = Ambassador::with('user')
                ->where('application_status', 'PENDING')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentAmbassadorApplications as $ambassador) {
                $activities[] = [
                    'id' => 'ambassador_' . $ambassador->id,
                    'type' => 'application_submitted',
                    'description' => 'Ambassador application submitted',
                    'timestamp' => $ambassador->created_at->toISOString(),
                    'user' => $ambassador->user ? [
                        'id' => $ambassador->user->id,
                        'firstName' => $ambassador->user->first_name,
                        'lastName' => $ambassador->user->last_name,
                        'email' => $ambassador->user->email,
                    ] : null,
                ];
            }

            // Sort all activities by timestamp and take the most recent 10
            usort($activities, function ($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            return array_slice($activities, 0, 10);
        } catch (\Exception $e) {
            Log::error('Error in getRecentActivity: ' . $e->getMessage());
            return [];
        }
    }

    private function getSystemHealth()
    {
        // Calculate uptime (simplified - in a real system you'd track this)
        $uptime = 99.8; // Mock value

        // Get active connections (simplified)
        $activeConnections = 0;
        try {
            if (DB::getSchemaBuilder()->hasTable('user_sessions')) {
                $activeConnections = DB::table('user_sessions')->count();
            }
        } catch (\Exception $e) {
            // If sessions table doesn't exist, use default value
            $activeConnections = 0;
        }

        // Calculate average response time (simplified)
        $averageResponseTime = 120; // Mock value in milliseconds

        return [
            'uptime' => $uptime,
            'activeConnections' => $activeConnections,
            'averageResponseTime' => $averageResponseTime,
        ];
    }
}
