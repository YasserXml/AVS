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
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique();
            $table->integer('kode_barang');
            $table->string('nama_barang');
            $table->json('spesifikasi')->nullable();
            $table->integer('jumlah_barang');
            $table->foreignId('kategori_id')->constrained('kategoris');
            $table->foreignId('subkategori_id')
                ->nullable()
                ->constrained('subkategoris')
                ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
