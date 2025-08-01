<?php

namespace App\Filament\Resources\BarangResource\Pages;

use App\Filament\Resources\BarangResource;
use App\Models\Barang;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBarang extends EditRecord
{
    protected static string $resource = BarangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getTitle(): string|Htmlable
    {
        return 'Edit data barang';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Untuk edit, kita perlu mengambil data dari barang dengan kode_barang yang sama
        $record = $this->getRecord();

        // Ambil semua barang dengan kode_barang yang sama
        $relatedBarangs = Barang::where('kode_barang', $record->kode_barang)
            ->orderBy('id')
            ->get();

        // Siapkan data items untuk form
        $items = [];
        foreach ($relatedBarangs as $barang) {
            $items[] = [
                'id' => $barang->id,
                'serial_number' => $barang->serial_number,
                'spesifikasi' => $barang->spesifikasi ?? []
            ];
        }

        $data['items'] = $items;
        $data['jumlah_barang'] = count($items);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi data sebelum save
        $this->validateBarangData($data);
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $items = $data['items'] ?? [];
        $jumlahBarang = $data['jumlah_barang'];

        try {
            DB::beginTransaction();

            // Ambil semua barang dengan kode_barang yang sama
            $existingBarangs = Barang::where('kode_barang', $record->kode_barang)->get();
            $existingIds = $existingBarangs->pluck('id')->toArray();

            // Update atau create barang berdasarkan items
            $updatedIds = [];

            foreach ($items as $index => $item) {
                $barangData = [
                    'serial_number' => $item['serial_number'],
                    'kode_barang' => $data['kode_barang'],
                    'nama_barang' => $data['nama_barang'],
                    'jumlah_barang' => 1,
                    'kategori_id' => $data['kategori_id'],
                    'spesifikasi' => $item['spesifikasi'] ?? [],
                ];

                if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                    // Update existing
                    $barang = Barang::find($item['id']);
                    $barang->update($barangData);
                    $updatedIds[] = $item['id'];
                } else {
                    // Create new
                    $newBarang = Barang::create($barangData);
                    $updatedIds[] = $newBarang->id;
                }
            }

            // Hapus barang yang tidak ada di form
            $toDelete = array_diff($existingIds, $updatedIds);
            if (!empty($toDelete)) {
                Barang::whereIn('id', $toDelete)->delete();
            }

            DB::commit();

            Notification::make()
                ->title('Berhasil!')
                ->body("Berhasil mengupdate {$jumlahBarang} barang.")
                ->success()
                ->send();

            // Return record pertama yang diupdate
            return Barang::find($updatedIds[0]) ?? $record;
        } catch (\Exception $e) {
            DB::rollback();

            Notification::make()
                ->title('Gagal Update Data!')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }

    private function validateBarangData(array $data): void
    {
        $items = $data['items'] ?? [];
        $jumlahBarang = $data['jumlah_barang'] ?? 0;
        $record = $this->getRecord();

        // Validasi jumlah items harus sama dengan jumlah_barang
        if (count($items) !== $jumlahBarang) {
            throw new \Exception("Jumlah detail barang ({$jumlahBarang}) harus sama dengan jumlah form yang diisi (" . count($items) . ").");
        }

        // Ambil serial numbers
        $serialNumbers = [];
        foreach ($items as $index => $item) {
            $serialNumber = $item['serial_number'] ?? '';

            if (empty($serialNumber)) {
                throw new \Exception("Serial number barang #" . ($index + 1) . " tidak boleh kosong.");
            }

            $serialNumbers[] = $serialNumber;
        }

        // Validasi serial number harus unik dalam form
        if (count($serialNumbers) !== count(array_unique($serialNumbers))) {
            throw new \Exception('Setiap barang harus memiliki Serial Number yang berbeda.');
        }

        // Validasi serial number tidak duplikat dengan database (kecuali record yang sedang diedit)
        $existingBarangIds = Barang::where('kode_barang', $record->kode_barang)->pluck('id')->toArray();

        foreach ($serialNumbers as $index => $serial) {
            $duplicateQuery = Barang::where('serial_number', $serial);

            // Exclude barang yang sedang diedit
            if (!empty($existingBarangIds)) {
                $duplicateQuery->whereNotIn('id', $existingBarangIds);
            }

            if ($duplicateQuery->exists()) {
                throw new \Exception("Serial number '{$serial}' (Barang #" . ($index + 1) . ") sudah digunakan di database.");
            }
        }
    }
}
