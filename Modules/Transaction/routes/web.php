<?php

use Illuminate\Support\Facades\Route;
use Modules\Transaction\Http\Controllers\TransactionController;
use Modules\Transaction\Http\Controllers\CashierShiftController;
use Modules\Transaction\Http\Controllers\OrderController;
use Modules\Transaction\Http\Controllers\ListOrderController;
use Modules\Transaction\Http\Controllers\KitchenDisplayController;
use Modules\Transaction\Http\Controllers\ReservationController;
use Modules\Transaction\Http\Controllers\StockController;
use Modules\Transaction\Http\Controllers\PrinterStruckController;
use Modules\Transaction\Http\Controllers\PrinterController;

Route::domain('cashier.' .config('app.domain'))->middleware(['auth', 'ensure.business.outlet'])->group(function () {
    Route::get('/', [TransactionController::class, 'index'])->name('transaction');

    Route::prefix('shift')->group(function (){
        Route::get('/', [CashierShiftController::class, 'index'])->name('transaction.shift');
        Route::get('/open', [CashierShiftController::class, 'open'])->name('transaction.shift.open');
        Route::post('/close/{shift}', [CashierShiftController::class, 'close'])->name('transaction.shift.close');
        Route::post('/petty-cash/out', [CashierShiftController::class, 'pettyCashOut'])->name('transaction.shift.petty-cash.out');
    });

    Route::prefix('inventory')->group(function () {
        Route::prefix('stock')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('transaction.inventory.stock');
            Route::get('/card/{ingredient}', [StockController::class, 'card'])->name('transaction.inventory.stock.card');
            Route::get('/recap/{ingredient}', [StockController::class, 'recap'])->name('transaction.inventory.stock.recap');
        });
    });

    Route::prefix('order')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [OrderController::class, 'index'])->name('transaction.order');
        Route::post('/store', [OrderController::class, 'store'])->name('transaction.order.store');
    });

    Route::prefix('reservation')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [ReservationController::class, 'index'])->name('transaction.reservation');
        Route::get('/preorder/{reservation}', [ReservationController::class, 'preorder'])->name('transaction.reservation.preorder');
        Route::get('/confirm/{reservation}', [ReservationController::class, 'confirm'])->name('transaction.reservation.confirm');
        Route::post('/store-preorder', [ReservationController::class, 'storePreorder'])->name('transaction.reservation.store-preorder');
        Route::post('/store', [ReservationController::class, 'store'])->name('transaction.reservation.store');
        Route::post('/pay', [ReservationController::class, 'pay'])->name('transaction.reservation.pay');
    });

    Route::prefix('list-order')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [ListOrderController::class, 'index'])->name('transaction.list-order');
        Route::get('/reorder/{order}', [ListOrderController::class, 'reOrder'])->name('transaction.list-order.reorder');
        Route::post('/store', [ListOrderController::class, 'store'])->name('transaction.list-order.store');
        Route::post('/pay', [ListOrderController::class, 'pay'])->name('transaction.list-order.pay');
        Route::post('/print-struck', [ListOrderController::class, 'printStruck'])->name('transaction.print-struck');
    });

    Route::prefix('kitchen-display')->middleware(['ensure.shift.active'])->group(function (){
        Route::get('/', [KitchenDisplayController::class, 'index'])->name('transaction.kitchen-display');
        Route::post('/update-items', [KitchenDisplayController::class, 'updateItems'])->name('transaction.kitchen-display.update-items');

        Route::get('/test-kds', function () {
            $order = \App\Models\Order::with('items')->first();

            broadcast(new \App\Events\OrderCreated($order));

            return 'ok';
        });
    });

    Route::prefix('printer-struck')->group(function (){
        Route::get('/', [PrinterStruckController::class, 'index'])->name('transaction.printer-struck');
        Route::get('/test/{printer}', [PrinterStruckController::class, 'printTest'])->name('transaction.printer-struck.test');
    });

    Route::prefix('printer')->group(function (){
        Route::post('/store', [PrinterController::class, 'store'])->name('transaction.printer.store');
        Route::post('/update/{printer}', [PrinterController::class, 'update'])->name('transaction.printer.update');
        Route::post('/print-test/{printer}', [PrinterController::class, 'printTest'])->name('transaction.printer.print-test');
    });
});
