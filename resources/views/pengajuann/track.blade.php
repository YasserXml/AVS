{{-- resources/views/pengajuann/track.blade.php --}}
@php
    $percentage = $getState()['percentage'] ?? 0;
    $status = $getState()['status'] ?? '';
    $statusHistory = $getState()['status_history'] ?? [];
    $record = $getState()['record'] ?? null;

    // Definisi semua status dalam urutan workflow yang benar untuk PROJECT
    $statusFlow = [
        'pengajuan_terkirim' => [
            'label' => 'Pengajuan Terkirim',
            'icon' => '📤',
            'description' => 'Pengajuan berhasil dikirim dan menunggu review PM',
            'percentage' => 5,
            'actor' => 'User',
        ],
        'pending_pm_review' => [
            'label' => 'Sedang Review PM',
            'icon' => '👤',
            'description' => 'Pengajuan sedang direview oleh Project Manager',
            'percentage' => 10,
            'actor' => 'Project Manager',
        ],
        'disetujui_pm_dikirim_ke_pengadaan' => [
            'label' => 'Dikirim ke Pengadaan',
            'icon' => '📋',
            'description' => 'Disetujui PM dan dikirim ke tim pengadaan',
            'percentage' => 15,
            'actor' => 'Project Manager',
        ],
        'disetujui_pengadaan' => [
            'label' => 'Disetujui Pengadaan',
            'icon' => '✅',
            'description' => 'Disetujui oleh tim pengadaan',
            'percentage' => 25,
            'actor' => 'Pengadaan',
        ],
        'pengajuan_dikirim_ke_direksi' => [
            'label' => 'Dikirim ke Direksi',
            'icon' => '🏢',
            'description' => 'Dikirim ke direksi untuk persetujuan',
            'percentage' => 30,
            'actor' => 'Pengadaan',
        ],
        'pending_direksi' => [
            'label' => 'Pending Direksi',
            'icon' => '⏸️',
            'description' => 'Pengajuan dipending oleh direksi hingga tanggal tertentu',
            'percentage' => 35,
            'actor' => 'Direktur Keuangan',
        ],
        'approved_by_direksi' => [
            'label' => 'Disetujui Direksi',
            'icon' => '👔',
            'description' => 'Disetujui oleh direksi',
            'percentage' => 40,
            'actor' => 'Direktur Keuangan',
        ],
        'pengajuan_dikirim_ke_keuangan' => [
            'label' => 'Dikirim ke Keuangan',
            'icon' => '💰',
            'description' => 'Dikirim ke bagian keuangan',
            'percentage' => 45,
            'actor' => 'Direktur Keuangan',
        ],
        'pending_keuangan' => [
            'label' => 'Review Keuangan',
            'icon' => '🔍',
            'description' => 'Sedang direview oleh tim keuangan',
            'percentage' => 50,
            'actor' => 'keuangan',
        ],
        'process_keuangan' => [
            'label' => 'Proses Keuangan',
            'icon' => '⚡',
            'description' => 'Sedang diproses oleh tim keuangan',
            'percentage' => 55,
            'actor' => 'keuangan',
        ],
        'execute_keuangan' => [
            'label' => 'Eksekusi Keuangan',
            'icon' => '💳',
            'description' => 'Eksekusi pencairan oleh tim keuangan',
            'percentage' => 60,
            'actor' => 'keuangan',
        ],
        'pengajuan_dikirim_ke_pengadaan_final' => [
            'label' => 'Dikirim ke Pengadaan ',
            'icon' => '📦',
            'description' => 'Dikirim ke pengadaan untuk proses',
            'percentage' => 70,
            'actor' => 'keuangan',
        ],
        'pengajuan_dikirim_ke_admin' => [
            'label' => 'Dikirim ke Admin',
            'icon' => '👨‍💼',
            'description' => 'Dikirim ke admin untuk proses',
            'percentage' => 75,
            'actor' => 'Pengadaan',
        ],
        'processing' => [
            'label' => 'Sedang Diproses',
            'icon' => '⚙️',
            'description' => 'Dalam tahap pengerjaan',
            'percentage' => 85,
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
            'icon' => '✅',
            'description' => 'Pengajuan telah selesai',
            'percentage' => 100,
            'actor' => 'Admin',
        ],
    ];

    // Status yang ditolak atau dibatalkan
    $rejectedStatuses = ['ditolak_pm', 'ditolak_pengadaan', 'reject_direksi'];
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
    // Jika tidak ada status history, atau status saat ini belum ada di history
    if (!empty($status) && !in_array($status, $completedStatuses)) {
        $completedStatuses[] = $status;
    }

    // Gunakan persentase dari $statusFlow
    $currentPercentage = $statusFlow[$status]['percentage'] ?? 0;

    // Jika $currentPercentage masih 0, coba ambil dari model
    if ($currentPercentage === 0 && $record && method_exists($record, 'getProgressPercentage')) {
        $currentPercentage = $record->getProgressPercentage();
    }

    // ANALISIS JALUR YANG DIAMBIL - Perbaikan logika deteksi
    $hasPendingDireksi = in_array('pending_direksi', $completedStatuses);
    $hasApprovedDireksi = in_array('approved_by_direksi', $completedStatuses);
    $hasKeuangan = in_array('pengajuan_dikirim_ke_keuangan', $completedStatuses);

    // Debug: Cek apa yang ada di completedStatuses
    // Uncomment baris ini untuk debugging:
    // dd('Current Status: ' . $status, 'Completed Statuses:', $completedStatuses, 'Direksi Path: ' . $direksiPath);

    // Tentukan jalur direksi yang diambil dengan logika yang lebih tepat
    $direksiPath = 'none';

    // Prioritas 1: Cek status saat ini
    if ($status === 'pending_direksi') {
        $direksiPath = 'pending';
    } elseif ($status === 'approved_by_direksi') {
        $direksiPath = 'approved';
    }
    // Prioritas 2: Cek dari history jika status sudah lewat
    elseif ($hasKeuangan) {
        // Jika sudah sampai keuangan, lihat jalur mana yang diambil sebelumnya
        if ($hasPendingDireksi && !$hasApprovedDireksi) {
            $direksiPath = 'pending';
        } elseif ($hasApprovedDireksi && !$hasPendingDireksi) {
            $direksiPath = 'approved';
        }
    }
    // Prioritas 3: Cek dari history langsung
    elseif ($hasPendingDireksi) {
        $direksiPath = 'pending';
    } elseif ($hasApprovedDireksi) {
        $direksiPath = 'approved';
    }

    // Buat array step yang akan ditampilkan
    $visibleSteps = [];

    if ($isRejected) {
        // Jika ditolak, tampilkan semua step yang ada di history
        $visibleSteps = $completedStatuses;
    } elseif ($status === 'completed') {
        // JIKA STATUS COMPLETED, HANYA TAMPILKAN STATUS SELESAI
        $visibleSteps = ['completed'];
    } else {
        // Step-step yang selalu ditampilkan (sebelum direksi)
        $alwaysVisibleSteps = [
            'pengajuan_terkirim',
            'pending_pm_review',
            'disetujui_pm_dikirim_ke_pengadaan',
            'disetujui_pengadaan',
            'pengajuan_dikirim_ke_direksi',
        ];

        // Step setelah direksi
        $afterDireksiSteps = [
            'pengajuan_dikirim_ke_keuangan',
            'pending_keuangan',
            'process_keuangan',
            'execute_keuangan',
            'pengajuan_dikirim_ke_pengadaan_final',
            'pengajuan_dikirim_ke_admin',
            'processing',
            'ready_pickup',
            'completed',
        ];

        $statusKeys = array_keys($statusFlow);
        $currentStatusIndex = array_search($status, $statusKeys);

        foreach ($statusKeys as $index => $statusKey) {
            $shouldShow = false;

            // 1. Step sebelum direksi - tampilkan jika sudah dilalui
            if (in_array($statusKey, $alwaysVisibleSteps)) {
                if (
                    in_array($statusKey, $completedStatuses) ||
                    $statusKey === $status ||
                    ($currentStatusIndex !== false && $index < $currentStatusIndex)
                ) {
                    $shouldShow = true;
                }
            }
            // 2. Step direksi - tampilkan berdasarkan jalur yang dipilih
            elseif ($statusKey === 'pending_direksi') {
                if ($direksiPath === 'pending') {
                    $shouldShow = true;
                }
            } elseif ($statusKey === 'approved_by_direksi') {
                if ($direksiPath === 'approved') {
                    $shouldShow = true;
                }
            }
            // 3. Step setelah direksi - tampilkan jika ada di history atau status saat ini
            elseif (in_array($statusKey, $afterDireksiSteps)) {
                if (in_array($statusKey, $completedStatuses) || $statusKey === $status) {
                    $shouldShow = true;
                }
            }

            if ($shouldShow) {
                $visibleSteps[] = $statusKey;
            }
        }
    }
