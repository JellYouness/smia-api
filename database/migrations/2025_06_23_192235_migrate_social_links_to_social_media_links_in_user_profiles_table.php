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
        // First, migrate the data from social_links to social_media_links
        $userProfiles = DB::table('user_profiles')->whereNotNull('social_links')->get();

        foreach ($userProfiles as $profile) {
            $socialLinks = json_decode($profile->social_links, true);

            if ($socialLinks && is_array($socialLinks)) {
                // Convert social_links format to social_media_links format
                $socialMediaLinks = [];

                // Map common social media platforms
                if (isset($socialLinks['linkedin'])) {
                    $socialMediaLinks['linkedin'] = $socialLinks['linkedin'];
                }
                if (isset($socialLinks['twitter'])) {
                    $socialMediaLinks['twitter'] = $socialLinks['twitter'];
                }
                if (isset($socialLinks['facebook'])) {
                    $socialMediaLinks['facebook'] = $socialLinks['facebook'];
                }
                if (isset($socialLinks['instagram'])) {
                    $socialMediaLinks['instagram'] = $socialLinks['instagram'];
                }
                if (isset($socialLinks['youtube'])) {
                    $socialMediaLinks['youtube'] = $socialLinks['youtube'];
                }
                if (isset($socialLinks['tiktok'])) {
                    $socialMediaLinks['tiktok'] = $socialLinks['tiktok'];
                }
                if (isset($socialLinks['website'])) {
                    $socialMediaLinks['website'] = $socialLinks['website'];
                }

                // If there are any other social links, preserve them
                foreach ($socialLinks as $key => $value) {
                    if (!in_array($key, ['linkedin', 'twitter', 'facebook', 'instagram', 'youtube', 'tiktok', 'website'])) {
                        $socialMediaLinks[$key] = $value;
                    }
                }

                // Update the social_media_links column
                DB::table('user_profiles')
                    ->where('id', $profile->id)
                    ->update(['social_media_links' => json_encode($socialMediaLinks)]);
            }
        }

        // Now remove the social_links column
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn('social_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the social_links column
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->json('social_links')->nullable()->after('contact_phone');
        });

        // Migrate data back from social_media_links to social_links
        $userProfiles = DB::table('user_profiles')->whereNotNull('social_media_links')->get();

        foreach ($userProfiles as $profile) {
            $socialMediaLinks = json_decode($profile->social_media_links, true);

            if ($socialMediaLinks && is_array($socialMediaLinks)) {
                // Convert back to social_links format
                $socialLinks = [];

                // Map back to social_links format
                if (isset($socialMediaLinks['linkedin'])) {
                    $socialLinks['linkedin'] = $socialMediaLinks['linkedin'];
                }
                if (isset($socialMediaLinks['twitter'])) {
                    $socialLinks['twitter'] = $socialMediaLinks['twitter'];
                }
                if (isset($socialMediaLinks['facebook'])) {
                    $socialLinks['facebook'] = $socialMediaLinks['facebook'];
                }
                if (isset($socialMediaLinks['instagram'])) {
                    $socialLinks['instagram'] = $socialMediaLinks['instagram'];
                }
                if (isset($socialMediaLinks['youtube'])) {
                    $socialLinks['youtube'] = $socialMediaLinks['youtube'];
                }
                if (isset($socialMediaLinks['tiktok'])) {
                    $socialLinks['tiktok'] = $socialMediaLinks['tiktok'];
                }
                if (isset($socialMediaLinks['website'])) {
                    $socialLinks['website'] = $socialMediaLinks['website'];
                }

                // Preserve any other social media links
                foreach ($socialMediaLinks as $key => $value) {
                    if (!in_array($key, ['linkedin', 'twitter', 'facebook', 'instagram', 'youtube', 'tiktok', 'website'])) {
                        $socialLinks[$key] = $value;
                    }
                }

                // Update the social_links column
                DB::table('user_profiles')
                    ->where('id', $profile->id)
                    ->update(['social_links' => json_encode($socialLinks)]);
            }
        }
    }
};
