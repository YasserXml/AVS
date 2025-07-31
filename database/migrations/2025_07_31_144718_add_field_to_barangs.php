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
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
            // Serial number hanya wajib unik untuk item individual (bukan master)
            $table->unique(['serial_number'], 'unique_serial_individual');

            $table->string('serial_number')->nullable()->change();

            // Tambah kolom untuk membedakan master dan individual item
            $table->enum('tipe_barang', ['master', 'individual'])->default('master');

            // Untuk item individual, referensi ke master barang
            $table->foreignId('parent_barang_id')->nullable()->constrained('barangs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            //
        });
    }
};
