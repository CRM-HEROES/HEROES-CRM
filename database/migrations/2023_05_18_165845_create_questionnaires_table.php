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
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->boolean('private')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('questionnaire_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('questionnaire_id');
            $table->string('name', 500);
            $table->integer('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('section_id');
            $table->string('name', 500);
            $table->string('type', 20);
            $table->boolean('is_required')->default(0);
            $table->integer('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('questionnaire_sections')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('questionnaire_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('redirection_id')->nullable();
            $table->string('name');
            $table->boolean('pre_checked')->default(0);
            $table->integer('order')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('questionnaire_questions')->onDelete('cascade');
            $table->foreign('redirection_id')->references('id')->on('questionnaire_questions')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('prospect_questionnaire', function (Blueprint $table) {
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('questionnaire_id');
            // Current question
            $table->unsignedBigInteger('question_id')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questionnaire_questions')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['prospect_id', 'questionnaire_id']);
        });
        
        Schema::create('prospect_questionnaire_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prospect_id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->string('response');
            $table->unsignedInteger('size')->default(0);
            $table->string('from_user')->default(1);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questionnaire_questions')->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('questionnaire_options')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_questionnaire', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('questionnaire_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('questionnaire_id')->references('id')->on('questionnaires')->onDelete('cascade');
            
            $table->unique(['user_id', 'questionnaire_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_questionnaire');
        Schema::dropIfExists('prospect_questionnaire_responses');
        Schema::dropIfExists('prospect_questionnaire');
        Schema::dropIfExists('questionnaire_options');
        Schema::dropIfExists('questionnaire_questions');
        Schema::dropIfExists('questionnaire_sections');
        Schema::dropIfExists('questionnaires');
    }
};
