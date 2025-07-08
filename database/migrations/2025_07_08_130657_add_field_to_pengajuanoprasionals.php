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
            $table->enum('status', [
                'pengajuan_terkirim',
                'pending_admin_review',
                'diajukan_ke_superadmin',
                'superadmin_approved',
                'superadmin_rejected',
                'pengajuan_dikirim_ke_admin',
                'processing',
                'ready_pickup',
                'completed',
                'cancelled'
            ])
                ->default('pengajuan_terkirim');
        });
    }
    
    public function down(): void
    {
        Schema::table('pengajuanoprasionals', function (Blueprint $table) {
            //
        });
    }
};
