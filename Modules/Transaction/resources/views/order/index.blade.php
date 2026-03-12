@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'order',
    'activeSubmenu' => 'order',
])
@section('title', 'Order')

@section('content')
    <div
        x-data="orderCart()"
        @add-to-cart.window="addMenu($event.detail)"
    >
        <div class="grid grid-cols-12 gap-4">
            {{-- LEFT : MENU LIST --}}
            <div x-data="menuSearch({{ $menus->toJson() }})" class="col-span-8 space-y-4">

                {{-- SEARCH & CATEGORY --}}
                <div class="bg-white rounded-xl border p-4 flex items-center gap-4">
                    <input
                        type="text"
                        placeholder="Cari menu / SKU…"
                        x-model="search"
                        @focus="setActiveInput($event.target)"
                        @keydown.enter.prevent="enterAdd()"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                    >

                    <select
                        x-model="category"
                        @change="search=''"
                        class="border rounded-lg px-3 py-2"
                    >
                        <option value="">Semua</option>

                        @foreach($categories as $category)
                            <option value="{{ $category }}">
                                {{ strtoupper($category) }}
                            </option>
                        @endforeach
                    </select>

                    <button
                        type="button"
                        @click="keyboardOpen = !keyboardOpen"
                        class="px-3 py-2 border rounded-lg hover:bg-orange-100 transition"
                        title="Toggle Keyboard"
                    >
                        <i class="fa fa-keyboard"></i>
                    </button>
                </div>

                {{-- MENU GRID --}}
                <div class="max-h-[75vh] overflow-y-auto pr-2 scroll-smooth">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <template x-for="menu in filteredMenus" :key="menu.id">
                            <button
                                type="button"
                                @click="addMenu(menu)"
                                class="relative group border rounded-lg overflow-hidden hover:bg-orange-100 bg-white transition text-left"
                            >
                                <!-- IMAGE -->
                                <div class="h-28 bg-gray-100 flex items-center justify-center">
                                    <img
                                        :src="menu.image_url || '/images/placeholder.png'"
                                        class="object-cover w-full h-full"
                                    >
                                </div>

                                <!-- CONTENT -->
                                <div class="p-3 space-y-1">
                                    <p class="text-[11px] text-gray-400 uppercase tracking-wide"
                                       x-text="menu.sku"></p>

                                    <p class="font-semibold text-gray-800 leading-tight line-clamp-2 min-h-[2.5rem]"
                                       x-text="menu.name"></p>

                                    <p class="text-sm font-bold text-orange-600"
                                       x-text="formatRp(menu.sell_price)"></p>
                                </div>

                                <div class="absolute bottom-3 right-3 w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center opacity-0 scale-75 group-hover:opacity-100 group-hover:scale-100 transition-all duration-200">
                                    <i class="fa fa-plus text-xs"></i>
                                </div>
                            </button>
                        </template>

                        <!-- EMPTY STATE -->
                        <div
                            x-show="filteredMenus.length === 0"
                            class="col-span-full text-center py-10 text-gray-400"
                        >
                            Menu tidak ditemukan
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT : CART --}}
           <div class="col-span-4">

                <div class="bg-white rounded-xl border h-full flex flex-col">

                    {{-- CART HEADER --}}
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <h3 class="font-semibold">Keranjang</h3>
                        <span class="text-xs text-gray-500"
                              x-text="`${cart.length} item`">
                        </span>
                    </div>

                    {{-- CART ITEMS --}}
                    <div class="flex-1 overflow-y-auto divide-y">
                        {{-- ITEM --}}
                        <div class="p-4 space-y-3 bg-white">

                            <!-- EMPTY STATE -->
                            <template x-if="cart.length === 0">
                                <div class="py-12 text-center text-gray-400 space-y-2">
                                    <i class="fa fa-cart-shopping text-3xl"></i>
                                    <p class="text-sm">Keranjang masih kosong</p>
                                    <p class="text-xs">Klik menu atau scan barcode</p>
                                </div>
                            </template>

                            <!-- CART ITEMS -->
                            <div class="max-h-[52vh] overflow-y-auto pr-2 scroll-smooth">
                                <template x-for="(item, index) in cart" :key="item.menu_id">
                                    <div
                                        class="p-3 rounded-lg border hover:bg-orange-50 transition space-y-2"
                                    >

                                        <!-- ROW ATAS: NAMA & HARGA -->
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="font-semibold text-gray-800" x-text="item.name"></p>
                                                <p class="text-xs text-gray-500"
                                                   x-text="formatRp(item.price)">
                                                </p>
                                            </div>

                                            <!-- HAPUS -->
                                            <button
                                                type="button"
                                                @click="removeItem(index)"
                                                class="w-8 h-8 rounded-full flex items-center justify-center text-red-500 bg-red-100 hover:bg-red-500 hover:text-white transition"
                                            >
                                                <i class="fa fa-trash text-xs"></i>
                                            </button>
                                        </div>

                                        <!-- ROW BAWAH: QTY & SUBTOTAL -->
                                        <div class="flex items-center justify-between">
                                            <!-- QTY CONTROL -->
                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    @click="updateQty(index, item.qty - 1)"
                                                    class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-orange-500 hover:text-white transition"
                                                >
                                                    −
                                                </button>

                                                <span class="w-8 text-center font-semibold"
                                                      x-text="item.qty"></span>

                                                <button
                                                    type="button"
                                                    @click="updateQty(index, item.qty + 1)"
                                                    class="w-8 h-8 rounded-full border flex items-center justify-center hover:bg-orange-500 hover:text-white transition"
                                                >
                                                    +
                                                </button>
                                            </div>

                                            <!-- SUBTOTAL -->
                                            <div class="text-right font-bold text-orange-600"
                                                 x-text="formatRp(item.subtotal)">
                                            </div>
                                        </div>

                                        <div class="mt-1">
                                            <input
                                                type="text"
                                                placeholder="Catatan (opsional)"
                                                @focus="setActiveInput($event.target)"
                                                x-model="item.note"
                                                class="w-full text-xs border rounded-md px-2 py-1 focus:ring-1 focus:ring-orange-400 focus:outline-none"
                                            >
                                        </div>

                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- CART FOOTER --}}
                    <div class="border-t p-4 space-y-3">

                        <div class="flex justify-between">
                            <span>Sub Total</span>
                            <span x-text="formatRp(subTotal)"></span>
                        </div>

                        <template x-for="tax in taxes" :key="tax.id">
                            <div
                                x-show="tax.is_active"
                                class="flex justify-between text-sm text-gray-600"
                            >
                                <!-- LABEL -->
                                <span>
                                    <span x-text="tax.name"></span>
                                    <template x-if="tax.calculation_type === 'percent'">
                                        <span x-text="` (${tax.value}%)`"></span>
                                    </template>
                                </span>

                                <!-- NOMINAL HASIL -->
                                <span x-text="formatRp(calcTaxAmount(tax))"></span>
                            </div>
                        </template>

