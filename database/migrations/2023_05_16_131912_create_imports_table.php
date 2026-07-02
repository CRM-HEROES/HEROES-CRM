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
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            
            $table->string('name');
            $table->string('path')->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->string('field_delimiter', 5)->nullable();
            $table->string('field_enclosure', 5)->nullable();

            $table->integer('cols_count')->default(0);
            $table->integer('rows_count')->default(0);

            $table->json('headers')->nullable(); // An array of the headers of each columns
            $table->json('values')->nullable(); // An array of the first values of each columns
            $table->json('mapping')->nullable(); // An array mapping each Excel column to a field attribute

            $table->json('projects')->nullable(); // List of projects to assign each prospect to
            $table->json('labels')->nullable(); // List of labels to assign each prospect to
            $table->json('users')->nullable(); // List of users to assign each prospect to
            $table->json('groups')->nullable(); // List of groups to assign each prospect to
            
            $table->json('duplicates_fields')->nullable();
            
            $table->boolean('is_processing')->default(0); // A boolean to know if an import is being processed
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->string('source')->default('file');
            $table->string('token')->nullable();
            
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('imports');
    }
};
