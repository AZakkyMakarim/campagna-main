@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'kitchen-display',
    'activeSubmenu' => 'kitchen-display',
])
@section('title', 'Kitchen Display')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Kitchen Display</h2>
    </div>

    <div
        x-data="kdsBoard({{ $orders->toJson() }})"
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4"
    >
        <template x-for="order in orders" :key="order.id">
            <div
                class="relative rounded-lg border bg-card shadow-sm hover:shadow-lg transition-all"
                @click="$dispatch('open-modal', {
                    id: 'modal-kds-detail',
                    payload: order
                })"
            >

                <!-- HEADER -->
                <div class="bg-gradient-to-r rounded-t-lg from-orange-100 via-orange-50 to-transparent p-4 border-b">
                    <div class="flex items-center justify-between">

                        <!-- QUEUE -->
                        <div class="bg-orange-600 text-white rounded-lg px-3 py-1.5 shadow-sm">
                            <span class="text-lg font-bold" x-text="order.code"></span>
                        </div>

                        <!-- TIMER -->
                        <div class="flex items-center gap-1 text-red-600 font-medium">
                            <i class="fa fa-clock text-sm"></i>
                            <span x-text="order.minutes + 'm'"></span>
                        </div>

                    </div>
                </div>

                <!-- BODY -->
                <div class="px-4 pt-4 pb-7 space-y-3">

                    <!-- META -->
                    <div class="flex items-center gap-2">
                        <template x-if="order.table_number">
                            <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-secondary gap-1">
                                <i class="fa fa-map-pin text-xs"></i>
                                <span x-text="'Meja ' + order.table_number"></span>
                            </div>
                        </template>

                        <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold text-xs">
                            <span x-text="order.channel_label"></span>
                        </div>
                    </div>

                    <!-- ITEMS -->
                    <div class="space-y-1.5">
                        <template x-for="item in order.items" :key="item.id">
                            <div class="flex items-center justify-between text-sm">
                                <span class="truncate flex-1" x-text="item.name_snapshot"></span>
                                <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 font-semibold bg-secondary ml-2 text-xs">
                                    x<span x-text="item.qty"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

            </div>
        </template>
    </div>

    <x-modal id="modal-kds-detail" title="Proses Pesanan" icon="fa-kitchen-set" size="xl">
        <div x-data="kdsModal()" class="p-6 space-y-6">

            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <!-- KIRI -->
                <h3 class="font-bold text-xl">
                    Antrian:
                    <span class="text-orange-600" x-text="payload?.code"></span>
                </h3>

                <!-- KANAN -->
                <p class="text-sm text-gray-500 font-semibold">
                    <span x-text="(payload?.items?.length ?? 0) + ' item'"></span>
                </p>
            </div>

            <!-- ITEM LIST -->
            <div class="space-y-4 max-h-[60vh] overflow-y-auto">
                <template x-for="(item, idx) in payload?.items" :key="item.id">
                    <div class="border rounded-xl p-4 shadow-sm space-y-3 bg-white">

                        <!-- TOP ROW -->
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-lg" x-text="item.name_snapshot"></p>
                                <p class="text-xs text-gray-500">
                                    Total: <span x-text="item.qty"></span>
                                </p>
                            </div>

                            <div class="text-right text-sm">
                            <span class="text-green-600 font-semibold">
                                Jadi: <span x-text="item.done_qty"></span>
                            </span>
                                <span class="mx-1">|</span>
                                <span class="text-red-600 font-semibold">
                                Void: <span x-text="item.void_qty"></span>
                            </span>
                                <span class="mx-1">|</span>
                                <span class="text-gray-600 font-semibold">
                                Sisa:
                                <span x-text="item.qty - item.done_qty - item.void_qty"></span>
                            </span>
                            </div>
                        </div>

                        <!-- PROGRESS BAR -->
                        <div class="h-3 w-full bg-gray-200 rounded-full overflow-hidden flex">
                            <div
                                class="bg-green-500 h-full transition-all"
                                :style="`width: ${(item.done_qty / item.qty) * 100}%`"
                            ></div>
                            <div
                                class="bg-red-500 h-full transition-all"
                                :style="`width: ${(item.void_qty / item.qty) * 100}%`"
                            ></div>
                        </div>

                        <!-- ACTION BUTTONS -->
                        <div class="grid grid-cols-4 gap-2 pt-2">

                            <!-- DONE + -->
                            <button
                                class="py-2 rounded-lg bg-green-600 text-white font-semibold"
                                @click="markDone(item)"
                            >
                                + Done
                            </button>

                            <!-- DONE - -->
                            <button
                                class="py-2 rounded-lg bg-green-100 text-green-800 font-semibold"
                                @click="undoDone(item)"
                            >
                                - Done
                            </button>

                            <!-- VOID + -->
                            <button
                                class="py-2 rounded-lg bg-red-600 text-white font-semibold"
                                @click="voidOne(item)"
                            >
                                + Void
                            </button>

                            <!-- VOID - -->
                            <button
                                class="py-2 rounded-lg bg-red-100 text-red-800 font-semibold"
                                @click="undoVoid(item)"
                            >
                                - Void
                            </button>

                        </div>
                    </div>
                </template>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button
                    class="px-5 py-2 border rounded-lg hover:bg-gray-100"
                    @click="$dispatch('close-modal')"
                >
                    Tutup
                </button>

                <button
                    class="px-5 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 font-semibold"
                    @click="saveProgress()"
                >
                    Simpan Progress
                </button>
            </div>

        </div>
    </x-modal>
