<?php

namespace App\Services;

use App\Imports\IngredientImport;
use App\Models\Ingredient;
use App\Models\Vendor;
use App\Models\VendorIngredient;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VendorImportService
{
    /**
     * Import vendor dari file Excel/CSV.
     * Format baris: Nama Vendor | No Telp | Alamat | Link Maps | Nama Bank | Nomor Rekening | Nama Penerima
     *
     * Import akan CREATE vendor baru jika nama+nomer telp belum ada,
     * atau UPDATE jika sudah ada.
     */
    public function import(string $filePath, int $businessId, int $outletId): array
    {
        $import = new IngredientImport();
        Excel::import($import, $filePath);

        $rows = $import->getRows();

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => 1, 'messages' => ['File kosong atau format tidak dikenali.']];
        }

        // Group by kombinasi nama vendor + no telp (key unik)
        $grouped = $rows->groupBy(function ($row) {
            $nama = strtolower(trim($row['nama_vendor'] ?? $row['nama vendor'] ?? ''));
            $telp = trim($row['no_telp'] ?? $row['no telp'] ?? '');
            return $nama . '||' . $telp;
        });

        $success = 0;
        $errors = 0;
        $messages = [];

        DB::beginTransaction();

        try {
            foreach ($grouped as $key => $rows) {
                $firstRow = $rows->first();

                $namaVendor = trim($firstRow['nama_vendor'] ?? $firstRow['nama vendor'] ?? '');
                $noTelp = trim($firstRow['no_telp'] ?? $firstRow['no telp'] ?? '');
                $alamat = trim($firstRow['alamat'] ?? '');
                $linkMaps = trim($firstRow['link_maps'] ?? $firstRow['link maps'] ?? '');
                $namaBank = trim($firstRow['nama_bank'] ?? $firstRow['nama bank'] ?? '');
                $nomorRekening = trim($firstRow['nomor_rekening'] ?? $firstRow['nomor rekening'] ?? $firstRow['no_rekening'] ?? $firstRow['no rekening'] ?? '');
                $namaPenerima = trim($firstRow['nama_penerima'] ?? $firstRow['nama penerima'] ?? '');

                if (empty($namaVendor)) {
                    $errors++;
                    $messages[] = "Ada baris dengan Nama Vendor kosong, dilewati.";
                    continue;
                }

                // Cari atau buat vendor (match by name + phone per business)
                $vendor = Vendor::firstOrNew([
                    'business_id' => $businessId,
                    'name' => $namaVendor,
                ]);

                $vendor->fill([
                    'business_id' => $businessId,
                    'name' => $namaVendor,
                    'phone_number' => $noTelp,
                    'address' => $alamat,
                    'link_maps' => $linkMaps ?: null,
                    'bank_name' => $namaBank ?: null,
                    'bank_account_number' => $nomorRekening ?: null,
                    'bank_account_name' => $namaPenerima ?: null,
                    'is_active' => 1,
                ]);

                // Save vendor
                $vendor->save();

                $success++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'messages' => $messages,
        ];
    }
}
