<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->unsignedBigInteger('ai_agent_id')->nullable()->after('project_id');
            $table->foreign('ai_agent_id')->references('id')->on('ai_agents')->nullOnDelete();
            $table->index(['project_id', 'ai_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropForeign(['ai_agent_id']);
            $table->dropIndex(['project_id', 'ai_agent_id']);
            $table->dropColumn('ai_agent_id');
        });
    }
};
