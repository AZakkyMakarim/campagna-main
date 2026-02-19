<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GeneralController;
use Illuminate\Support\Facades\Route;

Route::domain(config('app.domain'))->group(function (){
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::post('/presigned-picture', [GeneralController::class, 'presignedPicture'])->name('presigned-picture');
    });
});

require __DIR__.'/auth.php';