@endphp

<style>
    .project-tracking {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .dark .project-tracking {
        background: #1f2937;
        border-color: #374151;
    }

    .tracking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f3f4f6;
    }

    .dark .tracking-header {
        border-bottom-color: #374151;
    }

    .tracking-title {
        font-size: 16px;
        font-weight: 600;
        color: #1f2937;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dark .tracking-title {
        color: #f9fafb;
    }

    .tracking-progress {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #6b7280;
    }

    .progress-bar {
        width: 100px;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        transition: width 0.8s ease;
        border-radius: 3px;
    }

    .progress-fill.rejected {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    .tracking-steps {
        position: relative;
        padding: 10px 0;
    }

    .tracking-step {
        display: flex;
        align-items: flex-start;
        position: relative;
        padding: 15px 0;
        margin-bottom: 10px;
        opacity: 0;
        transform: translateY(20px);
        animation: slideUp 0.6s ease forwards;
    }

    .tracking-step:nth-child(1) {
        animation-delay: 0.1s;
    }

    .tracking-step:nth-child(2) {
        animation-delay: 0.2s;
    }

    .tracking-step:nth-child(3) {
        animation-delay: 0.3s;
    }

    .tracking-step:nth-child(4) {
        animation-delay: 0.4s;
    }

    .tracking-step:nth-child(5) {
        animation-delay: 0.5s;
    }

    .tracking-step:nth-child(6) {
        animation-delay: 0.6s;
    }

    .tracking-step:nth-child(7) {
        animation-delay: 0.7s;
    }

    .tracking-step:nth-child(8) {
        animation-delay: 0.8s;
    }

    .tracking-step:nth-child(9) {
        animation-delay: 0.9s;
    }

    .tracking-step:nth-child(10) {
        animation-delay: 1.0s;
    }

    .step-connector {
        position: absolute;
        left: 19px;
        top: 50px;
        width: 2px;
        height: calc(100% - 15px);
        background: #e5e7eb;
        z-index: 1;
    }

    .step-connector.completed {
        background: #10b981;
        animation: growLine 0.8s ease forwards;
    }

    .step-connector.active {
        background: linear-gradient(to bottom, #10b981, #e5e7eb);
        animation: growLine 0.8s ease forwards;
    }

    .tracking-step:last-child .step-connector {
        display: none;
    }

    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-right: 15px;
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        border: 3px solid #e5e7eb;
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
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }

    .step-icon.active {
        background: #10b981;
        border-color: #10b981;
        color: white;
        transform: scale(1.2);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        animation: popUpPulse 2s infinite;
    }

    .step-icon.rejected {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }

    /* Style khusus untuk status completed */
    .step-icon.final-completed {
        background: #10b981;
        border-color: #10b981;
        color: white;
        transform: scale(1.3);
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
        animation: finalCompletedPulse 3s infinite;
    }

    .step-content {
        flex: 1;
        padding-top: 5px;
    }

    .step-label {
        font-size: 14px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 5px;
        transition: color 0.3s ease;
    }

    .dark .step-label {
        color: #f9fafb;
    }

    .step-label.completed {
        color: #10b981;
    }

    .step-label.active {
        color: #10b981;
        font-weight: 700;
    }

    .step-label.rejected {
        color: #ef4444;
    }

    /* Style khusus untuk label final completed */
    .step-label.final-completed {
        color: #10b981;
        font-weight: 700;
        font-size: 16px;
    }

    .step-description {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.4;
        margin-bottom: 6px;
    }

    .dark .step-description {
        color: #9ca3af;
    }

    .step-actor {
        font-size: 11px;
        color: #3b82f6;
        font-weight: 500;
        margin-bottom: 3px;
    }

    .step-timestamp {
        font-size: 11px;
        color: #6b7280;
        font-style: italic;
    }

    .dark .step-timestamp {
        color: #9ca3af;
    }

    .step-note {
        font-size: 11px;
        color: #374151;
        margin-top: 6px;
        padding: 6px 10px;
        background: #f3f4f6;
        border-radius: 6px;
        border-left: 3px solid #10b981;
    }

    .dark .step-note {
        color: #d1d5db;
        background: #374151;
    }

    .step-status {
        font-size: 11px;
        margin-top: 4px;
        font-weight: 500;
        padding: 2px 6px;
        border-radius: 10px;
        display: inline-block;
    }

    .step-status.completed {
        color: #10b981;
        background: #ecfdf5;
    }

    .step-status.active {
        color: #10b981;
        background: #ecfdf5;
        animation: statusPulse 2s infinite;
    }

    .step-status.rejected {
        color: #ef4444;
        background: #fef2f2;
    }

    /* Style khusus untuk status final completed */
    .step-status.final-completed {
        color: #10b981;
        background: #ecfdf5;
        font-weight: 600;
        padding: 4px 8px;
        font-size: 12px;
    }

    .tracking-footer {
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #f3f4f6;
        font-size: 13px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dark .tracking-footer {
        border-top-color: #374151;
        color: #9ca3af;
    }

    .rejected-banner {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dark .rejected-banner {
        background: #1f2937;
        border-color: #ef4444;
    }

    .rejected-banner-text {
        font-size: 14px;
        color: #dc2626;
        font-weight: 500;
    }

    .dark .rejected-banner-text {
        color: #ef4444;
    }

    .project-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .dark .project-badge {
        background: #1e3a8a;
        color: #bfdbfe;
    }

    /* Animasi khusus untuk status completed */
    @keyframes finalCompletedPulse {
        0%, 100% {
            transform: scale(1.3);
            box-shadow: 0 8px 30px rgba(16, 185, 129, 0.5);
        }
        50% {
            transform: scale(1.4);
            box-shadow: 0 12px 40px rgba(16, 185, 129, 0.7);
        }
    }

    @keyframes slideUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }

        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes growLine {
        0% {
            height: 0;
        }

        100% {
            height: calc(100% - 15px);
        }
    }

    @keyframes popUpPulse {

        0%,
        100% {
            transform: scale(1.2);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        50% {
            transform: scale(1.3);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6);
        }
    }

    @keyframes statusPulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    @media (max-width: 640px) {
        .tracking-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .tracking-progress {
            align-self: stretch;
            justify-content: space-between;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }

        .step-icon.active {
            transform: scale(1.15);
        }

        .step-icon.completed {
            transform: scale(1.05);
        }

        .step-icon.final-completed {
            transform: scale(1.25);
        }
    }
</style>

<div class="project-tracking">
    <!-- Project Badge -->
    <div class="project-badge">
        <span>📋</span>
        <span>Proses Pengajuan Barang Project</span>
    </div>

    <!-- Header -->
    <div class="tracking-header">
        <div class="tracking-title">
            <span>🚀</span>
            <span>Tracking Status Pengajuan</span>
        </div>
        <div class="tracking-progress">
            @if (!$isRejected)
                <span>{{ $status === 'completed' ? 100 : $currentPercentage }}% Selesai</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $status === 'completed' ? 100 : $currentPercentage }}%">
                    </div>
                </div>
            @else
                <span style="color: #ef4444; font-weight: 500;">❌ Ditolak</span>
            @endif
        </div>
    </div>

    @if ($isRejected)
        <!-- Rejected Banner -->
        <div class="rejected-banner">
            <span style="font-size: 18px;">❌</span>
            <div>
                <div class="rejected-banner-text">
                    @if ($status === 'ditolak_pm')
                        Pengajuan Ditolak oleh Project Manager
                    @elseif ($status === 'ditolak_pengadaan')
                        Pengajuan Ditolak oleh Tim Pengadaan
                    @elseif ($status === 'reject_direksi')
                        Pengajuan Ditolak oleh Direksi
                    @endif
                </div>
                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                    Silakan hubungi admin untuk informasi lebih lanjut atau buat pengajuan baru
                </div>
            </div>
        </div>
    @endif

    <!-- Steps -->
    <div class="tracking-steps">
        @if (!$isRejected)
            @foreach ($visibleSteps as $statusKey)
                @php
                    $statusInfo = $statusFlow[$statusKey];
                    $isCurrentStatus = $statusKey === $status;

                    // Untuk status completed, pastikan ditampilkan sebagai final completed
                    if ($status === 'completed' && $statusKey === 'completed') {
                        $stepCompleted = false;
                        $stepActive = false;
                        $finalCompleted = true;
                    } else {
                        // Tentukan status step berdasarkan posisi dalam workflow
                        $statusKeys = array_keys($statusFlow);
                        $currentIndex = array_search($status, $statusKeys);
                        $stepIndex = array_search($statusKey, $statusKeys);

                        $stepCompleted = $stepIndex < $currentIndex;
                        $stepActive = $stepIndex === $currentIndex;
                        $finalCompleted = false;
                    }
                @endphp

                <div class="tracking-step">
                    @if (!$loop->last)
                        <div class="step-connector {{ $stepCompleted ? 'completed' : ($stepActive ? 'active' : '') }}">
                        </div>
                    @endif

                    <div class="step-icon {{ $finalCompleted ? 'final-completed' : ($stepCompleted ? 'completed' : ($stepActive ? 'active' : '')) }}">
                        @if ($finalCompleted)
                            🎉
                        @elseif ($stepCompleted)
                            ✓
                        @else
                            {{ $statusInfo['icon'] }}
                        @endif
                    </div>

                    <div class="step-content">
                        <div class="step-label {{ $finalCompleted ? 'final-completed' : ($stepCompleted ? 'completed' : ($stepActive ? 'active' : '')) }}">
                            {{ $statusInfo['label'] }}
                        </div>
                        <div class="step-description">
                            {{ $statusInfo['description'] }}
                        </div>

                        <div class="step-actor">
                            👤 {{ $statusInfo['actor'] }}
                        </div>

                        @if ($finalCompleted)
                            @php
                                $completedHistoryInfo = null;
                                if ($record && method_exists($record, 'getStatusHistoryByStatus')) {
                                    $completedHistoryInfo = $record->getStatusHistoryByStatus('completed');
                                }
                            @endphp

                            @if ($completedHistoryInfo)
                                <div class="step-timestamp">
                                    @if ($record && method_exists($record, 'formatIndonesianDate'))
                                        📅 {{ $record->formatIndonesianDate($completedHistoryInfo['created_at']) }}
                                    @else
                                        📅 {{ $completedHistoryInfo['created_at'] }}
                                    @endif
                                </div>

                                @if (isset($completedHistoryInfo['note']) && !empty($completedHistoryInfo['note']))
                                    <div class="step-note">
                                        💬 {{ $completedHistoryInfo['note'] }}
                                    </div>
                                @endif
                            @endif
                        @endif

                        <div class="step-status {{ $finalCompleted ? 'final-completed' : ($stepCompleted ? 'completed' : ($stepActive ? 'active' : '')) }}">
                            @if ($finalCompleted)
                                ✅  Pengajuan Selesai
                            @elseif ($stepCompleted)
                                ✅ Selesai
                            @elseif ($stepActive)
                                ⏳ Sedang Berlangsung
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <!-- Tampilkan langkah yang sudah selesai sebelum penolakan -->
            @foreach ($visibleSteps as $statusKey)
                @php
                    $statusInfo = $statusFlow[$statusKey];
                    $statusHistoryInfo = null;
                    if ($record && method_exists($record, 'getStatusHistoryByStatus')) {
                        $statusHistoryInfo = $record->getStatusHistoryByStatus($statusKey);
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
                                    📅 {{ $statusHistoryInfo['created_at'] }}
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
            @endforeach

            <!-- Tampilkan langkah penolakan -->
            <div class="tracking-step">
                <div class="step-icon rejected">
                    ❌
                </div>
                <div class="step-content">
                    <div class="step-label rejected">
                        @if ($status === 'ditolak_pm')
                            Ditolak oleh Project Manager
                        @elseif ($status === 'ditolak_pengadaan')
                            Ditolak oleh Tim Pengadaan
                        @elseif ($status === 'reject_direksi')
                            Ditolak oleh Direksi
                        @endif
                    </div>
                    <div class="step-description">
                        Pengajuan tidak dapat dilanjutkan dengan alasan tertentu
                    </div>

                    @php
                        $rejectedHistoryInfo = null;
                        if ($record && method_exists($record, 'getStatusHistoryByStatus')) {
                            $rejectedHistoryInfo = $record->getStatusHistoryByStatus($status);
                        }
                    @endphp

                    @if ($rejectedHistoryInfo)
                        <div class="step-timestamp">
                            @if ($record && method_exists($record, 'formatIndonesianDate'))
                                📅 {{ $record->formatIndonesianDate($rejectedHistoryInfo['created_at']) }}
                            @else
                                📅 {{ $rejectedHistoryInfo['created_at'] }}
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
                Pengajuan barang project telah selesai! Terima kasih atas kesabarannya
            @else
                Pengajuan barang project sedang dalam proses. Update akan diberikan secara berkala
            @endif
        </span>
    </div>
</div>
