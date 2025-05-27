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
        Schema::create('barangkeluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->foreignId('pengajuan_id')->constrained('pengajuans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('jumlah_barang_keluar');
            $table->date('tanggal_keluar_barang')->nullable;
            $table->text('keterangan')->nullable();
            $table->string('project_name')->nullable();
            $table->enum('status', ['oprasional_kantor', 'project']);
            $table->foreignId('kategori_id')->nullable()->constrained('kategoris');
             $table->enum('sumber', ['manual', 'pengajuan'])->default('manual');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangkeluars');
    }
};
