<?php

namespace App\Services;

use App\Imports\IngredientImport;
use App\Models\Ingredient;
use App\Models\Unit;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class IngredientImportService
{
    /**
     * Import ingredients from a file path.
     *
     * @param string $filePath
     * @param int $businessId
     * @param int $outletId
     * @return array Result summary [success, errors, error_messages]
     */
    public function import(string $filePath, int $businessId, int $outletId): array
    {
        // Use Maatwebsite Excel to import to collection
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                return ['success' => 0, 'errors' => 1, 'messages' => ["File not found."]];
            }

            $import = new IngredientImport();
            // Store the imported data into the import object
            Excel::import($import, $filePath);

            $rows = $import->getRows();
        } catch (\Exception $e) {
            return ['success' => 0, 'errors' => 1, 'messages' => ["Error reading file: " . $e->getMessage()]];
        }

        $success = 0;
        $errors = 0;
        $errorMessages = [];
        $rowNumber = 1; // Header is 1, data starts at 2

        foreach ($rows as $row) {
            $rowNumber++;

            // Keys are slugged by the library (e.g. "Nama Bahan" -> "nama_bahan")

            // Map keys
            $name = $row['nama_bahan'] ?? $row['name'] ?? null;
            $type = $row['tipe_rawsemifinished'] ?? $row['tipe'] ?? $row['type'] ?? null;
            $unitName = $row['satuan'] ?? $row['base_unit'] ?? null;
            $minStock = $row['stok_minimum'] ?? $row['min_stock'] ?? 0;

            if (!$name || !$type || !$unitName) {
                $errorMessages[] = "Row $rowNumber: Missing required fields (Nama Bahan, Tipe, Satuan). Skipping.";
                $errors++;
                continue;
            }

            // Trim inputs
            $name = trim($name);
            $type = strtolower(trim($type));
            $unitName = trim($unitName);

            // Validate Type
            if (!in_array($type, ['raw', 'semi', 'finished'])) {
                $errorMessages[] = "Row $rowNumber: Invalid type '$type'. Must be raw, semi, or finished. Skipping.";
                $errors++;
                continue;
            }

            // Find Unit
            $unit = Unit::where('name', 'like', $unitName)
                ->orWhere('symbol', 'like', $unitName)
                ->first();

            if (!$unit) {
                $errorMessages[] = "Row $rowNumber: Unit '$unitName' not found. Skipping.";
                $errors++;
                continue;
            }

            // FirstOrNew to check if exists
            $ingredient = Ingredient::firstOrNew([
                'business_id' => $businessId,
                'outlet_id' => $outletId,
                'name' => $name,
            ]);

            // Set code only if new
            if (!$ingredient->exists) {
                $ingredient->code = uniqid();
            }

            $ingredient->type = $type;
            $ingredient->base_unit_id = $unit->id;
            $ingredient->min_stock = (float) $minStock;

            try {
                $ingredient->save();
                $success++;
            } catch (\Exception $e) {
                $errorMessages[] = "Row $rowNumber: Error saving '$name' - " . $e->getMessage();
                $errors++;
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'messages' => $errorMessages
        ];
    }
}
