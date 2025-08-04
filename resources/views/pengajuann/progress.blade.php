{{-- resource/views/pengajuann/progress.blade.php--}}
@php
    $percentage = $getState()['percentage'] ?? 0;
    $color = $getState()['color'] ?? 'gray';
    $status = $getState()['status'] ?? '';
    $statusHistory = $getState()['status_history'] ?? [];
    $record = $getState()['record'] ?? null;

    // Definisi semua status dalam urutan workflow yang benar
    $statusFlow = [
        'pengajuan_terkirim' => [
            'label' => 'Pengajuan Terkirim',
            'icon' => '📤',
            'description' => 'Pengajuan berhasil dikirim',
            'percentage' => 5,
            'actor' => 'User',
        ],
        'pending_admin_review' => [
            'label' => 'Review Admin',
            'icon' => '👀',
            'description' => 'Sedang direview oleh admin',
            'percentage' => 10,
            'actor' => 'Admin',
        ],
        'diajukan_ke_superadmin' => [
            'label' => 'Dikirim ke Pengadaan',
            'icon' => '📋',
            'description' => 'Dikirim ke tim pengadaan',
            'percentage' => 15,
            'actor' => 'Admin',
        ],
        'superadmin_approved' => [
            'label' => 'Disetujui Pengadaan',
            'icon' => '✅',
            'description' => 'Disetujui oleh tim pengadaan',
            'percentage' => 20,
            'actor' => 'Pengadaan',
        ],
        'pengajuan_dikirim_ke_direksi' => [
            'label' => 'Dikirim ke Direksi',
            'icon' => '🏢',
            'description' => 'Dikirim ke direksi',
            'percentage' => 25,
            'actor' => 'Pengadaan',
        ],
        'pending_direksi' => [
            'label' => 'Pending Direksi',
            'icon' => '👔',
            'description' => 'Pengajuan dipending oleh direksi hingga tanggal tertentu',
            'percentage' => 30,
            'actor' => 'Direktur Keuangan',
        ],
        'approved_by_direksi' => [
            'label' => 'Disetujui Direksi',
            'icon' => '👔',
            'description' => 'Disetujui oleh direksi',
            'percentage' => 35,
            'actor' => 'Direktur Keuangan',
        ],
        'pengajuan_dikirim_ke_keuangan' => [
            'label' => 'Dikirim ke Keuangan',
            'icon' => '💰',
            'description' => 'Dikirim ke bagian keuangan',
            'percentage' => 40,
            'actor' => 'Direktur Keuangan',
        ],
        'pending_keuangan' => [
            'label' => 'Review Keuangan',
            'icon' => '🔍',
            'description' => 'Sedang direview keuangan',
            'percentage' => 45,
            'actor' => 'Keuangan',
        ],
        'process_keuangan' => [
            'label' => 'Proses Keuangan',
            'icon' => '⚙️',
            'description' => 'Sedang diproses keuangan',
            'percentage' => 50,
            'actor' => 'Keuangan',
        ],
        'execute_keuangan' => [
            'label' => 'Selesai Proses Keuangan',
            'icon' => '💸',
            'description' => 'Proses keuangan selesai',
            'percentage' => 55,
            'actor' => 'Keuangan',
        ],
        'pengajuan_dikirim_ke_pengadaan' => [
            'label' => 'Dikirim ke Pengadaan',
            'icon' => '🛒',
            'description' => 'Dikirim ke bagian pengadaan',
            'percentage' => 65,
            'actor' => 'Keuangan',
        ],
        'pengajuan_dikirim_ke_admin' => [
            'label' => 'Dikirim ke Admin',
            'icon' => '👤',
            'description' => 'Dikirim kembali ke admin',
            'percentage' => 70,
            'actor' => 'Pengadaan',
        ],
        'processing' => [
            'label' => 'Sedang Diproses',
            'icon' => '⚙️',
            'description' => 'Dalam tahap pengerjaan',
            'percentage' => 80,
            'actor' => 'Admin',
        ],
        'ready_pickup' => [
            'label' => 'Siap Diambil',
            'icon' => '📦',
            'description' => 'Siap untuk diambil',
            'percentage' => 95,
            'actor' => 'Admin',
        ],
        'completed' => [
            'label' => 'Selesai',
            'icon' => '🎉',
            'description' => 'Pengajuan selesai',
            'percentage' => 100,
            'actor' => 'Admin',
        ],
    ];

    // Status yang ditolak atau dibatalkan
    $rejectedStatuses = ['superadmin_rejected', 'reject_direksi', 'cancelled'];
    $isRejected = in_array($status, $rejectedStatuses);

    // Dapatkan status history yang sudah terjadi dari database
    $completedStatuses = [];
    if ($statusHistory && is_array($statusHistory)) {
        foreach ($statusHistory as $history) {
            if (isset($history['status'])) {
                $completedStatuses[] = $history['status'];
            }
        }
    }

    // PENTING: Pastikan status saat ini juga ada di completedStatuses
    if (!empty($status) && !in_array($status, $completedStatuses)) {
        $completedStatuses[] = $status;
    }

    // Analisis jalur yang diambil untuk direksi
    $hasPendingDireksi = in_array('pending_direksi', $completedStatuses);
    $hasApprovedDireksi = in_array('approved_by_direksi', $completedStatuses);
    $hasKeuangan = in_array('pengajuan_dikirim_ke_keuangan', $completedStatuses);

    // Tentukan jalur direksi yang diambil
    $direksiPath = 'none';
    if ($status === 'pending_direksi') {
        $direksiPath = 'pending';
    } elseif ($status === 'approved_by_direksi') {
        $direksiPath = 'approved';
    } elseif ($hasKeuangan) {
        if ($hasPendingDireksi && !$hasApprovedDireksi) {
            $direksiPath = 'pending';
        } elseif ($hasApprovedDireksi && !$hasPendingDireksi) {
            $direksiPath = 'approved';
        }
    } elseif ($hasPendingDireksi) {
        $direksiPath = 'pending';
    } elseif ($hasApprovedDireksi) {
        $direksiPath = 'approved';
    }

    // Buat array step yang akan ditampilkan (sama seperti track.blade.php)
    $visibleSteps = [];

    if ($isRejected) {
        $visibleSteps = $completedStatuses;
    } else {
        $alwaysVisibleSteps = [
            'pengajuan_terkirim',
            'pending_admin_review',
            'diajukan_ke_superadmin',
            'superadmin_approved',
            'pengajuan_dikirim_ke_direksi',
        ];

        $afterDireksiSteps = [
            'pengajuan_dikirim_ke_keuangan',
            'pending_keuangan',
            'process_keuangan',
            'execute_keuangan',
            'pengajuan_dikirim_ke_pengadaan',
            'pengajuan_dikirim_ke_admin',
            'processing',
            'ready_pickup',
            'completed',
        ];

        $statusKeys = array_keys($statusFlow);
        $currentStatusIndex = array_search($status, $statusKeys);

        foreach ($statusKeys as $index => $statusKey) {
            $shouldShow = false;

            // Step sebelum direksi
            if (in_array($statusKey, $alwaysVisibleSteps)) {
                if (
                    in_array($statusKey, $completedStatuses) ||
                    $statusKey === $status ||
                    ($currentStatusIndex !== false && $index < $currentStatusIndex)
                ) {
                    $shouldShow = true;
                }
            }
            // Step direksi
            elseif ($statusKey === 'pending_direksi') {
                if ($direksiPath === 'pending') {
                    $shouldShow = true;
                }
            } elseif ($statusKey === 'approved_by_direksi') {
                if ($direksiPath === 'approved') {
                    $shouldShow = true;
                }
            }
            // Step setelah direksi
            elseif (in_array($statusKey, $afterDireksiSteps)) {
                if (in_array($statusKey, $completedStatuses) || $statusKey === $status) {
                    $shouldShow = true;
                }
            }

            if ($shouldShow) {
                $visibleSteps[] = $statusKey;
            }
        }

        // Khusus untuk status completed - hanya tampilkan step "Selesai" saja
        if ($status === 'completed') {
            $visibleSteps = ['completed'];
        }
    }

    // Hitung persentase berdasarkan status aktif
    $currentPercentage = $statusFlow[$status]['percentage'] ?? 0;

    // Untuk status yang ditolak
    if ($isRejected) {
        if ($status === 'superadmin_rejected') {
            $currentPercentage = 15;
        } elseif ($status === 'reject_direksi') {
            $currentPercentage = 30;
        } else {
            $currentPercentage = 0;
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
        opacity: 0;
        transform: translateY(10px);
        animation: slideUp 0.5s ease forwards;
    }

    .tracking-step:nth-child(1) { animation-delay: 0.1s; }
    .tracking-step:nth-child(2) { animation-delay: 0.2s; }
    .tracking-step:nth-child(3) { animation-delay: 0.3s; }
    .tracking-step:nth-child(4) { animation-delay: 0.4s; }
    .tracking-step:nth-child(5) { animation-delay: 0.5s; }
    .tracking-step:nth-child(6) { animation-delay: 0.6s; }
    .tracking-step:nth-child(7) { animation-delay: 0.7s; }
    .tracking-step:nth-child(8) { animation-delay: 0.8s; }
    .tracking-step:nth-child(9) { animation-delay: 0.9s; }
    .tracking-step:nth-child(10) { animation-delay: 1.0s; }

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
        transition: all 0.3s ease;
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
        margin-bottom: 4px;
    }

    .dark .step-description {
        color: #9ca3af;
    }

    .step-actor {
        font-size: 10px;
        color: #3b82f6;
        font-weight: 500;
        margin-bottom: 2px;
    }

    .step-timestamp {
        font-size: 10px;
        color: #6b7280;
        font-style: italic;
        margin-bottom: 2px;
    }

    .dark .step-timestamp {
        color: #9ca3af;
    }

    .step-note {
        font-size: 10px;
        color: #374151;
        margin-top: 4px;
        padding: 4px 8px;
        background: #f3f4f6;
        border-radius: 4px;
        border-left: 2px solid #10b981;
    }

    .dark .step-note {
        color: #d1d5db;
        background: #374151;
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

    @keyframes slideUp {
        0% {
            opacity: 0;
            transform: translateY(10px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
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

    <!-- Steps - Dinamis berdasarkan status history -->
    <div class="tracking-steps">
        @if (!$isRejected)
            @foreach ($visibleSteps as $statusKey)
                @php
                    $statusInfo = $statusFlow[$statusKey];
                    $isCurrentStatus = $statusKey === $status;

                    // Tentukan status step berdasarkan posisi dalam workflow
                    $statusKeys = array_keys($statusFlow);
                    $currentIndex = array_search($status, $statusKeys);
                    $stepIndex = array_search($statusKey, $statusKeys);

                    // Jika status adalah completed, semua step menjadi completed
                    if ($status === 'completed') {
                        $stepCompleted = true;
                        $stepActive = false;
                    } else {
                        $stepCompleted = $stepIndex < $currentIndex;
                        $stepActive = $stepIndex === $currentIndex;
                    }

                    // Cari informasi dari history jika ada
                    $statusHistoryInfo = null;
                    if ($statusHistory && is_array($statusHistory)) {
                        foreach ($statusHistory as $history) {
                            if (isset($history['status']) && $history['status'] === $statusKey) {
                                $statusHistoryInfo = $history;
                                break;
                            }
                        }
                    }
                @endphp

                <div class="tracking-step">
                    @if (!$loop->last)
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

                        <div class="step-actor">
                            👤 {{ $statusInfo['actor'] }}
                        </div>

                        @if ($statusHistoryInfo)
                            <div class="step-timestamp">
                                @if ($record && method_exists($record, 'formatIndonesianDate'))
                                    📅 {{ $record->formatIndonesianDate($statusHistoryInfo['created_at']) }}
                                @else
                                    📅 {{ date('d M Y H:i', strtotime($statusHistoryInfo['created_at'])) }}
                                @endif
                            </div>

                            @if (isset($statusHistoryInfo['note']) && !empty($statusHistoryInfo['note']))
                                <div class="step-note">
                                    💬 {{ $statusHistoryInfo['note'] }}
                                </div>
                            @endif
                        @endif

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
            <!-- Tampilkan langkah yang sudah selesai sebelum penolakan -->
            @foreach ($visibleSteps as $statusKey)
                @if (isset($statusFlow[$statusKey]))
                    @php
                        $statusInfo = $statusFlow[$statusKey];
                        $statusHistoryInfo = null;
                        if ($statusHistory && is_array($statusHistory)) {
                            foreach ($statusHistory as $history) {
                                if (isset($history['status']) && $history['status'] === $statusKey) {
                                    $statusHistoryInfo = $history;
                                    break;
                                }
                            }
                        }
                    @endphp

                    <div class="tracking-step">
                        @if (!$loop->last)
                            <div class="step-connector completed"></div>
                        @endif

                        <div class="step-icon completed">
                            ✓
                        </div>

                        <div class="step-content">
                            <div class="step-label completed">
                                {{ $statusInfo['label'] }}
                            </div>
                            <div class="step-description">
                                {{ $statusInfo['description'] }}
                            </div>

                            <div class="step-actor">
                                👤 {{ $statusInfo['actor'] }}
                            </div>

                            @if ($statusHistoryInfo)
                                <div class="step-timestamp">
                                    @if ($record && method_exists($record, 'formatIndonesianDate'))
                                        📅 {{ $record->formatIndonesianDate($statusHistoryInfo['created_at']) }}
                                    @else
                                        📅 {{ date('d M Y H:i', strtotime($statusHistoryInfo['created_at'])) }}
                                    @endif
                                </div>

                                @if (isset($statusHistoryInfo['note']) && !empty($statusHistoryInfo['note']))
                                    <div class="step-note">
                                        💬 {{ $statusHistoryInfo['note'] }}
                                    </div>
                                @endif
                            @endif

                            <div class="step-status completed">
                                ✅ Selesai
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Tampilkan langkah penolakan -->
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

                    @php
                        $rejectedHistoryInfo = null;
                        if ($statusHistory && is_array($statusHistory)) {
                            foreach ($statusHistory as $history) {
                                if (isset($history['status']) && $history['status'] === $status) {
                                    $rejectedHistoryInfo = $history;
                                    break;
                                }
                            }
                        }
                    @endphp

                    @if ($rejectedHistoryInfo)
                        <div class="step-timestamp">
                            @if ($record && method_exists($record, 'formatIndonesianDate'))
                                📅 {{ $record->formatIndonesianDate($rejectedHistoryInfo['created_at']) }}
                            @else
                                📅 {{ date('d M Y H:i', strtotime($rejectedHistoryInfo['created_at'])) }}
                            @endif
                        </div>

                        @if (isset($rejectedHistoryInfo['note']) && !empty($rejectedHistoryInfo['note']))
                            <div class="step-note" style="border-left-color: #ef4444; background: #fef2f2;">
                                💬 {{ $rejectedHistoryInfo['note'] }}
                            </div>
                        @endif
                    @endif

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
