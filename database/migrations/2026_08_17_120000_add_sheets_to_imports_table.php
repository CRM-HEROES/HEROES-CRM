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
        Schema::table('imports', function (Blueprint $table) {
            // Every sheet/tab name found in the workbook (e.g. Google
            // Sheets tabs), detected once when the file is uploaded.
            $table->json('sheets')->nullable()->after('headers');
            // The subset of "sheets" the user chose to actually import.
            // Null/empty means "import every sheet" (backward compatible
            // default for imports created before this feature).
            $table->json('selected_sheets')->nullable()->after('sheets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn(['sheets', 'selected_sheets']);
        });
    }
};
