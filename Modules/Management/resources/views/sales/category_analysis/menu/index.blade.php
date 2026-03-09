@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_menu'
])
@section('title', 'Analisa Kategori - Menu')

@section('content')
<div x-data="orderDetail()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Menu</h2>
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-3">
                <a
                    href="{{ $xlsUrl }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-xl shadow hover:bg-green-500 transition flex items-center gap-2 hover:cursor-pointer">
                    <i class="fa fa-file-excel"></i>
                    Export
                </a>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-orange-700 text-white uppercase text-xs">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Nama Menu</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Total HPP</th>
                <th class="px-4 py-3">Total Harga Jual</th>
                <th class="px-4 py-3">Total Omzet</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($sales as $key => $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->sku }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ strtoupper($sale->category) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->qty_terjual }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_hpp) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_harga_jual) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_omzet) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
