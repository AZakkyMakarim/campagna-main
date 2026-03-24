@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'list-order',
    'activeSubmenu' => 'list-order',
])
@section('title', 'List Order')

@section('content')
    <div x-data="formFilter()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">List Order</h2>
        </div>

        <!-- FILTER -->
        <div class="bg-white border rounded-xl p-4 mb-4 shadow-sm">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">

                <!-- SEARCH -->
                <div>
                    <input
                        type="text"
                        name="code"
                        value="{{ request('code') }}"
                        placeholder="Cari kode order"
                        @focus="setActiveInput($event.target)"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>

                <!-- PAGER -->
                <div>
                    <input
                        type="text"
                        name="table_number"
                        value="{{ request('table_number') }}"
                        placeholder="Cari pager / customer..."
                        @focus="setActiveInput($event.target)"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                </div>

                <!-- ORDER TYPE -->
                <div>
                    <select
                        name="type"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Jenis Order</option>
                        @foreach(\App\Models\OrderType::get() as $type)
                            <option value="{{ $type->name }}" @selected($type == request('type'))>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- ORDER STATUS -->
                <div>
                    <select
                        name="status"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Status Pesanan</option>
                        @foreach(config('array.order.status') as $key => $status)
                            <option value="{{ $key }}" @selected($key == request('status'))>{{ $status['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- PAYMENT STATUS -->
                <div>
                    <select
                        name="payment_status"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <option value="">Status Pembayaran</option>
                        @foreach(config('array.order.payment_status') as $key => $paymentStat)
                            <option value="{{ $key }}" @selected($key == request('payment_status'))>{{ $paymentStat['label'] }}</option>
                        @endforeach
                    </select>
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

        <div class="bg-white border rounded-xl shadow-sm mb-4 w-full">
            <div class="flex w-full divide-x">

                <!-- TOTAL ORDER -->
                <div class="flex-1 flex items-center gap-3 px-4 py-4">
                    <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                        <i class="fa fa-receipt"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">
                            Total Order
                        </p>
                        <p class="font-bold text-xl text-gray-800">
                            {{ $summary['total_orders'] }}
                        </p>
                    </div>
                </div>

                <!-- TOTAL REVENUE -->
                <div class="flex-1 flex items-center gap-3 px-4 py-4">
                    <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                        <i class="fa fa-coins"></i>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">
                            Total Pendapatan
                        </p>
                        <p class="font-bold text-lg text-green-600">
                            {{ rp_format($summary['total_revenue']) }}
                        </p>
                    </div>
                </div>

                <!-- PAYMENT METHODS DINAMIS -->
                @foreach($summary['revenue_by_method'] as $method => $data)

                    <div class="flex-1 flex items-center gap-3 px-4 py-4">

                        <div class="w-9 h-9 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center">
                            <i class="fa fa-credit-card"></i>
                        </div>

                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">
                                {{ $method }}
                            </p>

                            <p class="font-semibold text-gray-800">
                                {{ rp_format($data['total_nominal']) }}
                            </p>

                            <p class="text-xs text-gray-400">
                                {{ $data['jumlah_transaksi'] }} transaksi
                            </p>
                        </div>

                    </div>

                @endforeach

            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($orders as $order)
                <div
                    class="relative rounded-lg border bg-white shadow-sm group
                   hover:shadow-lg transition-all cursor-pointer
                   hover:border-orange-300"

                    @click="$dispatch('open-modal', {
                        id: 'modal-order-detail',
                        payload: {
                            id: '{{ $order->id }}',
                            code: '{{ $order->code }}',
                            status: '{{ $order->status }}',
                            payment_status: '{{ $order->payment_status }}',
                            payment_type: '{{ @$order->payment->method }}',
                            table: '{{ $order->table_number ?? $order->customer_name }}',
                            channel: '{{ @config('array.order.channel')[$order->channel]['display_name'] }}',
                            type: '{{ $order->type }}',
                            time: '{{ $order->created_at->format('H.i') }}',
                            reorder_url: '{{ route('transaction.list-order.reorder', $order) }}',
                            items: {{ $order->items->map(fn($i) => [
                                'id'        => $i->id,
                                'name'      => $i->name_snapshot,
                                'qty'       => $i->qty,
                                'subtotal'  => $i->subtotal,
                                'note'      => $i->note,
                                'done_qty'  => $i->done_qty,
                                'void_qty'  => $i->void_qty,
                                'batch'     => $i->batch,
                            ])->values()->toJson() }},
                            adjustments: {{ $order->adjustments->map(fn($a) => [
                                'type'        => $a->type,
                                'name'        => $a->name,
                                'method'      => $a->method,
                                'value'       => $a->value,
                                'amount'      => $a->amount,
                                'is_addition' => $a->is_addition,
                            ])->values()->toJson() }},
                            paid_amount: {{ $order->paid_amount }},
                            sub_total: {{ $order->sub_total }},
                            grand_total: {{ $order->grand_total }},
                            payments: {{ $order->payments->map(fn($p) => [
                                'method' => $p->method,
                                'amount' => $p->amount,
                            ])->values()->toJson() }},
                        }
                    })"
                >
                    {{-- HEADER --}}
                    <div class="bg-gradient-to-r rounded-t-lg from-orange-100 via-orange-50 to-transparent p-4 border-b">
                        <div class="flex items-center justify-between">

                            {{-- QUEUE NUMBER --}}
                            <div class="flex items-center gap-2">
                                <div class="bg-orange-600 text-white rounded-lg px-3 py-1.5">
                                <span class="text-lg font-bold">
                                    {{ $order->code }}
                                </span>
                                </div>
                            </div>

                            {{-- STATUS --}}
                            <div class="flex flex-col items-end gap-1 text-right">

                                {{-- ORDER STATUS --}}
                                @php
                                    $orderStatus = config('array.order.status')[strtolower($order->status)] ?? null;
                                @endphp

                                @if($orderStatus)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                                             text-xs font-semibold {{ $orderStatus['class'] }}">
                                    <i class="fa {{ $orderStatus['icon'] }} text-xs mr-1"></i>
                                    {{ $orderStatus['label'] }}
                                </span>
                                @endif

                                {{-- PAYMENT STATUS --}}
                                @php
                                    $paymentStatus = config('array.order.payment_status')[strtolower($order->payment_status)] ?? null;
                                @endphp

                                @if($paymentStatus)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5
                                             text-xs font-semibold {{ $paymentStatus['class'] }}">
                                    <i class="fa {{ $paymentStatus['icon'] }} text-xs mr-1"></i>
                                    {{ $paymentStatus['label'] }}
                                </span>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- BODY --}}
                    <div class="p-4 space-y-3">
                        {{-- META --}}
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if($order->table_number ?? $order->customer_name)
                                <span class="inline-flex items-center rounded-full bg-blue-100 text-blue-700 px-2.5 py-0.5 font-semibold">
                                <i class="fa fa-map-pin mr-1"></i>
                                    Pager {{ $order->table_number ?? $order->customer_name }}
                                </span>
                            @endif

                            <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-600 px-2.5 py-0.5 font-semibold">
                                {{ $order->type }}
                            </span>

                            <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-600 px-2.5 py-0.5 font-semibold">
                                {{ @$order->payment->method }}
                            </span>
                        </div>

                        {{-- ITEMS --}}
                        <div class="space-y-1.5">
                            @foreach($order->items->take(3) as $item)
                                <div class="flex justify-between text-sm">
                                <span class="text-gray-500 truncate flex-1">
                                    <span class="text-gray-800">x{{ $item->qty }}</span> |
                                    {{ $item->name_snapshot }}
                                </span>
                                    <span class="text-gray-800 ml-2">
                                    {{ rp_format($item->subtotal) }}
                                </span>
                                </div>
                            @endforeach

                            @if($order->items->count() > 3)
                                <p class="text-xs text-gray-400">
                                    +{{ $order->items->count() - 3 }} item lainnya
                                </p>
                            @endif
                        </div>

                        <div class="h-px bg-gray-200"></div>

                        {{-- FOOTER --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <i class="fa fa-clock"></i>
                                {{ $order->created_at->format('H.i') }}
                            </div>

                            <span class="font-semibold text-orange-600">
                            {{ rp_format($order->grand_total) }}
                        </span>
                        </div>
                    </div>

                    {{-- HOVER ACTION --}}
                    <div
                        class="absolute inset-x-0 bottom-0 rounded-b-lg
                           bg-gradient-to-t from-orange-600/90 via-orange-500/70 to-transparent
                           px-4 py-3 text-center
                           opacity-0 translate-y-2
                           group-hover:opacity-100 group-hover:translate-y-0
                           transition-all duration-200"
                    >
                    <span class="text-xs text-white font-semibold flex items-center justify-center gap-2">
                        Lihat Detail
                        <i class="fa fa-arrow-right text-xs"></i>
                    </span>
                    </div>
                </div>
            @endforeach
        </div>

        <x-modal id="modal-order-detail" idModalTitle="modal-title-order-detail" idSubModalTitle="modal-sub-title-order-detail" icon="fa-hashtag" title="Detail Order" size="xl">
            <div x-data="detailOrder()">
                <div class="p-6 space-y-6">
                    {{-- META --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- JENIS ORDER (LEBIH PANJANG) -->
                        <div class="md:col-span-2 rounded-lg border bg-gray-50 p-3">
                            <p class="text-xs text-gray-500">Jenis Order</p>
                            <p class="font-semibold truncate" x-text="payload?.type ?? '-'"></p>
                        </div>

                        <!-- MEJA -->
                        <div class="rounded-lg border bg-gray-50 p-3">
                            <p class="text-xs text-gray-500">Pager</p>
                            <p class="font-semibold" x-text="payload?.table"></p>
                        </div>

                        <!-- WAKTU -->
                        <div class="rounded-lg border bg-gray-50 p-3">
                            <p class="text-xs text-gray-500">Waktu</p>
                            <p class="font-semibold" x-text="payload?.time"></p>
                        </div>
                    </div>

                    {{-- ITEM LIST --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-semibold flex items-center gap-2">
                                <i class="fa fa-bag-shopping"></i>
                                Detail Pesanan
                            </h4>

                            <p class="text-sm text-gray-500 font-semibold"
                               x-text="(payload?.items?.length ?? 0) + ' item'">
                            </p>
                        </div>

                        <div class="space-y-4 max-h-[35vh] overflow-y-auto">

                            <template x-for="(batchItems, batchIdx) in groupItemsByBatch(payload?.items || [])" :key="batchIdx">

                                <div class="space-y-3">

                                    <!-- HEADER BATCH -->
                                    <div class="text-sm font-bold border-b pb-1">
                                        Pesanan <span x-text="batchIdx + 1"></span>
                                    </div>

                                    <!-- ITEMS -->
                                    <template x-for="(item, idx) in batchItems" :key="idx">
                                        <div class="rounded-lg border p-4 flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-xs rounded bg-orange-100 text-orange-700">
                                    #<span x-text="idx + 1"></span>
                                </span>
                                                    <span class="font-medium" x-text="item.name"></span>
                                                </div>

                                                <p x-show="item.note" class="text-xs text-gray-500 italic pl-6 mt-1">
                                                    • <span x-text="item.note"></span>
                                                </p>

                                                <div class="flex gap-2 mt-2 text-xs">
                                                    <span x-show="item.done_qty > 0"
                                                          class="px-2 py-0.5 rounded bg-green-100 text-green-700">
                                                        Done <span x-text="item.done_qty"></span>
                                                    </span>

                                                    <span x-show="item.void_qty > 0"
                                                              class="px-2 py-0.5 rounded bg-red-100 text-red-700">
                                                        Void <span x-text="item.void_qty"></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="text-right flex flex-col items-end gap-2">
                                                <button type="button" 
                                                        x-show="item.qty - item.void_qty > 0 && payload?.status != 'VOIDED'" 
                                                        @click="$dispatch('open-void-modal', {type:'ITEM', orderId: payload.id, itemId: item.id, itemName: item.name, maxQty: item.qty - item.void_qty})" 
                                                        class="text-red-500 hover:bg-red-50 p-1 rounded-md" 
                                                        title="Void Item">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                                <div class="text-right mt-auto">
                                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100">
                                                        x<span x-text="item.qty - item.void_qty"></span>
                                                    </span>
                                                    <p class="font-semibold text-orange-600"
                                                       x-text="formatRp(item.subtotal / item.qty * (item.qty - item.void_qty))">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                </div>

                            </template>

                        </div>
                    </div>

                    {{-- TOTAL --}}
                    <div class="rounded-lg border bg-gradient-to-br from-orange-100 to-orange-50 p-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span x-text="formatRp(payload?.sub_total)"></span>
                            </div>

                            <!-- TAX / ADJUSTMENT -->
                            <template x-if="payload?.adjustments?.length">
                                <div class="space-y-1 text-sm">
                                    <template x-for="(adj, i) in payload.adjustments" :key="i">
                                        <div class="flex justify-between text-gray-600">
                                            <span x-text="adj.name"></span>
                                            <span
                                                :class="adj.is_addition ? 'text-green-600' : 'text-red-600'"
                                                x-text="(adj.is_addition ? '+ ' : '- ') + formatRp(adj.amount)"
                                            ></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <hr>

                            <div class="flex justify-between">
                                <span>Total</span>
                                <span class="font-semibold text-orange-600" x-text="formatRp(payload?.grand_total)"></span>
                            </div>

                            {{-- PEMBAYARAN PER METODE --}}
                            <template x-if="payload?.payments?.length">
                                <div class="space-y-1">
                                    <template x-for="(p, i) in payload.payments" :key="i">
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span x-text="p.method"></span>
                                            <span class="font-medium" x-text="formatRp(p.amount)"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!payload?.payments?.length">
                                <div class="flex justify-between text-sm text-gray-600">
                                    <span>Sudah Dibayar</span>
                                    <span x-text="formatRp(payload?.paid_amount ?? 0)"></span>
                                </div>
                            </template>

                            <!-- SISA -->
                            <div class="flex justify-between text-lg font-bold">
                                <span x-text="remainingLabel()"></span>

                                <span
                                    :class="remaining() < 0 ? 'text-green-600' : 'text-red-600'"
                                    x-text="formatRp(Math.abs(remaining()))">
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="p-4 flex items-center justify-between">

                    <!-- LEFT -->
                    <div>
                        <button
                            x-show="payload?.status != 'VOIDED'"
                            class="px-4 py-2 text-red-600 rounded-lg hover:bg-red-50 flex items-center gap-2 font-medium"
                            @click="$dispatch('open-void-modal', {type:'NOTA', orderId: payload.id})"
                        >
                            <i class="fa-solid fa-ban"></i>
                            Void Nota
                        </button>
                    </div>

                    <!-- RIGHT -->
                    <div class="flex gap-3">
                        <a
                            x-show="payload?.payment_status != 'PAID'"
                            :href="payload?.reorder_url"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 flex items-center gap-2"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            Reorder
                        </a>

                        <button
                            x-show="['UNPAID','PARTIAL'].includes(payload?.payment_status)"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center gap-2"
                            @click="$dispatch('open-pay-order', payload)"
                        >
                            <i class="fa-solid fa-credit-card"></i>
                            Bayar
                        </button>

                        <button
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 flex items-center gap-2"
                            @click="printStruk(payload.id)"
                        >
                            <i class="fa-solid fa-print"></i>
                            Print Struk
                        </button>

                    </div>

                </div>
            </div>
        </x-modal>

        <div
            x-data="paymentModal()"
            x-show="open"
            x-cloak
            x-on:open-pay-order.window="openModal($event.detail)"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60" @click="close()"></div>

            <!-- Box -->
            <div class="relative z-10 bg-white w-full max-w-lg rounded-xl shadow-xl p-6 space-y-4">

                <h3 class="text-lg font-bold">Pembayaran Order</h3>

                <!-- SUMMARY -->
                <div class="rounded-lg border bg-orange-50 p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Total</span>
                        <span class="font-semibold" x-text="formatRp(grandTotal)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Sudah Dibayar</span>
                        <span class="font-semibold text-green-600" x-text="formatRp(alreadyPaid)"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold pt-1">
                        <span>Sisa</span>
                        <span class="text-orange-600" x-text="formatRp(remaining)"></span>
                    </div>
                </div>

                <!-- MODE -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Mode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            type="button"
                            @click="setPaymentMode('FULL')"
                            :class="paymentMode==='FULL' ? 'bg-orange-600 text-white' : 'border'"
                            class="rounded-md py-2 text-sm font-semibold"
                        >
                            Bayar Lunas
                        </button>
                        <button
                            type="button"
                            @click="setPaymentMode('SPLIT')"
                            :class="paymentMode==='SPLIT' ? 'bg-orange-600 text-white' : 'border'"
                            class="rounded-md py-2 text-sm font-semibold"
                        >
                            Split
                        </button>
                    </div>
                </div>

                {{-- SPLIT UI --}}
                <div x-show="paymentMode === 'SPLIT'" class="space-y-3 mt-2">
                    <template x-for="(split, i) in splits" :key="i">
                        <div
                            class="rounded-xl border p-3 space-y-2 transition-all"
                            :class="split.confirmed ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200'"
                        >
                            {{-- HEADER BARIS --}}
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500" x-text="`Metode ${i + 1}`"></span>
                                <div class="flex items-center gap-1">
                                    <span
                                        x-show="split.confirmed"
                                        class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full flex items-center gap-1"
                                    >
                                        <i class="fa fa-check"></i> Terkonfirmasi
                                    </span>
                                    <button
                                        type="button"
                                        x-show="split.confirmed"
                                        @click="unconfirmSplit(i)"
                                        class="text-xs text-orange-500 hover:text-orange-700 px-1"
                                        title="Batalkan konfirmasi"
                                    >
                                        <i class="fa fa-rotate-left"></i>
                                    </button>
                                    <button
                                        type="button"
                                        x-show="!split.confirmed && splits.length > 1"
                                        @click="removeSplit(i)"
                                        class="text-red-400 hover:text-red-600 px-1"
                                        title="Hapus baris"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- METODE + NOMINAL --}}
                            <div class="flex gap-2 items-center">
                                <select
                                    x-model="split.method"
                                    :disabled="split.confirmed"
                                    class="border rounded-lg px-2 py-2 flex-1 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 text-gray-700 disabled:bg-gray-100"
                                >
                                    <option value="">Pilih Metode</option>
                                    <option value="CASH">Cash / Tunai</option>
                                    <option value="QRIS">QRIS</option>
                                    <option value="CARD">Kartu Debit/Kredit</option>
                                    <option value="TRANSFER">Transfer Bank</option>
                                </select>
                                <input
                                    type="text" inputmode="numeric"
                                    :disabled="split.confirmed"
                                    :value="split.amount ? split.amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : ''"
                                    @input="
                                        const clean = $event.target.value.replace(/[^0-9]/g,'');
                                        split.amount = Number(clean);
                                        $event.target.value = split.amount ? split.amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g,'.') : '';
                                    "
                                    placeholder="Nominal"
                                    class="border rounded-lg px-2 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-orange-400 text-gray-700 disabled:bg-gray-100"
                                />
                            </div>

                            {{-- TOMBOL KONFIRMASI --}}
                            <button
                                type="button"
                                x-show="!split.confirmed"
                                @click="confirmSplit(i)"
                                class="w-full py-2 rounded-lg text-sm font-semibold bg-orange-500 text-white hover:bg-orange-600 transition flex items-center justify-center gap-2"
                            >
                                <i class="fa fa-check"></i>
                                Konfirmasi Sudah Dibayar
                            </button>

                            {{-- KEMBALIAN PER BARIS TUNAI --}}
                            <div
                                x-show="split.confirmed && ['CASH','TUNAI'].includes(split.method.toUpperCase()) && splitRemainingAmount() < 0"
                                class="text-xs text-green-700 bg-green-100 rounded px-2 py-1 text-center"
                            >
                                Kembalian: <span x-text="formatRp(Math.abs(splitRemainingAmount()))"></span>
                            </div>
                        </div>
                    </template>

                    {{-- RINGKASAN SPLIT --}}
                    <div class="bg-gray-50 rounded-xl p-3 space-y-1 text-sm border">
                        <div class="flex justify-between text-gray-500">
                            <span>Sudah Dikonfirmasi</span>
                            <span x-text="formatRp(splitConfirmedTotal())"></span>
                        </div>
                        <div
                            class="flex justify-between font-bold text-base"
                            :class="splitRemainingAmount() > 0 ? 'text-red-500' : 'text-green-600'"
                        >
                            <span x-text="splitRemainingAmount() > 0 ? 'Sisa yang perlu dibayar' : 'Lunas ✓'"></span>
                            <span x-text="formatRp(splitRemainingAmount())"></span>
                        </div>
                    </div>

                    {{-- TAMBAH METODE --}}
                    <button
                        type="button"
                        x-show="splitRemainingAmount() > 0"
                        @click="addSplit()"
                        class="text-sm text-orange-600 border border-orange-300 px-3 py-2 rounded-lg hover:bg-orange-50 w-full"
                    >
                        <i class="fa fa-plus mr-1"></i> Tambah Metode Pembayaran
                    </button>
                </div>

                <!-- METHOD -->
                <div class="space-y-2" x-show="paymentMode !== 'SPLIT'">
                    <label class="text-sm font-medium">Metode</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="paymentMethod='CASH'"
                                :class="paymentMethod==='CASH' ? 'bg-orange-600 text-white' : 'border'"
                                class="rounded-md py-2 py-3 flex flex-col items-center gap-1">

                            <i class="fa fa-money-bill text-lg"></i>
                            <span class="text-xs">Cash</span>
                        </button>
                        <button @click="paymentMethod='CARD'"
                                :class="paymentMethod==='CARD' ? 'bg-orange-600 text-white' : 'border'"
                                class="rounded-md py-2 py-3 flex flex-col items-center gap-1">
                            <i class="fa fa-credit-card text-lg"></i>
                            <span class="text-xs">Kartu</span>
                        </button>
                        <button @click="paymentMethod='QRIS'"
                                :class="paymentMethod==='QRIS' ? 'bg-orange-600 text-white' : 'border'"
                                class="rounded-md py-2 py-3 flex flex-col items-center gap-1">
                            <i class="fa fa-qrcode text-lg"></i>
                            <span class="text-xs">QRIS</span>
                        </button>
                    </div>
                </div>

                <!-- AMOUNT -->
                <div class="space-y-2" x-show="paymentMode !== 'SPLIT'">
                    <label class="text-sm font-medium">Uang Diterima</label>
                    <input
                        type="text"
                        inputmode="numeric"
                        @focus="setActiveInput($event.target)"
                        @keydown="if(!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test($event.key)) $event.preventDefault()"
                        class="w-full text-gray-700 pl-2 pr-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        :value="payAmount ? payAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''"
                        @input="
                        const clean = $event.target.value.replace(/[^0-9]/g, '');
                        payAmount = Number(clean);
                        $event.target.value = payAmount ? payAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                    "
                    >
                </div>

                <!-- QUICK -->
                <div class="grid grid-cols-4 gap-2" x-show="paymentMode !== 'SPLIT'">
                    <template x-for="n in [50000,100000,200000,500000]" :key="n">
                        <button class="border rounded py-2" @click="payAmount=n" x-text="formatRp(n)"></button>
                    </template>
                </div>

                <button class="w-full border rounded py-2" @click="payAmount = remaining" x-show="paymentMode !== 'SPLIT'">
                    Uang Pas
                </button>

                <!-- CHANGE -->
                <div class="bg-gray-100 rounded-lg p-3 text-center">
                    <template x-if="paymentMode!=='DP'">
                        <p class="text-sm text-gray-500">Kembalian</p>
                    </template>
                    <template x-if="paymentMode==='DP'">
                        <p class="text-sm text-gray-500">Sisa Pembayaran</p>
                    </template>
                    <p class="text-xl font-bold"
                       :class="change < 0 ? 'text-red-500' : 'text-green-600'"
                       x-text="formatRp(change)">
                    </p>
                </div>

                {{-- ACTION --}}
                <div class="flex gap-2">
                    <button class="flex-1 border rounded py-2" @click="close()">Batal</button>
                    <button
                        class="flex-1 text-white rounded py-2 transition font-semibold"
                        :disabled="paymentMode === 'SPLIT' ? !allSplitConfirmed() : payAmount <= 0"
                        :class="(paymentMode === 'SPLIT' ? !allSplitConfirmed() : payAmount <= 0)
                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            : 'bg-orange-600 hover:bg-orange-500'"
                        @click="submitPayment()"
                    >
                        <span x-text="paymentMode === 'SPLIT' ? (allSplitConfirmed() ? 'Simpan Pembayaran' : 'Konfirmasi dulu') : 'Bayar Sisa'"></span>
                    </button>
                </div>
            </div>
        </div>

        @include('components.virtual-keyboard')
        <!-- VOID MODAL -->
        <div
            x-data="voidModal()"
            x-show="open"
            x-cloak
            x-on:open-void-modal.window="openModal($event.detail)"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="close()"></div>

            <!-- Modal Content -->
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="bg-red-50 p-4 border-b border-red-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-ban text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-error-900 font-bold text-lg" x-text="type === 'ITEM' ? 'Void Item' : 'Void Nota'"></h3>
                            <p class="text-error-600 text-sm">Otorisasi Supervisor diperlukan</p>
                        </div>
                    </div>
                    <button @click="close()" class="w-8 h-8 rounded-full bg-white text-gray-400 hover:text-gray-600 hover:bg-gray-100 flex items-center justify-center transition-colors">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-5">
                    
                    <!-- Item Info (For ITEM Mode Only) -->
                    <div x-show="type === 'ITEM'" class="bg-gray-50 rounded-lg p-3 border border-gray-200 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Item yang di-void</p>
                            <p class="font-medium text-gray-800" x-text="itemName"></p>
                        </div>
                    </div>

                    <!-- Quantity Input (For ITEM Mode Only) -->
                    <div x-show="type === 'ITEM'" class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-700">Jumlah Void</label>
                        <div class="flex items-center">
                            <button 
                                type="button" 
                                @click="form.qty = Math.max(1, form.qty - 1)" 
                                class="w-10 h-10 flex items-center justify-center bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200">
                                <i class="fa fa-minus"></i>
                            </button>
                            <input 
                                type="number" 
                                x-model.number="form.qty" 
                                min="1" 
                                :max="maxQty"
                                @input="form.qty = Math.min(Math.max(1, form.qty), maxQty)"
                                class="w-16 h-10 border-y border-gray-300 text-center focus:outline-none focus:ring-1 focus:ring-red-500 font-medium">
                            <button 
                                type="button" 
                                @click="form.qty = Math.min(maxQty, form.qty + 1)" 
                                class="w-10 h-10 flex items-center justify-center bg-gray-100 border border-gray-300 rounded-r-lg hover:bg-gray-200">
                                <i class="fa fa-plus"></i>
                            </button>
                            <span class="ml-3 text-sm text-gray-500">
                                Maks: <span x-text="maxQty"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Void Code -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-700">Kode Supervisor <span class="text-red-500">*</span></label>
                        <div class="relative relative-group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa fa-lock text-gray-400"></i>
                            </div>
                            <input 
                                type="password" 
                                x-model="form.void_code" 
                                placeholder="Masukkan kode otorisasi" 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all placeholder:text-sm">
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-gray-700">Alasan Void <span class="text-red-500">*</span></label>
                        <textarea 
                            x-model="form.note" 
                            rows="2" 
                            placeholder="Cth: Salah input, Customer batal..." 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all text-sm resize-none"></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 p-4 border-t flex items-center justify-end gap-3">
                    <button 
                        @click="close()" 
                        class="px-4 py-2 font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-colors">
                        Batal
                    </button>
                    <button 
                        @click="submitVoid()" 
                        :disabled="loading"
                        class="px-4 py-2 font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors flex items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-spinner fa-spin" x-show="loading"></i>
                        <span x-text="loading ? 'Memproses...' : 'Konfirmasi Void'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
    @endsection

@push('js')
    <script>
        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }

        function showToast(message, type = 'success') {
            const colors = {
                success: { bg: '#fff7ed', border: '#fed7aa', icon: '\u2713', iconBg: '#ea580c', iconColor: '#fff', text: '#9a3412' },
                error:   { bg: '#fef2f2', border: '#fecaca', icon: '\u2717', iconBg: '#dc2626', iconColor: '#fff', text: '#991b1b' },
            };
            const c = colors[type] || colors.success;
            const toast = document.createElement('div');
            toast.style.cssText = `
                position:fixed; bottom:24px; right:24px; z-index:9999;
                display:flex; align-items:center; gap:12px;
                background:${c.bg}; border:1px solid ${c.border}; color:${c.text};
                padding:12px 20px; border-radius:12px;
                box-shadow:0 4px 20px rgba(0,0,0,0.12);
                font-size:14px; font-weight:600;
                animation: slideInToast 0.3s ease;
                max-width:320px;
            `;
            toast.innerHTML = `
                <div style="width:28px;height:28px;border-radius:50%;background:${c.iconBg};color:${c.iconColor};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">${c.icon}</div>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);

            if (!document.getElementById('toast-style')) {
                const style = document.createElement('style');
                style.id = 'toast-style';
                style.textContent = `@keyframes slideInToast { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }`;
                document.head.appendChild(style);
            }

            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(8px)';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }

        /* ===============================
           KEYBOARD FILTER
        ================================ */

        function formFilter() {
            return {
                keyboardOpen: false,
                activeInput: null,

                isMobile() {
                    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                },

                setActiveInput(el) {
                    this.activeInput = el
                    this.keyboardOpen = !this.isMobile()
                },

                pressKey(key) {
                    if (!this.activeInput) return

                    this.activeInput.value += key
                    this.activeInput.dispatchEvent(new Event('input'))
                },

                backspace() {
                    if (!this.activeInput) return

                    this.activeInput.value =
                        this.activeInput.value.slice(0, -1)

                    this.activeInput.dispatchEvent(new Event('input'))
                },

                clearSearch() {
                    if (!this.activeInput) return

                    this.activeInput.value = ''
                    this.activeInput.dispatchEvent(new Event('input'))
                }
            }
        }

        /* ===============================
           VOID MODAL
        ================================ */
        function voidModal() {
            return {
                open: false,
                loading: false,
                type: 'ITEM', // or 'NOTA'
                orderId: null,
                itemId: null,
                itemName: '',
                maxQty: 0,
                
                form: {
                    qty: 1,
                    void_code: '',
                    note: ''
                },

                openModal(payload) {
                    this.type = payload.type;
                    this.orderId = payload.orderId;
                    this.itemId = payload.itemId || null;
                    this.itemName = payload.itemName || '';
                    this.maxQty = payload.maxQty || 0;
                    
                    this.form = {
                        qty: this.maxQty > 0 ? 1 : 0,
                        void_code: '',
                        note: ''
                    };
                    
                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                async submitVoid() {
                    if(!this.form.void_code) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Silakan masukkan kode supervisor', confirmButtonColor: '#ea580c' });
                        return;
                    }

                    if(!this.form.note) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Silakan isi alasan void', confirmButtonColor: '#ea580c' });
                        return;
                    }

                    this.loading = true;

                    const url = this.type === 'ITEM' 
                        ? '{{ route('transaction.void.item') }}' 
                        : '{{ route('transaction.void.nota') }}';

                    const payloadData = this.type === 'ITEM' 
                        ? {
                            order_item_id: this.itemId,
                            qty: this.form.qty,
                            void_code: this.form.void_code,
                            note: this.form.note
                        } 
                        : {
                            order_id: this.orderId,
                            void_code: this.form.void_code,
                            note: this.form.note
                        };

                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payloadData)
                        });

                        const result = await res.json();

                        if(!res.ok || !result.success) {
                            Swal.fire({ icon: 'error', title: 'Oops!', text: result.message || 'Gagal melakukan void. Coba lagi.', confirmButtonColor: '#ea580c' });
                            return;
                        }

                        Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, confirmButtonColor: '#ea580c' }).then(() => {
                            window.location.reload();
                        });

                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: e.message, confirmButtonColor: '#ea580c' });
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        /* ===============================
           ORDER DETAIL
        ================================ */

        function detailOrder(){

            return {

                printing:false,

                groupItemsByBatch(items){
                    return Object.values(
                        items.reduce((acc, item) => {

                            if(!acc[item.batch]) acc[item.batch] = [];
                            acc[item.batch].push(item);

                            return acc;

                        }, {})
                    );
                },

                remaining() {
                    return (this.payload?.grand_total ?? 0) - (this.payload?.paid_amount ?? 0);
                },

                remainingLabel() {
                    return this.remaining() < 0 ? 'Kembalian' : 'Sisa Bayar';
                },

                async printStruk(orderId){

                    if(this.printing) return;

                    this.printing = true;

                    try {

                        const res = await fetch('{{ route('transaction.print-struck') }}', {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_id:orderId
                            })
                        });

                        let result = {};

                        try {
                            result = await res.json();
                        } catch {
                            Swal.fire('Oops!', 'Response dari server tidak valid.', 'error');
                            return;
                        }

                        if(!res.ok){
                            Swal.fire('Oops!', result.message || 'Terjadi kesalahan pada server. Coba lagi.', 'error');
                            return;
                        }

                        if(!result.success){
                            Swal.fire('Oops!', result.message || 'Print gagal. Coba lagi.', 'error');
                            return;
                        }

                        showToast('Struk berhasil dicetak', 'success');
                        console.log('🖨️ PRINT SUCCESS', result);

                    } catch(err){

                        console.error(err);
                        Swal.fire('Oops!', err.message || 'Tidak bisa menghubungi server', 'error');

                    } finally {

                        this.printing = false;

                    }

                }

            }

        }

        /* ===============================
           PAYMENT MODAL
        ================================ */

        function paymentModal() {

            return {

                open:false,
                order:null,

                grandTotal:0,
                alreadyPaid:0,
                remaining:0,

                paymentMode:'FULL',
                paymentMethod:'CASH',
                payAmount:0,
                splits: [{ method: 'CASH', amount: 0, confirmed: false }],

                addSplit() {
                    const sisa = this.splitRemainingAmount();
                    this.splits.push({ method: '', amount: sisa > 0 ? sisa : 0, confirmed: false });
                },
                removeSplit(i) {
                    if (this.splits[i].confirmed) return;
                    this.splits.splice(i, 1);
                },
                confirmSplit(i) {
                    const split = this.splits[i];
                    if (!split.method) {
                        Swal.fire('Perhatian', 'Pilih metode pembayaran terlebih dahulu', 'warning');
                        return;
                    }
                    if (!split.amount || split.amount <= 0) {
                        Swal.fire('Perhatian', 'Masukkan nominal terlebih dahulu', 'warning');
                        return;
                    }
                    split.confirmed = true;
                    const sisa = this.splitRemainingAmount();
                    const nextUnconfirmed = this.splits.findIndex((s, idx) => idx > i && !s.confirmed);
                    if (nextUnconfirmed !== -1 && sisa > 0) {
                        this.splits[nextUnconfirmed].amount = sisa;
                    }
                },
                unconfirmSplit(i) {
                    this.splits[i].confirmed = false;
                },
                splitConfirmedTotal() {
                    return this.splits
                        .filter(s => s.confirmed)
                        .reduce((sum, s) => sum + (Number(s.amount) || 0), 0);
                },
                splitTotal() {
                    return this.splits.reduce((s, p) => s + (Number(p.amount) || 0), 0);
                },
                splitRemainingAmount() {
                    return Math.max(0, this.remaining - this.splitConfirmedTotal());
                },
                splitRemaining() {
                    const total = this.splitTotal();
                    const remaining = this.remaining - total;
                    return remaining;
                },
                allSplitConfirmed() {
                    return this.splits.length > 0 &&
                           this.splits.every(s => s.confirmed) &&
                           this.splitConfirmedTotal() >= this.remaining;
                },

                get change(){

                    if(this.paymentMode === 'DP'){
                        return this.payAmount - this.remaining;
                    }

                    if (this.paymentMode === 'SPLIT') {
                        const rem = this.splitRemaining();
                        return rem < 0 ? Math.abs(rem) : 0;
                    }

                    return Math.max(0, this.payAmount - this.remaining);

                },

                openModal(payload){

                    this.order = payload;

                    this.grandTotal = payload.grand_total;
                    this.alreadyPaid = payload.paid_amount || 0;
                    this.remaining = this.grandTotal - this.alreadyPaid;

                    this.paymentMethod = 'CASH';
                    this.payAmount = this.remaining;

                    this.open = true;

                },

                close(){
                    this.open = false;
                },

                setPaymentMode(mode){

                    this.paymentMode = mode;

                    if(mode === 'FULL'){
                        this.payAmount = this.remaining;
                    }else{
                        this.payAmount = 0;
                    }

                    if (mode === 'SPLIT') {
                        this.splits = [{ method: 'CASH', amount: this.remaining, confirmed: false }];
                    }

                },

                formatRp(n){
                    return new Intl.NumberFormat('id-ID').format(n || 0);
                },

                async submitPayment(){

                    try{

                        const res = await fetch('{{ route('transaction.list-order.pay') }}', {
                            method:'POST',
                            headers:{
                                'Content-Type':'application/json',
                                'X-CSRF-TOKEN':'{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_id:this.order.id,
                                payments: this.paymentMode === 'SPLIT'
                                    ? this.splits
                                    : [{ method: this.paymentMethod, amount: this.payAmount }]
                            })
                        });

                        const result = await res.json();

                        if(!res.ok){
                            Swal.fire('Oops!', result.message || 'Terjadi kesalahan pada server. Coba lagi.', 'error');
                            return;
                        }

                        if(!result.success){
                            Swal.fire('Oops!', result.message || 'Pembayaran gagal. Coba lagi.', 'error');
                            return;
                        }

                        // ===== SPLIT =====
                        if (this.paymentMode === 'SPLIT') {
                            const splitLines = this.splits
                                .map(s => `<b>${s.method}</b>: Rp ${s.amount.toLocaleString('id-ID')}`)
                                .join('<br>');
                            const kembalian = this.splitConfirmedTotal() - this.remaining;
                            const kembalianBaris = kembalian > 0
                                ? `<br><br>Kembalian: <b>Rp ${kembalian.toLocaleString('id-ID')}</b>`
                                : '';
                            await Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                html: splitLines + kembalianBaris,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ea580c',
                            });
                            this.close();
                            window.location.reload();
                            return;
                        }

                        // ===== FULL / CASH dengan kembalian =====
                        const change = Math.max(0, this.payAmount - this.remaining);
                        if (this.paymentMethod === 'CASH' && change > 0) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Berhasil!',
                                html: `Kembalian: <b>Rp ${change.toLocaleString('id-ID')}</b>`,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#ea580c',
                            });
                            this.close();
                            window.location.reload();
                            return;
                        }

                        // ===== FULL / non-cash atau pas =====
                        await Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ea580c',
                        });
                        this.close();
                        window.location.reload();

                    }catch(err){

                        console.error(err);
                        const retryResult = await Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Gagal',
                            text: err.message || 'Tidak dapat terhubung ke server.',
                            showCancelButton: true,
                            confirmButtonText: 'Coba Lagi',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#ea580c',
                        });
                        if (retryResult.isConfirmed) {
                            this.submitPayment();
                        }

                    }

                }

            }

        }

    </script>
@endpush
