@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'dashboard',
    'activeSubmenu' => 'dashboard',
])
@section('title', 'Dashboard')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Dashboard</h2>
    </div>

    <div class="p-6 space-y-6">

        {{-- TOP STATS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Transaksi --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['transactions_today'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center">
                    <i class="fa fa-receipt text-xl"></i>
                </div>
            </div>

            {{-- Revenue --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold text-orange-600">
                        Rp{{ number_format($stats['revenue_today'], 0, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fa fa-coins text-xl"></i>
                </div>
            </div>

            {{-- Ongoing --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-sm text-gray-500">Order Berjalan</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stats['ongoing_orders'] }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa fa-fire text-xl"></i>
                </div>
            </div>

            {{-- Compare --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition">
                <p class="text-sm text-gray-500">Vs Kemarin</p>
                <div class="flex items-end gap-2 mt-1">
                    <p class="text-2xl font-bold {{ $stats['delta_percent'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $stats['delta_percent'] }}%
                    </p>
                    <i class="fa {{ $stats['delta_percent'] >= 0 ? 'fa-arrow-up text-green-600' : 'fa-arrow-down text-red-600' }}"></i>
                </div>
                <div class="mt-3 h-2 rounded bg-gray-200 overflow-hidden">
                    <div class="h-full {{ $stats['delta_percent'] >= 0 ? 'bg-green-500' : 'bg-red-500' }}"
                         style="width: {{ min(abs($stats['delta_percent']),100) }}%"></div>
                </div>
            </div>
        </div>

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: CHART + PAYMENT (2 kolom) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Chart --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa fa-chart-line text-blue-500"></i>
                            Grafik Penjualan
                        </h3>

                        <select id="chartRange"
                                class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="hour">Per Jam</option>
                            <option value="day">Per Hari</option>
                        </select>
                    </div>

                    <div class="relative">
                        <canvas id="salesChart" height="120"></canvas>

                        {{-- Optional empty overlay --}}
                        @if(empty($chartData) || count($chartData) === 0)
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                                <i class="fa fa-chart-bar text-3xl mb-2"></i>
                                <p class="text-sm">Belum ada data penjualan</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Ongoing Orders --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa fa-fire text-orange-500"></i>
                            Order Berjalan
                        </h3>
                        <a href="{{ route('transaction.list-order') }}"
                           class="text-sm text-orange-600 hover:underline">
                            Lihat Semua
                        </a>
                    </div>

                    @if($ongoingOrders->count() > 0)
                        <div class="divide-y">
                            @foreach($ongoingOrders->take(5) as $order)
                                <div class="py-3 flex items-center justify-between group">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            #{{ $order->code ?? $order->queue_number }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $order->items->count() }} item •
                                            {{ \Carbon\Carbon::parse($order->created_at)?->format('H:i') }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        {{-- Status Badge --}}
                                        @php
                                            $statusMap = [
                                                'OPEN' => 'bg-blue-100 text-blue-700',
                                                'IN_PROGRESS' => 'bg-yellow-100 text-yellow-700',
                                                'READY' => 'bg-green-100 text-green-700',
                                            ];
                                            $cls = $statusMap[$order->status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $cls }}">
                                            {{ str_replace('_',' ', $order->status) }}
                                        </span>

                                        {{-- Quick Action --}}
                                        <a href="{{ route('transaction.list-order') }}"
                                           class="opacity-0 group-hover:opacity-100 transition text-gray-400 hover:text-orange-600"
                                           title="Detail">
                                            <i class="fa fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($ongoingOrders->count() > 5)
                            <div class="mt-3 text-center">
                                <a href="{{ route('transaction.list-order') }}"
                                   class="text-sm text-gray-500 hover:text-orange-600">
                                    + {{ $ongoingOrders->count() - 5 }} order lainnya
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <i class="fa fa-inbox text-3xl mb-2"></i>
                            <p class="text-sm">Tidak ada order berjalan</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: TOP MENU + LOW STOCK (1 kolom) --}}
            <div class="space-y-6">

                {{-- Top Menu --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa fa-fire text-orange-500"></i>
                            Menu Terlaris Hari Ini
                        </h3>
                    </div>

                    @if($topMenus->count() > 0)
                        <div class="space-y-3">
                            @foreach($topMenus as $menu)
                                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50 transition">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $menu->name }}</p>
                                        <p class="text-xs text-gray-400">Total terjual hari ini</p>
                                    </div>
                                    <span class="px-3 py-1 text-sm rounded-full bg-orange-100 text-orange-600 font-semibold">
                            {{ $menu->total_sold }}
                        </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <i class="fa fa-box-open text-2xl mb-2"></i>
                            <p class="text-sm">Belum ada penjualan hari ini</p>
                        </div>
                    @endif
                </div>

                {{-- Payment --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            <i class="fa fa-credit-card text-green-500"></i>
                            Metode Pembayaran
                        </h3>
                    </div>

                    @if($paymentMethods->count() > 0)
                        <div class="space-y-4">
                            @php $max = $paymentMethods->max('total') ?: 1; @endphp

                            @foreach($paymentMethods as $pm)
                                <div class="group">
                                    <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium text-gray-700">
                                {{ strtoupper($pm->method) }}
                            </span>
                                        <span class="font-semibold text-gray-800">
                                Rp{{ number_format($pm->total,0,',','.') }}
                            </span>
                                    </div>

                                    <div class="h-2.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-orange-400 to-orange-600 transition-all duration-500"
                                             style="width: {{ ($pm->total / $max) * 100 }}%">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <i class="fa fa-credit-card text-3xl mb-2"></i>
                            <p class="text-sm">Belum ada transaksi hari ini</p>
                        </div>
                    @endif
                </div>

                {{-- Low Stock --}}
                <div class="bg-white rounded-xl border border-red-200 shadow-sm p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-red-700 flex items-center gap-2">
                            <i class="fa fa-triangle-exclamation"></i>
                            Stok Hampir Habis
                        </h3>
                    </div>

                    @if($lowStocks->count() > 0)
                        <div class="space-y-3">
                            @foreach($lowStocks as $item)
                                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-red-50 transition">
                                    <div>
                                        <p class="font-medium text-red-700">{{ $item->name }}</p>
                                        <p class="text-xs text-red-400">Segera lakukan restock</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-bold">
                            {{ $item->total_stock }} tersisa
                        </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <i class="fa fa-check-circle text-2xl mb-2 text-green-500"></i>
                            <p class="text-sm">Semua stok aman</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('salesChart');

        const chartData = @json($chartData);

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Penjualan',
                    data: chartData.values,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.1)',
                    tension: 0.3,
                    fill: true
                }]
            }
        });
    </script>
@endpush
