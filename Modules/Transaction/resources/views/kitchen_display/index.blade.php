@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'kitchen-display',
    'activeSubmenu' => 'kitchen-display',
])
@section('title', 'Kitchen Display')

@section('content')
    <div x-data="formFilter()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800">Kitchen Display</h2>
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

        <div
            x-data="kdsBoard({{ $orders->toJson() }}, {{ active_outlet_id() }})"
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
                                <span x-text="order.type"></span>
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

                        <!-- PROGRESS -->
                        <div class="pt-3">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span x-text="progressLabel(order)"></span>
                            </div>

                            <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full transition-all duration-300"
                                    :class="progressPercent(order) === 100
            ? 'bg-green-500'
            : progressPercent(order) > 50
                ? 'bg-orange-500'
                : 'bg-red-400'"
                                    :style="`width: ${progressPercent(order)}%`"
                                ></div>
                            </div>
                        </div>

                    </div>

                </div>
            </template>
        </div>

        <x-modal id="modal-kds-detail" title="Proses Pesanan" icon="fa-kitchen-set" size="xl">
            <div x-data="kdsModal()" class="p-6 space-y-6">

                <!-- HEADER -->
                <div class="flex items-center justify-between">

                    <!-- LEFT -->
                    <div class="space-y-1">
                        <h3 class="font-bold text-xl flex items-center gap-2">
                            <span>Antrian:</span>
                            <span class="text-orange-600" x-text="payload?.code"></span>
                        </h3>

                        <!-- MEJA -->
                        <p
                            x-show="payload?.table_number"
                            class="text-sm text-gray-500 font-medium flex items-center gap-1"
                        >
                            <i class="fa fa-table-cells"></i>
                            <span x-text="'Meja ' + payload?.table_number"></span>
                        </p>
                    </div>

                    <!-- RIGHT -->
                    <p class="text-sm text-gray-500 font-semibold">
                        <span x-text="payload?.items.filter(i=>i.checked).length"></span>
                        /
                        <span x-text="payload?.items.length"></span>
                        selesai
                    </p>

                </div>

                <!-- ITEM LIST -->
                <div class="space-y-3 max-h-[60vh] overflow-y-auto">

                    <template x-for="[category, items] in grouped" :key="category">

                        <div class="space-y-3">

                            <!-- CATEGORY HEADER -->
                            <div class="sticky top-0 bg-gray-50 px-3 py-1 rounded text-xs font-semibold text-gray-600 uppercase">
                                <span x-text="category"></span>
                            </div>

                            <!-- ITEMS -->
                            <template x-for="item in items" :key="item.id">

                                <div
                                    class="flex items-center justify-between border rounded-xl p-4 bg-white shadow-sm hover:bg-orange-50 transition"
                                    :class="item.checked ? 'opacity-60' : ''"
                                >

                                    <!-- LEFT -->
                                    <div class="flex-1 space-y-1">

                                        <div class="flex items-center gap-2">

                                            <p
                                                class="font-semibold text-lg leading-tight"
                                                :class="item.checked ? 'line-through text-gray-400' : 'text-gray-800'"
                                                x-text="item.name_snapshot"
                                            ></p>

                                            <span
                                                class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700 font-semibold"
                                            >
                                x<span x-text="item.qty"></span>
                            </span>

                                        </div>

                                        <div
                                            x-show="item.note && item.note.trim() !== ''"
                                            class="text-xs italic text-orange-600 bg-orange-50 px-2 py-1 rounded w-fit"
                                        >
                                            <i class="fa fa-note-sticky mr-1"></i>
                                            <span x-text="item.note"></span>
                                        </div>

                                    </div>

                                    <!-- CHECKLIST -->
                                    <div class="ml-4 flex items-center">

                                        <label class="cursor-pointer">

                                            <input
                                                type="checkbox"
                                                x-model="item.checked"
                                                :disabled="item.done_qty >= item.qty"
                                                class="hidden"
                                            >

                                            <div
                                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center transition"
                                                :class="item.checked
                                    ? 'bg-orange-600 border-orange-600 text-white'
                                    : 'border-gray-300 hover:border-orange-500'"
                                            >
                                                <i class="fa fa-check text-sm" x-show="item.checked"></i>
                                            </div>

                                        </label>

                                    </div>

                                </div>

                            </template>

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

        @include('components.virtual-keyboard')
    </div>
