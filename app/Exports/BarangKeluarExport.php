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
    protected $groupedData; // Property untuk data yang sudah digroup

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
        $this->groupDataBySubkategori();
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
            // Pastikan relasi di-load dengan benar
            $this->data = $this->selectedRecords->load([
                'barang.kategori',
                'barang.subkategori',
                'user'
            ]);
        } else {
            // Jika tidak ada data terpilih, ambil semua data
            $this->data = BarangKeluar::with([
                'barang.kategori',
                'barang.subkategori',
                'user'
            ])
                ->join('barangs', 'barangkeluars.barang_id', '=', 'barangs.id')
                ->whereNull('barangkeluars.deleted_at')
                ->orderBy('barangs.subkategori_id', 'asc')
                ->orderBy('barangkeluars.created_at', 'desc')
                ->select('barangkeluars.*')
                ->get();
        }

        return $this;
    }

    /**
     * Group data berdasarkan subkategori
     */
    protected function groupDataBySubkategori()
    {
        // Jika data dari selected records, kita perlu sort ulang
        if ($this->selectedRecords !== null) {
            $this->data = $this->data->sortBy(function ($item) {
                return $item->barang->subkategori_id ?? 999999; // Null values di akhir
            })->values(); // Reset index array
        }

        $this->groupedData = $this->data->groupBy(function ($item) {
            if ($item->barang && $item->barang->subkategori) {
                return $item->barang->subkategori->nama_subkategori;
            }
            return 'Tanpa Subkategori';
        });

        return $this;
    }

    public function withQuery(\Closure $queryCallback)
    {
        // Hanya bisa digunakan jika tidak ada selected records
        if ($this->selectedRecords === null) {
            $query = BarangKeluar::with([
                'barang.kategori',
                'barang.subkategori',
                'user'
            ])
                ->join('barangs', 'barangkeluars.barang_id', '=', 'barangs.id')
                ->whereNull('barangkeluars.deleted_at');

            $queryCallback($query);

            $this->data = $query
                ->orderBy('barangs.subkategori_id', 'asc')
                ->orderBy('barangkeluars.created_at', 'desc')
                ->select('barangkeluars.*')
                ->get();
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
        $sheet->mergeCells('C1:K1');
        $sheet->mergeCells('C2:K2');
        $sheet->mergeCells('C3:K3');
        $sheet->mergeCells('C4:K4');

        // Tambahkan garis bawah dan atas untuk header
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '008000'], // Warna hijau untuk border
                ],
            ],
        ];
        $sheet->getStyle('B1:K4')->applyFromArray($styleArray);

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

        $sheet->mergeCells('A6:K6');
        $sheet->mergeCells('A7:K7');

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
            'Serial Number',
            'Nama Barang',
            'Kategori',
            'Jumlah Keluar',
            'Status',
            'Nama Project',
            'Sumber',
            'Yang Input',
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers dengan gradient biru yang elegan
        $headerRange = 'A9:K9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->setColor(new Color(Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_GRADIENT_LINEAR)
            ->setStartColor(new Color('1F497D'))
            ->setEndColor(new Color('2E5984'));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Tambahkan data dengan grouping berdasarkan subkategori
        $row = 10;
        $no = 1;
        $totalBarangKeluarKeseluruhan = 0;

        foreach ($this->groupedData as $subkategoriNama => $items) {
            // Hitung total jumlah barang keluar untuk subkategori ini
            $totalJumlahBarangKeluar = $items->sum('jumlah_barang_keluar');
            $totalBarangKeluarKeseluruhan += $totalJumlahBarangKeluar;

            // Tambahkan header subkategori
            $headerText = $subkategoriNama . ' ( Total: ' . number_format($totalJumlahBarangKeluar) . ' unit)';
            $sheet->setCellValue('A' . $row, $headerText);
            $sheet->mergeCells('A' . $row . ':K' . $row);

            // Style untuk header subkategori
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7F3FF');
            $sheet->getStyle('A' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . $row)->getAlignment()->setIndent(1);
            $sheet->getRowDimension($row)->setRowHeight(22);

            $row++;

            // Tambahkan data items dalam subkategori
            foreach ($items as $item) {
                $sheet->setCellValue('A' . $row, $no++);

                // Tanggal Keluar
                $tanggalKeluar = $item->tanggal_keluar_barang
                    ? \Carbon\Carbon::parse($item->tanggal_keluar_barang)->format('d/m/Y')
                    : '-';
                $sheet->setCellValue('B' . $row, $tanggalKeluar);

                // Kode Barang
                $sheet->setCellValueExplicit('C' . $row, (string)($item->barang->kode_barang ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // Serial Number
                $sheet->setCellValue('D' . $row, strtoupper($item->barang->serial_number ?? '-'));

                // Nama Barang
                $sheet->setCellValue('E' . $row, $item->barang->nama_barang ?? '-');

                // Kategori
                $sheet->setCellValue('F' . $row, $item->barang->kategori->nama_kategori ?? '-');

                // Jumlah Keluar
                $jumlahKeluar = (int)$item->jumlah_barang_keluar;
                $sheet->setCellValue('G' . $row, $jumlahKeluar);
                $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

                // Status
                $status = match ($item->status) {
                    'oprasional_kantor' => 'Operasional Kantor',
                    'project' => 'Project',
                    default => ucfirst($item->status ?? '-')
                };
                $sheet->setCellValue('H' . $row, $status);

                // Nama Project
                $sheet->setCellValue('I' . $row, $item->project_name ?? '-');

                // Sumber
                $sumber = match ($item->sumber) {
                    'manual' => 'Manual',
                    'pengajuan' => 'Pengajuan',
                    default => ucfirst($item->sumber ?? 'Manual')
                };
                $sheet->setCellValue('J' . $row, $sumber);

                // Yang Input (user yang menginput data)
                $yangInput = $item->user?->name ?? '-';
                $sheet->setCellValue('K' . $row, ucwords($yangInput));

                // Style baris bergantian dengan warna yang lebih soft
                if ($row % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                } else {
                    $sheet->getStyle('A' . $row . ':K' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                }

                // Tambahkan border untuk setiap baris
                $sheet->getStyle('A' . $row . ':K' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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
        $dataRange = 'A10:K' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A10:A' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
        $sheet->getStyle('B10:B' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
        $sheet->getStyle('C10:C' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kode Barang
        $sheet->getStyle('D10:D' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Serial Number
        $sheet->getStyle('G10:G' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jumlah
        $sheet->getStyle('H10:H' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
        $sheet->getStyle('J10:J' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Sumber

        // Ratakan kolom nama barang, kategori, project, dan yang input ke kiri dengan indent
        $sheet->getStyle('E10:E' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Nama Barang
        $sheet->getStyle('E10:E' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('F10:F' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Kategori
        $sheet->getStyle('F10:F' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('I10:I' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Project
        $sheet->getStyle('I10:I' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('K10:K' . $endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Yang Input
        $sheet->getStyle('K10:K' . $endRow)->getAlignment()->setIndent(1);

        // Tambahkan footer dengan total keseluruhan
        $row += 1;
        $sheet->setCellValue('F' . $row, 'TOTAL KESELURUHAN BARANG KELUAR:');
        $sheet->setCellValue('G' . $row, $totalBarangKeluarKeseluruhan);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $row . ':G' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getRowDimension($row)->setRowHeight(25);

        // Atur lebar kolom yang lebih optimal
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(14);  // Tanggal Keluar
        $sheet->getColumnDimension('C')->setWidth(16);  // Kode Barang
        $sheet->getColumnDimension('D')->setWidth(16);  // Serial Number
        $sheet->getColumnDimension('E')->setWidth(28);  // Nama Barang
        $sheet->getColumnDimension('F')->setWidth(16);  // Kategori
        $sheet->getColumnDimension('G')->setWidth(14);  // Jumlah Keluar
        $sheet->getColumnDimension('H')->setWidth(18);  // Status
        $sheet->getColumnDimension('I')->setWidth(20);  // Nama Project
        $sheet->getColumnDimension('J')->setWidth(12);  // Sumber
        $sheet->getColumnDimension('K')->setWidth(18);  // Yang Input

        // Set print area dan page setup
        $sheet->getPageSetup()->setPrintArea('A1:K' . ($row + 1));
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
        $filenamePrefix = $this->selectedRecords !== null ? 'Laporan-Barang-Keluar-Terpilih-' : 'Laporan-Barang-Keluar-';
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
