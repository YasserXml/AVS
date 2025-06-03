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
        Schema::create('asetpts', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable()->after('id');
            $table->string('nama_barang')->after('tanggal');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->nullOnDelete()->cascadeOnUpdate();
            $table->integer('qty')->after('nama_barang');
            $table->string('brand')->nullable()->after('qty');
            $table->enum('status', ['pengembalian', 'stok'])->default('stok')->after('brand');
            $table->string('pic')->nullable()->after('status');
            $table->enum('kondisi', ['baik', 'rusak'])->default('baik')->after('pic');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asetpts');
    }
};
