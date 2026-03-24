@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_menu'
])
@section('title', 'Analisa Kategori - Menu')

@section('content')
<div>
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

    <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
        <form method="GET" class="flex items-center gap-2">

            <!-- SEARCH -->
            <div>
                <input
                    type="text"
                    name="name_snapshot"
                    value="{{ request('name_snapshot') }}"
                    placeholder="Nama menu"
{{--                    @focus="setActiveInput($event.target)"--}}
                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
            </div>

            <div>
                <input
                    type="text"
                    name="sku"
                    value="{{ request('sku') }}"
                    placeholder="SKU"
{{--                    @focus="setActiveInput($event.target)"--}}
                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
            </div>


            <div>
                <select
                    name="category"
                    class="w-full appearance-none p-2 pr-10 rounded-lg
                   border border-gray-300 bg-white text-gray-700 text-sm
                   focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                    <option value="">Pilih kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') == $category)>{{ strtoupper($category) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative">

                <input
                    type="text"
                    name="date_range_order"
                    id="date_range"
                    value="{{ request('date_range_order') }}"
                    placeholder="Range pembelian"
                    class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none"
                >

                <i class="fa fa-calendar absolute right-3 top-2.5 text-gray-400"></i>

            </div>


            <!-- BUTTON -->
            <div class="flex gap-2">
                <button
                    type="submit"
                    class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-500"
                >
                    Filter
                </button>

                <a
                    href="{{ url()->current() }}"
                    class="px-4 py-2 border rounded-lg hover:bg-gray-100"
                >
                    Reset
                </a>
            </div>

        </form>
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
                    <td class="px-4 py-3 text-nowrap">{{ $sale->menu->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->menu->sku }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ strtoupper($sale->menu->category) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->qty_terjual }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_hpp) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_harga_jual) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_omzet) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @if($sales->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $sales->appends(Request::except('page'))->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_orange.css">
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script !src="">
        document.addEventListener("DOMContentLoaded", function () {

            flatpickr("#date_range", {

                mode: "range",

                dateFormat: "Y-m-d",

                allowInput: true,

                onClose: function(selectedDates, dateStr, instance) {

                    // otomatis submit kalau mau
                    // instance.element.form.submit();

                }

            });

        });
    </script>
@endpush
