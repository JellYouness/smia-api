<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCreatorsTable extends Migration
{
    public function up(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // Supprimer les anciennes colonnes qui existent
            $columnsToDrop = [
                'specialization',
                'portfolio_url',
                'social_media_links',
                'years_of_experience',
                'availability_status',
                'preferred_industries',
                'project_count',
                'rating',
                'completed_projects',
                'equipment',
                'software',
                'preferred_project_types',
                'working_hours',
                'travel_preference',
                'insurance_info'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('creators', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Ajouter uniquement les nouvelles colonnes qui n'existent pas
            if (!Schema::hasColumn('creators', 'verification_status')) {
                $table->enum('verification_status', ['UNVERIFIED', 'PENDING', 'VERIFIED', 'FEATURED'])->after('skills');
            }
            if (!Schema::hasColumn('creators', 'portfolio')) {
                $table->json('portfolio')->nullable()->after('verification_status');
            }
            if (!Schema::hasColumn('creators', 'experience')) {
                $table->integer('experience')->after('portfolio');
            }
            if (!Schema::hasColumn('creators', 'availability')) {
                $table->enum('availability', ['AVAILABLE', 'LIMITED', 'UNAVAILABLE', 'BUSY'])->after('hourly_rate');
            }
            if (!Schema::hasColumn('creators', 'average_rating')) {
                $table->decimal('average_rating', 2, 1)->nullable()->after('availability');
            }
            if (!Schema::hasColumn('creators', 'rating_count')) {
                $table->integer('rating_count')->default(0)->after('average_rating');
            }
            if (!Schema::hasColumn('creators', 'regional_expertise')) {
                $table->json('regional_expertise')->after('rating_count');
            }
            if (!Schema::hasColumn('creators', 'is_journalist')) {
                $table->boolean('is_journalist')->default(false)->after('languages');
            }
            if (!Schema::hasColumn('creators', 'media_types')) {
                $table->json('media_types')->after('is_journalist');
            }
            if (!Schema::hasColumn('creators', 'certifications')) {
                $table->json('certifications')->nullable()->after('media_types');
            }
            if (!Schema::hasColumn('creators', 'biography')) {
                $table->text('biography')->after('certifications');
            }
            if (!Schema::hasColumn('creators', 'equipment_info')) {
                $table->json('equipment_info')->nullable()->after('biography');
            }
        });
    }

    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            // Supprimer les nouvelles colonnes
            $columnsToDrop = [
                'verification_status',
                'portfolio',
                'experience',
                'availability',
                'average_rating',
                'rating_count',
                'regional_expertise',
                'is_journalist',
                'media_types',
                'certifications',
                'biography',
                'equipment_info'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('creators', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Restaurer les anciennes colonnes
            $table->string('specialization')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->json('social_media_links')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('availability_status')->nullable();
            $table->json('preferred_industries')->nullable();
            $table->integer('project_count')->nullable();
            $table->float('rating')->nullable();
            $table->integer('completed_projects')->nullable();
            $table->json('equipment')->nullable();
            $table->json('software')->nullable();
            $table->json('preferred_project_types')->nullable();
            $table->json('working_hours')->nullable();
            $table->string('travel_preference')->nullable();
            $table->json('insurance_info')->nullable();
        });
    }
}
