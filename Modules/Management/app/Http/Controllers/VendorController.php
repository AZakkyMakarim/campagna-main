<?php

namespace Modules\Management\Http\Controllers;

use App\Exports\VendorTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Vendor;
use App\Models\VendorIngredient;
use App\Services\VendorImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendors = Vendor::where('business_id', Auth::user()->business_id)->latest()->paginate();

        $ingredients = Ingredient::where('outlet_id', active_outlet_id())->latest()->get();

        return view('management::purchasing.vendor.index', compact('vendors', 'ingredients'));
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
        $vendor = Vendor::create([
            'business_id' => Auth::user()->business_id,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'link_maps' => $request->link_maps,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ]);

        DB::commit();

        toast('Vendor berhasil dimasukan!');
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
    public function update(Request $request, Vendor $vendor) {
        $vendor->update($request->all());

        if ($request->expectsJson()) {
            return api_status_ok($vendor);
        }

        toast('Vendor berhasil diubah!');
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    }

    public function import(Request $request, VendorImportService $importService)
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
                toast("Import berhasil! " . $result['success'] . " vendor ditambahkan/diperbarui.", 'success');
            }
        } catch (\Exception $e) {
            toast("Terjadi kesalahan sistem: " . $e->getMessage(), 'error');
        }

        return back();
    }

    public function downloadTemplate()
    {
        return Excel::download(new VendorTemplateExport, 'template_import_vendor.xlsx');
    }
}
