<?php

namespace App\Console\Commands;

use App\Models\IngredientBatch;
use App\Models\IngredientStock;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncIngredientStocks extends Command
{
    protected $signature = 'stock:sync-snapshot {--truncate : Kosongkan snapshot dulu sebelum sync}';
    protected $description = 'Rebuild ingredient_stocks snapshot from stock_movements ledger';

    public function handle()
    {
        $this->info('🚀 Start syncing ingredient stocks snapshot...');

        if ($this->option('truncate')) {
            $this->warn('⚠️ Truncating ingredient_stocks table...');
            IngredientStock::truncate();
        }

        // Ambil semua kombinasi business + outlet + ingredient
        $this->info('📦 Aggregating stock movements...');

        $rows = IngredientBatch::select(
            'ingredient_id',
            'outlet_id',
            DB::raw('SUM(qty_remaining) as total_qty'),
            DB::raw('SUM(qty_remaining * cost_per_unit) as total_cost')
        )
            ->groupBy('ingredient_id', 'outlet_id')
            ->get();

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $qty = (float) $row->total_qty;
            $avg = $qty > 0 ? ((float)$row->total_cost / $qty) : 0;

            IngredientStock::create([
                'ingredient_id' => $row->ingredient_id,
                'business_id'   => $row->outlet->business_id,
                'outlet_id'     => $row->outlet_id,
                'qty'           => $qty,
                'avg_cost'      => $avg,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('✅ Sync finished. ingredient_stocks is now up to date.');
        return Command::SUCCESS;
    }
}
