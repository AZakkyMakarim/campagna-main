<?php

namespace App\Services;

use App\Imports\IngredientImport;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RecipeSemiImportService
{
    /**
     * Import semi-ingredient recipes from a flat Excel/CSV file.
     *
     * Expected headings (slugged by library):
     *   no | nama_semi | qty_hasil | satuan_hasil | bahan_komponen | qty_komponen | satuan_komponen
     *
     * Each semi ingredient may span multiple rows (one row per component).
     * The import will REPLACE (delete + recreate) recipe items for every semi
     * ingredient found in the file.
     *
     * @return array{success: int, errors: int, messages: string[]}
     */
    public function import(string $filePath, int $businessId, int $outletId): array
    {
        // ── 1. Read file ──────────────────────────────────────────────────────
        try {
            if (!file_exists($filePath)) {
                return ['success' => 0, 'errors' => 1, 'messages' => ['File tidak ditemukan.']];
            }

            $import = new IngredientImport();
            Excel::import($import, $filePath);
            $rows = $import->getRows();
        } catch (\Exception $e) {
            return ['success' => 0, 'errors' => 1, 'messages' => ['Gagal membaca file: ' . $e->getMessage()]];
        }

        // ── 2. Group rows by Nama Semi ────────────────────────────────────────
        $grouped = [];
        $rowNumber = 1;
        $parseErrors = [];

        foreach ($rows as $row) {
            $rowNumber++;

            $namaRaw = $row['nama_semi'] ?? $row['nama semi'] ?? null;
            $qtyHasil = $row['qty_hasil'] ?? $row['qty hasil'] ?? null;
            $satuanH = $row['satuan_hasil'] ?? $row['satuan hasil'] ?? null;
            $komponen = $row['bahan_komponen'] ?? $row['bahan komponen'] ?? null;
            $qtyKomp = $row['qty_komponen'] ?? $row['qty komponen'] ?? null;
            $satuanK = $row['satuan_komponen'] ?? $row['satuan komponen'] ?? null;

            // Skip completely empty rows
            if (!$namaRaw && !$komponen) {
                continue;
            }

            if (!$namaRaw) {
                $parseErrors[] = "Baris $rowNumber: kolom 'Nama Semi' kosong. Baris di-skip.";
                continue;
            }

            $nama = trim($namaRaw);

            if (!isset($grouped[$nama])) {
                $grouped[$nama] = [
                    'qty_hasil' => $qtyHasil,
                    'satuan_hasil' => $satuanH ? trim($satuanH) : null,
                    'components' => [],
                ];
            }

            // A row may only define the semi header without a component
            if ($komponen) {
                $grouped[$nama]['components'][] = [
                    'bahan_komponen' => trim($komponen),
                    'qty_komponen' => $qtyKomp,
                    'satuan_komponen' => $satuanK ? trim($satuanK) : null,
                    'row' => $rowNumber,
                ];
            }
        }

        // ── 3. Process each semi ingredient ───────────────────────────────────
        $success = 0;
        $errors = count($parseErrors);
        $errorMessages = $parseErrors;

        foreach ($grouped as $namasSemi => $data) {
            DB::beginTransaction();
            try {
                // Lookup semi ingredient
                $semiIngredient = Ingredient::where('business_id', $businessId)
                    ->where('outlet_id', $outletId)
                    ->where('type', 'semi')
                    ->whereRaw('LOWER(name) = ?', [strtolower($namasSemi)])
                    ->first();

                if (!$semiIngredient) {
                    $errors++;
                    $errorMessages[] = "Bahan semi '$namasSemi' tidak ditemukan di sistem. Pastikan sudah ditambahkan di halaman Bahan 1/2 Jadi.";
                    DB::rollBack();
                    continue;
                }

                // Lookup hasil unit (optional – use ingredient base unit as fallback)
                $unitHasil = null;
                if ($data['satuan_hasil']) {
                    $unitHasil = Unit::where('name', 'like', $data['satuan_hasil'])
                        ->orWhere('symbol', 'like', $data['satuan_hasil'])
                        ->first();

                    if (!$unitHasil) {
                        $errors++;
                        $errorMessages[] = "Satuan hasil '$data[satuan_hasil]' untuk '$namasSemi' tidak ditemukan. Baris di-skip.";
                        DB::rollBack();
                        continue;
                    }
                } else {
                    $unitHasil = $semiIngredient->baseUnit;
                }

                // firstOrCreate Recipe for this semi ingredient
                $recipe = Recipe::firstOrCreate(
                    [
                        'outlet_id' => $outletId,
                        'ingredient_id' => $semiIngredient->id,
                    ],
                    [
                        'business_id' => $businessId,
                        'name' => $semiIngredient->name,
                        'unit_id' => $unitHasil?->id,
                        'quantity' => $data['qty_hasil'] ?? 1,
                    ]
                );

                // Update qty & unit if recipe already existed
                $recipe->update([
                    'quantity' => $data['qty_hasil'] ?? $recipe->quantity,
                    'unit_id' => $unitHasil?->id ?? $recipe->unit_id,
                ]);

                // Replace all recipe items
                $recipe->items()->delete();

                foreach ($data['components'] as $comp) {
                    $rowNum = $comp['row'];
                    $namaKomp = $comp['bahan_komponen'];
                    $qtyKomp = $comp['qty_komponen'];
                    $satuanK = $comp['satuan_komponen'];

                    // Lookup raw ingredient (any type except finished)
                    $ingKomp = Ingredient::where('business_id', $businessId)
                        ->where('outlet_id', $outletId)
                        ->whereRaw('LOWER(name) = ?', [strtolower($namaKomp)])
                        ->first();

                    if (!$ingKomp) {
                        $errors++;
                        $errorMessages[] = "Baris $rowNum: Bahan komponen '$namaKomp' tidak ditemukan. Komponen di-skip.";
                        continue;
                    }

                    $unitKomp = null;
                    if ($satuanK) {
                        $unitKomp = Unit::where('name', 'like', $satuanK)
                            ->orWhere('symbol', 'like', $satuanK)
                            ->first();

                        if (!$unitKomp) {
                            $errors++;
                            $errorMessages[] = "Baris $rowNum: Satuan komponen '$satuanK' tidak ditemukan. Komponen di-skip.";
                            continue;
                        }
                    } else {
                        $unitKomp = $ingKomp->baseUnit;
                    }

                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $ingKomp->id,
                        'quantity' => (float) ($qtyKomp ?? 1),
                        'unit_id' => $unitKomp?->id,
                    ]);
                }

                DB::commit();
                $success++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errors++;
                $errorMessages[] = "Error memproses '$namasSemi': " . $e->getMessage();
                Log::error("RecipeSemiImportService: " . $e->getMessage(), ['semi' => $namasSemi]);
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'messages' => $errorMessages,
        ];
    }
}