@endsection

@push('js')
    <script>
        function kdsModal() {
            return {
                payload: null,

                init() {
                    window.addEventListener('open-modal', (e) => {
                        if (e.detail.id === 'modal-kds-detail') {
                            this.payload = JSON.parse(JSON.stringify(e.detail.payload));
                        }
                    });
                },

                markDone(item) {
                    const sisa = item.qty - item.done_qty - item.void_qty;
                    if (sisa <= 0) return;
                    item.done_qty++;
                },

                undoDone(item) {
                    if (item.done_qty <= 0) return;
                    item.done_qty--;
                },

                voidOne(item) {
                    const sisa = item.qty - item.done_qty - item.void_qty;
                    if (sisa <= 0) return;
                    item.void_qty++;
                },

                undoVoid(item) {
                    if (item.void_qty <= 0) return;
                    item.void_qty--;
                },

                saveProgress() {
                    fetch('{{ route('transaction.kitchen-display.update-items') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            order_id: this.payload.order_id,
                            items: this.payload.items.map(i => ({
                                id: i.id,
                                done_qty: i.done_qty,
                                void_qty: i.void_qty
                            }))
                        })
                    })
                        .then(res => res.json())
                        .then(res => {
                            if (!res.success) {
                                alert(res.message || 'Gagal simpan');
                                return;
                            }
                            location.reload(); // atau update UI manual
                        });
                }
            }
        }
    </script>
    <script>
        function kdsBoard(initialOrders) {
            return {
                orders: initialOrders.map(o => ({
                    ...o,
                    minutes: Math.floor((Date.now() - new Date(o.opened_at)) / 60000),
                    channel_label: o.channel_display_name ?? o.channel
                })),

                init() {
                    console.log('KDS Board init, listening reverb...');

                    Echo.channel('kds')
                        .listen('.order.created', (e) => {
                            console.log('🔥 Order baru dari Reverb:', e.order);

                            const order = {
                                ...e.order,
                                minutes: 0,
                                channel_label: e.order.channel_display_name ?? e.order.channel
                            };

                            // Masukin ke paling atas
                            this.orders.unshift(order);
                        });

                    // Update timer tiap menit
                    setInterval(() => {
                        this.orders.forEach(o => {
                            o.minutes++;
                        });
                    }, 60000);
                }
            }
        }
    </script>
@endpush
