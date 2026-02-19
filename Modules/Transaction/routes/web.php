<?php

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Http\Controllers\TransactionController;
use Modules\Transaction\Http\Controllers\CashierShiftController;
use Modules\Transaction\Http\Controllers\OrderController;
use Modules\Transaction\Http\Controllers\ListOrderController;
use Modules\Transaction\Http\Controllers\KitchenDisplayController;

Route::domain('cashier.' .config('app.domain'))->middleware(['auth', 'ensure.business.outlet'])->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transaction');

    Route::prefix('shift')->group(function (){
        Route::get('/', [CashierShiftController::class, 'index'])->name('transaction.shift');
        Route::get('/open', [CashierShiftController::class, 'open'])->name('transaction.shift.open');
        Route::post('/close/{shift}', [CashierShiftController::class, 'close'])->name('transaction.shift.close');
        Route::post('/petty-cash/out', [CashierShiftController::class, 'pettyCashOut'])->name('transaction.shift.petty-cash.out');
    });

    Route::prefix('order')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [OrderController::class, 'index'])->name('transaction.order');
        Route::post('/store', [OrderController::class, 'store'])->name('transaction.order.store');
    });

    Route::prefix('list-order')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [ListOrderController::class, 'index'])->name('transaction.list-order');
        Route::post('/pay', [ListOrderController::class, 'pay'])->name('transaction.list-order.pay');
    });

    Route::prefix('kitchen-display')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [KitchenDisplayController::class, 'index'])->name('transaction.kitchen-display');
        Route::post('/update-items', [KitchenDisplayController::class, 'updateItems'])->name('transaction.kitchen-display.update-items');
    });
});
