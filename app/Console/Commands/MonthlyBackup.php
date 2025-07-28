<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthlyBackup extends Command
{
    protected $signature = 'backup:monthly {--notify : Send notification after backup}';
    protected $description = 'Run monthly backup with custom logging';

    public function handle()
    {
        $this->info('Starting monthly backup...');

        $startTime = now();

        try {
            // Jalankan backup
            $this->call('backup:run');

            $endTime = now();
            $duration = $endTime->diffInMinutes($startTime);

            // Log keberhasilan
            Log::info("Monthly backup completed successfully", [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $duration,
                'disk_usage' => $this->getDiskUsage()
            ]);

            $this->info("Monthly backup completed in {$duration} minutes");

            // Kirim notifikasi jika diminta
            if ($this->option('notify')) {
                $this->call('backup:monitor');
            }
        } catch (\Exception $e) {
            Log::error("Monthly backup failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->error("Monthly backup failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getDiskUsage()
    {
        $backupPath = storage_path('app/backup'); // sesuai dengan konfigurasi Anda
        if (is_dir($backupPath)) {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($backupPath)) as $file) {
                $size += $file->getSize();
            }
            return round($size / 1024 / 1024, 2) . ' MB'; // Convert to MB
        }
        return 'Unknown';
    }
}
