<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportIngredients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:ingredients {file : Path to the CSV file} {--email= : User email for context (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ingredients from a CSV file';

    protected $importService;

    public function __construct(\App\Services\IngredientImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $email = $this->option('email');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        // Get User Context
        $user = $email ? User::where('email', $email)->first() : User::first();
        if (!$user) {
            $this->error("User not found. Please provide a valid email with --email option or ensure at least one user exists.");
            return 1;
        }

        $businessId = $user->business_id;

        // Find Outlet
        // Since active_outlet_id() uses session, we can't rely on it in CLI.
        // We'll pick the first outlet for the business.
        $outlet = \App\Models\Outlet::where('business_id', $businessId)->first();

        if (!$outlet) {
            $this->error("No outlet found for business ID: $businessId");
            return 1;
        }
        $outletId = $outlet->id;

        $this->info("Importing as User: {$user->name} ({$user->email})");
        $this->info("Business ID: $businessId, Outlet ID: $outletId ({$outlet->name})");

        $result = $this->importService->import($file, $businessId, $outletId);

        if (!empty($result['messages'])) {
            foreach ($result['messages'] as $msg) {
                if ($result['success'] == 0 && $result['errors'] == 1 && count($result['messages']) == 1) {
                    $this->error($msg); // Fatal errors
                } else {
                    $this->warn($msg); // Value errors
                }
            }
        }

        $this->info("Import Completed.");
        $this->info("Success: " . $result['success']);
        $this->info("Errors: " . $result['errors']);

        return 0;
    }
}
