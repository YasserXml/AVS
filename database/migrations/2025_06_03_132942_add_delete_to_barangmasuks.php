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
        Schema::table('barangmasuks', function (Blueprint $table) {
            $table->dropColumn('project_name');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangmasuks', function (Blueprint $table) {
            //
        });
    }
};
