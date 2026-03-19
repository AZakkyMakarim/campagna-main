<?php

namespace App\Services;

use App\Imports\IngredientImport;
use App\Models\Menu;
use App\Models\MenuComponent;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MenuBundleImportService
{
    /**
     * Import paket/bundle menu dari file Excel/CSV.
     * Format baris: Nama Paket | SKU | Kategori | Harga Jual | Nama Menu | Qty
     *
     * Setiap paket bisa punya banyak baris (banyak menu komponen).
     * Import akan CREATE paket baru jika SKU belum ada,
     * atau UPDATE & replace komponennya jika SKU sudah ada.
     *
     * Komponen yang direferensi harus berupa Menu ber-tipe 'single' yang sudah ada.
     */
    public function import(string $filePath, int $businessId, int $outletId): array
    {
        $import = new IngredientImport();
        Excel::import($import, $filePath,null,\Maatwebsite\Excel\Excel::XLSX);

        $rows = $import->getRows();

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => 1, 'messages' => ['File kosong atau format tidak dikenali.']];
        }

        // Group by SKU supaya bisa proses per paket
        $grouped = $rows->groupBy(function ($row) {
            return strtolower(trim($row['sku'] ?? ''));
        });

        $success = 0;
        $errors = 0;
        $messages = [];

        DB::beginTransaction();

        try {
            foreach ($grouped as $sku => $components) {
                $firstRow = $components->first();

                $namaPaket = trim($firstRow['nama_paket'] ?? $firstRow['nama paket'] ?? '');
                $kategori = strtolower(trim($firstRow['kategori'] ?? ''));
                $hargaJual = (float) ($firstRow['harga_jual'] ?? $firstRow['harga jual'] ?? 0);
                $skuRaw = trim($firstRow['sku'] ?? '');

                // Validasi kolom wajib
                if (empty($namaPaket) || empty($skuRaw)) {
                    $errors++;
                    $messages[] = "Baris dengan SKU '{$skuRaw}': Nama Paket atau SKU kosong.";
                    continue;
                }

                if (empty($kategori)) {
                    $errors++;
                    $messages[] = "Paket '{$namaPaket}': Kategori tidak boleh kosong.";
                    continue;
                }

                // Cari atau buat bundle menu
                $bundle = Menu::firstOrNew([
                    'sku' => $skuRaw,
                    'outlet_id' => $outletId,
                ]);

                $bundle->fill([
                    'business_id' => $businessId,
                    'outlet_id' => $outletId,
                    'name' => $namaPaket,
                    'sku' => $skuRaw,
                    'category' => $kategori,
                    'type' => 'bundle',
                    'sell_price' => $hargaJual,
                    'hpp' => 0,
                    'is_active' => 1,
                ]);

                $bundle->save();

                // Hapus komponen lama (replace logic)
                $bundle->components()->delete();

                $totalHpp = 0;

                foreach ($components as $row) {
                    $namaMenu = trim($row['nama_menu'] ?? $row['nama menu'] ?? '');
                    $qty = (float) ($row['qty'] ?? $row['jumlah'] ?? 0);

                    if (empty($namaMenu)) {
                        $errors++;
                        $messages[] = "Paket '{$namaPaket}': Ada baris tanpa nama menu komponen.";
                        continue;
                    }

                    // Cari menu single berdasarkan nama
                    $menu = Menu::where('outlet_id', $outletId)
                        ->where('type', 'single')
                        ->whereRaw('LOWER(name) = ?', [strtolower($namaMenu)])
                        ->first();

                    if (!$menu) {
                        $errors++;
                        $messages[] = "Paket '{$namaPaket}': Menu '{$namaMenu}' tidak ditemukan di daftar Menu Single.";
                        continue;
                    }

                    MenuComponent::create([
                        'menu_id' => $bundle->id,
                        'componentable_type' => Menu::class,
                        'componentable_id' => $menu->id,
                        'qty' => $qty,
                    ]);

                    // HPP bundel = HPP tiap menu × qty
                    $totalHpp += ($menu->hpp ?? 0) * $qty;
                }

                // Update HPP paket
                $bundle->update(['hpp' => $totalHpp]);

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
