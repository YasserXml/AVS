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
        Schema::create('pengajuanoprasionals', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->string('batch_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_pending')->nullable();
            $table->date('tanggal_dibutuhkan')->nullable();
            $table->json('detail_barang')->nullable();
            $table->json('uploaded_files')->nullable();
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
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->dateTime('rejected_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->string('received_by_name')->nullable();
            $table->json('status_history')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index(['status']);
            $table->index('tanggal_pengajuan');
            $table->index('tanggal_dibutuhkan');
            $table->index('nomor_pengajuan');
            $table->index(['rejected_by', 'rejected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuanoprasionals');
    }
};
