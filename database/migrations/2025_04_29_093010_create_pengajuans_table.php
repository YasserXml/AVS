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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('barangs');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('kategoris_id')->constrained('kategoris');
            $table->integer('Jumlah_barang_diajukan');
            $table->enum('status',['pending','approved','rejected'])->default('pending');
            $table->date('tanggal_pengajuan');
            $table->text('keterangan')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('reject_by')->nullable()->constrained('users');
            $table->text('reject_reason')->nullable();
            $table->foreignId('barang_keluar_id')->nullable()->constrained('barangkeluars');
            $table->enum('status_barang',['oprasional_kantor', 'project']);
            $table->string('nama_project')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
