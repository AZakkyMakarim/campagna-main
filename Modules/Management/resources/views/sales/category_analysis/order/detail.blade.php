@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_order'
])
@section('title', 'Analisa Kategori - Menu')

@section('content')
<div x-data="orderDetail()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Menu</h2>
        <div class="flex items-center space-x-3">
            <div class="flex items-center space-x-3">

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
            @foreach($items as $key => $item)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $item->menu->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $item->menu->sku }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ strtoupper($item->menu->category) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $item->qty }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($item->hpp) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($item->subtotal) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($item->subtotal - $item->hpp) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if($items->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $items->appends(Request::except('page'))->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
