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
        Schema::create('barangmasuks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('barang_id')->constrained('barangs');
            $table->integer('jumlah_barang_masuk');
            $table->date('tanggal_barang_masuk');
            $table->enum('status', ['oprasional_kantor', 'project']);
            $table->string('dibeli')->nullable();
            $table->string('project_name')->nullable();
            $table->foreignId('kategori_id')->nullable()->constrained('kategoris')->onDelete('cascade');
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
        Schema::dropIfExists('barangmasuks');
    }
};
