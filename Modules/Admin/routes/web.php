<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

Route::domain('admin.' .config('app.domain'))->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');

    Route::post('/switch-outlet', [AdminController::class, 'switchOutlet'])->name('admin.switch-outlet');
});
