@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'sales',
    'activeSubmenu' => 'category_analysis_nota'
])
@section('title', 'Analisa Kategori - Nota')

@section('content')
<div x-data="orderDetail()">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Nota</h2>
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
                    name="code"
                    value="{{ request('code') }}"
                    placeholder="Cari kode order"
                    {{--                    @focus="setActiveInput($event.target)"--}}
                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
            </div>

            <div class="">
                <select
                    name="payment_methods[]"
                    multiple
                    class="select2 w-full"
                    data-placeholder="Metode Pembayaran"
                >
                    <option value="CASH" @selected(in_array('CASH', request('payment_methods', [])))>CASH</option>
                    <option value="QRIS" @selected(in_array('QRIS', request('payment_methods', [])))>QRIS</option>
                    <option value="CARD" @selected(in_array('CARD', request('payment_methods', [])))>CARD</option>
                </select>
            </div>

            <div class="space-y-1">
                <select
                    name="order_types[]"
                    multiple
                    class="select2 w-full"
                    data-placeholder="Jenis Order"
                >
                    @foreach(\App\Models\OrderType::get() as $type)
                        <option value="{{ $type->name }}" {{ in_array($type->menu, request('order_types', [])) }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="relative">

                <input
                    type="text"
                    name="date_range_order"
                    id="date_range"
                    value="{{ request('date_range_order') }}"
                    placeholder="Pilih periode"
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
                <th class="px-4 py-3">No. Nota</th>
                <th class="px-4 py-3">Metode Pembayaran</th>
                <th class="px-4 py-3">Jenis Order</th>
                <th class="px-4 py-3">Tanggal Transaksi</th>
                <th class="px-4 py-3">Total HPP</th>
                <th class="px-4 py-3">Total Harga Jual</th>
                <th class="px-4 py-3">Total Omzet</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($sales as $key => $sale)
                @php
                    $hpp = @$sale->calculateHpp();
                    $pay = @$sale->grand_total;
                @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->code }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->payment->method }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $sale->type }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ parse_date_time($sale->created_at) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($hpp) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($pay) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ rp_format($pay - $hpp) }}</td>
                    <td class="px-4 py-2 text-center">
                        <button
                            @click="openDetail({{ $sale->id }})"
                            class="px-3 py-2 bg-yellow-500 text-white rounded">
                            <i class="fa fa-magnifying-glass"></i>
                        </button>
                    </td>
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

    <div
        x-show="detailOrder"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center"
    >
        <div class="absolute inset-0 bg-black/60" @click="closeDetail()"></div>

        <div class="relative bg-white w-full max-w-2xl rounded-xl shadow-xl">
            <div x-show="loading" class="flex justify-center items-center py-10">
                <i class="fa fa-spinner fa-spin text-2xl text-orange-500"></i>
            </div>

            <!-- CONTENT -->
            <div class="py-4" x-show="!loading">

                <!-- LEFT : ORDER SUMMARY -->
                <div class="space-y-4 border-r border-gray-200 pr-6 pl-6">

                    <!-- META -->
                    <div class="flex items-start justify-between">

                        {{-- LEFT : INFO UTAMA --}}
                        <div class="space-y-2">

                            {{-- NAMA OUTLET --}}
                            <h1 class="text-2xl font-bold text-gray-900 font-serif leading-tight">
                                {{ \App\Models\Outlet::find(active_outlet_id())->name }}
                            </h1>

                            {{-- ORDER META --}}
                            <div class="flex flex-wrap items-center gap-2 text-xs">

                                    <span
                                        x-show="orderType"
                                        class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-semibold"
                                        x-text="orderTypeLabel()"
                                    ></span>

                                <span
                                    x-show="orderChannel"
                                    class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 font-semibold"
                                    x-text="orderChannelLabel()"
                                ></span>

                                <span
                                    x-show="tableNumber"
                                    class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold"
                                >
                                        Meja <span x-text="tableNumber"></span>
                                    </span>

                            </div>

                            {{-- WAKTU --}}
                            <p class="text-xs text-gray-500">
                                {{ parse_date_full(now()) }} • {{ parse_time_hm(now()) }}
                            </p>
                        </div>

                        {{-- RIGHT : STATUS ORDER --}}
                        <div class="flex flex-col items-end gap-2 text-right">

                            {{-- STATUS ORDER --}}
                            <span
                                class="px-3 py-1 rounded-lg text-xs font-semibold"
                                :class="statusBadgeClass()"
                                x-text="orderStatus"
                            ></span>

                            <span
                                class="px-3 py-1 rounded-lg text-xs font-semibold"
                                :class="paymentBadgeClass()"
                                x-text="paymentStatus"
                            ></span>

                        </div>

                    </div>

                    <hr>

                    <!-- ITEM LIST -->
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <template x-for="item in cart" :key="item.menu_id">
                            <div class="space-y-0.5 text-sm">

                                <!-- NAMA + QTY + SUBTOTAL -->
                                <div class="flex justify-between">
                                        <span class="text-gray-700">
                                            <span x-text="item.name"></span>
                                            <span class="text-gray-400">
                                                x<span x-text="item.qty"></span>
                                            </span>
                                        </span>

                                    <span class="text-gray-800"
                                          x-text="formatRp(item.subtotal)">
                                        </span>
                                </div>

                                <!-- NOTE (OPSIONAL) -->
                                <div
                                    x-show="item.note && item.note.trim() !== ''"
                                    class="text-xs text-gray-500 italic pl-2"
                                >
                                    • <span x-text="item.note"></span>
                                </div>

                            </div>
                        </template>
                    </div>

                    <hr>

                    <!-- TOTAL -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span x-text="formatRp(subTotal)"></span>
                        </div>

                        <template x-for="adj in adjustments" :key="adj.id">
                            <div
                                x-show="adj.is_active"
                                class="flex justify-between text-sm text-gray-600"
                            >
                                <span>
                                    <span x-text="adj.name"></span>

                                    <template x-if="adj.calculation_type === 'percent'">
                                        <span x-text="` (${adj.value}%)`"></span>
                                    </template>
                                </span>

                                <span
                                    :class="adj.type === 'discount' ? 'text-red-600' : ''"
                                    x-text="formatRp(adj.amount)"
                                ></span>
                            </div>
                        </template>

                        <div class="flex justify-between font-bold text-xl pt-2">
                            <span>Total</span>
                            <span class="text-orange-600"
                                  x-text="formatRp(finalTotal())">
                            </span>
                        </div>

                        <!-- PEMBAYARAN -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Pembayaran</span>
                            <span x-text="formatRp(paidAmount)"></span>
                        </div>

                        <!-- KEMBALIAN -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Kembalian</span>
                            <span
                                :class="change < 0 ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold'"
                                x-text="formatRp(change)">
                            </span>
                        </div>

                        <hr>

                        <div class="flex justify-between text-sm">
                            <span>Total HPP</span>
                            <span x-text="formatRp(hppTotal)"></span>
                        </div>

                        <div class="flex justify-between font-bold text-lg">
                            <span>Profit</span>
                            <span :class="profitColor()"
                                  x-text="formatRp(profit())">
                            </span>
                        </div>
                    </div>
                </div>
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

        window.orderConfig = @json(config('array.order'));

        const orderDetailUrlTemplate = "{{ route('management.purchasing.sales.category_analysis.get-order', ':id') }}";

        function orderDetail() {
            return {

                // ========================
                // STATE
                // ========================

                detailOrder: false,
                loading: false,

                currentOrderId: null,

                cart: [],
                taxes: [],
                adjustments: [],

                subTotal: 0,
                grandTotal: 0,
                paidAmount: 0,
                hppTotal: 0,

                orderType: null,
                orderChannel: null,
                tableNumber: null,
                orderStatus: null,
                paymentStatus: null,

                // ========================
                // OPEN DETAIL
                // ========================

                openDetail(orderId) {
                    this.resetState();
                    this.loading = true;
                    this.detailOrder = true;
                    this.currentOrderId = orderId;

                    const url = orderDetailUrlTemplate.replace(':id', orderId);

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {

                            this.adjustments = data.adjustments || [];

                            this.cart = (data.items || []).map(it => ({
                                menu_id: it.menu_id,
                                name: it.name,
                                qty: Number(it.qty),
                                price: Number(it.price),
                                subtotal: Number(it.subtotal),
                                note: it.note ?? '',
                                hpp: Number(it.hpp ?? 0)
                            }));

                            this.subTotal = Number(data.sub_total || 0);
                            this.grandTotal = Number(data.grand_total || 0);
                            this.paidAmount = Number(data.paid_amount || 0);
                            this.hppTotal = Number(data.hpp_total || 0);

                            console.log(data);

                            this.orderType = data.type;
                            this.orderChannel = data.channel;
                            this.tableNumber = data.table_number;
                            this.orderStatus = data.status;
                            this.paymentStatus = data.payment_status;

                            this.loading = false;
                        })
                        .catch(() => {
                            alert('Gagal load detail order');
                            this.loading = false;
                            this.detailOrder = false;
                        });
                },

                // ========================
                // CLOSE
                // ========================

                closeDetail() {
                    this.detailOrder = false;
                    this.resetState();
                },

                resetState() {
                    this.cart = [];
                    this.subTotal = 0;
                    this.grandTotal = 0;
                    this.paidAmount = 0;
                    this.hppTotal = 0;
                    this.orderType = null;
                    this.orderChannel = null;
                    this.tableNumber = null;
                    this.orderStatus = null;
                    this.paymentStatus = null;
                },

                // ========================
                // CALCULATIONS
                // ========================

                remainingAmount() {
                    return Math.max(0, this.grandTotal - this.paidAmount);
                },

                profit() {
                    return this.grandTotal - this.hppTotal;
                },

                profitColor() {
                    return this.profit() >= 0
                        ? 'text-green-600'
                        : 'text-red-600';
                },

                // ========================
                // STATUS BADGE
                // ========================

                statusBadgeClass() {

                    if (this.orderStatus === 'CANCELLED')
                        return 'bg-red-100 text-red-700';

                    if (this.orderStatus === 'READY')
                        return 'bg-blue-100 text-blue-700';

                    if (this.orderStatus === 'IN_PROGRESS')
                        return 'bg-yellow-100 text-yellow-700';

                    if (this.orderStatus === 'OPEN')
                        return 'bg-green-100 text-green-700';

                    return 'bg-gray-100 text-gray-700';
                },

                paymentBadgeClass() {

                    if (this.paymentStatus === 'PAID')
                        return 'bg-green-100 text-green-700';

                    if (this.paymentStatus === 'PARTIAL')
                        return 'bg-yellow-100 text-yellow-700';

                    if (this.paymentStatus === 'UNPAID')
                        return 'bg-red-100 text-red-700';

                    return 'bg-gray-100 text-gray-700';
                },

                // ========================
                // LABEL FORMAT
                // ========================

                orderTypeLabel() {
                    return window.orderConfig?.type?.[this.orderType]?.display_name ?? this.orderType
                },

                orderChannelLabel() {
                    return window.orderConfig?.channel?.[this.orderChannel]?.display_name ?? this.orderChannel
                },

                formatRp(value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(value || 0);
                },

                calcTaxAmount(tax) {
                    // tax: {calculation_type: 'percent'|'fixed', value, is_active}
                    if (!tax || !tax.is_active) return 0;

                    const base = Number(this.subTotal || 0);

                    if (tax.calculation_type === 'percent') {
                        const pct = Number(tax.value || 0);
                        return Math.round(base * pct / 100);
                    }

                    return Number(tax.value || 0);
                },

                taxTotal() {
                    return (this.taxes || []).reduce((sum, t) => sum + Number(this.calcTaxAmount(t) || 0), 0);
                },

                adjustmentTotal() {
                    return this.adjustments.reduce((sum, adj) => {
                        return sum + Number(adj.amount || 0);
                    }, 0);
                },

                rounding() {
                    // kalau ada logic pembulatan custom, taro sini.
                    // default: 0
                    return 0;
                },

                get change() {
                    return this.paidAmount - this.finalTotal();
                },

                finalTotal() {
                    return Number(this.grandTotal || 0) + this.taxTotal() + this.rounding();
                },
            }
        }
    </script>
@endpush
