<?php

namespace App\Exports;

use App\Models\Asetpt;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AsetExporter
{
    protected $data;
    protected $spreadsheet;
    protected $selectedRecords = null;

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
        $this->setupSpreadsheet();

        return $this->downloadResponse();
    }

    /**
     * Load data from database
     */
    protected function loadData()
    {
        if ($this->selectedRecords !== null) {
            // gunakan selectedRecords langsung karena sudah berupa Collection
            // dan pastikan relasi sudah di-load
            $this->data = $this->selectedRecords->load(['barang']);
        } else {
            $this->data = AsetPt::with(['barang'])->get();
        }
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
        $query = AsetPt::with(['barang']);
        $queryCallback($query);
        $this->data = $query->get();

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
            ->setTitle('Laporan Aset PT')
            ->setSubject('Aset PT')
            ->setDescription('Laporan Data Aset PT')
            ->setKeywords('laporan, aset pt, inventory')
            ->setCategory('Laporan');

        // Tambahkan logo - Ukuran dan posisi disesuaikan
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo Perusahaan');
        $drawing->setPath(public_path('images/logoAVS.png')); // Sesuaikan dengan path logo Anda
        $drawing->setHeight(115); // Ukuran logo disesuaikan
        $drawing->setWidth(100); // Ukuran logo disesuaikan
        $drawing->setCoordinates('B1'); // Posisi di B1 sesuai screenshot
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);

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

        // Tambahkan judul laporan
        $sheet->setCellValue('A6', 'LAPORAN DATA ASET PT');
        $sheet->setCellValue('A7', 'Per Tanggal: ' . now()->format('d-m-Y'));
        $sheet->mergeCells('A6:G6');
        $sheet->mergeCells('A7:G7');

        // Style untuk judul laporan
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DCE6F1');
        $sheet->getRowDimension(6)->setRowHeight(25);

        $sheet->getStyle('A7')->getFont()->setItalic(true)->setSize(11);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // Tambahkan jarak sebelum tabel
        $sheet->getRowDimension(8)->setRowHeight(10);

        // Add headers
        $headers = [
            'Tanggal',
            'Nama Barang',
            'Qty',
            'Brand',
            'Status',
            'PIC',
            'Kondisi'
        ];

        $column = 'A';
        $row = 9;

        foreach ($headers as $header) {
            $sheet->setCellValue($column++ . $row, $header);
        }

        // Style headers
        $headerRange = 'A9:G9';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1F497D');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getRowDimension(9)->setRowHeight(20);

        // Add data
        $row = 10;
        $startRow = $row;
        $totalQty = 0;

        foreach ($this->data as $item) {
            // Format tanggal - hanya tanggal tanpa jam
            $tanggal = $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-';
            $sheet->setCellValue('A' . $row, $tanggal);

            $sheet->setCellValue('B' . $row, $item->nama_barang ?? '-');

            // Format qty
            $qty = (int)$item->qty;
            $sheet->setCellValue('C' . $row, $qty);
            $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $totalQty += $qty;

            $sheet->setCellValue('D' . $row, $item->brand ?? '-');

            // Format status
            $statusText = match ($item->status) {
                'pengembalian' => 'Pengembalian',
                'stok' => 'Stok',
                default => ucfirst($item->status ?? '-')
            };
            $sheet->setCellValue('E' . $row, $statusText);

            $sheet->setCellValue('F' . $row, $item->pic ?? '-');

            // Format kondisi
            $kondisiText = match ($item->kondisi) {
                'baik' => 'Baik',
                'rusak' => 'Rusak',
                default => ucfirst($item->kondisi ?? '-')
            };
            $sheet->setCellValue('G' . $row, $kondisiText);

            // Style baris bergantian
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
            }

            $row++;
        }

        $endRow = $row - 1;

        // Style sel data
        $dataRange = 'A' . $startRow . ':G' . $endRow;
        $sheet->getStyle($dataRange)->getFont()->setSize(11);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Ratakan kolom-kolom tertentu ke tengah
        $sheet->getStyle('A' . $startRow . ':A' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $startRow . ':C' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $startRow . ':E' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G' . $startRow . ':G' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Ratakan kolom-kolom tertentu ke kiri
        $sheet->getStyle('B' . $startRow . ':B' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D' . $startRow . ':D' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $startRow . ':F' . $endRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Tambahkan footer dengan total
        $sheet->setCellValue('B' . $row, 'TOTAL QTY:');
        $sheet->setCellValue('C' . $row, $totalQty);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('B' . $row . ':C' . $row)->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $row . ':C' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
        $sheet->getStyle('B' . $row . ':C' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Tambahkan ringkasan berdasarkan status
        $row += 2;
        $sheet->setCellValue('A' . $row, 'RINGKASAN BERDASARKAN STATUS:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A' . $row . ':G' . $row);

        $row++;
        $statusSummary = $this->data->groupBy('status')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total' => $items->sum('qty')
            ];
        });

        foreach ($statusSummary as $status => $summary) {
            $statusText = match ($status) {
                'pengembalian' => 'Pengembalian',
                'stok' => 'Stok',
                default => ucfirst($status)
            };

            $sheet->setCellValue('B' . $row, $statusText . ':');
            $sheet->setCellValue('C' . $row, $summary['count'] . ' item');
            $sheet->setCellValue('D' . $row, $summary['total'] . ' unit');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setSize(11);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Tambahkan ringkasan berdasarkan kondisi
        $row += 1;
        $sheet->setCellValue('A' . $row, 'RINGKASAN BERDASARKAN KONDISI:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
        $sheet->mergeCells('A' . $row . ':G' . $row);

        $row++;
        $kondisiSummary = $this->data->groupBy('kondisi')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total' => $items->sum('qty')
            ];
        });

        foreach ($kondisiSummary as $kondisi => $summary) {
            $kondisiText = match ($kondisi) {
                'baik' => 'Baik',
                'rusak' => 'Rusak',
                default => ucfirst($kondisi)
            };

            $sheet->setCellValue('B' . $row, $kondisiText . ':');
            $sheet->setCellValue('C' . $row, $summary['count'] . ' item');
            $sheet->setCellValue('D' . $row, $summary['total'] . ' unit');
            $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0');

            $sheet->getStyle('B' . $row . ':D' . $row)->getFont()->setSize(11);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Atur lebar kolom
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(12);
    }

    /**
     * Generate download response
     *
     * @return StreamedResponse
     */
    protected function downloadResponse()
    {
        $filename = 'Laporan-Aset-PT-' . now()->format('d-m-Y') . '.xlsx';

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
