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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->string('for')->default('prospect');
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('relevance')->default(0);
            $table->unsignedBigInteger('folder_id')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('name');
            $table->unsignedInteger('pages_count')->default(0);
            $table->string('path');
            $table->unsignedInteger('size')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('document_fonts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->string('name');
            $table->string('path');
            $table->unsignedInteger('size')->default(0);
            $table->enum('style', ['normal', 'italic', 'oblique'])->default("normal");
            $table->enum('weight', ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'])->default("normal");
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedInteger('page')->default(0);
            $table->unsignedInteger('file_page')->default(0);
            $table->string('width')->default("210.016mm");
            $table->string('height')->default("297.011mm");
            $table->text('description')->nullable();
            $table->enum('orientation', ['P', 'L'])->default('P');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('document_files')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('document_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('type');
            $table->mediumText('content')->nullable();
            $table->json('style');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('document_pages')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('document_page_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('option_id');

            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('page_id')->references('id')->on('document_pages')->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('questionnaire_options')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('user_document', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('document_id');
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->unique(['user_id', 'document_id']);
        });
        
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('order_id');
            $table->string('docusign_envelope_id')->nullable();
            $table->unsignedBigInteger('signer_id')->nullable();
            $table->string('state')->default('generated');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('signer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
        
        Schema::create('prospect_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('prospect_id');
            $table->string('docusign_envelope_id')->nullable();
            $table->unsignedBigInteger('signer_id')->nullable();
            $table->string('state')->default('generated');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->timestamps();
            
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('prospect_id')->references('id')->on('prospects')->onDelete('cascade');
            $table->foreign('signer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospect_documents');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('user_document');
        Schema::dropIfExists('document_page_rules');
        Schema::dropIfExists('document_fields');
        Schema::dropIfExists('document_pages');
        Schema::dropIfExists('document_fonts');
        Schema::dropIfExists('document_files');
        Schema::dropIfExists('documents');
    }
};
