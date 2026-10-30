<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link between a workflow and the legacy campaign it was converted from.
 *
 * It makes the conversion idempotent (a campaign is converted once) and
 * keeps the origin visible. No foreign key: the campaign is soft-deleted and
 * may disappear, the workflow must not depend on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_campaign_id')->nullable()->unique()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropUnique(['legacy_campaign_id']);
            $table->dropColumn('legacy_campaign_id');
        });
    }
};
