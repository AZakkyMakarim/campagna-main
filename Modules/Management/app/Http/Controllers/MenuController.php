<?php

namespace Modules\Management\Http\Controllers;

use App\Exports\MenuBundleTemplateExport;
use App\Exports\MenuSingleTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuComponent;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Services\ExportService;
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
        $sort = request('sort', 'name');
        $direction = request('direction', 'asc');

        $allowedSort = [
            'name',
            'sku',
            'category',
            'sell_price',
            'is_active'
        ];

        if (!in_array($sort, $allowedSort)) {
            $sort = 'created_at';
        }

        $rawMenus = Menu::where('outlet_id', active_outlet_id())->orderBy($sort, $direction);

        $categories = (clone $rawMenus)->pluck('category')->unique();

        $menus = (clone $rawMenus)->where('type', 'single')->paginate(1000);

        $ingredients = Ingredient::query()
            ->where('outlet_id', active_outlet_id())
            ->where('is_active', 1)
            ->with([
                'baseUnit',
                'ingredientStock',
            ])
            ->orderBy('name')
            ->get();

        $currentQueries = \request()->query();
        $xls = ['download' => 'XLS'];
        $xlsQ = array_merge($currentQueries, $xls);
        $xlsUrl = \request()->fullUrlWithQuery($xlsQ);

        if (request('download')) {
            if (\request()->download == 'XLS') {
                $title = 'Daftar Menu';
                $outlet = Outlet::find(active_outlet_id())->name;

                $export = new ExportService($rawMenus->get(), 'management::menu.single.xls', ['thead_rows' => 3, 'outlet' => $outlet, 'title' => $title]);

                return Excel::download($export, $title.'.xls');
            }
        }

        return view('management::menu.single.index', compact('ingredients', 'menus', 'categories', 'xlsUrl'));
    }

    public function bundle()
    {
        $raw = Menu::where('outlet_id', active_outlet_id())->latest();


        $categories = (clone $raw)->pluck('category')->unique();

        $menus = (clone $raw)->where('type', 'single')->get();
        $bundles = (clone $raw)->where('type', 'bundle')->get();

        $ingredients = Ingredient::with('ingredientStock')
            ->where('outlet_id', active_outlet_id())
            ->where('is_active', 1)
            ->get();

        return view('management::menu.bundle.index', compact( 'menus', 'bundles', 'ingredients', 'categories'));
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
            'name'       => 'required|string|max:255',
            'sku'        => 'required|string|max:100|unique:menus,sku',
            'sell_price' => 'required|numeric|min:0',
            'type'       => 'required|in:single,bundle',
            'components' => 'required|array|min:1',
            'attachment' => 'nullable|image|mimes:jpg,jpeg,png|max:300',
            ],[
            'attachment.image' => 'File harus berupa gambar.',
            'attachment.mimes' => 'Format gambar harus JPG atau PNG.',
            'attachment.max' => 'Ukuran gambar maksimal 300 KB.',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // CREATE MENU
            // =========================
            $menu = Menu::create([
                'business_id' => auth()->user()->business_id,
                'outlet_id'   => active_outlet_id(),
                'name'        => $request->name,
                'sku'         => $request->sku,
                'category'    => strtolower($request->category),
                'type'        => $request->type,
                'hpp'         => $request->hpp,
                'sell_price'  => $request->sell_price,
            ]);

            if ($request->attachment){
                insert_picture($request->attachment, $menu, 'menu '.$request->type);
            }

            // =========================
            // CREATE MENU COMPONENTS (POLYMORPH)
            // =========================
            foreach ($request->components as $row) {

                if ($request->type === 'single') {
                    MenuComponent::create([
                        'menu_id'            => $menu->id,
                        'componentable_type' => Ingredient::class,
                        'componentable_id'   => $row['recipe_id'],
                        'qty'                => $row['qty'],
                    ]);
                }

                if ($request->type === 'bundle') {
                    MenuComponent::create([
                        'menu_id'            => $menu->id,
                        'componentable_type' => $row['componentable_type'],
                        'componentable_id'   => $row['componentable_id'],
                        'qty'                => $row['qty'],
                    ]);
                }
            }

            DB::commit();

            toast('Menu berhasil ditambahkan');
            return back();

        } catch (\Throwable $e) {
            DB::rollBack();

            toast('Gagal menyimpan menu: '.$e->getMessage(), 'error');
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
    public function update(Request $request, Menu $menu) {
        DB::beginTransaction();

        try {
            $request->validate([
                'name'       => 'required|string|max:255',
                'sell_price' => 'required|numeric|min:0',
                'type'       => 'required|in:single,bundle',
                'components' => 'required|array|min:1',
                'attachment' => 'nullable|image|mimes:jpg,jpeg,png|max:300',
            ],[
                'attachment.image' => 'File harus berupa gambar.',
                'attachment.mimes' => 'Format gambar harus JPG atau PNG.',
                'attachment.max' => 'Ukuran gambar maksimal 300 KB.',
            ]);

            $menu->update($request->all());

            if ($request->attachment){
                insert_picture($request->attachment, $menu, 'menu '.$request->type);
            }

            if ($request->components) {
                $menu->components()->delete();

                foreach ($request->components as $row) {
                    if ($request->type === 'single') {
                        MenuComponent::create([
                            'menu_id'            => $menu->id,
                            'componentable_type' => Ingredient::class,
                            'componentable_id'   => $row['recipe_id'],
                            'qty'                => $row['qty'],
                        ]);
                    }

                    if ($request->type === 'bundle') {
                        MenuComponent::create([
                            'menu_id'            => $menu->id,
                            'componentable_type' => $row['componentable_type'],
                            'componentable_id'   => $row['componentable_id'],
                            'qty'                => $row['qty'],
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

            toast('Gagal menyimpan menu: '.$e->getMessage(), 'error');
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
                session()->flash('import_errors_count', $result['errors']);
                session()->flash('import_success_count', $result['success']);
                session()->flash('import_errors_messages', $result['messages']);
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
                session()->flash('import_errors_count', $result['errors']);
                session()->flash('import_success_count', $result['success']);
                session()->flash('import_errors_messages', $result['messages']);
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


}
