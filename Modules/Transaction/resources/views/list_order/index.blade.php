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

                                            <div class="text-right">
                                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100">
                                                x<span x-text="item.qty"></span>
                                            </span>
                                                <p class="font-semibold text-orange-600"
                                                   x-text="formatRp(item.subtotal)">
                                                </p>
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

                            <!-- SUDAH BAYAR -->
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Sudah Dibayar</span>
                                <span x-text="formatRp(payload?.paid_amount ?? 0)"></span>
                            </div>

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
                {{--            <div class="space-y-2">--}}
                {{--                <label class="text-sm font-medium">Mode Pembayaran</label>--}}
                {{--                <div class="grid grid-cols-2 gap-2">--}}
                {{--                    <button--}}
                {{--                        type="button"--}}
                {{--                        @click="setPaymentMode('FULL')"--}}
                {{--                        :class="paymentMode==='FULL' ? 'bg-orange-600 text-white' : 'border'"--}}
                {{--                        class="rounded-md py-2 text-sm font-semibold"--}}
                {{--                    >--}}
                {{--                        Bayar Lunas--}}
                {{--                    </button>--}}
                {{--                    <button--}}
                {{--                        type="button"--}}
                {{--                        @click="setPaymentMode('DP')"--}}
                {{--                        :class="paymentMode==='DP' ? 'bg-orange-600 text-white' : 'border'"--}}
                {{--                        class="rounded-md py-2 text-sm font-semibold"--}}
                {{--                    >--}}
                {{--                        DP--}}
                {{--                    </button>--}}
                {{--                </div>--}}
                {{--            </div>--}}

                <!-- METHOD -->
                <div class="space-y-2">
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
                <div class="space-y-2">
                    <label class="text-sm font-medium" x-text="paymentMode==='FULL' ? 'Uang Diterima' : 'Nominal DP'"></label>
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
                <div class="grid grid-cols-4 gap-2">
                    <template x-for="n in [50000,100000,200000,500000]" :key="n">
                        <button class="border rounded py-2" @click="payAmount=n" x-text="formatRp(n)"></button>
                    </template>
                </div>

                <button class="w-full border rounded py-2" @click="payAmount = remaining">
                    Uang Pas
                </button>

                <!-- CHANGE -->
                <div class="bg-gray-100 rounded-lg p-3 text-center">
                    <template x-if="paymentMode==='FULL'">
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

                <!-- ACTION -->
                <div class="flex gap-2">
                    <button class="flex-1 border rounded py-2" @click="close()">Batal</button>
                    <button
                        class="flex-1 bg-orange-600 text-white rounded py-2"
                        :disabled="payAmount <= 0"
                        @click="submitPayment()"
                    >
                        <span x-text="paymentMode==='DP' ? 'Bayar DP' : 'Bayar Sisa'"></span>
                    </button>
                </div>
            </div>
        </div>

        @include('components.virtual-keyboard')
    </div>
@endsection

@push('js')
    <script>
        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
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
                            throw new Error('Invalid JSON response');
                        }

                        if(!res.ok){
                            throw new Error(result.message || 'Server error');
                        }

                        if(!result.success){
                            throw new Error(result.message || 'Print gagal');
                        }

                        alert('Print berhasil!');
                        console.log('🖨️ PRINT SUCCESS', result);

                    } catch(err){

                        console.error(err);
                        alert(err.message || 'Tidak bisa menghubungi server');

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

                get change(){

                    if(this.paymentMode === 'DP'){
                        return this.payAmount - this.remaining;
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
                                method:this.paymentMethod,
                                amount:this.payAmount
                            })
                        });

                        const result = await res.json();

                        if(!res.ok){
                            throw new Error(result.message || 'Server error');
                        }

                        if(!result.success){
                            throw new Error(result.message || 'Gagal bayar');
                        }

                        this.close();
                        window.location.reload();

                    }catch(err){

                        console.error(err);
                        alert(err.message || 'Terjadi kesalahan');

                    }

                }

            }

        }

    </script>
@endpush
