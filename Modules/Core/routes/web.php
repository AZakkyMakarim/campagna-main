<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Core\Http\Controllers\BusinessProfileController;
use Modules\Core\Http\Controllers\OutletController;
use Modules\Core\Http\Controllers\QZController;
use Modules\Core\Http\Controllers\OrderTypeController;
use Modules\Core\Http\Controllers\PaymentMethodController;
use Modules\Core\Http\Controllers\UserRoleController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\RoleController;
use Modules\Core\Http\Controllers\TaxRuleController;
use Modules\Core\Http\Controllers\PrinterStruckController;
use Modules\Core\Http\Controllers\PrinterController;

Route::domain('admin.' .config('app.domain'))->middleware('auth')->group(function () {
    Route::prefix('core')->group(function (){
        Route::get('/', [CoreController::class, 'index'])->name('core');

        Route::prefix('business-profile')->group(function (){
            Route::get('/', [BusinessProfileController::class, 'index'])->name('core.business-profile');
            Route::post('/store', [BusinessProfileController::class, 'store'])->name('core.business-profile.store');
        });

        Route::prefix('outlet')->group(function (){
            Route::get('/', [OutletController::class, 'index'])->name('core.outlet');
            Route::post('/store', [OutletController::class, 'store'])->name('core.outlet.store');
            Route::post('/update/{outlet}', [OutletController::class, 'update'])->name('core.outlet.update');
        });

        Route::prefix('qz')->group(function (){
            Route::post('/sign', [QZController::class, 'sign'])->name('core.qz.sign');
            Route::get('/cert', [QZController::class, 'cert'])->name('core.qz.cert');
        });

        Route::middleware('ensure.business.outlet')->group(function (){
            Route::prefix('order-type')->group(function (){
                Route::get('/', [OrderTypeController::class, 'index'])->name('core.order-type');
                Route::post('/store', [OrderTypeController::class, 'store'])->name('core.order-type.store');
                Route::post('/update/{orderType}', [OrderTypeController::class, 'update'])->name('core.order-type.update');
            });

            Route::prefix('payment-method')->group(function (){
                Route::get('/', [PaymentMethodController::class, 'index'])->name('core.payment-method');
                Route::post('/store', [PaymentMethodController::class, 'store'])->name('core.payment-method.store');
                Route::post('/update/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('core.payment-method.update');
            });

            Route::prefix('tax-rule')->group(function (){
                Route::get('/', [TaxRuleController::class, 'index'])->name('core.tax-rule');
                Route::post('/store', [TaxRuleController::class, 'store'])->name('core.tax-rule.store');
                Route::post('/update/{paymentMethod}', [TaxRuleController::class, 'update'])->name('core.tax-rule.update');
            });

            Route::prefix('user-role')->group(function (){
                Route::get('/', [UserRoleController::class, 'index'])->name('core.user-role');
                Route::post('/update/{user}', [UserRoleController::class, 'update'])->name('core.user-role.update');
            });

            Route::prefix('user')->group(function (){
                Route::post('/store', [UserController::class, 'store'])->name('core.user.store');
                Route::post('/update/{user}', [UserController::class, 'update'])->name('core.user.update');
            });

            Route::prefix('role')->group(function (){
                Route::post('/store', [RoleController::class, 'store'])->name('core.role.store');
                Route::post('/update/{user}', [RoleController::class, 'update'])->name('core.role.update');
                Route::post('/update-permission/{role}', [RoleController::class, 'updatePermission'])->name('core.role.update-permission');
                Route::get('/get-role-permission/{role}', [RoleController::class, 'getRolePermission'])->name('core.role.get-role-permission');
            });

            Route::prefix('printer-struck')->group(function (){
                Route::get('/', [PrinterStruckController::class, 'index'])->name('core.printer-struck');
                Route::get('/test/{printer}', [PrinterStruckController::class, 'printTest'])->name('core.printer-struck.test');
            });

            Route::prefix('printer')->group(function (){
                Route::post('/store', [PrinterController::class, 'store'])->name('core.printer.store');
                Route::post('/update/{printer}', [PrinterController::class, 'update'])->name('core.printer.update');
                Route::post('/print-test/{printer}', [PrinterController::class, 'printTest'])->name('core.printer.print-test');
            });
        });
    });
});