{{--                        <div class="flex justify-between text-sm">--}}
{{--                            <span>Total Pajak</span>--}}
{{--                            <span x-text="formatRp(taxTotal)"></span>--}}
{{--                        </div>--}}

                        <div class="flex justify-between text-sm">
                            <span>Pembulatan</span>
                            <span x-text="formatRp(rounding())"></span>
                        </div>

                        <div class="flex justify-between font-bold text-lg text-orange-600">
                            <span>Total</span>
                            <span x-text="formatRp(finalTotal())"></span>
                        </div>

                        <button
                            class="w-full bg-orange-600 text-white py-2 rounded-lg hover:bg-orange-500"
                            @click="openPayment()"
                        >
                            Bayar
                        </button>
                    </div>
                </div>
           </div>

        </div>

        <!-- MODAL ORDER META -->
        <div
            x-show="orderMetaOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center"
        >
            <div class="absolute inset-0 bg-black/60"></div>

            <div class="relative bg-white w-full max-w-md rounded-xl shadow-xl p-6 mt-10 space-y-5">

                <h2 class="text-lg font-semibold">Informasi Order</h2>

                <!-- TIPE PESANAN -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Tipe Pesanan
                    </label>
                    <select x-model="orderType" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Pilih Tipe</option>
                        @foreach(config('array.order.type') as $key => $type)
                            <option value="{{ $key }}">{{ $type['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- JENIS ORDER -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Jenis Order
                    </label>
                    <select x-model="orderChannel" class="w-full border rounded-lg px-3 py-2">
                        <option value="">Pilih Jenis</option>
                        @foreach(config('array.order.channel') as $key => $channel)
                            <option value="{{ $key }}">{{ $channel['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- NOMOR MEJA -->
                <div x-show="orderType === 'dine_in'">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Nomor Pager
                    </label>
                    <input
                        type="text"
                        @focus="setActiveInput($event.target)"
                        x-model="tableNumber"
                        placeholder="Contoh: A1 / 5"
                        class="w-full border rounded-lg px-3 py-2"
                    >
                </div>

                <div class="flex gap-3">
                    <button
                        class="flex-1 border py-2 rounded-lg"
                        @click="orderMetaOpen = false"
                    >
                        Batal
                    </button>

                    <button
                        class="flex-1 bg-orange-600 text-white py-2 rounded-lg"
                        @click="confirmOrderMeta()"
                    >
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL PAYMENT -->
        <div
            x-show="paymentOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center"
        >
            <div class="absolute inset-0 bg-black/60" @click="closePayment()"></div>

            <div class="relative bg-white w-full max-w-4xl rounded-xl mt-10 shadow-xl">

                <!-- CONTENT -->
                <div class="grid grid-cols-2 gap-6 py-4">

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
                                        Pager <span x-text="tableNumber"></span>
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
                                    class="px-3 py-1 rounded-lg text-xs font-semibold bg-green-100 text-green-700"
                                >
                                    ORDER AKTIF
                                </span>

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

                            <template x-for="tax in taxes" :key="tax.id">
                                <div
                                    x-show="tax.is_active"
                                    class="flex justify-between text-sm text-gray-600"
                                >
                                    <!-- LABEL -->
                                    <span>
                                    <span x-text="tax.name"></span>
                                    <template x-if="tax.calculation_type === 'percent'">
                                        <span x-text="` (${tax.value}%)`"></span>
                                    </template>
                                </span>

                                    <!-- NOMINAL HASIL -->
                                    <span x-text="formatRp(calcTaxAmount(tax))"></span>
                                </div>
                            </template>

                            {{--                        <div class="flex justify-between text-sm">--}}
                            {{--                            <span>Total Pajak</span>--}}
                            {{--                            <span x-text="formatRp(taxTotal)"></span>--}}
                            {{--                        </div>--}}

                            <div class="flex justify-between text-sm">
                                <span>Pembulatan</span>
                                <span x-text="formatRp(rounding())"></span>
                            </div>

                            <div class="flex justify-between font-bold text-xl pt-2">
                                <span>Total</span>
                                <span class="text-orange-600"
                                      x-text="formatRp(finalTotal())">
                        </span>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT : PAYMENT -->
                    <div class="space-y-6 pr-6">
                        <div class="flex border-gray-200 bg-white border border-black rounded-xl p-2">
                            <button
                                @click="paymentType = 'PAY'"
                                :class="paymentType === 'PAY'
                                            ? 'bg-orange-600 text-white'
                                            : 'border-transparent text-gray-500 hover:text-orange-600'"
                                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                                Bayar
                            </button>
                            <button
                                @click="paymentType = 'DRAFT'"
                                :class="paymentType === 'DRAFT'
                                            ? 'bg-orange-600 text-white'
                                            : 'border-transparent text-gray-500 hover:text-orange-600'"
                                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                                Open Bill
                            </button>
                        </div>

                        <!-- DRAFT INPUT -->
                        <div x-show="paymentType==='DRAFT'" class="space-y-4">

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Nama Customer</label>
                                <input
                                    type="text"
                                    @focus="setActiveInput($event.target)"
                                    x-model="customerName"
                                    placeholder="Opsional"
                                   class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">No. HP</label>
                                <input
                                    type="text"
                                    @focus="setActiveInput($event.target)"
                                    x-model="customerPhone"
                                    placeholder="Opsional"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Catatan Order</label>
                                <textarea
                                    x-model="draftNote"
                                    @focus="setActiveInput($event.target)"
                                    rows="3"
                                    placeholder="Contoh: bayar nanti, reservasi jam 7"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                ></textarea>
                            </div>

                        </div>

                        <div x-show="paymentType==='PAY'" class="space-y-2">
                            <!-- PAYMENT MODE -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mode Pembayaran</label>

                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="setPaymentMode('FULL')"
                                        :class="paymentMode==='FULL' ? 'bg-orange-600 text-white' : 'border hover:bg-orange-100'"
                                        class="rounded-md py-2 text-sm font-semibold"
                                    >
                                        Bayar Lunas
                                    </button>

                                    <button
                                        type="button"
                                        @click="setPaymentMode('DP')"
                                        :class="paymentMode==='DP' ? 'bg-orange-600 text-white' : 'border hover:bg-orange-100'"
                                        class="rounded-md py-2 text-sm font-semibold"
                                    >
                                        DP
                                    </button>
                                </div>
                            </div>

                            <!-- PAYMENT METHOD -->
                            <div class="space-y-3">
                                <label class="text-sm font-medium">Metode Pembayaran</label>

                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        type="button"
                                        @click="paymentMethod='CASH'"
                                        :class="paymentMethod==='CASH'
                                    ? 'bg-orange-600 text-white'
                                    : 'border hover:bg-orange-100'"
                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"
                                    >
                                        <i class="fa fa-money-bill text-lg"></i>
                                        <span class="text-xs">Cash</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="paymentMethod='CARD'"
                                        :class="paymentMethod==='CARD'
                                    ? 'bg-orange-600 text-white'
                                    : 'border hover:bg-orange-100'"
                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"
                                    >
                                        <i class="fa fa-credit-card text-lg"></i>
                                        <span class="text-xs">Kartu</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="paymentMethod='QRIS'"
                                        :class="paymentMethod==='QRIS'
                                    ? 'bg-orange-600 text-white'
                                    : 'border hover:bg-orange-100'"
                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"
                                    >
                                        <i class="fa fa-qrcode text-lg"></i>
                                        <span class="text-xs">QRIS</span>
                                    </button>
                                </div>
                            </div>

                            <!-- CASH INPUT -->
                            <div x-show="paymentMethod==='CASH'" class="space-y-4">

                                <div class="space-y-2">
                                    <label class="text-sm font-medium">
                                        <template x-if="paymentMode==='FULL'">
                                            <span>Uang Diterima</span>
                                        </template>

                                        <template x-if="paymentMode==='DP'">
                                            <span>Nominal DP</span>
                                        </template>
                                    </label>
                                    <input
                                        type="number"
                                        @focus="setActiveInput($event.target)"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                        x-model.number="payAmount"
                                    >
                                </div>

                                <!-- QUICK CASH -->
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="n in [50000,100000,150000,200000]" :key="n">
                                        <button
                                            type="button"
                                            @click="payAmount=n"
                                            class="border rounded-md py-2 hover:bg-orange-100"
                                            x-text="formatRp(n)"
                                        ></button>
                                    </template>
                                </div>

                                <button
                                    type="button"
                                    @click="payAmount=grandTotal"
                                    class="w-full border rounded-md py-2 hover:bg-orange-100"
                                >
                                    Uang Pas
                                </button>

                                <!-- CHANGE -->
                                <div class="bg-gray-100 rounded-lg p-4 text-center">
                                    <p class="text-sm text-gray-500">Kembalian</p>
                                    <p class="text-2xl font-bold"
                                       :class="change < 0 ? 'text-red-500' : 'text-green-600'"
                                       x-text="formatRp(change)">
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ACTION PAY -->
                        <button
                            x-show="paymentType==='PAY'"
                            class="w-full h-14 text-lg bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center justify-center gap-2"
                            @click="processPayment()"
                        >
                            <i class="fa fa-print"></i>
                            <span x-text="paymentMode==='DP' ? 'Bayar DP' : 'Bayar & Cetak Struk'"></span>
                        </button>

                        <!-- ACTION DRAFT -->
                        <button
                            x-show="paymentType==='DRAFT'"
                            class="w-full h-14 text-lg bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center justify-center gap-2"
                            @click="processPayment()"
                        >
                            <i class="fa fa-save"></i>
                            Simpan Draft
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL KEYBOARD -->
        <div
            x-show="keyboardOpen"
            x-transition
            class="fixed bottom-4 z-50
           bg-white/95 backdrop-blur
           rounded-xl border shadow-2xl
           w-[900px] max-w-[95vw]"
        >

            <!-- HEADER -->
            <div class="flex items-center justify-between px-4 py-2 border-b">
        <span class="text-sm font-semibold text-gray-600">
            Virtual Keyboard
        </span>

                <button
                    @click="keyboardOpen = false"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-200 transition"
                >
                    <i class="fa fa-times text-gray-600"></i>
                </button>
            </div>

            <!-- BODY -->
            <div class="p-4 space-y-3 text-center">

                <!-- ROW 1 -->
                <div class="grid grid-cols-10 gap-2">
                    <template x-for="key in ['1','2','3','4','5','6','7','8','9','0']">
                        <button
                            @click="pressKey(key)"
                            class="key-btn w-full"
                            x-text="key"
                        ></button>
                    </template>
                </div>

                <!-- ROW 2 -->
                <div class="grid grid-cols-10 gap-2">
                    <template x-for="key in ['Q','W','E','R','T','Y','U','I','O','P']">
                        <button
                            @click="pressKey(key)"
                            class="key-btn w-full"
                            x-text="key"
                        ></button>
                    </template>
                </div>

                <!-- ROW 3 -->
                <div class="grid grid-cols-11 gap-2 justify-center">
                    <div></div>

                    <template x-for="key in ['A','S','D','F','G','H','J','K','L']">
                        <button
                            @click="pressKey(key)"
                            class="key-btn w-full"
                            x-text="key"
                        ></button>
                    </template>

                    <div></div>
                </div>

                <!-- ROW 4 -->
                <div class="grid grid-cols-11 gap-2 justify-center">
                    <div></div>
                    <div></div>

                    <template x-for="key in ['Z','X','C','V','B','N','M']">
                        <button
                            @click="pressKey(key)"
                            class="key-btn w-full"
                            x-text="key"
                        ></button>
                    </template>

                    <div></div>
                    <div></div>
                </div>

                <!-- ROW 5 -->
                <div class="grid grid-cols-10 gap-2">
                    <button @click="clearSearch()" class="key-btn col-span-2">CLR</button>
                    <button @click="pressKey(' ')" class="key-btn col-span-6">Space</button>
                    <button @click="backspace()" class="key-btn col-span-2">⌫</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .key-btn {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 18px;
            font-weight: 600;
            background: white;
        }
        .key-btn:hover {
            background: #fff7ed;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.js"></script>

    <script>
        window.outletName = @json($outlet->name);
        window.outletAddress = @json($outlet->address);
        window.cashierName = @json(auth()->user()->name);
        window.cashierPrinters = @json($printers->pluck('device_name'));
        window.orderConfig = @json(config('array.order'));
        window.taxRules = @json($taxes);

        function orderCart() {
            return {
                cart: [],
                subTotal: 0,
                adjustmentTotal: 0,
                grandTotal: 0,
                taxes: window.taxRules || [],
                taxTotal: 0,

                orderType: '',
                orderChannel: '',
                tableNumber: '',

                paymentOpen: false,
                paymentMethod: 'CASH',
                payAmount: 0,

                paymentType: 'PAY', // PAY | DRAFT
                draftNote: '',
                customerName: '',
                customerPhone: '',

                paymentMode: 'FULL', // FULL | DP
                paymentMethod: 'CASH',
                payAmount: 0,
                remainingAmount: 0,

                orderMetaOpen: false,
                keyboardOpen: false,
                activeInput: null,

                // =====================
                // ADD MENU
                // =====================
                addMenu(menu) {
                    const existing = this.cart.find(i => i.menu_id === menu.id);

                    if (existing) {
                        existing.qty++;
                        existing.subtotal = existing.qty * existing.price;
                    } else {
                        this.cart.push({
                            menu_id: menu.id,
                            name: menu.name,
                            price: Number(menu.sell_price),
                            qty: 1,
                            subtotal: Number(menu.sell_price),
                            note: '',
                        });
                    }

                    this.recalculate();
                },

                // =====================
                // UPDATE QTY
                // =====================
                updateQty(index, qty) {
                    if (qty <= 0) {
                        this.removeItem(index);
                        return;
                    }

                    this.cart[index].qty = qty;
                    this.cart[index].subtotal =
                        this.cart[index].qty * this.cart[index].price;

                    this.recalculate();
                },

                // =====================
                // REMOVE ITEM
                // =====================
                removeItem(index) {
                    this.cart.splice(index, 1);
                    this.recalculate();
                },

                // =====================
                // RECALCULATE TOTAL
                // =====================
                recalculate() {
                    // =====================
                    // SUBTOTAL
                    // =====================
                    this.subTotal = this.cart.reduce(
                        (sum, item) => sum + Number(item.subtotal || 0),
                        0
                    );

                    // =====================
                    // TAX (AKUMULASI, BUKAN DITAMPILIN TOTALNYA)
                    // =====================
                    this.taxTotal = this.taxes.reduce((sum, tax) => {
                        if (!tax.is_active) return sum;

                        // percent
                        if (tax.calculation_type === 'percent') {
                            return sum + (this.subTotal * Number(tax.value) / 100);
                        }

                        // fixed
                        if (tax.calculation_type === 'fixed') {
                            return sum + Number(tax.value);
                        }

                        return sum;
                    }, 0);

                    // =====================
                    // GRAND TOTAL
                    // =====================
                    this.grandTotal =
                        this.subTotal +
                        this.taxTotal +
                        this.adjustmentTotal;
                },

                calcTaxAmount(tax) {
                    if (!tax.is_active) return 0;

                    if (tax.calculation_type === 'percent') {
                        return this.subTotal * Number(tax.value) / 100;
                    }

                    if (tax.calculation_type === 'fixed') {
                        return Number(tax.value);
                    }

                    return 0;
                },

                rounding() {
                    const lastTwo = this.grandTotal % 100;
                    return lastTwo < 50 ? -lastTwo : (100 - lastTwo);
                },

                finalTotal() {
                    return this.grandTotal + this.rounding();
                },

                // 🔁 OPEN PAYMENT
                openPayment() {
                    if (this.cart.length === 0) {
                        alert('Keranjang masih kosong');
                        return;
                    }

                    // jangan langsung validasi
                    this.orderMetaOpen = true;
                },

                // 🔁 CLOSE PAYMENT
                closePayment() {
                    this.paymentOpen = false;
                },

                // 💰 KEMBALIAN
                get change() {
                    if (this.paymentMode === 'DP') return 0;
                    return Math.max(0, this.payAmount - this.finalTotal());
                },

                setPaymentMode(mode) {
                    this.paymentMode = mode;

                    if (mode === 'FULL') {
                        this.payAmount = this.finalTotal();
                    }

                    if (mode === 'DP') {
                        this.payAmount = 0;
                    }
                },

                orderTypeLabel() {
                    return window.orderConfig?.type?.[this.orderType]?.display_name ?? this.orderType
                },

                orderChannelLabel() {
                    return window.orderConfig?.channel?.[this.orderChannel]?.display_name ?? this.orderChannel
                },

                // =====================
                // 🔥 PROCESS PAYMENT + PRINT
                // =====================
                async processPayment() {
                    let data = JSON.stringify({
                        // ===== ORDER META =====
                        type: this.orderType,          // dine_in | take_away | delivery
                        channel: this.orderChannel,    // dine_in_regular | booking_event | dll
                        table_number: this.tableNumber || null,

                        // ===== ITEMS =====
                        items: this.cart.map(i => ({
                            menu_id: i.menu_id,
                            qty: i.qty,
                            note: i.note || null
                        })),

                        // ===== PAYMENT FLOW =====
                        payment_type: this.paymentType, // PAY | DRAFT

                        payment: this.paymentType === 'PAY'
                            ? {
                                mode: this.paymentMode,     // FULL | DP
                                method: this.paymentMethod, // CASH | CARD | QRIS
                                amount: this.payAmount
                            }
                            : null,

                        // ===== OPTIONAL =====
                        customer_name: this.customerName || null,
                        customer_phone: this.customerPhone || null,
                        note: this.paymentType === 'DRAFT'
                            ? this.draftNote || null
                            : null
                    });

                    fetch('{{ route('transaction.order.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: data
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            alert(res.message);
                            return;
                        }

                        const items = this.cart.map(item => ({
                            name: item.name,
                            qty: item.qty,
                            price: item.price,
                            subtotal: item.subtotal
                        }));

                        const order = {
                            code: this.orderCode || '-',
                            date: new Date().toLocaleString('id-ID'),
                            table: this.tableNumber || null,
                            sub_total: this.subTotal,
                            adjustment_total: this.adjustmentTotal,
                            grand_total: this.grandTotal
                        };

                        const payment = {
                            method: this.paymentMethod,
                            paid: this.payAmount,
                            change: this.change
                        };

                        const receipt = buildReceipt({
                            outlet: {
                                name: window.outletName,
                                address: window.outletAddress || ''
                            },
                            cashier: window.cashierName,
                            order,
                            items,
                            payment
                        });

                        // kirim ke QZ / printer
                        // printReceipt(receipt);

                        // 🔄 RESET
                        // this.resetOrder();
                        // this.paymentOpen = false;
                    })
                    .catch(() => {
                        alert('Gagal memproses order');
                    })
                    .finally(() => {
                        // 🔥 SELALU RESET
                        this.resetOrder();
                        this.paymentOpen = false;
                    });
                },

                // =====================
                // RESET ORDER
                // =====================
                resetOrder() {
                    this.orderType = '';
                    this.orderChannel = '';
                    this.tableNumber = '';
                    this.cart = [];
                    this.subTotal = 0;
                    this.adjustmentTotal = 0;
                    this.grandTotal = 0;
                    this.paymentOpen = false;
                    this.payAmount = 0;
                },

                saveOrderMeta() {
                    if (!this.orderType) {
                        alert('Tipe Pesanan wajib dipilih');
                        return;
                    }

                    if (!this.orderChannel) {
                        alert('Jenis Order wajib dipilih');
                        return;
                    }

                    if (this.orderType === 'dine_in' && !this.tableNumber) {
                        alert('Nomor pager wajib diisi untuk Dine In');
                        return;
                    }

                    this.orderMetaOpen = false;
                },

                confirmOrderMeta() {
                    if (!this.orderType) {
                        alert('Tipe Pesanan wajib dipilih');
                        return;
                    }

                    if (!this.orderChannel) {
                        alert('Jenis Order wajib dipilih');
                        return;
                    }

                    if (this.orderType === 'dine_in' && !this.tableNumber) {
                        alert('Nomor pager wajib diisi untuk Dine In');
                        return;
                    }

                    this.orderMetaOpen = false;

                    // baru buka payment
                    this.payAmount = this.finalTotal();
                    this.paymentMethod = 'CASH';
                    this.paymentOpen = true;
                },

                setActiveInput(el) {
                    this.activeInput = el
                    this.keyboardOpen = true
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

                // =====================
                // FORMAT RUPIAH
                // =====================
                formatRp(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(n || 0);
                }
            }
        }

        function menuSearch(menus) {
            return {
                search: '',
                category: '',
                menus,

                get filteredMenus() {

                    let result = this.menus;

                    // filter category
                    if (this.category) {
                        result = result.filter(m => m.category === this.category);
                    }

                    // filter search
                    if (this.search) {
                        const q = this.search.toLowerCase();

                        result = result.filter(m =>
                            m.name.toLowerCase().includes(q) ||
                            (m.sku && m.sku.toLowerCase().includes(q)) ||
                            (m.barcode && m.barcode === this.search)
                        );
                    }

                    return result;
                },

                enterAdd() {
                    // 1. Prioritas barcode exact
                    const byBarcode = this.menus.find(
                        m => m.barcode && m.barcode === this.search
                    );

                    if (byBarcode && byBarcode.stock <= 0) {
                        alert('Stok habis');
                        this.search = '';
                        return;
                    }

                    if (byBarcode) {
                        this.$dispatch('add-to-cart', byBarcode);
                        this.search = '';
                        return;
                    }

                    // 2. Kalau hasil filter cuma 1 → add
                    if (this.filteredMenus.length === 1) {
                        this.$dispatch('add-to-cart', this.filteredMenus[0]);
                        this.search = '';
                    }

                    this.$nextTick(() => {
                        document.querySelector('input[x-model="search"]')?.focus();
                    });
                },

                addMenu(menu) {
                    this.$dispatch('add-to-cart', menu);
                },

                formatRp(val) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(val);
                }
            }
        }

        function buildReceipt({ outlet, cashier, order, items, payment }) {
            const line = '--------------------------------\n';

            let text = '';

            // INIT
            text += '\x1B\x40';
            text += '\x1B\x61\x01'; // CENTER

            // HEADER TOKO
            text += `${outlet.name}\n`;
            if (outlet.address) text += `${outlet.address}\n`;
            text += '\n';

            // META ORDER
            text += '\x1B\x61\x00'; // LEFT
            text += `Order  : ${order.code}\n`;
            text += `Kasir  : ${cashier}\n`;
            text += `Tanggal: ${order.date}\n`;
            if (order.table) text += `Pager   : ${order.table}\n`;
            text += line;

            // =====================
            // LIST ITEM
            // =====================
            items.forEach(item => {
                // Nama menu
                text += `${item.name}\n`;

                // Qty x Harga (kiri) + Subtotal (kanan)
                const left  = `  ${item.qty} @ ${formatRp(item.price)}`;
                const right = formatRp(item.subtotal);

                // padding biar kanan rata (32 char printer 58mm)
                const space = Math.max(1, 32 - left.length - right.length);
                text += left + ' '.repeat(space) + right + '\n';
            });

            text += line;

            // =====================
            // TOTAL
            // =====================
            text += padLine('Subtotal', formatRp(order.sub_total));

            if (order.adjustment_total !== 0) {
                text += padLine('Penyesuaian', formatRp(order.adjustment_total));
            }

            text += padLine('TOTAL', formatRp(order.grand_total), true);
            text += line;

            // =====================
            // PEMBAYARAN
            // =====================
            text += `Pembayaran: ${payment.method}\n`;
            text += padLine('Dibayar', formatRp(payment.paid));
            text += padLine('Kembali', formatRp(payment.change));
            text += '\n';

            // FOOTER
            text += '\x1B\x61\x01'; // CENTER
            text += 'Terima kasih 🙏\n';
            text += 'Powered by Campagna POS\n\n';

            // CUT
            text += '\x1D\x56\x00';
            text += '\x1B\x70\x00\x19\xFA';
// ESC p 0 25 250  (paling umum)

            return text;
        }

        function padLine(label, value, bold = false) {
            let line = '';
            if (bold) line += '\x1B\x45\x01'; // bold on

            const left = label;
            const right = value;
            const space = Math.max(1, 32 - left.length - right.length);
            line += left + ' '.repeat(space) + right + '\n';

            if (bold) line += '\x1B\x45\x00'; // bold off
            return line;
        }

        async function printReceipt(escposText) {
            if (!window.cashierPrinters || window.cashierPrinters.length === 0) {
                alert('Printer kasir belum diset');
                return;
            }

            // ambil printer pertama (kasir)
            const printerName = window.cashierPrinters[0];

            // connect QZ kalau belum
            if (!qz.websocket.isActive()) {
                await qz.websocket.connect();
            }

            const config = qz.configs.create(printerName, {
                encoding: 'UTF-8'
            });

            const data = [
                {
                    type: 'raw',
                    format: 'command',
                    data: escposText
                }
            ];

            try {
                await qz.print(config, data);
                console.log('✅ Struk tercetak');
            } catch (err) {
                console.error('❌ Gagal cetak', err);
                alert('Gagal cetak struk');
            }
        }

        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }
    </script>
@endpush
