<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow engine tables.
 *
 * They live next to the legacy campaign tables (which are left untouched):
 * existing campaigns keep running on `app:campaign` until they are migrated.
 *
 *  workflows               the automation the user edits (status draft/active/paused/archived)
 *  workflow_versions       immutable published graphs; an execution is pinned to one of them
 *  workflow_executions     one run of a workflow for one subject (prospect / order / event)
 *  workflow_node_executions  what happened on each node of an execution (the audit log)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            // draft | active | paused | archived
            $table->string('status', 20)->default('draft');
            // Version used by the NEW executions
            $table->unsignedBigInteger('active_version_id')->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            // Trigger lookup: "active workflows of this project"
            $table->index(['project_id', 'status']);
        });

        Schema::create('workflow_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedInteger('version');
            // { nodes: [{id, type, config, ...}], edges: [{from, to, branch?}] }
            $table->json('graph');
            // Denormalized from the trigger node, to find the workflows
            // of an event with a single indexed query
            $table->string('trigger_type', 60)->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['workflow_id', 'version']);
            $table->index('trigger_type');
        });

        Schema::table('workflows', function (Blueprint $table) {
            $table->foreign('active_version_id')->references('id')->on('workflow_versions')->onDelete('set null');
        });

        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('workflow_id');
            $table->unsignedBigInteger('workflow_version_id');

            // The entity the workflow runs on: prospect | order | event
            $table->string('subject_type', 20);
            $table->unsignedBigInteger('subject_id');

            // pending | running | waiting | completed | failed | cancelled | skipped
            $table->string('status', 20)->default('pending');
            // Node to execute next
            $table->string('current_node_id', 60)->nullable();
            // waiting / pending: not before this date
            $table->timestamp('resume_at')->nullable();
            // running: when the execution was claimed (stuck execution recovery)
            $table->timestamp('locked_at')->nullable();

            // Idempotency: one execution per (workflow, trigger occurrence),
            // whatever the number of times the event is received.
            $table->string('trigger_key', 150);

            $table->json('context')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            $table->foreign('workflow_version_id')->references('id')->on('workflow_versions')->onDelete('cascade');

            $table->unique(['workflow_id', 'trigger_key']);
            // Resume sweep: due waiting / pending executions
            $table->index(['status', 'resume_at']);
            // History of a prospect / order / event
            $table->index(['subject_type', 'subject_id']);
            // Monitoring
            $table->index(['workflow_id', 'status']);
        });

        Schema::create('workflow_node_executions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('execution_id');
            $table->string('node_id', 60);
            $table->string('node_type', 40);
            // running | completed | failed | skipped
            $table->string('status', 20)->default('running');
            // Branch taken by a condition ("yes" / "no")
            $table->string('branch', 20)->nullable();
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->foreign('execution_id')->references('id')->on('workflow_executions')->onDelete('cascade');

            // A node runs at most once per execution (loops are not allowed):
            // this is what makes a retried job safe.
            $table->unique(['execution_id', 'node_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_node_executions');
        Schema::dropIfExists('workflow_executions');

        Schema::table('workflows', function (Blueprint $table) {
            $table->dropForeign(['active_version_id']);
        });

        Schema::dropIfExists('workflow_versions');
        Schema::dropIfExists('workflows');
    }
};
