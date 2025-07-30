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
        Schema::create('pengajuanprojects', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique();
            $table->string('batch_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_dibutuhkan')->nullable();
            $table->json('detail_barang')->nullable();
            $table->json('uploaded_files')->nullable();
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
            $table->date('tanggal_pending')->nullable();
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
            $table->text('catatan')->nullable();
            $table->foreignId('project_id')->constrained('nameprojects');
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
        Schema::dropIfExists('pengajuanprojects');
    }
};
