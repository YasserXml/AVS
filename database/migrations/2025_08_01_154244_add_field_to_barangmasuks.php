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
            $table->foreignId('subkategori_id')
                ->nullable()
                ->constrained('subkategoris')
                ->onDelete('set null'); // Menambahkan kolom subkategori_id setelah kategori_id
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
