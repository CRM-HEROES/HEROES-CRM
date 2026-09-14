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
        Schema::table('users', function (Blueprint $table) {
            // Change phone_country from string(2) to json to support multiple country codes
            $table->json('phone_country')->nullable()->change();
        });

        // Migrate existing single values to array format
        DB::statement('UPDATE users SET phone_country = JSON_ARRAY(phone_country) WHERE phone_country IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to string - take first element if it exists
            $table->string('phone_country', 2)->nullable()->change();
        });

        // Migrate data back to single string
        DB::statement('UPDATE users SET phone_country = JSON_UNQUOTE(JSON_EXTRACT(phone_country, "$[0]")) WHERE phone_country IS NOT NULL');
    }
};