@endsection

@push('js')
    <script>
        const outletId = {{ active_outlet_id() }};
    </script>
    <script>
        function kdsModal() {
            return {
                payload: null,
                grouped: [],

                init() {
                    window.addEventListener('open-modal', (e) => {

                        if (e.detail.id === 'modal-kds-detail') {

                            this.payload = JSON.parse(JSON.stringify(e.detail.payload));

                            console.log(this.payload);

                            // default checklist
                            this.payload.items.forEach(i => {
                                i.checked = (i.done_qty ?? 0) >= i.qty;
                            });

                            // 🔥 generate grouped items
                            this.grouped = this.groupItemsByCategory(this.payload.items);

                        }

                    });
                },

                groupItemsByCategory(items = []) {

                    const groups = {};

                    items.forEach(i => {
                        const cat = (i.menu.category ?? 'lainnya')
                            .toLowerCase()
                            .trim();

                        if (!groups[cat]) groups[cat] = [];
                        groups[cat].push(i);

                    });

                    return Object.entries(groups).sort(([a], [b]) => {

                        const aMinuman = a === 'minuman';
                        const bMinuman = b === 'minuman';

                        const aJede = a.includes('jede');
                        const bJede = b.includes('jede');

                        if (aMinuman) return -1;
                        if (bMinuman) return 1;

                        if (aJede) return 1;
                        if (bJede) return -1;

                        return a.localeCompare(b);

                    });
                },

                saveProgress() {
                    const checkedItems = this.payload.items
                        .filter(i => i.checked)
                        .map(i => ({
                            id: i.id
                        }));

                    fetch('{{ route('transaction.kitchen-display.update-items') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            order_id: this.payload.id,
                            items: checkedItems
                        })
                    })
                        .then(async res => {

                            const text = await res.text(); // ambil raw response dulu

                            console.log('HTTP Status:', res.status);
                            console.log('RAW Response:', text);

                            try {
                                const json = JSON.parse(text);
                                return json;
                            } catch (e) {
                                console.error('Response bukan JSON');
                                throw e;
                            }

                        })
                        .then(res => {

                            console.log('Parsed JSON:', res);

                            if (!res.success) {
                                alert(res.message || 'Gagal simpan');
                                return;
                            }

                            location.reload();

                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            alert('Terjadi error di request');
                        });

                }
            }
        }

        function kdsBoard(initialOrders, outletId) {
            return {

                orders: initialOrders.map(o => ({
                    ...o,
                    minutes: Math.floor((Date.now() - new Date(o.opened_at)) / 60000),
                    channel_label: o.channel_display_name ?? o.type
                })),

                init() {
                    console.log('KDS init outlet:', outletId);

                    window.Echo.connector.pusher.connection.bind('connected', () => {
                        console.log('✅ Reverb connected');
                    });

                    Echo.channel(`kds.${outletId}`)
                        .subscribed(() => console.log('SUBSCRIBED KDS', outletId))
                        .listen('.order.created', (e) => {

                            console.log('🔥 Order baru:', e.order);

                            const order = {
                                ...e.order,
                                minutes: 0,
                                channel_label: e.order.channel_display_name ?? e.order.type
                            };

                            this.orders.unshift(order);
                        });

                    setInterval(() => {
                        this.orders.forEach(o => o.minutes++);
                    }, 60000);
                },

                progressPercent(order) {
                    const total = order.items.reduce((t, i) => t + (i.qty || 0), 0);
                    const done  = order.items.reduce((t, i) => t + (i.done_qty || 0), 0);
                    if (!total) return 0;
                    return Math.min(100, Math.round((done / total) * 100));
                },

                progressLabel(order) {
                    const total = order.items.reduce((t, i) => t + (i.qty || 0), 0);
                    const done  = order.items.reduce((t, i) => t + (i.done_qty || 0), 0);
                    return `${done}/${total}`;
                }
            }
        }

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
                },
            }
        }
    </script>
@endpush
