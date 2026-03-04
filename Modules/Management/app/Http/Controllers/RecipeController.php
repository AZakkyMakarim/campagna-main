<?php

namespace Modules\Management\Http\Controllers;

use App\Exports\RecipeSemiTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Unit;
use App\Services\RecipeSemiImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::get();

        $ingredients = Ingredient::with(['baseUnit', 'convertedUnits', 'unitConversions.toUnit'])
            ->withSum('batches as total_stock', 'qty_remaining')
            ->withAvg('batches as avg_cost', 'cost_per_unit')
            ->where(
                [
                    'business_id' => Auth::user()->business_id,
                    'outlet_id' => active_outlet_id()
                ]
            );

        $menuIngredients = (clone $ingredients)->get();
        $semiIngredients = (clone $ingredients)->where('type', 'raw')->get();
        $ingredientSemis = (clone $ingredients)->where('type', 'semi')->get();

        $recipes = Recipe::where('outlet_id', active_outlet_id())->latest();

        $menus = (clone $recipes)->whereNull('ingredient_id')->get();

        $semis = (clone $recipes)
            ->whereHas('ingredient', function ($query) {
                $query->where('type', 'semi');
            })->get();

        return view('management::recipe.index', compact('menus', 'semis', 'units', 'menuIngredients', 'semiIngredients', 'ingredientSemis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('management::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $businessId = Auth::user()->business_id;

            $recipe = Recipe::create([
                'business_id' => $businessId,
                'outlet_id' => active_outlet_id(),
                'name' => $request->name ?? Ingredient::find($request->ingredient_id)->name,
                'ingredient_id' => $request->ingredient_id,
                'quantity' => $request->quantity,
                'unit_id' => $request->unit_id,
            ]);

            foreach ($request->components as $component) {
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $component['ingredient_id'],
                    'quantity' => $component['qty'],
                    'unit_id' => $component['unit_id'],
                ]);
            }

            toast('Resep berhasil dimasukan!');
            DB::commit();
        } catch (\Exception $exception) {
            toast('Resep gagal dimasukan!', 'warning');
            DB::rollBack();
        }
        return back();
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('management::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('management::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $recipe->update($request->all());

        if ($recipe->ingredient) {
            $recipe->ingredient->update([
                'base_unit_id' => $request->base_unit_id,
            ]);
        }

        $recipe->items()->delete();

        foreach ($request->components as $component) {
            RecipeItem::create([
                'recipe_id' => $recipe->id,
                'ingredient_id' => $component['ingredient_id'],
                'quantity' => $component['quantity'],
                'unit_id' => $component['unit_id'],
            ]);
        }

        if ($request->expectsJson()) {
            return api_status_ok($recipe);
        }

        toast('Resep berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }

    public function import(Request $request, RecipeSemiImportService $importService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $businessId = Auth::user()->business_id;
        $outletId = active_outlet_id();

        try {
            $result = $importService->import($path, $businessId, $outletId);

            if ($result['errors'] > 0) {
                if ($result['success'] > 0) {
                    toast(
                        "Import selesai: " . $result['success'] . " resep berhasil, " . $result['errors'] . " error.",
                        'warning'
                    );
                } else {
                    $firstError = $result['messages'][0] ?? 'Terjadi kesalahan.';
                    toast("Import gagal: $firstError", 'error');
                }
            } else {
                toast("Import berhasil! " . $result['success'] . " resep ditambahkan/diperbarui.", 'success');
            }
        } catch (\Exception $e) {
            toast("Terjadi kesalahan sistem: " . $e->getMessage(), 'error');
        }

        return back();
    }

    public function downloadTemplate()
    {
        return Excel::download(new RecipeSemiTemplateExport, 'template_import_resep_semi.xlsx');
    }
}
