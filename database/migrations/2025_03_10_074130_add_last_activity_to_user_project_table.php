<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('user_project', 'last_activity')) {
            Schema::table('user_project', function (Blueprint $table) {
                $table->timestamp('last_activity')->nullable()->after('prospects_count');
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_project', 'last_activity')) {
            Schema::table('user_project', function (Blueprint $table) {
                $table->dropColumn('last_activity');
            });
        }
    }
};
