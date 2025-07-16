<?php

namespace App\Http\Controllers;

use App\Models\Pengajuanproject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;

class DownloadProjectController extends Controller
{
    public function downloadFile(Request $request)
    {
        $filePath = $request->input('file_path');

        if (!$filePath) {
            abort(404, 'File tidak ditemukan');
        }

        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../'], '', $filePath);

        // Cek apakah file ada menggunakan Storage facade
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }

        // Path lengkap ke file
        $fullPath = Storage::disk('public')->path($filePath);

        // Dapatkan nama file asli
        $fileName = basename($filePath);

        // Return file download response
        return Response::download($fullPath, $fileName, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Download multiple files sebagai ZIP untuk project
     */
    public function downloadMultipleFiles(Request $request)
    {
        $files = $request->input('files', []);

        if (empty($files)) {
            abort(404, 'Tidak ada file yang dipilih');
        }

        $zip = new ZipArchive();
        $zipFileName = 'pengajuan_project_files_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Buat folder temp jika belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                if (Storage::disk('public')->exists($file)) {
                    $filePath = Storage::disk('public')->path($file);
                    $zip->addFile($filePath, basename($file));
                }
            }
            $zip->close();

            return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        abort(500, 'Gagal membuat file ZIP');
    }

    public function downloadAllProjectFiles(Request $request)
    {
        $projectId = $request->input('project_id');

        if (!$projectId) {
            abort(404, 'Project tidak ditemukan');
        }

        // Ambil data pengajuan project
        $pengajuan = Pengajuanproject::find($projectId);

        if (!$pengajuan) {
            abort(404, 'Pengajuan project tidak ditemukan');
        }

        // Kumpulkan semua file
        $allFiles = [];

        // File umum project
        if (!empty($pengajuan->uploaded_files)) {
            $allFiles = array_merge($allFiles, $pengajuan->uploaded_files);
        }

        // File dari detail barang
        if (!empty($pengajuan->detail_barang)) {
            foreach ($pengajuan->detail_barang as $barang) {
                if (!empty($barang['file_barang'])) {
                    $allFiles = array_merge($allFiles, $barang['file_barang']);
                }
            }
        }

        if (empty($allFiles)) {
            abort(404, 'Tidak ada file yang tersedia');
        }

        $zip = new ZipArchive();
        $zipFileName = 'pengajuan_project_' . $projectId . '_all_files_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Buat folder temp jika belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($allFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    $filePath = Storage::disk('public')->path($file);
                    $zip->addFile($filePath, basename($file));
                }
            }
            $zip->close();

            return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        abort(500, 'Gagal membuat file ZIP');
    }

    public function downloadBarangFiles(Request $request)
    {
        $files = $request->input('files', []);
        $barangName = $request->input('barang_name', 'barang');

        if (empty($files)) {
            abort(404, 'Tidak ada file yang dipilih');
        }

        if (count($files) === 1) {
            $file = $files[0];
            if (Storage::disk('public')->exists($file)) {
                $filePath = Storage::disk('public')->path($file);
                return Response::download($filePath, basename($file));
            }
        } else {
            $zip = new ZipArchive();
            $zipFileName = 'barang_' . Str::slug($barangName) . '_files_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Buat folder temp jika belum ada
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($files as $file) {
                    if (Storage::disk('public')->exists($file)) {
                        $filePath = Storage::disk('public')->path($file);
                        $zip->addFile($filePath, basename($file));
                    }
                }
                $zip->close();

                return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
            }
        }

        abort(500, 'Gagal mengunduh file');
    }

    public function previewFile(Request $request)
    {
        $filePath = $request->input('file_path');

        if (!$filePath) {
            abort(404, 'File tidak ditemukan');
        }

        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../'], '', $filePath);

        // Log untuk debugging
        Log::info('Preview file attempt', [
            'original_file_path' => $request->input('file_path'),
            'cleaned_file_path' => $filePath,
            'storage_exists' => Storage::disk('public')->exists($filePath)
        ]);

        // PERBAIKAN: Ubah dari !Storage::disk('public')->exists menjadi Storage::disk('public')->exists
        if (!Storage::disk('public')->exists($filePath)) {
            Log::error('File tidak ditemukan di storage', [
                'file_path' => $filePath,
                'storage_path' => Storage::disk('public')->path($filePath)
            ]);
            abort(404, 'File tidak ditemukan di storage');
        }

        // Path lengkap ke file
        $fullPath = Storage::disk('public')->path($filePath);

        // Cek sekali lagi apakah file fisik ada
        if (!file_exists($fullPath)) {
            Log::error('File fisik tidak ditemukan', [
                'full_path' => $fullPath
            ]);
            abort(404, 'File fisik tidak ditemukan');
        }

        $mimeType = mime_content_type($fullPath);
        $fileName = basename($filePath);

        // Log untuk debugging
        Log::info('Preview file success', [
            'file_path' => $filePath,
            'full_path' => $fullPath,
            'mime_type' => $mimeType,
            'file_exists' => file_exists($fullPath)
        ]);

        // Untuk file gambar, PDF, dan teks - tampilkan inline
        if (in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript'
        ])) {
            return Response::file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
        }

        // Untuk file lainnya - download
        return Response::download($fullPath, $fileName, [
            'Content-Type' => $mimeType
        ]);
    }
}
