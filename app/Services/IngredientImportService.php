<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        if (!file_exists($filePath)) {
            return ['success' => 0, 'errors' => 1, 'messages' => ["File not found: $filePath"]];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['success' => 0, 'errors' => 1, 'messages' => ["Could not open file: $filePath"]];
        }

        // Headers
        // Expected: No, Nama Bahan, Tipe, Satuan, Stok Minimum
        $headers = fgetcsv($handle);

        if (!$headers) {
            fclose($handle);
            return ['success' => 0, 'errors' => 1, 'messages' => ["Empty CSV file."]];
        }

        // Normalize headers to lowercase and trim
        $headers = array_map(function ($h) {
            return strtolower(trim($h));
        }, $headers);

        // Map expected headers to internal keys
        $headerMap = [
            'nama bahan' => 'name',
            'tipe' => 'type',
            'satuan' => 'base_unit',
            'stok minimum' => 'min_stock',
        ];

        // Validate basic headers exist
        $missingHeaders = [];
        foreach ($headerMap as $csvHeader => $key) {
            if (!in_array($csvHeader, $headers)) {
                $missingHeaders[] = $csvHeader;
            }
        }

        if (!empty($missingHeaders)) {
            // Fallback to English headers if Indonesian ones are missing, strictly for backward compatibility or if the user used the old format
            $fallbackMap = [
                'name' => 'name',
                'type' => 'type',
                'base_unit' => 'base_unit',
                'min_stock' => 'min_stock',
            ];

            $useFallback = true;
            foreach ($fallbackMap as $csvHeader => $key) {
                if (!in_array($csvHeader, $headers)) {
                    $useFallback = false;
                    break;
                }
            }

            if (!$useFallback) {
                fclose($handle);
                return ['success' => 0, 'errors' => 1, 'messages' => ["Missing required headers: " . implode(', ', $missingHeaders)]];
            }
            // Should verify this logic, but for now assuming if fallback works we use it. 
            // Actually, let's stick to the user request. They provided specific headers.
            // If the user's file has "Nama Bahan", match to 'name'.
        }

        $row = 1; // Header is row 1
        $success = 0;
        $errors = 0;
        $errorMessages = [];

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $row++;

                // Map data to headers
                if (count($headers) != count($data)) {
                    $errorMessages[] = "Row $row: Column count mismatch. Expected " . count($headers) . ", got " . count($data) . ". Skipping.";
                    $errors++;
                    continue;
                }

                $rowPayload = array_combine($headers, $data);

                // Extract using map
                $name = $this->getValue($rowPayload, ['nama bahan', 'name']);
                $type = $this->getValue($rowPayload, ['tipe', 'type']); // raw, semi, finished
                $unitName = $this->getValue($rowPayload, ['satuan', 'base_unit', 'unit']);
                $minStock = $this->getValue($rowPayload, ['stok minimum', 'min_stock']) ?? 0;

                // Optional
                $sellable = $this->getValue($rowPayload, ['sellable', 'dijual']) ?? 0;

                if (!$name || !$type || !$unitName) {
                    $errorMessages[] = "Row $row: Missing required fields (Nama Bahan, Tipe, Satuan). Skipping.";
                    $errors++;
                    continue;
                }

                // Trim inputs
                $name = trim($name);
                $type = strtolower(trim($type));
                $unitName = trim($unitName);

                // Validate Type
                if (!in_array($type, ['raw', 'semi', 'finished'])) {
                    $errorMessages[] = "Row $row: Invalid type '$type'. Must be raw, semi, or finished. Skipping.";
                    $errors++;
                    continue;
                }

                // Find Unit
                $unit = Unit::where('name', 'like', $unitName)
                    ->orWhere('symbol', 'like', $unitName)
                    ->first();

                if (!$unit) {
                    // Start: Attempt to create unit if really needed, or just error?
                    // For now, let's error to be safe, or default to something?
                    // Task says "Satuan" is dropdown in the image, so it implies existing units.
                    $errorMessages[] = "Row $row: Unit '$unitName' not found. Skipping.";
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
                // Parse boolean/int for sellable. If type is finished, it's likely sellable but not always.
                // Default sellable to 0 unless specified or logic dictates.
                // In the image, 'finished' items are like 'PISANG GORENG MADU', which are sold.
                // 'raw' items are ingredients.

                // Let's trust the input or default
                $ingredient->is_sellable = filter_var($sellable, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

                // Force finished goods to be sellable if not specified? 
                // Let's keep it simple for now.

                $ingredient->save();

                $success++;
            }

            DB::commit();
            fclose($handle);

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'messages' => $errorMessages
        ];
    }

    private function getValue(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($row[$key])) {
                return $row[$key];
            }
        }
        return null;
    }
}
