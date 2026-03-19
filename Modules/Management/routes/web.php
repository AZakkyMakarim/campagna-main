<?php

use Illuminate\Support\Facades\Route;
use Modules\Management\Http\Controllers\ManagementController;
use Modules\Management\Http\Controllers\IngredientController;
use Modules\Management\Http\Controllers\VendorController;
use Modules\Management\Http\Controllers\UnitConversionController;
use Modules\Management\Http\Controllers\RecipeController;
use Modules\Management\Http\Controllers\PurchaseController;
use Modules\Management\Http\Controllers\StockController;
use Modules\Management\Http\Controllers\ProductionController;
use Modules\Management\Http\Controllers\MenuController;
use Modules\Management\Http\Controllers\CategoryAnalysisController;

Route::domain('admin.' .config('app.domain'))->middleware(['auth', 'ensure.business.outlet'])->group(function () {
    Route::prefix('management')->group(function (){
        Route::get('/', [ManagementController::class, 'index'])->name('management');

        Route::prefix('ingredient')->group(function () {
            Route::get('/', [IngredientController::class, 'index'])->name('management.ingredient');
            Route::post('/store', [IngredientController::class, 'store'])->name('management.ingredient.store');
            Route::post('/update/{ingredient}', [IngredientController::class, 'update'])->name('management.ingredient.update');
            Route::post('/import', [IngredientController::class, 'import'])->name('management.ingredient.import');
            Route::get('/download-template', [IngredientController::class, 'downloadTemplate'])->name('management.ingredient.download-template');
        });

        Route::prefix('recipe')->group(function () {
            Route::get('/', [RecipeController::class, 'index'])->name('management.recipe');
            Route::post('/store', [RecipeController::class, 'store'])->name('management.recipe.store');
            Route::post('/update/{recipe}', [RecipeController::class, 'update'])->name('management.recipe.update');
            Route::post('/import', [RecipeController::class, 'import'])->name('management.recipe.import');
            Route::get('/download-template', [RecipeController::class, 'downloadTemplate'])->name('management.recipe.download-template');
        });

        Route::prefix('inventory')->group(function () {
            Route::prefix('stock')->group(function () {
                Route::get('/', [StockController::class, 'index'])->name('management.inventory.stock');
                Route::get('/card/{ingredient}', [StockController::class, 'card'])->name('management.inventory.stock.card');
                Route::get('/recap/{ingredient}', [StockController::class, 'recap'])->name('management.inventory.stock.recap');
            });
        });

        Route::prefix('purchasing')->group(function () {
            Route::prefix('unit-conversion')->group(function () {
                Route::get('/', [UnitConversionController::class, 'index'])->name('management.purchasing.unit-conversion');
                Route::post('/store', [UnitConversionController::class, 'store'])->name('management.purchasing.unit-conversion.store');
                Route::post('/update/{conversion}', [UnitConversionController::class, 'update'])->name('management.purchasing.unit-conversion.update');
            });

            Route::prefix('vendor')->group(function () {
                Route::get('/', [VendorController::class, 'index'])->name('management.purchasing.vendor');
                Route::post('/store', [VendorController::class, 'store'])->name('management.purchasing.vendor.store');
                Route::post('/update/{vendor}', [VendorController::class, 'update'])->name('management.purchasing.vendor.update');
                Route::post('/import', [VendorController::class, 'import'])->name('management.purchasing.vendor.import');
                Route::get('/download-template', [VendorController::class, 'downloadTemplate'])->name('management.purchasing.vendor.download-template');
            });

            Route::prefix('purchase')->group(function () {
                Route::get('/', [PurchaseController::class, 'index'])->name('management.purchasing.purchase');
                Route::post('/store', [PurchaseController::class, 'store'])->name('management.purchasing.purchase.store');
                Route::post('/update/{purchase}', [PurchaseController::class, 'update'])->name('management.purchasing.purchase.update');
                Route::get('/vendor/{vendor}/ingredients', [PurchaseController::class, 'vendorIngredients'])->name('management.purchasing.purchase.vendor.ingredients');
                Route::get('/detail/{purchase}', [PurchaseController::class, 'detail'])->name('management.purchasing.purchase.detail');
            });

            Route::prefix('production')->group(function () {
                Route::get('/', [ProductionController::class, 'index'])->name('management.purchasing.production');
                Route::post('/store', [ProductionController::class, 'store'])->name('management.purchasing.production.store');
            });
        });

        Route::prefix('menu')->group(function () {
            Route::prefix('single')->group(function () {
                Route::get('/', [MenuController::class, 'single'])->name('management.purchasing.menu.single');
                Route::post('/store', [MenuController::class, 'store'])->name('management.purchasing.menu.single.store');
                Route::post('/update/{menu}', [MenuController::class, 'update'])->name('management.purchasing.menu.single.update');
                Route::post('/import', [MenuController::class, 'importSingle'])->name('management.purchasing.menu.single.import');
                Route::get('/download-template', [MenuController::class, 'downloadTemplateSingle'])->name('management.purchasing.menu.single.download-template');
            });

            Route::prefix('bundle')->group(function () {
                Route::get('/', [MenuController::class, 'bundle'])->name('management.purchasing.menu.bundle');
                Route::post('/store', [MenuController::class, 'store'])->name('management.purchasing.menu.bundle.store');
                Route::post('/update/{menu}', [MenuController::class, 'update'])->name('management.purchasing.menu.bundle.update');
                Route::post('/import', [MenuController::class, 'importBundle'])->name('management.purchasing.menu.bundle.import');
                Route::get('/download-template', [MenuController::class, 'downloadTemplateBundle'])->name('management.purchasing.menu.bundle.download-template');
            });
        });
        Route::prefix('sales')->group(function (){
            Route::prefix('category_analysis')->group(function (){
                Route::get('/nota', [CategoryAnalysisController::class, 'nota'])->name('management.purchasing.sales.category_analysis.nota');
                Route::get('/menu', [CategoryAnalysisController::class, 'menu'])->name('management.purchasing.sales.category_analysis.menu');
                Route::get('/payment-method', [CategoryAnalysisController::class, 'paymentMethod'])->name('management.purchasing.sales.category_analysis.payment_method');

                Route::prefix('order')->group(function (){
                    Route::get('/', [CategoryAnalysisController::class, 'order'])->name('management.purchasing.sales.category_analysis.order');
                    Route::get('/detail/{type}', [CategoryAnalysisController::class, 'detailOrder'])->name('management.purchasing.sales.category_analysis.order.detail-order');
                });

                Route::get('/get-order/{id}', [CategoryAnalysisController::class, 'getOrder'])->name('management.purchasing.sales.category_analysis.get-order');
            });
        });
    });
});
