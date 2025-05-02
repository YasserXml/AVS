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
        Schema::table('pengajuans', function (Blueprint $table) {
            $table->foreignId('barang_id')->constrained('barangs');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('kategoris_id')->constrained('kategoris');
            $table->foreignId('approved_by')->constrained('users');
            $table->foreignId('reject_by')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuans', function (Blueprint $table) {
            //
        });
    }
};
