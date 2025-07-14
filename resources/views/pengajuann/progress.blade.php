@php
    $percentage = $getState()['percentage'] ?? 0;
    $color = $getState()['color'] ?? 'gray';
    $status = $getState()['status'] ?? '';

    // Definisi semua status dalam urutan workflow yang benar
    $statusFlow = [
        'pengajuan_terkirim' => [
            'label' => 'Pengajuan Terkirim',
            'icon' => '📤',
            'description' => 'Pengajuan berhasil dikirim',
            'percentage' => 5,
        ],
        'pending_admin_review' => [
            'label' => 'Review Admin',
            'icon' => '👀',
            'description' => 'Sedang direview oleh admin',
            'percentage' => 10,
        ],
        'diajukan_ke_superadmin' => [
            'label' => 'Dikirim ke Pengadaan',
            'icon' => '📋',
            'description' => 'Dikirim ke tim pengadaan',
            'percentage' => 15,
        ],
        'superadmin_approved' => [
            'label' => 'Disetujui Pengadaan',
            'icon' => '✅',
            'description' => 'Disetujui oleh tim pengadaan',
            'percentage' => 20,
        ],
        'pengajuan_dikirim_ke_direksi' => [
            'label' => 'Dikirim ke Direksi',
            'icon' => '🏢',
            'description' => 'Dikirim ke direksi',
            'percentage' => 25,
        ],
        'approved_by_direksi' => [
            'label' => 'Disetujui Direksi',
            'icon' => '👔',
            'description' => 'Disetujui oleh direksi',
            'percentage' => 30, //Tingkatkan persentase
        ],
        'pengajuan_dikirim_ke_keuangan' => [
            'label' => 'Dikirim ke Keuangan',
            'icon' => '💰',
            'description' => 'Dikirim ke bagian keuangan',
            'percentage' => 35, //Sesuaikan persentase
        ],
        'pending_keuangan' => [
            'label' => 'Review Keuangan',
            'icon' => '🔍',
            'description' => 'Sedang direview keuangan',
            'percentage' => 40, //Sesuaikan persentase
        ],
        'process_keuangan' => [
            'label' => 'Proses Keuangan',
            'icon' => '⚙️',
            'description' => 'Sedang diproses keuangan',
            'percentage' => 45, //Sesuaikan persentase
        ],
        'execute_keuangan' => [
            'label' => 'Selesai Proses Keuangan',
            'icon' => '💸',
            'description' => 'Proses keuangan selesai',
            'percentage' => 50, //Sesuaikan persentase
        ],
        'pengajuan_dikirim_ke_pengadaan' => [
            'label' => 'Dikirim ke Pengadaan',
            'icon' => '🛒',
            'description' => 'Dikirim ke bagian pengadaan',
            'percentage' => 60, //Sesuaikan persentase
        ],
        'pengajuan_dikirim_ke_admin' => [
            'label' => 'Dikirim ke Admin',
            'icon' => '👤',
            'description' => 'Dikirim kembali ke admin',
            'percentage' => 70,
        ],
        'processing' => [
            'label' => 'Sedang Diproses',
            'icon' => '⚙️',
            'description' => 'Dalam tahap pengerjaan',
            'percentage' => 80,
        ],
        'ready_pickup' => [
            'label' => 'Siap Diambil',
            'icon' => '📦',
            'description' => 'Siap untuk diambil',
            'percentage' => 95,
        ],
        'completed' => [
            'label' => 'Selesai',
            'icon' => '🎉',
            'description' => 'Pengajuan selesai',
            'percentage' => 100,
        ],
    ];

    // Status yang ditolak atau dibatalkan
    $rejectedStatuses = ['superadmin_rejected', 'reject_direksi', 'cancelled'];
    $isRejected = in_array($status, $rejectedStatuses);

    // Tentukan status yang sudah selesai
    $currentStatusIndex = array_search($status, array_keys($statusFlow));
    $currentPercentage = $statusFlow[$status]['percentage'] ?? 0;
    
    // Cek apakah sudah completed (selesai)
    $isCompleted = $status === 'completed';

    // Untuk status yang ditolak, tampilkan persentase berdasarkan di mana penolakan terjadi
    if ($isRejected) {
        if ($status === 'superadmin_rejected') {
            $currentPercentage = 15; // Ditolak di tahap review pengadaan
        } elseif ($status === 'reject_direksi') {
            $currentPercentage = 30; // ✅ PERBAIKAN: Ditolak di tahap direksi (sesuai dengan approved_by_direksi)
        } else {
            $currentPercentage = 0; // Cancelled
        }
    }

    $uniqueId = 'tracking-' . uniqid();
@endphp

