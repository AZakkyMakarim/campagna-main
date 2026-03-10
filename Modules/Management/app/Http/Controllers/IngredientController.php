<?php

namespace Modules\Management\Http\Controllers;

use App\Exports\IngredientTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::get();

        $ingredients = Ingredient::where('business_id', Auth::user()->business_id)->where('outlet_id', active_outlet_id());

        $raws = (clone $ingredients)->where('type', 'raw')->paginate(10, ['*'], 'raw_page')->withQueryString();
        $semis = (clone $ingredients)->where('type', 'semi')->paginate(10, ['*'], 'semi_page')->withQueryString();
        $finisheds = (clone $ingredients)->where('type', 'finished')->paginate(10, ['*'], 'finished_page')->withQueryString();

        return view('management::ingredient.index', compact('units', 'raws', 'semis', 'finisheds'));
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
    public function store(Request $request) {
        $businessId = Auth::user()->business_id;

        Ingredient::create([
            'business_id'   => $businessId,
            'outlet_id'     => active_outlet_id(),
            'name'          => $request->name,
            'code'          => uniqid(),
            'type'          => $request->type,
            'base_unit_id'  => $request->base_unit_id,
            'min_stock'     => $request->min_stock,
            'is_sellable'   => $request->is_sellable ?: 0,
        ]);

        toast('Bahan berhasil dimasukan!');
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
    public function update(Request $request, Ingredient $ingredient) {
        $ingredient->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($ingredient);
        }

        toast('Bahan berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    public function import(Request $request, \App\Services\IngredientImportService $importService)
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
            session()->flash('import_errors_count', $result['errors']);
            session()->flash('import_success_count', $result['success']);
            session()->flash('import_errors_messages', $result['messages']);
        } else {
            toast("Import berhasil! " . $result['success'] . " bahan ditambahkan/diperbarui.", 'success');
        }
    } catch (\Exception $e) {
            toast("Terjadi kesalahan sistem: " . $e->getMessage(), 'error');
        }

        return back();
    }

    public function downloadTemplate()
    {
        return Excel::download(new IngredientTemplateExport, 'template_import_bahan.xlsx');
    }
}
