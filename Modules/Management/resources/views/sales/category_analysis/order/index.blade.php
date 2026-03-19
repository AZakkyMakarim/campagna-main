@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_order'
])
@section('title', 'Analisa Kategori - Order')

@section('content')
<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Order</h2>
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
                    name="type"
                    value="{{ request('type') }}"
                    placeholder="Jenis order"
                    {{--                    @focus="setActiveInput($event.target)"--}}
                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
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
                <th class="px-4 py-3">Jenis Order</th>
                <th class="px-4 py-3">Jumlah Transaksi</th>
                <th class="px-4 py-3">Total Nominal</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($sales as $key => $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->jenis_order }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->jumlah_transaksi }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_nominal) }}</td>
                    <td class="px-4 py-3 text-nowrap">
                        <a
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 gap-2"
                            href="{{ route('management.purchasing.sales.category_analysis.order.detail-order', $sale->jenis_order) }}">
                            <i class="fa fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
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
