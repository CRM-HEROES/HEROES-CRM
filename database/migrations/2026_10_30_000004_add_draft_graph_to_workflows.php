<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the workflow builder is editing.
 *
 * The versions (workflow_versions) are immutable and only exist once a
 * workflow was published and valid. The builder needs somewhere to save the
 * work in progress, however incomplete: that is the draft graph. Publishing
 * validates it and turns it into the next version.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->json('draft_graph')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn('draft_graph');
        });
    }
};
