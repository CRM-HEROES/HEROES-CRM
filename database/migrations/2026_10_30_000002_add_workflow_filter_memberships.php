<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Differential detection of "enters / leaves a filter" triggers.
 *
 * A row means: this subject matches the filter of this workflow. The absence
 * of a row means it does not. Only the matching subjects are stored, and only
 * a transition (row added / removed) fires the trigger, atomically: the one
 * that inserts / deletes the row owns the transition.
 *
 * Workflow versions get the date their memberships were last initialised
 * (see BaselineFilterMemberships): until then the filter triggers of the
 * version are not evaluated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_filter_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->string('subject_type', 20);
            $table->unsignedBigInteger('subject_id');
            $table->timestamp('created_at')->nullable();

            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');

            $table->unique(['workflow_id', 'subject_type', 'subject_id'], 'workflow_filter_memberships_unique');
        });

        Schema::table('workflow_versions', function (Blueprint $table) {
            $table->timestamp('baselined_at')->nullable()->after('trigger_type');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_versions', function (Blueprint $table) {
            $table->dropColumn('baselined_at');
        });

        Schema::dropIfExists('workflow_filter_memberships');
    }
};
