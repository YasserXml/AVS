<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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
            abort(404, 'File tidak ditemukan');
        }
        
        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../'], '', $filePath);
        
        // Path lengkap ke file
        $fullPath = storage_path('app/public/' . $filePath);
        
        // Cek apakah file ada
        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan');
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
     * Download multiple files sebagai ZIP
     */
    public function downloadMultipleFiles(Request $request)
    {
        $files = $request->input('files', []);
        
        if (empty($files)) {
            abort(404, 'Tidak ada file yang dipilih');
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
                $filePath = storage_path('app/public/' . $file);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($file));
                }
            }
            $zip->close();
            
            return Response::download($zipPath, $zipFileName)->deleteFileAfterSend(true);
        }
        
        abort(500, 'Gagal membuat file ZIP');
    }
    
    /**
     * Preview file (untuk gambar atau PDF)
     */
    public function previewFile(Request $request)
    {
        $filePath = $request->input('file_path');
        
        if (!$filePath) {
            abort(404, 'File tidak ditemukan');
        }
        
        // Bersihkan path untuk keamanan
        $filePath = str_replace(['../', '../'], '', $filePath);
        
        // Path lengkap ke file
        $fullPath = storage_path('app/public/' . $filePath);
        
        // Cek apakah file ada
        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan');
        }
        
        $mimeType = mime_content_type($fullPath);
        
        return Response::file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"'
        ]);
    }
}
