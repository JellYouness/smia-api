<?php

use App\Enums\PROJECT_STATUS;
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
        // First, let's check what the current enum values are
        $currentEnum = DB::select("SHOW COLUMNS FROM projects LIKE 'status'")[0];
        $currentValues = str_replace(['enum(', ')'], '', $currentEnum->Type);
        $currentValues = str_replace("'", '', $currentValues);
        $currentValuesArray = explode(',', $currentValues);

        // Get the expected enum values
        $expectedValues = array_map(fn($status) => $status->value, PROJECT_STATUS::cases());

        // If the values don't match, we need to update the column
        if (array_diff($currentValuesArray, $expectedValues) !== [] || array_diff($expectedValues, $currentValuesArray) !== []) {
            // Convert the column to string first
            DB::statement('ALTER TABLE projects MODIFY COLUMN status VARCHAR(20)');

            // Then convert it back to enum with the correct values
            $enumValues = "'" . implode("','", $expectedValues) . "'";
            DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM($enumValues) DEFAULT 'in_progress'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's fixing data integrity
    }
};
