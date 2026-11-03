<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * AI agents platform tables.
 *
 * An agent belongs to a user and carries its own configuration (persona,
 * instructions, skills, model). It never has rights of its own: a run acts as
 * the owner, inside ONE project (see App\Agents\AgentExecutionContext).
 *
 *  agents / agent_versions    the configuration, published as immutable versions
 *  agent_skills / agent_tool_grants   what the agent may use
 *  agent_conversations / agent_messages   the exchanges (append only: the blocks
 *                             of the API are kept as they were returned)
 *  agent_runs / agent_run_steps   one run and its audit log (model + tool calls)
 *  agent_actions              the writes waiting for the approval of a human
 *  agent_memories             what an agent remembers (visible, editable by the user)
 *  agent_reports              reports produced by an agent
 *  agent_schedules            missions run on a cron cadence
 *  agent_usage_daily          tokens and cost per user, project and day
 *  agent_kb_chunks            the documentation of the CRM an agent can search
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // null: usable in every project of the owner
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('specialty', 100)->nullable();
            $table->text('persona')->nullable();
            // System prompt written by the user
            $table->mediumText('instructions')->nullable();
            $table->text('objectives')->nullable();
            $table->string('model', 50);
            $table->string('effort', 10)->default('medium');
            // read_only | propose | auto
            $table->string('autonomy', 20)->default('read_only');
            $table->json('settings')->nullable();
            // active | disabled
            $table->string('status', 20)->default('active');
            // Version used by the NEW runs
            $table->unsignedBigInteger('active_version_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->index(['user_id', 'status']);
        });

        Schema::create('agent_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedInteger('version');
            // Snapshot of the configuration (persona, instructions, skills, grants, model, ...)
            $table->json('config');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['agent_id', 'version']);
        });

        Schema::table('agents', function (Blueprint $table) {
            $table->foreign('active_version_id')->references('id')->on('agent_versions')->onDelete('set null');
        });

        Schema::create('agent_skills', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id');
            // Skills are declared in the code base, not in the database
            $table->string('skill_key', 60);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');

            $table->unique(['agent_id', 'skill_key']);
        });

        Schema::create('agent_tool_grants', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id');
            $table->string('tool_key', 80);
            // allow | deny (deny always wins over the skills)
            $table->string('access', 10)->default('allow');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');

            $table->unique(['agent_id', 'tool_key']);
        });

        Schema::create('agent_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('user_id');
            // A conversation always happens in one project
            $table->unsignedBigInteger('project_id');
            $table->string('title', 150)->nullable();
            // open | archived
            $table->string('status', 20)->default('open');
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->index(['user_id', 'project_id', 'last_message_at'], 'agent_conversations_listing_index');
        });

        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            // null: a run started by a schedule or a workflow
            $table->unsignedBigInteger('conversation_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('agent_version_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            // user | schedule | workflow
            $table->string('trigger', 20)->default('user');
            // pending | running | waiting_approval | completed | failed | cancelled
            $table->string('status', 20)->default('pending');
            $table->string('model', 50);
            $table->unsignedSmallInteger('iterations')->default(0);
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('cache_read_tokens')->default(0);
            $table->unsignedBigInteger('cache_write_tokens')->default(0);
            // Millionths of the billing currency
            $table->unsignedBigInteger('cost_micro')->default(0);
            // running: when the run was claimed (stuck run recovery)
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('agent_conversations')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('agent_version_id')->references('id')->on('agent_versions')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Stuck run sweep
            $table->index(['status', 'locked_at']);
            $table->index(['project_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('agent_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('run_id')->nullable();
            // user | assistant
            $table->string('role', 12);
            // Content blocks exactly as the API returned them (thinking blocks
            // included): the history is replayed unchanged, never edited
            $table->json('content');
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('agent_conversations')->onDelete('cascade');
            $table->foreign('run_id')->references('id')->on('agent_runs')->onDelete('set null');

            $table->index(['conversation_id', 'id']);
        });

        Schema::create('agent_run_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedSmallInteger('seq');
            // model_call | tool_call
            $table->string('type', 20);
            $table->string('tool_key', 80)->nullable();
            $table->string('tool_use_id', 80)->nullable();
            // running | completed | failed | denied | pending_approval
            $table->string('status', 20)->default('running');
            // allow | approve | deny
            $table->string('permission_decision', 10)->nullable();
            // Redacted input, never a secret
            $table->json('input')->nullable();
            $table->text('output_summary')->nullable();
            $table->unsignedInteger('rows')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('run_id')->references('id')->on('agent_runs')->onDelete('cascade');

            $table->unique(['run_id', 'seq']);
            // A tool call runs at most once per run: this is what makes a
            // retried job safe (same guarantee as the workflow nodes)
            $table->unique(['run_id', 'tool_use_id']);
        });

        Schema::create('agent_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('run_id');
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->string('tool_key', 80);
            $table->string('tool_use_id', 80);
            // write | communication | destructive
            $table->string('risk', 15);
            $table->json('payload');
            // What will change, computed by the server (not written by the model)
            $table->json('preview')->nullable();
            // pending | approved | rejected | executed | failed | expired
            $table->string('status', 15)->default('pending');
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('run_id')->references('id')->on('agent_runs')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('decided_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['run_id', 'tool_use_id']);
            $table->index(['project_id', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('agent_memories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id')->nullable();
            // preference | fact | decision
            $table->string('kind', 20)->default('fact');
            $table->text('content');
            // user | agent
            $table->string('origin', 10)->default('user');
            $table->unsignedBigInteger('source_run_id')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('source_run_id')->references('id')->on('agent_runs')->onDelete('set null');

            $table->index(['agent_id', 'project_id']);
        });

        Schema::create('agent_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('run_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->string('title', 200);
            // markdown | json
            $table->string('format', 10)->default('markdown');
            $table->longText('content');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('run_id')->references('id')->on('agent_runs')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->index(['project_id', 'created_at']);
        });

        Schema::create('agent_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->string('name', 150);
            // The mission given to the agent at each firing
            $table->text('prompt');
            $table->string('cron', 60);
            $table->string('timezone', 40)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            // Sweep of the schedules that are due
            $table->index(['enabled', 'next_run_at']);
        });

        Schema::create('agent_usage_daily', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');
            $table->date('date');
            $table->unsignedInteger('runs')->default(0);
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('cache_read_tokens')->default(0);
            $table->unsignedBigInteger('cost_micro')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');

            $table->unique(['user_id', 'project_id', 'date'], 'agent_usage_daily_uniqueness');
        });

        Schema::create('agent_kb_chunks', function (Blueprint $table) {
            $table->id();
            $table->string('module', 60)->index();
            // Where the passage comes from (schema, routes, permissions, a document)
            $table->string('source', 150);
            $table->string('title', 200);
            $table->text('content');
            // Hash of the content: unchanged passages are not rewritten by agents:sync-knowledge
            $table->char('hash', 40);
            $table->string('version', 40)->nullable();
            $table->timestamps();

            $table->unique(['source', 'hash']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('agent_kb_chunks', function (Blueprint $table) {
                $table->fullText(['title', 'content'], 'agent_kb_chunks_fulltext');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_kb_chunks');
        Schema::dropIfExists('agent_usage_daily');
        Schema::dropIfExists('agent_schedules');
        Schema::dropIfExists('agent_reports');
        Schema::dropIfExists('agent_memories');
        Schema::dropIfExists('agent_actions');
        Schema::dropIfExists('agent_run_steps');
        Schema::dropIfExists('agent_messages');
        Schema::dropIfExists('agent_runs');
        Schema::dropIfExists('agent_conversations');
        Schema::dropIfExists('agent_tool_grants');
        Schema::dropIfExists('agent_skills');

        Schema::table('agents', function (Blueprint $table) {
            $table->dropForeign(['active_version_id']);
        });

        Schema::dropIfExists('agent_versions');
        Schema::dropIfExists('agents');
    }
};
