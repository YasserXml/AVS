<?php

namespace App\Exports;

use App\Models\Barang;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangExporter
{
    protected $data;
    protected $spreadsheet;
    protected $selectedRecords = null; // Property untuk menyimpan data terpilih
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
            // Pastikan relasi kategori dan subkategori di-load
            $this->data = $this->selectedRecords->load(['kategori', 'subkategori']);
        } else {
            // Jika tidak ada data terpilih, ambil semua data
            $this->data = Barang::with(['kategori', 'subkategori'])
                ->whereNull('deleted_at')
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

    public function withQuery(\Closure $queryCallback)
    {
        // Hanya bisa digunakan jika tidak ada selected records
        if ($this->selectedRecords === null) {
            $query = Barang::with(['kategori', 'subkategori'])->whereNull('deleted_at');
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
            ->setTitle('Laporan Data Barang')
            ->setSubject('Inventory')
            ->setDescription('Laporan Data Barang Inventory')
            ->setKeywords('laporan, inventory, barang')
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
        $sheet->mergeCells('C1:G1');
        $sheet->mergeCells('C2:G2');
        $sheet->mergeCells('C3:G3');
        $sheet->mergeCells('C4:G4');

        // Tambahkan garis bawah dan atas untuk header
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '008000'], // Warna hijau untuk border
                ],
            ],
        ];
        $sheet->getStyle('B1:G4')->applyFromArray($styleArray);

        // Style untuk teks header
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C2:C4')->getFont()->setSize(11);
        $sheet->getStyle('C2:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tambahkan jarak sebelum judul utama
        $sheet->getRowDimension(5)->setRowHeight(10);

        // Judul laporan
        $sheet->setCellValue('A6', 'LAPORAN KETERSEDIAAN BARANG');

        $subtitleText = $this->selectedRecords !== null
            ? 'Data Terpilih (' . $this->data->count() . ' item) - ' . now()->format('d-m-Y')
            : 'Per Tanggal: ' . now()->format('d-m-Y');
        $sheet->setCellValue('A7', $subtitleText);

        $sheet->mergeCells('A6:G6');
        $sheet->mergeCells('A7:G7');

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

        // Tambahkan header kolom
        $headers = [
            'No',
            'Serial Number',
            'Kode Barang',
            'Nama Barang',
            'Jumlah Barang',
            'Kategori',
            'Subkategori'
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers dengan gradient biru yang elegan
        $headerRange = 'A9:G9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR)
            ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F497D'))
            ->setEndColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E5984'));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Tambahkan data dengan grouping
        $row = 10;
        $no = 1;

        foreach ($this->groupedData as $subkategoriNama => $items) {
            // Hitung total jumlah barang untuk subkategori ini
            $totalJumlahBarang = $items->sum('jumlah_barang');

            // Tambahkan header subkategori
            $sheet->setCellValue('A' . $row, $subkategoriNama . ' (Total: ' . number_format($totalJumlahBarang) . ')');
            $sheet->mergeCells('A' . $row . ':G' . $row);

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
                $sheet->setCellValue('B' . $row, strtoupper($item->serial_number));

                // Set kode_barang sebagai text untuk mempertahankan format seperti 02
                $sheet->setCellValueExplicit('C' . $row, (string)$item->kode_barang, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->setCellValue('D' . $row, $item->nama_barang);

                // Format jumlah barang
                $jumlahBarang = (int)$item->jumlah_barang;
                $sheet->setCellValue('E' . $row, $jumlahBarang);
                $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

                // Kategori
                $kategoriNama = $item->kategori?->nama_kategori ?? '-';
                $sheet->setCellValue('F' . $row, $kategoriNama);

                // Subkategori
                $subkategoriItemNama = $item->subkategori?->nama_subkategori ?? '-';
                $sheet->setCellValue('G' . $row, $subkategoriItemNama);

                // Style baris bergantian dengan warna yang lebih soft
                if ($row % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                } else {
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
                }

                // Tambahkan border untuk setiap baris
                $sheet->getStyle('A' . $row . ':G' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Set row height untuk konsistensi
                $sheet->getRowDimension($row)->setRowHeight(18);

                $row++;
            }

            // Tambahkan baris kosong setelah setiap group (opsional)
            $sheet->getRowDimension($row)->setRowHeight(8);
            $row++;
        }

        $endRow = $row - 1;

        // Style sel data
        $dataRange = 'A10:G' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A10:A' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B10:B' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C10:C' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E10:E' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Ratakan kolom nama barang, kategori, dan subkategori ke kiri dengan indent
        $sheet->getStyle('D10:D' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D10:D' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('F10:F' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F10:F' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('G10:G' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G10:G' . $endRow)->getAlignment()->setIndent(1);

        // Atur lebar kolom yang lebih optimal
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(16);  // Serial Number
        $sheet->getColumnDimension('C')->setWidth(12);  // Kode Barang
        $sheet->getColumnDimension('D')->setWidth(32);  // Nama Barang
        $sheet->getColumnDimension('E')->setWidth(14);  // Jumlah Barang
        $sheet->getColumnDimension('F')->setWidth(18);  // Kategori
        $sheet->getColumnDimension('G')->setWidth(18);  // Subkategori

        // Set print area dan page setup
        $sheet->getPageSetup()->setPrintArea('A1:G' . ($row + 1));
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
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
        $filenamePrefix = $this->selectedRecords !== null ? 'Laporan-Ketersediaan-Barang-Terpilih-' : 'Laporan-Ketersediaan-Barang-';
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
