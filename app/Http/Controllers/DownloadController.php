<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadController extends Controller
{
    /**
     * Download file pendukung pengajuan
     */
    public function downloadFile(Request $request)
    {
        $filePath = $request->input('file_path');

        if (!$filePath) {
            return response()->json(['error' => 'File path tidak ditemukan'], 404);
        }

        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../', '\\'], '', $filePath);

        // Cek apakah file ada menggunakan Storage facade
        if (!Storage::disk('public')->exists($filePath)) {
            return response()->json(['error' => 'File tidak ditemukan di storage'], 404);
        }

        // Path lengkap ke file
        $fullPath = Storage::disk('public')->path($filePath);

        // Double check apakah file benar-benar ada
        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File tidak dapat diakses'], 404);
        }

        // Dapatkan nama file asli
        $fileName = basename($filePath);

        // Return file download response
        return Response::download($fullPath, $fileName, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Preview file (untuk gambar atau PDF)
     */
    public function previewFile(Request $request)
    {
        $filePath = $request->get('file_path');

        if (!$filePath) {
            abort(404, 'File path tidak ditemukan');
        }

        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../', '\\'], '', $filePath);

        // Cek apakah file ada menggunakan Storage facade
        if (Storage::disk('public')->exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage');
        }

        // Path lengkap ke file
        $fullPath = Storage::disk('public')->path($filePath);

        $mimeType = mime_content_type($fullPath);

        // Untuk file yang bisa di-preview di browser
        $previewableMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/plain',
            'text/html'
        ];

        if (in_array($mimeType, $previewableMimes)) {
            return Response::file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
            ]);
        } else {
            // Jika tidak bisa di-preview, download saja
            return $this->downloadFile($request);
        }
    }

    /**
     * Download multiple files sebagai ZIP
     */
    public function downloadMultipleFiles(Request $request)
    {
        $files = $request->input('files', []);

        if (empty($files)) {
            return response()->json(['error' => 'Tidak ada file yang dipilih'], 400);
        }

        $zip = new ZipArchive();
        $zipFileName = 'pengajuan_files_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Buat folder temp jika belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($files as $file) {
                $filePath = Storage::disk('public')->path($file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($file));
                }
            }
            $zip->close();

            return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'Gagal membuat file ZIP'], 500);
    }
}
