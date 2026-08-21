<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "duplicate_id" only ever points from the earlier-created prospect of a
 * matching pair to the later one (see CheckDuplicatedProspects) — by
 * design, so the duplicate-management panel lists each pair once instead
 * of twice. That leaves it unable to represent "every prospect sharing
 * this same email", nor to tell two unrelated duplicate clusters apart.
 * duplicate_group_id instead holds the same value (the lowest id in the
 * cluster) for every prospect sharing the matched value(s) — letting the
 * table both flag every one of them and give each cluster its own color,
 * without touching duplicate_id or the panel that relies on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->unsignedBigInteger('duplicate_group_id')->nullable()->after('duplicate_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn('duplicate_group_id');
        });
    }
};
