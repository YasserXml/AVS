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
        Schema::table('pengajuanoprasionals', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->date('tanggal_pending')->nullable()->after('tanggal_pengajuan');
            $table->enum('status', [
                'pengajuan_terkirim',
                'pending_admin_review',
                'diajukan_ke_superadmin',
                'superadmin_approved',
                'superadmin_rejected',
                'pengajuan_dikirim_ke_direksi',
                'pending_direksi',
                'approved_by_direksi',
                'approved_at_direksi',
                'reject_direksi',
                'pengajuan_dikirim_ke_keuangan',
                'pending_keuangan',
                'process_keuangan',
                'execute_keuangan',
                'executed_by_keuangan',
                'executed_at_keuangan',
                'pengajuan_dikirim_ke_pengadaan',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'completed',
                'cancelled'
            ])
                ->default('pengajuan_terkirim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuanoprasionals', function (Blueprint $table) {
            //
        });
    }
};
