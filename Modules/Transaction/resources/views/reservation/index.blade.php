@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'reservation',
    'activeSubmenu' => 'reservation',
])
@section('title', 'Reservasi')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Reservasi</h2>

        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form-reservation')"
                class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                <i class="fa fa-plus"></i>
                Tambah
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-orange-700 text-white uppercase text-xs">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Tanggal Reservasi</th>
                <th class="px-4 py-3">Kode Order</th>
                <th class="px-4 py-3">Nama Customer</th>
                <th class="px-4 py-3">No. HP</th>
                <th class="px-4 py-3">DP</th>
                <th class="px-4 py-3">Order</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($reservations as $key => $reservation)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3">{{ parse_date_time($reservation->reserved_at) }}</td>
                        <td class="px-4 py-3">{{ @$reservation->order->code }}</td>
                        <td class="px-4 py-3">{{ $reservation->customer_name }}</td>
                        <td class="px-4 py-3">{{ $reservation->phone }}</td>
                        <td class="px-4 py-3">{{ rp_format(@$reservation->payments->where('type', 'DP')->first()->amount) }}</td>
                        <td class="px-4 py-3">{{ count(@$reservation->order->items ?? []) }} Menu</td>
                        <td class="px-4 py-3">{{ $reservation->status }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @if($reservation->status == 'ORDERING')
                                    <a
                                        href="{{ route('transaction.reservation.confirm', $reservation) }}"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 flex items-center gap-2"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                    </a>
                                @endif
                                @if(in_array($reservation->status, ['CONFIRMED', 'COMPLETED']))
                                    @php
                                        $order = $reservation->order;
                                    @endphp
                                    <button
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 flex items-center gap-2"

                                        @click="$dispatch('open-modal', {
                                            id: 'modal-order-detail',
                                            payload: {
                                                reservation_id: '{{ $reservation->id }}',
                                                id: '{{ $order->id }}',
                                                code: '{{ $order->code }}',
                                                status: '{{ $order->status }}',
                                                payment_status: '{{ $order->payment_status }}',
                                                table: '{{ $order->table_number ?? $order->customer_name }}',
                                                channel: '{{ @config('array.order.channel')[$order->channel]['display_name'] }}',
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
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </button>
                                @endif
                                @if(!in_array($reservation->status, ['COMPLETED']))
                                    @if($reservation->order)
                                        <a
                                            href="{{ route('transaction.list-order.reorder', $reservation->order) }}"
                                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center gap-2"
                                        >
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('transaction.reservation.preorder', $reservation) }}"
                                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center gap-2"
                                        >
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-modal id="modal-order-detail" idModalTitle="modal-title-order-detail" idSubModalTitle="modal-sub-title-order-detail" icon="fa-hashtag" title="Detail Order" size="xl">
        <div x-data="detailOrder()" class="p-6 space-y-6">
            {{-- META --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <!-- JENIS ORDER (LEBIH PANJANG) -->
                <div class="md:col-span-2 rounded-lg border bg-gray-50 p-3">
                    <p class="text-xs text-gray-500">Jenis Order</p>
                    <p class="font-semibold truncate" x-text="payload?.channel ?? '-'"></p>
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
                        <span>Sisa Bayar</span>
                        <span class="text-red-600"
                              x-text="formatRp((payload?.grand_total ?? 0) - (payload?.paid_amount ?? 0))">
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

                {{--                <button--}}
                {{--                    class="px-4 py-2 border rounded-lg hover:bg-gray-100 flex items-center gap-2"--}}
                {{--                    @click="open=false"--}}
                {{--                >--}}
                {{--                    <i class="fa-solid fa-xmark"></i>--}}
                {{--                    Tutup--}}
                {{--                </button>--}}

                <a
                    x-show="payload?.status != 'COMPLETED'"
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
@endsection

<x-modal id="modal-form-reservation" title="Tambah Reservasi" icon="fa-plus" size="xl">
    <form method="POST" action="{{ route('transaction.reservation.store') }}">
        @csrf
        <div class="p-5 space-y-5" x-data="reservation()">
            <div class="space-y-2">
                {{-- INFO MENU --}}
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Customer</label>
                        <input type="text" name="name" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="phone" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="date" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Waktu</label>
                        <input type="time" name="time" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-sm font-medium">Metode Pembayaran</label>

                    <div class="grid grid-cols-3 gap-2">
                        <!-- CASH -->
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="pay_method"
                                value="CASH"
                                class="hidden"
                                x-model="paymentMethod"
                            >
                            <div
                                :class="paymentMethod === 'CASH'
                ? 'bg-orange-600 text-white border-orange-600'
                : 'border hover:bg-orange-100'"
                                class="rounded-md px-4 py-3 flex flex-col items-center gap-1 border transition"
                            >
                                <i class="fa fa-money-bill text-lg"></i>
                                <span class="text-xs">Cash</span>
                            </div>
                        </label>

                        <!-- CARD -->
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="pay_method"
                                value="CARD"
                                class="hidden"
                                x-model="paymentMethod"
                            >
                            <div
                                :class="paymentMethod === 'CARD'
                ? 'bg-orange-600 text-white border-orange-600'
                : 'border hover:bg-orange-100'"
                                class="rounded-md px-4 py-3 flex flex-col items-center gap-1 border transition"
                            >
                                <i class="fa fa-credit-card text-lg"></i>
                                <span class="text-xs">Kartu</span>
                            </div>
                        </label>

                        <!-- QRIS -->
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="pay_method"
                                value="QRIS"
                                class="hidden"
                                x-model="paymentMethod"
                            >
                            <div
                                :class="paymentMethod === 'QRIS'
                ? 'bg-orange-600 text-white border-orange-600'
                : 'border hover:bg-orange-100'"
                                class="rounded-md px-4 py-3 flex flex-col items-center gap-1 border transition"
                            >
                                <i class="fa fa-qrcode text-lg"></i>
                                <span class="text-xs">QRIS</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">
                        <span>Uang DP</span>
                    </label>
                    <input
                        type="number"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        name="pay_amount"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Note (opsional)</label>
                    <textarea name="note" id="" cols="20" rows="5" class="w-full rounded-lg border border-gray-300 px-3 py-2"></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-5 py-4 border-t">
            <button type="button"
                    @click="$dispatch('close-modal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-500">
                Batal
            </button>

            <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500">
                Simpan
            </button>
        </div>
    </form>
</x-modal>

@push('js')
    <script>
        function reservation() {
            return {
                paymentMethod: 'CASH',
            }
        }

        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }

        function detailOrder(){
            return {
                groupItemsByBatch(items){
                    return Object.values(
                        items.reduce((acc, item) => {
                            if(!acc[item.batch]) acc[item.batch] = [];
                            acc[item.batch].push(item);
                            return acc;
                        }, {})
                    );
                },
            }
        }

        function paymentModal() {
            return {
                open: false,
                order: null,
                reservation_id: null,

                grandTotal: 0,
                alreadyPaid: 0,
                remaining: 0,

                paymentMode: 'FULL', // FULL | DP
                paymentMethod: 'CASH',
                payAmount: 0,

                get change() {
                    if (this.paymentMode === 'DP'){
                        return this.payAmount - this.remaining;
                    };
                    return Math.max(0, this.payAmount - this.remaining);
                },

                openModal(payload) {
                    this.order = payload;
                    this.reservation_id = payload.reservation_id;

                    this.grandTotal = payload.grand_total;
                    this.alreadyPaid = payload.paid_amount || 0;
                    this.remaining = this.grandTotal - this.alreadyPaid;

                    // this.paymentMode = this.remaining < this.grandTotal ? 'DP' : 'FULL';
                    this.paymentMethod = 'CASH';
                    this.payAmount = this.remaining;

                    this.open = true;
                },

                close() {
                    this.open = false;
                },

                setPaymentMode(mode) {
                    this.paymentMode = mode;
                    if (mode === 'FULL') {
                        this.payAmount = this.remaining;
                    } else {
                        this.payAmount = 0;
                    }
                },

                formatRp(n) {
                    return new Intl.NumberFormat('id-ID').format(n || 0);
                },

                submitPayment() {
                    fetch('{{ route('transaction.reservation.pay') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ @csrf_token() }}'
                        },
                        body: JSON.stringify({
                            reservation_id: this.reservation_id,
                            order_id: this.order.id,
                            method: this.paymentMethod,
                            amount: this.payAmount
                        })
                    })
                        .then(r => r.json())
                        .then(res => {
                            if (!res.success) {
                                alert(res.message || 'Gagal bayar');
                                return;
                            }

                            this.close();
                            window.location.reload();
                        });
                }
            }
        }
    </script>
@endpush
