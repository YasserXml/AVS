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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_keluar_barang')->nullable();
            $table->integer('jumlah_barang_keluar');
            $table->text('keterangan')->nullable();
            $table->string('project_name')->nullable();
            $table->enum('status', ['oprasional_kantor', 'project']);
            $table->foreignId('kategori_id')->nullable()->constrained('kategoris');
            $table->enum('sumber', ['manual', 'pengajuan'])->default('manual');
            $table->foreignId('subkategori_id')
                ->nullable()
                ->constrained('subkategoris')
                ->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangkeluars');
    }
};
