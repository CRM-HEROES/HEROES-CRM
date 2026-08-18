<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('job_title');
            $table->unsignedTinyInteger('appetency_score')->nullable()->after('sector');

            $table->boolean('verified_email')->default(false)->after('email');
            $table->boolean('verified_phone')->default(false)->after('mobile_phone_number');
            $table->string('linkedin_url')->nullable()->after('website_url');

            $table->unsignedTinyInteger('archer_score')->nullable()->after('linkedin_url');
            $table->boolean('archer_priority')->default(false)->after('archer_score');
            $table->timestamp('archer_scored_at')->nullable()->after('archer_priority');

            $table->index('sector');
            $table->index('archer_score');
            $table->index('archer_priority');
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            $table->dropIndex(['sector']);
            $table->dropIndex(['archer_score']);
            $table->dropIndex(['archer_priority']);

            $table->dropColumn([
                'sector',
                'appetency_score',
                'verified_email',
                'verified_phone',
                'linkedin_url',
                'archer_score',
                'archer_priority',
                'archer_scored_at',
            ]);
        });
    }
};
