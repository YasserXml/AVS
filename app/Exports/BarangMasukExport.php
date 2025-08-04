<?php

namespace App\Exports;

use App\Models\BarangMasuk;
use App\Models\Kategori;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangMasukExport
{
    protected $data;
    protected $spreadsheet;
    protected $selectedRecords = null;
    protected $groupedData; // Property untuk data yang sudah digroup

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }

    /**
     * Export data to Excel file
     *
     * @return StreamedResponse
     */
    public function export($selectedRecords = null)
    {
        if ($selectedRecords !== null) {
            $this->selectedRecords = $selectedRecords;
        }

        $this->loadData();
        $this->groupDataBySubkategori();
        $this->setupSpreadsheet();

        return $this->downloadResponse();
    }

    /**
     * Load data from database
     */
    protected function loadData()
    {
        if ($this->selectedRecords !== null) {
            // Pastikan relasi sudah di-load
            $this->data = $this->selectedRecords->load(['user', 'barang', 'kategori', 'subkategori']);
        } else {
            $this->data = BarangMasuk::with(['user', 'barang', 'kategori', 'subkategori'])
                ->orderBy('subkategori_id')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return $this;
    }

    /**
     * Group data berdasarkan subkategori
     */
    protected function groupDataBySubkategori()
    {
        $this->groupedData = $this->data->groupBy(function ($item) {
            if ($item->subkategori) {
                return $item->subkategori->nama_subkategori;
            }
            return 'Tanpa Subkategori';
        });

        return $this;
    }

    /**
     * Apply query filters if needed
     *
     * @param \Closure $queryCallback
     * @return $this
     */
    public function withQuery(\Closure $queryCallback)
    {
        // Hanya bisa digunakan jika tidak ada selected records
        if ($this->selectedRecords === null) {
            $query = BarangMasuk::with(['user', 'barang', 'kategori', 'subkategori']);
            $queryCallback($query);
            $this->data = $query->get();
        }

        return $this;
    }

    /**
     * Setup the spreadsheet
     */
    protected function setupSpreadsheet()
    {
        $sheet = $this->spreadsheet->getActiveSheet();

        // Set properti dokumen
        $this->spreadsheet->getProperties()
            ->setCreator('PT ALAM VIRTUAL SEMESTA')
            ->setLastModifiedBy('System')
            ->setTitle('Laporan Barang Masuk')
            ->setSubject('Barang Masuk')
            ->setDescription('Laporan Data Barang Masuk')
            ->setKeywords('laporan, barang masuk, inventory')
            ->setCategory('Laporan');

        // Tambahkan logo
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
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '008000'], // Warna hijau untuk border
                ],
            ],
        ];
        $sheet->getStyle('B1:I4')->applyFromArray($styleArray);

        // Style untuk teks header
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C2:C4')->getFont()->setSize(11);
        $sheet->getStyle('C2:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tambahkan jarak sebelum judul utama
        $sheet->getRowDimension(5)->setRowHeight(10);

        // Tambahkan judul laporan
        $sheet->setCellValue('A6', 'LAPORAN DATA BARANG MASUK');

        $subtitleText = $this->selectedRecords !== null
            ? 'Data Terpilih (' . $this->data->count() . ' item) - ' . now()->format('d-m-Y')
            : 'Per Tanggal: ' . now()->format('d-m-Y');
        $sheet->setCellValue('A7', $subtitleText);

        $sheet->mergeCells('A6:I6');
        $sheet->mergeCells('A7:I7');

        // Style untuk judul laporan
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getStyle('A6')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getRowDimension(6)->setRowHeight(25);

        $sheet->getStyle('A7')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Tambahkan jarak sebelum tabel
        $sheet->getRowDimension(8)->setRowHeight(15);

        // Add headers
        $headers = [
            'No',
            'Serial Number',
            'Nama Barang',
            'Kode Barang',
            'Jumlah Masuk',
            'Tanggal Masuk',
            'Diajukan Oleh',
            'Status',
            'Nama Project'
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers dengan gradient biru yang elegan
        $headerRange = 'A9:I9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR)
            ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F497D'))
            ->setEndColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E5984'));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Tambahkan data dengan grouping berdasarkan subkategori
        $row = 10;
        $no = 1;
        $totalBarangMasukKeseluruhan = 0;

        foreach ($this->groupedData as $subkategoriNama => $items) {
            // Hitung total jumlah barang masuk untuk subkategori ini
            $totalJumlahBarangMasuk = $items->sum('jumlah_barang_masuk');
            $totalBarangMasukKeseluruhan += $totalJumlahBarangMasuk;

            // Tambahkan header subkategori
            $headerText = $subkategoriNama . ' ( Total: ' . number_format($totalJumlahBarangMasuk) . ' unit)';
            $sheet->setCellValue('A' . $row, $headerText);
            $sheet->mergeCells('A' . $row . ':I' . $row);

            // Style untuk header subkategori
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E7F3FF');
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row)->getAlignment()->setIndent(1);
            $sheet->getRowDimension($row)->setRowHeight(22);

            $row++;

            // Tambahkan data items dalam subkategori
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, strtoupper($item->barang?->serial_number ?? '-'));
                $sheet->setCellValue('C' . $row, $item->barang?->nama_barang ?? '-');

                // Set kode_barang sebagai text untuk mempertahankan format
                $sheet->setCellValueExplicit('D' . $row, (string)($item->barang?->kode_barang ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // Format jumlah barang masuk
                $jumlahMasuk = (int)$item->jumlah_barang_masuk;
                $sheet->setCellValue('E' . $row, $jumlahMasuk);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

                // Format tanggal masuk - DIPERBAIKI: hapus jam (00:00:00)
                if ($item->tanggal_barang_masuk) {
                    $tanggalFormatted = \Carbon\Carbon::parse($item->tanggal_barang_masuk)->format('d/m/Y');
                    $sheet->setCellValue('F' . $row, $tanggalFormatted);
                } else {
                    $sheet->setCellValue('F' . $row, '-');
                }

                $sheet->setCellValue('G' . $row, ucwords($item->dibeli ?? '-'));

                // Format status
                $statusText = match ($item->status) {
                    'oprasional_kantor' => 'Operasional Kantor',
                    'project' => 'Project',
                    default => ucfirst($item->status ?? '-')
                };
                $sheet->setCellValue('H' . $row, $statusText);

                $sheet->setCellValue('I' . $row, $item->project_name ?? '-');

                // Style baris bergantian dengan warna yang lebih soft
                if ($row % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                } else {
                    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                }

                // Tambahkan border untuk setiap baris
                $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Set row height untuk konsistensi
                $sheet->getRowDimension($row)->setRowHeight(18);

                $row++;
            }

            // Tambahkan baris kosong setelah setiap group
            $sheet->getRowDimension($row)->setRowHeight(8);
            $row++;
        }

        $endRow = $row - 1;

        // Style sel data
        $dataRange = 'A10:I' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A10:A' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B10:B' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D10:D' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E10:E' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F10:F' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H10:H' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Ratakan kolom-kolom tertentu ke kiri dengan indent
        $sheet->getStyle('C10:C' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('C10:C' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('G10:G' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G10:G' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('I10:I' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('I10:I' . $endRow)->getAlignment()->setIndent(1);

        // Tambahkan footer dengan total keseluruhan
        $row += 1;
        $sheet->setCellValue('D' . $row, 'TOTAL KESELURUHAN BARANG MASUK:');
        $sheet->setCellValue('E' . $row, $totalBarangMasukKeseluruhan);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getRowDimension($row)->setRowHeight(25);

        // Atur lebar kolom yang lebih optimal - DIPERBAIKI: perlebar kolom yang diminta
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(16);  // Serial Number
        $sheet->getColumnDimension('C')->setWidth(28);  // Nama Barang
        $sheet->getColumnDimension('D')->setWidth(16);  // Kode Barang - DIPERLEBAR dari 12 ke 16
        $sheet->getColumnDimension('E')->setWidth(18);  // Jumlah Masuk - DIPERLEBAR dari 14 ke 18
        $sheet->getColumnDimension('F')->setWidth(15);  // Tanggal Masuk
        $sheet->getColumnDimension('G')->setWidth(18);  // Diajukan Oleh
        $sheet->getColumnDimension('H')->setWidth(20);  // Status - DIPERLEBAR dari 16 ke 20
        $sheet->getColumnDimension('I')->setWidth(20);  // Nama Project

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
        $filenamePrefix = $this->selectedRecords !== null ? 'Laporan-Barang-Masuk-Terpilih-' : 'Laporan-Barang-Masuk-';
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
