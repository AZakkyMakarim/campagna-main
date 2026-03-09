@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_payment_method'
])
@section('title', 'Analisa Kategori - Metode Pembayaran')

@section('content')
<div x-data="orderDetail()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Metode Pembayaran</h2>
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
                <th class="px-4 py-3">Metode Pembayaran</th>
                <th class="px-4 py-3">Jumlah Transaksi</th>
                <th class="px-4 py-3">Total Nominal</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($sales as $key => $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->method }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->jumlah_transaksi }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($sale->total_nominal) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
