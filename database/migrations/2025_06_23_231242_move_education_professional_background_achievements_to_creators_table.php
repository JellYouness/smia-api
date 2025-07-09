<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add the columns to the creators table
        Schema::table('creators', function (Blueprint $table) {
            // Education as JSON array of objects
            $table->json('education')->nullable()->after('equipment_info');

            // Professional background as JSON array of objects
            $table->json('professional_background')->nullable()->after('education');

            // Achievements as JSON array
            $table->json('achievements')->nullable()->after('professional_background');
        });

        // Now migrate the data from user_profiles to creators
        $userProfiles = DB::table('user_profiles')
            ->whereNotNull('education')
            ->orWhereNotNull('professional_background')
            ->orWhereNotNull('achievements')
            ->get();

        foreach ($userProfiles as $profile) {
            // Find the corresponding creator record
            $creator = DB::table('creators')->where('user_id', $profile->user_id)->first();

            if ($creator) {
                $updateData = [];

                // Migrate education data
                if (!empty($profile->education)) {
                    $updateData['education'] = $profile->education;
                }

                // Migrate professional background data
                if (!empty($profile->professional_background)) {
                    $updateData['professional_background'] = $profile->professional_background;
                }

                // Migrate achievements data
                if (!empty($profile->achievements)) {
                    $updateData['achievements'] = $profile->achievements;
                }

                // Update the creator record
                if (!empty($updateData)) {
                    DB::table('creators')
                        ->where('id', $creator->id)
                        ->update($updateData);
                }
            }
        }

        // Now remove the columns from user_profiles table
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['education', 'professional_background', 'achievements']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, add the columns back to user_profiles table
        Schema::table('user_profiles', function (Blueprint $table) {
            // Education as JSON array of objects
            $table->json('education')->nullable()->after('contact_phone');

            // Professional background as JSON array of objects
            $table->json('professional_background')->nullable()->after('education');

            // Achievements as JSON array
            $table->json('achievements')->nullable()->after('professional_background');
        });

        // Migrate data back from creators to user_profiles
        $creators = DB::table('creators')
            ->whereNotNull('education')
            ->orWhereNotNull('professional_background')
            ->orWhereNotNull('achievements')
            ->get();

        foreach ($creators as $creator) {
            $updateData = [];

            // Migrate education data back
            if (!empty($creator->education)) {
                $updateData['education'] = $creator->education;
            }

            // Migrate professional background data back
            if (!empty($creator->professional_background)) {
                $updateData['professional_background'] = $creator->professional_background;
            }

            // Migrate achievements data back
            if (!empty($creator->achievements)) {
                $updateData['achievements'] = $creator->achievements;
            }

            // Update the user_profile record
            if (!empty($updateData)) {
                DB::table('user_profiles')
                    ->where('user_id', $creator->user_id)
                    ->update($updateData);
            }
        }

        // Remove the columns from creators table
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn(['education', 'professional_background', 'achievements']);
        });
    }
};
