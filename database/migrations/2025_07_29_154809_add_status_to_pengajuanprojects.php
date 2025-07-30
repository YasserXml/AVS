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
        Schema::table('pengajuanprojects', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum('status', [
                'pengajuan_terkirim',
                'pending_pm_review',
                'disetujui_pm_dikirim_ke_pengadaan',    
                'ditolak_pm',
                'disetujui_pengadaan',
                'ditolak_pengadaan',
                'pengajuan_dikirim_ke_direksi',
                'pending_direksi',
                'approved_by_direksi',
                'reject_direksi',
                'pengajuan_dikirim_ke_keuangan',
                'pending_keuangan',
                'process_keuangan',
                'execute_keuangan',
                'pengajuan_dikirim_ke_pengadaan_final',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'completed'
            ])
                ->default('pengajuan_terkirim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuanprojects', function (Blueprint $table) {
            //
        });
    }
};
