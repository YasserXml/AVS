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
    protected $selectedRecords = null; // Tambahkan property untuk menyimpan data terpilih

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
            // Pastikan relasi kategori di-load
            $this->data = $this->selectedRecords->load('kategori');
        } else {
            // Jika tidak ada data terpilih, ambil semua data
            $this->data = Barang::with('kategori')
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
            $query = Barang::with('kategori')->whereNull('deleted_at');
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
        $sheet->mergeCells('C1:F1');
        $sheet->mergeCells('C2:F2');
        $sheet->mergeCells('C3:F3');
        $sheet->mergeCells('C4:F4');

        // Tambahkan garis bawah dan atas untuk header
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '008000'], // Warna hijau untuk border
                ],
            ],
        ];
        $sheet->getStyle('B1:F4')->applyFromArray($styleArray);

        // Style untuk teks header
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('C2:C4')->getFont()->setSize(11);
        $sheet->getStyle('C2:C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Tambahkan jarak sebelum judul utama
        $sheet->getRowDimension(5)->setRowHeight(10);

        // Modifikasi judul laporan berdasarkan jenis export
        // Judul laporan tetap sama
        $sheet->setCellValue('A6', 'LAPORAN KETERSEDIAAN BARANG');

        $subtitleText = $this->selectedRecords !== null
            ? 'Data Terpilih (' . $this->data->count() . ' item) - ' . now()->format('d-m-Y')
            : 'Per Tanggal: ' . now()->format('d-m-Y');
        $sheet->setCellValue('A7', $subtitleText);

        $sheet->mergeCells('A6:F6');
        $sheet->mergeCells('A7:F7');

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
            'Kategori'
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers dengan gradient biru yang elegan
        $headerRange = 'A9:F9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR)
            ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1F497D'))
            ->setEndColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2E5984'));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM);
        $sheet->getRowDimension(9)->setRowHeight(25);

        // Tambahkan data
        $row = 10;
        $startRow = $row;
        $no = 1;

        foreach ($this->data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, strtoupper($item->serial_number));
            $sheet->setCellValue('C' . $row, $item->kode_barang);
            $sheet->setCellValue('D' . $row, $item->nama_barang);

            // Format jumlah barang dengan conditional formatting
            $jumlahBarang = (int)$item->jumlah_barang;
            $sheet->setCellValue('E' . $row, $jumlahBarang);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');

            // Kategori dengan styling
            $kategoriNama = $item->kategori?->nama_kategori ?? '-';
            $sheet->setCellValue('F' . $row, $kategoriNama);

            // Style baris bergantian dengan warna yang lebih soft
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
            } else {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFFFF');
            }

            // Tambahkan border untuk setiap baris
            $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Set row height untuk konsistensi
            $sheet->getRowDimension($row)->setRowHeight(18);

            $row++;
        }

        $endRow = $row - 1;

        // Style sel data
        $dataRange = 'A' . $startRow . ':F' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A' . $startRow . ':A' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $startRow . ':B' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $startRow . ':C' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $startRow . ':E' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Ratakan kolom nama barang dan kategori ke kiri dengan indent
        $sheet->getStyle('D' . $startRow . ':D' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D' . $startRow . ':D' . $endRow)->getAlignment()->setIndent(1);
        $sheet->getStyle('F' . $startRow . ':F' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $startRow . ':F' . $endRow)->getAlignment()->setIndent(1);

        // Hitung total barang dan statistik
        $totalBarang = $this->data->sum('jumlah_barang');
        $totalKategori = $this->data->pluck('kategori.nama_kategori')->filter()->unique()->count();
        $stokAman = $this->data->where('jumlah_barang', '>', 10)->count();
        $stokRendah = $this->data->where('jumlah_barang', '<=', 10)->where('jumlah_barang', '>', 0)->count();
        $stokHabis = $this->data->where('jumlah_barang', '<=', 0)->count();

        // Tambahkan separator line
        $row++;
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

        // Atur lebar kolom yang lebih optimal
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(16);  // Serial Number
        $sheet->getColumnDimension('C')->setWidth(12);  // Kode Barang
        $sheet->getColumnDimension('D')->setWidth(32);  // Nama Barang
        $sheet->getColumnDimension('E')->setWidth(14);  // Jumlah Barang
        $sheet->getColumnDimension('F')->setWidth(18);  // Kategori

        // Set print area dan page setup
        $sheet->getPageSetup()->setPrintArea('A1:F' . ($row + 1));
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Set margins
        // (You can add margin settings here if needed)
    }

    /**
     * Generate download response
     *
     * @return StreamedResponse
     */
    protected function downloadResponse()
    {
        $filenamePrefix = $this->selectedRecords !== null ? 'Laporan-Ketersediaan-Barang' : 'Laporan-Ketersediaan-Barang-';
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