<style>
    .shopee-tracking {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 16px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .dark .shopee-tracking {
        background: #1f2937;
        border-color: #374151;
    }

    .tracking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .tracking-header {
        border-bottom-color: #374151;
    }

    .tracking-title {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dark .tracking-title {
        color: #f9fafb;
    }

    .tracking-progress {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #6b7280;
    }

    .progress-bar {
        width: 60px;
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.8s ease;
        border-radius: 2px;
    }

    .progress-fill.rejected {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    .tracking-steps {
        position: relative;
    }

    .tracking-step {
        display: flex;
        align-items: flex-start;
        position: relative;
        padding: 8px 0;
    }

    .step-connector {
        position: absolute;
        left: 15px;
        top: 32px;
        width: 2px;
        height: calc(100% - 8px);
        background: #e5e7eb;
        z-index: 1;
    }

    .step-connector.completed {
        background: #10b981;
    }

    .step-connector.active {
        background: linear-gradient(to bottom, #10b981, #e5e7eb);
    }

    .tracking-step:last-child .step-connector {
        display: none;
    }

    .step-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        margin-right: 12px;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        border: 2px solid #e5e7eb;
        background: #fff;
    }

    .dark .step-icon {
        background: #1f2937;
        border-color: #374151;
    }

    .step-icon.completed {
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    .step-icon.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
        animation: pulse 2s infinite;
    }

    .step-icon.rejected {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
    }

    .step-content {
        flex: 1;
        padding-top: 2px;
    }

    .step-label {
        font-size: 13px;
        font-weight: 500;
        color: #1f2937;
        margin-bottom: 2px;
    }

    .dark .step-label {
        color: #f9fafb;
    }

    .step-label.completed {
        color: #10b981;
    }

    .step-label.active {
        color: #3b82f6;
    }

    .step-label.rejected {
        color: #ef4444;
    }

    .step-description {
        font-size: 11px;
        color: #6b7280;
        line-height: 1.4;
    }

    .dark .step-description {
        color: #9ca3af;
    }

    .step-status {
        font-size: 11px;
        margin-top: 2px;
        font-weight: 500;
    }

    .step-status.completed {
        color: #10b981;
    }

    .step-status.active {
        color: #3b82f6;
    }

    .step-status.rejected {
        color: #ef4444;
    }

    .step-status.pending {
        color: #9ca3af;
    }

    .tracking-footer {
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .dark .tracking-footer {
        border-top-color: #374151;
        color: #9ca3af;
    }

    .rejected-banner {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dark .rejected-banner {
        background: #1f2937;
        border-color: #ef4444;
    }

    .rejected-banner-text {
        font-size: 13px;
        color: #dc2626;
        font-weight: 500;
    }

    .dark .rejected-banner-text {
        color: #ef4444;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    @media (max-width: 640px) {
        .tracking-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .tracking-progress {
            align-self: stretch;
            justify-content: space-between;
        }
    }
</style>

<div class="shopee-tracking">
    <!-- Header -->
    <div class="tracking-header">
        <div class="tracking-title">
            <span>🚚</span>
            <span>Tracking Pengajuan</span>
        </div>
        <div class="tracking-progress">
            @if (!$isRejected)
                <span>{{ $currentPercentage }}% Selesai</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $currentPercentage }}%"></div>
                </div>
            @else
                <span style="color: #ef4444; font-weight: 500;">❌ Ditolak</span>
            @endif
        </div>
    </div>

    @if ($isRejected)
        <!-- Rejected Banner -->
        <div class="rejected-banner">
            <span style="font-size: 16px;">❌</span>
            <div>
                <div class="rejected-banner-text">
                    @if ($status === 'superadmin_rejected')
                        Pengajuan Ditolak oleh Tim Pengadaan
                    @elseif ($status === 'reject_direksi')
                        Pengajuan Ditolak oleh Direksi
                    @else
                        Pengajuan Dibatalkan
                    @endif
                </div>
                <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">
                    Silakan hubungi admin untuk informasi lebih lanjut
                </div>
            </div>
        </div>
    @endif

    <!-- Steps -->
    <div class="tracking-steps">
        @if (!$isRejected)
            @foreach ($statusFlow as $statusKey => $statusInfo)
                @php
                    $itemIndex = array_search($statusKey, array_keys($statusFlow));
                    
                    // Logika: jika status completed, semua step dianggap completed
                    $stepCompleted = $isCompleted || $itemIndex < $currentStatusIndex;
                    $stepActive = !$isCompleted && $statusKey === $status;
                    $stepPending = !$isCompleted && $itemIndex > $currentStatusIndex;
                    $isLast = $statusKey === array_key_last($statusFlow);
                @endphp

                <div class="tracking-step">
                    @if (!$isLast)
                        <div class="step-connector {{ $stepCompleted ? 'completed' : ($stepActive ? 'active' : '') }}">
                        </div>
                    @endif

                    <div class="step-icon {{ $stepCompleted ? 'completed' : ($stepActive ? 'active' : '') }}">
                        @if ($stepCompleted)
                            ✓
                        @else
                            {{ $statusInfo['icon'] }}
                        @endif
                    </div>

                    <div class="step-content">
                        <div class="step-label {{ $stepCompleted ? 'completed' : ($stepActive ? 'active' : '') }}">
                            {{ $statusInfo['label'] }}
                        </div>
                        <div class="step-description">
                            {{ $statusInfo['description'] }}
                        </div>
                        <div class="step-status {{ $stepCompleted ? 'completed' : ($stepActive ? 'active' : 'pending') }}">
                            @if ($stepCompleted)
                                ✓ Selesai
                            @elseif($stepActive)
                                ⏳ Sedang Berlangsung
                            @else
                                ⏸️ Menunggu
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- Single rejected step -->
            <div class="tracking-step">
                <div class="step-icon rejected">
                    ❌
                </div>
                <div class="step-content">
                    <div class="step-label rejected">
                        @if ($status === 'superadmin_rejected')
                            Ditolak oleh Tim Pengadaan
                        @elseif ($status === 'reject_direksi')
                            Ditolak oleh Direksi
                        @else
                            Pengajuan Dibatalkan
                        @endif
                    </div>
                    <div class="step-description">
                        Pengajuan tidak dapat dilanjutkan
                    </div>
                    <div class="step-status rejected">
                        ❌ Ditolak
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="tracking-footer">
        <span>💡</span>
        <span>
            @if ($isRejected)
                Hubungi admin untuk informasi lebih lanjut atau buat pengajuan baru
            @elseif($status === 'completed')
                Pengajuan telah selesai! Terima kasih atas kesabarannya
            @else
                Pengajuan sedang dalam proses. Update akan diberikan secara berkala
            @endif
        </span>
    </div>
</div>