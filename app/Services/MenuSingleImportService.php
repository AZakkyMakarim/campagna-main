<?php

namespace App\Services;

use App\Imports\IngredientImport;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuComponent;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MenuSingleImportService
{
    /**
     * Import menu single dari file Excel/CSV.
     * Format baris: Nama Menu | SKU | Kategori | Harga Jual | Nama Bahan | Qty
     *
     * Setiap menu bisa punya banyak baris (banyak komponen).
     * Import akan CREATE menu baru jika SKU belum ada,
     * atau UPDATE & replace komponennya jika SKU sudah ada.
     */
    public function import(string $filePath, int $businessId, int $outletId): array
    {
        $import = new IngredientImport();
        Excel::import($import, $filePath);

        $rows = $import->getRows();

        if ($rows->isEmpty()) {
            return ['success' => 0, 'errors' => 1, 'messages' => ['File kosong atau format tidak dikenali.']];
        }

        // Group by SKU supaya bisa proses per menu
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

                $namaMenu = trim($firstRow['nama_menu'] ?? $firstRow['nama menu'] ?? '');
                $kategori = strtolower(trim($firstRow['kategori'] ?? ''));
                $hargaJual = (float) ($firstRow['harga_jual'] ?? $firstRow['harga jual'] ?? 0);
                $skuRaw = trim($firstRow['sku'] ?? '');

                // Validasi kolom wajib
                if (empty($namaMenu) || empty($skuRaw)) {
                    $errors++;
                    $messages[] = "Baris dengan SKU '{$skuRaw}': Nama Menu atau SKU kosong.";
                    continue;
                }

                if (!in_array($kategori, ['makanan', 'minuman'])) {
                    $errors++;
                    $messages[] = "Menu '{$namaMenu}': Kategori harus 'makanan' atau 'minuman', dapat '{$kategori}'.";
                    continue;
                }

                // Cari atau buat menu
                $menu = Menu::firstOrNew([
                    'sku' => $skuRaw,
                    'outlet_id' => $outletId,
                ]);

                $menu->fill([
                    'business_id' => $businessId,
                    'outlet_id' => $outletId,
                    'name' => $namaMenu,
                    'sku' => $skuRaw,
                    'category' => $kategori,
                    'type' => 'single',
                    'sell_price' => $hargaJual,
                    'hpp' => 0,
                    'is_active' => 1,
                ]);

                $menu->save();

                // Hapus komponen lama (replace logic)
                $menu->components()->delete();

                $totalHpp = 0;

                foreach ($components as $row) {
                    $namaBahan = trim($row['nama_bahan'] ?? $row['nama bahan'] ?? '');
                    $qty = (float) ($row['qty'] ?? $row['jumlah'] ?? 0);

                    if (empty($namaBahan)) {
                        $errors++;
                        $messages[] = "Menu '{$namaMenu}': Ada baris komponen tanpa nama bahan.";
                        continue;
                    }

                    $ingredient = Ingredient::where('outlet_id', $outletId)
                        ->whereRaw('LOWER(name) = ?', [strtolower($namaBahan)])
                        ->first();

                    if (!$ingredient) {
                        $errors++;
                        $messages[] = "Menu '{$namaMenu}': Bahan '{$namaBahan}' tidak ditemukan di sistem.";
                        continue;
                    }

                    MenuComponent::create([
                        'menu_id' => $menu->id,
                        'componentable_type' => Ingredient::class,
                        'componentable_id' => $ingredient->id,
                        'qty' => $qty,
                    ]);

                    // Hitung HPP dari avg_cost stock
                    $avgCost = $ingredient->stock?->avg_cost ?? 0;
                    $totalHpp += $qty * $avgCost;
                }

                // Update HPP menu
                $menu->update(['hpp' => $totalHpp]);

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
