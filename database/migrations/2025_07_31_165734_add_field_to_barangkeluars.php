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
        Schema::table('barangkeluars', function (Blueprint $table) {
            $table->foreignId('jenis_id')->nullable()->constrained('jenis')->after('kategori_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangkeluars', function (Blueprint $table) {
            //
        });
    }
};
