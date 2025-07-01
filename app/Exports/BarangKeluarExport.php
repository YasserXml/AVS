<?php

namespace App\Exports;

use App\Models\BarangKeluar;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangKeluarExport
{
    protected $data;
    protected $spreadsheet;
    protected $selectedRecords = null;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    public function export($selectedRecords = null)
    {
        // Set selected records jika ada
        if ($selectedRecords !== null) {
            $this->selectedRecords = $selectedRecords;
        }

        $this->loadData();
        $this->setupSpreadsheet();

        return $this->downloadResponse();
    }

    /**
     * Load data dari database
     */
    protected function loadData()
    {
        // Jika ada data terpilih, gunakan data tersebut
        if ($this->selectedRecords !== null) {
            // Pastikan relasi di-load
            $this->data = $this->selectedRecords->load([
                'barang.kategori',
                'pengajuan.user',
                'user'
            ]);
        } else {
            // Jika tidak ada data terpilih, ambil semua data
            $this->data = BarangKeluar::with([
                'barang.kategori',
                'pengajuan.user', 
                'user'
            ])
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return $this;
    }

    public function withQuery(\Closure $queryCallback)
    {
        // Hanya bisa digunakan jika tidak ada selected records
        if ($this->selectedRecords === null) {
            $query = BarangKeluar::with([
                'barang.kategori',
                'pengajuan.user',
                'user'
            ])->whereNull('deleted_at');
            $queryCallback($query);
            $this->data = $query->get();
        }

        return $this;
    }

    /**
     * Setup spreadsheet
     */
    protected function setupSpreadsheet()
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Set properti dokumen
        $this->spreadsheet->getProperties()
            ->setCreator('PT ALAM VIRTUAL SEMESTA')
            ->setLastModifiedBy('System')
            ->setTitle('Laporan Barang Keluar')
            ->setSubject('Inventory')
            ->setDescription('Laporan Barang Keluar Inventory')
            ->setKeywords('laporan, inventory, barang keluar')
            ->setCategory('Laporan');

        // Tambahkan logo - Ukuran dan posisi disesuaikan
        $logoPath = public_path('images/logoAVS.png');
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo Perusahaan');
            $drawing->setPath($logoPath);
            $drawing->setHeight(115);
            $drawing->setWidth(100);
            $drawing->setCoordinates('B1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // Tambahkan baris kosong untuk ruang logo
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Tambahkan header perusahaan dengan posisi di tengah
        $sheet->setCellValue('C1', 'PT ALAM VIRTUAL SEMESTA');
        $sheet->setCellValue('C2', 'Jalan Cihampelas No. 180, Cipaganti, Kecamatan Coblong, Kota Bandung');
        $sheet->setCellValue('C3', 'Jawa Barat 40131');
        $sheet->setCellValue('C4', 'Telp: (022) 63183003 | Email: support@avsimulator.com');

        // Merge sel untuk header
        $sheet->mergeCells('C1:I1');
        $sheet->mergeCells('C2:I2');
        $sheet->mergeCells('C3:I3');
        $sheet->mergeCells('C4:I4');

        // Tambahkan garis bawah dan atas untuk header
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '008000'], // Warna hijau untuk border
                ],
            ],
        ];
        $sheet->getStyle('B1:J4')->applyFromArray($styleArray);

        // Style untuk teks header
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C2:C4')->getFont()->setSize(11);
        $sheet->getStyle('C2:C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Tambahkan jarak sebelum judul utama
        $sheet->getRowDimension(5)->setRowHeight(10);

        // Judul laporan
        $sheet->setCellValue('A6', 'LAPORAN BARANG KELUAR');

        $subtitleText = $this->selectedRecords !== null
            ? 'Data Terpilih (' . $this->data->count() . ' item) - ' . now()->format('d-m-Y')
            : 'Per Tanggal: ' . now()->format('d-m-Y');
        $sheet->setCellValue('A7', $subtitleText);

        $sheet->mergeCells('A6:J6');
        $sheet->mergeCells('A7:J7');

        // Style untuk judul laporan
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getStyle('A6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getRowDimension(6)->setRowHeight(25);

        $sheet->getStyle('A7')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Tambahkan jarak sebelum tabel
        $sheet->getRowDimension(8)->setRowHeight(15);

        // Tambahkan header kolom
        $headers = [
            'No',
            'Tanggal Keluar',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Jumlah Keluar',
            'Status',
            'Nama Project',
            'Pemohon',
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers dengan gradient biru yang elegan
        $headerRange = 'A9:J9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->setColor(new Color(Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
            ->setStartColor(new Color('1F497D'))
            ->setEndColor(new Color('2E5984'));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Tambahkan data
        $row = 10;
        $startRow = $row;
        $no = 1;

        foreach ($this->data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            
            // Tanggal Keluar
            $tanggalKeluar = $item->tanggal_keluar_barang 
                ? \Carbon\Carbon::parse($item->tanggal_keluar_barang)->format('d/m/Y')
                : '-';
            $sheet->setCellValue('B' . $row, $tanggalKeluar);
            
            // Kode Barang
            $sheet->setCellValue('C' . $row, $item->barang->kode_barang ?? '-');
            
            // Nama Barang
            $sheet->setCellValue('D' . $row, $item->barang->nama_barang ?? '-');
            
            // Kategori
            $sheet->setCellValue('E' . $row, $item->barang->kategori->nama_kategori ?? '-');
            
            // Jumlah Keluar
            $sheet->setCellValue('F' . $row, $item->jumlah_barang_keluar . ' unit');
            $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            // Status
            $status = match($item->status) {
                'oprasional_kantor' => 'Operasional Kantor',
                'project' => 'Project',
                default => $item->status
            };
            $sheet->setCellValue('G' . $row, $status);
            
            // Nama Project
            $sheet->setCellValue('H' . $row, $item->project_name ?? '-');
            
            // Pemohon (dari pengajuan atau user yang input)
            $pemohon = $item->pengajuan?->user?->name ?? $item->user?->name ?? '-';
            $sheet->setCellValue('I' . $row, $pemohon);
            
            // Keterangan
            $sheet->setCellValue('J' . $row, $item->keterangan ?? '-');

            // Style baris bergantian dengan warna yang lebih soft
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
            } else {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            }

            // Tambahkan border untuk setiap baris
            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Set row height untuk konsistensi
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        $endRow = $row - 1;

        // Style sel data
        $dataRange = 'A' . $startRow . ':J' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A' . $startRow . ':A' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
        $sheet->getStyle('B' . $startRow . ':B' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
        $sheet->getStyle('C' . $startRow . ':C' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kode Barang
        $sheet->getStyle('F' . $startRow . ':F' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jumlah
        $sheet->getStyle('G' . $startRow . ':G' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status

        // Ratakan kolom nama barang, kategori, dan keterangan ke kiri dengan indent
        $sheet->getStyle('D' . $startRow . ':D' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Nama Barang
        $sheet->getStyle('D' . $startRow . ':D' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('E' . $startRow . ':E' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Kategori
        $sheet->getStyle('E' . $startRow . ':E' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('H' . $startRow . ':H' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Project
        $sheet->getStyle('H' . $startRow . ':H' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('I' . $startRow . ':I' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Pemohon
        $sheet->getStyle('I' . $startRow . ':I' . $endRow)->getAlignment()->setIndent(1);
        // Tambahkan separator line
        $row++;
        $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);

        // Atur lebar kolom yang lebih optimal
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(14);  // Tanggal Keluar
        $sheet->getColumnDimension('C')->setWidth(12);  // Kode Barang
        $sheet->getColumnDimension('D')->setWidth(28);  // Nama Barang
        $sheet->getColumnDimension('E')->setWidth(16);  // Kategori
        $sheet->getColumnDimension('F')->setWidth(12);  // Jumlah Keluar
        $sheet->getColumnDimension('G')->setWidth(16);  // Status
        $sheet->getColumnDimension('H')->setWidth(20);  // Nama Project
        $sheet->getColumnDimension('I')->setWidth(18);  // Pemohon  

        // Set print area dan page setup
        $sheet->getPageSetup()->setPrintArea('A1:I' . ($row + 1));
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    /**
     * Generate download response
     *
     * @return StreamedResponse
     */
    protected function downloadResponse()
    {
        $filenamePrefix = $this->selectedRecords !== null ? 'Laporan-Barang-Keluar' : 'Laporan-Barang-Keluar-';
        $filename = $filenamePrefix . now()->format('d-m-Y') . '.xlsx';

        return new StreamedResponse(function () {
            $writer = new Xlsx($this->spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}