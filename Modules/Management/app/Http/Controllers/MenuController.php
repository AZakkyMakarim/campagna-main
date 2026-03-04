<?php

namespace Modules\Management\Http\Controllers;

use App\Exports\MenuBundleTemplateExport;
use App\Exports\MenuSingleTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuComponent;
use App\Models\Recipe;
use App\Services\MenuBundleImportService;
use App\Services\MenuSingleImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function single()
    {
        $menus = Menu::where('outlet_id', active_outlet_id())->where('type', 'single')->latest()->paginate();

        $ingredients = Ingredient::query()
            ->where('outlet_id', active_outlet_id())
            ->where('is_active', 1)
            ->with([
                'baseUnit',
                'stock',
            ])
            ->orderBy('name')
            ->get();

        return view('management::menu.single.index', compact('ingredients', 'menus'));
    }

    public function bundle()
    {
        $raw = Menu::where('outlet_id', active_outlet_id())->latest();

        $menus = (clone $raw)->where('type', 'single')->get();
        $bundles = (clone $raw)->where('type', 'bundle')->get();

        return view('management::menu.bundle.index', compact('menus', 'bundles'));
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
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:menus,sku',
            'category' => 'required|in:makanan,minuman',
            'sell_price' => 'required|numeric|min:0',
            'type' => 'required|in:single,bundle',
            'components' => 'required|array|min:1',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // CREATE MENU
            // =========================
            $menu = Menu::create([
                'business_id' => auth()->user()->business_id,
                'outlet_id' => active_outlet_id(),
                'name' => $request->name,
                'sku' => $request->sku,
                'category' => $request->category,
                'type' => $request->type,
                'hpp' => $request->hpp,
                'sell_price' => $request->sell_price,
            ]);

            // =========================
            // CREATE MENU COMPONENTS (POLYMORPH)
            // =========================
            foreach ($request->components as $row) {

                if ($request->type === 'single') {
                    MenuComponent::create([
                        'menu_id' => $menu->id,
                        'componentable_type' => Ingredient::class,
                        'componentable_id' => $row['recipe_id'],
                        'qty' => $row['qty'],
                    ]);
                }

                if ($request->type === 'bundle') {
                    MenuComponent::create([
                        'menu_id' => $menu->id,
                        'componentable_type' => Menu::class,
                        'componentable_id' => $row['menu_id'],
                        'qty' => $row['qty'],
                    ]);
                }
            }

            DB::commit();

            toast('Menu berhasil ditambahkan');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e);

            toast('Gagal menyimpan menu: ' . $e->getMessage(), 'error');
            return back();
        }
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
    public function update(Request $request, Menu $menu)
    {
        DB::beginTransaction();

        try {
            $menu->update($request->all());

            if ($request->components) {
                $menu->components()->delete();

                foreach ($request->components as $row) {
                    if ($request->type === 'single') {
                        MenuComponent::create([
                            'menu_id' => $menu->id,
                            'componentable_type' => Ingredient::class,
                            'componentable_id' => $row['recipe_id'],
                            'qty' => $row['qty'],
                        ]);
                    }

                    if ($request->type === 'bundle') {
                        MenuComponent::create([
                            'menu_id' => $menu->id,
                            'componentable_type' => Menu::class,
                            'componentable_id' => $row['menu_id'],
                            'qty' => $row['qty'],
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return api_status_ok($menu);
            }

            toast('Menu berhasil diubah!');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();

            toast('Gagal menyimpan menu: ' . $e->getMessage(), 'error');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }

    public function importSingle(Request $request, MenuSingleImportService $importService)
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
                        "Import selesai: " . $result['success'] . " menu berhasil, " . $result['errors'] . " error.",
                        'warning'
                    );
                } else {
                    $firstError = $result['messages'][0] ?? 'Terjadi kesalahan.';
                    toast("Import gagal: $firstError", 'error');
                }
            } else {
                toast("Import berhasil! " . $result['success'] . " menu ditambahkan/diperbarui.", 'success');
            }
        } catch (\Exception $e) {
            toast("Terjadi kesalahan sistem: " . $e->getMessage(), 'error');
        }

        return back();
    }

    public function downloadTemplateSingle()
    {
        return Excel::download(new MenuSingleTemplateExport, 'template_import_menu_single.xlsx');
    }

    public function importBundle(Request $request, MenuBundleImportService $importService)
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
                        "Import selesai: " . $result['success'] . " paket berhasil, " . $result['errors'] . " error.",
                        'warning'
                    );
                } else {
                    $firstError = $result['messages'][0] ?? 'Terjadi kesalahan.';
                    toast("Import gagal: $firstError", 'error');
                }
            } else {
                toast("Import berhasil! " . $result['success'] . " paket ditambahkan/diperbarui.", 'success');
            }
        } catch (\Exception $e) {
            toast("Terjadi kesalahan sistem: " . $e->getMessage(), 'error');
        }

        return back();
    }

    public function downloadTemplateBundle()
    {
        return Excel::download(new MenuBundleTemplateExport, 'template_import_paket_bundle.xlsx');
    }

    public function transaction(Menu $menu)
    {
        //        foreach ($menu->components as $component) {
//
//            $ingredient = $component->componentable;
//            $qtyNeeded  = $component->qty * $soldQty;
//
//            if ($ingredient->recipe) {
//                foreach ($ingredient->recipe->items as $item) {
//                    consumeFifo(
//                        $item->ingredient_id,
//                        $item->quantity * $qtyNeeded,
//                        $outletId,
//                        Order::class,
//                        $orderId
//                    );
//                }
//            } else {
//                consumeFifo(
//                    $ingredient->id,
//                    $qtyNeeded,
//                    $outletId,
//                    Order::class,
//                    $orderId
//                );
//            }
//        }
    }
}
