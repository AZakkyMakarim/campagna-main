<?php

use Illuminate\Support\Facades\Route;
use Modules\Management\Http\Controllers\ManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('management', ManagementController::class)->names('management');
});
