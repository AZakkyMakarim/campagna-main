<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IngredientImport implements ToCollection, WithHeadingRow
{
    private $rows = [];
    private $errors = [];

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
