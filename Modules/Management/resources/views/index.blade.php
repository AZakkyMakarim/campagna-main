@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'dashboard',
])

@section('title', 'Dashboard Manajemen')

@section('content')

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">

        <h2 class="text-xl font-semibold text-gray-800">
            Dashboard
        </h2>

        <form method="GET" class="flex items-center gap-2">

            <div class="relative">

                <input
                    type="text"
                    name="date_range"
                    id="date_range"
                    value="{{ request('date_range') }}"
                    placeholder="Pilih periode"
                    class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 focus:outline-none"
                >

                <i class="fa fa-calendar absolute right-3 top-2.5 text-gray-400"></i>

            </div>

            <button
                type="submit"
                class="px-3 py-2 bg-orange-600 text-white rounded-lg text-sm hover:bg-orange-500"
            >
                Filter
            </button>

            <a
                href="{{ route('management') }}"
                class="px-3 py-2 border rounded-lg text-sm hover:bg-gray-100"
            >
                Reset
            </a>

        </form>

    </div>

    <div class="space-y-6">

        {{-- ================= STATS ================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Revenue Today --}}
            <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Penjualan</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ rp_format($stats['revenue_today']) }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-wave text-lg"></i>
                </div>
            </div>

            {{-- Transactions --}}
            <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $stats['transactions_today'] }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-receipt text-lg"></i>
                </div>
            </div>

            {{-- Ongoing Orders --}}
            <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Order Berjalan</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $stats['ongoing_orders'] }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-lg"></i>
                </div>
            </div>

            {{-- Avg Transaction --}}
            <div class="bg-white rounded-xl border p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Rata-rata Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ rp_format($stats['avg_transaction']) }}
                    </p>
                </div>

                <div class="w-12 h-12 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
            </div>

        </div>


        {{-- ================= DASHBOARD GRID ================= --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- SALES PER HOUR --}}
            <div class="bg-white rounded-xl border shadow-sm p-6 xl:col-span-2">
                <h3 class="font-semibold text-gray-800 mb-4">
                    Penjualan Per Jam
                </h3>

                <div class="h-[220px]">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            {{-- CATEGORY CHART --}}
            <div class="bg-white rounded-xl border shadow-sm p-6 xl:col-span-2">
                <h3 class="font-semibold text-gray-800 mb-4">
                    Penjualan per Kategori
                </h3>

                <div class="h-[200px]">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            {{-- PAYMENT METHODS (SPAN 2 ROW) --}}
            <div class="bg-white rounded-xl border shadow-sm p-6 xl:col-span-2">
                <h3 class="font-semibold text-gray-800 mb-4">
                    Metode Pembayaran
                </h3>

                <div class="space-y-5">

                    @foreach($paymentMethods as $method)

                        <div>

                            <div class="flex justify-between text-sm mb-1">
        <span class="text-gray-600">
            {{ strtoupper($method->method) }}
        </span>

                                <span class="font-semibold">
            {{ $method->percent }}%
        </span>
                            </div>

                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-orange-500"
                                    style="width: {{ $method->percent }}%">
                                </div>
                            </div>

                            <div class="flex justify-between text-xs text-gray-400 mt-1">
                                <span>{{ rp_format($method->total) }}</span>
                                <span>{{ $method->total_transaction }} transaksi</span>
                            </div>

                        </div>

                    @endforeach

                </div>
            </div>

            {{-- TOP MENU --}}
            <div class="bg-white rounded-xl border shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">
                        Top Menu Terlaris
                    </h3>
                    <i class="fa-solid fa-fire text-orange-500"></i>
                </div>

                <div class="space-y-3">

                    @foreach($topMenus as $menu)

                        <div class="flex items-center justify-between">

                    <span class="text-gray-700">
                        {{ $menu->menu->name }}
                    </span>

                            <span class="text-sm font-semibold bg-orange-100 text-orange-600 px-2 py-1 rounded">
                        {{ $menu->total_sold }}
                    </span>

                        </div>

                    @endforeach

                </div>
            </div>


            {{-- LEAST MENU --}}
            <div class="bg-white rounded-xl border shadow-sm p-6">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">
                        Menu Jarang Laku
                    </h3>

                    <i class="fa-solid fa-arrow-trend-down text-red-500"></i>
                </div>

                <div class="space-y-3">

                    @foreach($leastMenus as $menu)

                        <div class="flex items-center justify-between">

                    <span class="text-gray-700">
                        {{ $menu->menu->name }}
                    </span>

                            <span class="text-sm font-semibold bg-red-100 text-red-600 px-2 py-1 rounded">
                        {{ $menu->total_sold }}
                    </span>

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- ONGOING ORDERS (FULL WIDTH) --}}
            <div class="bg-white rounded-xl border shadow-sm p-6 xl:col-span-2">

                <h3 class="font-semibold text-gray-800 mb-4">
                    Order Berjalan
                </h3>

                <div class="overflow-x-auto">

                    <table class="w-full text-sm">

                        <thead class="text-left text-gray-400 border-b">
                        <tr>
                            <th class="py-2">Kode</th>
                            <th>Meja</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                        </thead>

                        <tbody>

                        @foreach($ongoingOrders as $order)

                            <tr class="border-b hover:bg-gray-50">

                                <td class="py-2 font-semibold text-gray-700">
                                    {{ $order->code }}
                                </td>

                                <td>
                                    {{ $order->table_number ?? '-' }}
                                </td>

                                <td>
                            <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-700">
                                {{ $order->status }}
                            </span>
                                </td>

                                <td class="font-semibold">
                                    {{ rp_format($order->grand_total) }}
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
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

        const salesCtx = document.getElementById('salesChart');

        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Penjualan',
                    data: @json($chartData['values']),
                    backgroundColor: '#f97316',
                    borderRadius: 4,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const categoryChart = new Chart(
            document.getElementById('categoryChart'),
            {
                type: 'bar',
                data: {
                    labels: @json($categoryChart['labels']),
                    datasets: [{
                        label: 'Kategori',
                        data: @json($categoryChart['values']),
                        backgroundColor: '#f97316',
                        borderRadius: 6,
                        barThickness: 22
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            }
        );
    </script>
@endpush
