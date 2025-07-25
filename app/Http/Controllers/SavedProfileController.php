<?php

namespace App\Http\Controllers;

use App\Models\SavedProfile;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedProfileController extends Controller
{
    // List all saved creator profiles for the authenticated user
    public function index()
    {
        $user = Auth::user();
        $savedProfiles = SavedProfile::with('creator.user')
            ->where('user_id', $user->id)
            ->get();
        return response()->json(['success' => true, 'data' => $savedProfiles]);
    }

    // Save a creator profile
    public function store(Request $request)
    {
        $user = Auth::user();
        $creatorId = $request->input('creator_id');
        if (!$creatorId || !Creator::find($creatorId)) {
            return response()->json(['success' => false, 'message' => 'Invalid creator'], 400);
        }
        SavedProfile::firstOrCreate([
            'user_id' => $user->id,
            'creator_id' => $creatorId,
        ]);
        return response()->json(['success' => true, 'data' => []]);
    }

    // Unsave a creator profile
    public function destroy($creatorId)
    {
        $user = Auth::user();
        $deleted = SavedProfile::where('user_id', $user->id)
            ->where('creator_id', $creatorId)
            ->delete();
        return response()->json(['success' => $deleted > 0]);
    }

    // Check if a creator is saved by the authenticated user
    public function show($creatorId)
    {
        $user = Auth::user();
        $exists = \App\Models\SavedProfile::where('user_id', $user->id)
            ->where('creator_id', $creatorId)
            ->exists();
        return response()->json(['success' => true, 'data' => ['saved' => $exists]]);
    }
}
