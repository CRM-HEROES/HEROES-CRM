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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');

            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('pending')->default(false);
            $table->boolean('processing')->default(false);
            $table->boolean('re_process_processed_prospects')->default(false);
            $table->boolean('error')->default(false);
            $table->string('for', 20)->default('prospect')->index();
            $table->string('frequency', 30)->default('once'); // once, hourly, daily, monthly, ...
            $table->timestamp('date');
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->json('style');
            $table->unsignedInteger('execution')->index()->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_operators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->boolean('and')->default(true);
            $table->json('style');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->enum('type', ['filter', 'count'])->default('filter');
            $table->json('rule');
            $table->json('style');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name')->nullable();
            $table->string('action');
            $table->json('value');
            $table->json('style');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_rule_operator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id');
            $table->unsignedBigInteger('operator_id');
            $table->boolean('error')->default(false);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('rule_id')->references('id')->on('campaign_rules')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('campaign_operators')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_operator_operator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('child_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('campaign_operators')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('campaign_operators')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_operator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('operator_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('operator_id')->references('id')->on('campaign_operators')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_rule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('rule_id');
            $table->boolean('error')->default(false);

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('rule_id')->references('id')->on('campaign_rules')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_action', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('action_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('campaign_actions')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('campaign_prospect', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('action_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->unsignedInteger('execution')->index();
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('campaign_actions')->onDelete('cascade');
        });
        
        Schema::create('campaign_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('order_id');
            $table->boolean('processing')->default(false);
            $table->boolean('error')->default(false);
            $table->unsignedInteger('execution')->index();
            $table->timestamp('begin_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->timestamps();
            
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_order');
        Schema::dropIfExists('campaign_prospect');
        Schema::dropIfExists('campaign_action');
        Schema::dropIfExists('campaign_rule');
        Schema::dropIfExists('campaign_operator');
        Schema::dropIfExists('campaign_operator_operator');
        Schema::dropIfExists('campaign_rule_rule');
        Schema::dropIfExists('campaign_rule_operator');
        Schema::dropIfExists('campaign_actions');
        Schema::dropIfExists('campaign_rules');
        Schema::dropIfExists('campaign_operators');
        Schema::dropIfExists('campaigns');
    }
};
