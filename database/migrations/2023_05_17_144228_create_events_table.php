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
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->string('type', 20)->default('physical')->index();
            $table->string('color', 7)->default('#333333');
            $table->string('bgcolor', 7)->default('#CCCCCC');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->boolean('done')->default(false);
            $table->unsignedBigInteger('done_by')->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('drop_off_location')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('email_to_prospect')->default(0);
            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();
            $table->boolean('valid_address')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('done_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('available_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_event', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'event_id']);
        });

        /**
         * Calendars that every employee 
         * has the right to access
         */
        Schema::create('user_calendar', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('calendar_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'calendar_id']);
        });

        /**
         */
        Schema::create('user_events_daily_directions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->date('direction_at');
            $table->boolean('searching')->default(1);
            $table->json('direction')->nullable();
            $table->json('events')->nullable();
            
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['user_id'/*, 'project_id'*/, 'direction_at'], 'user_event_daily_directions_uniqueness');
        });
        
        Schema::create('event_description_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('body');
            $table->unsignedInteger('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_description_templates');
        Schema::dropIfExists('user_events_daily_directions');
        Schema::dropIfExists('user_calendar');
        Schema::dropIfExists('user_event');
        Schema::dropIfExists('available_events');
        Schema::dropIfExists('events');
        Schema::dropIfExists('calendars');
    }
};
