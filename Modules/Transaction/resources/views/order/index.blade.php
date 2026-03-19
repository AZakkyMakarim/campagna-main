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
                <div class="bg-white rounded-xl border p-4 space-y-3">

                    <!-- SEARCH ROW -->
                    <div class="flex items-center gap-3">

                        <input
                            type="text"
                            placeholder="Cari menu / SKU…"
                            x-model="search"
                            @focus="setActiveInput($event.target)"
                            @keydown.enter.prevent="enterAdd()"
                            class="flex-1 text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        >

                        <button
                            type="button"
                            @click="keyboardOpen = !keyboardOpen"
                            class="px-3 py-2 border rounded-lg hover:bg-orange-100 transition"
                            title="Toggle Keyboard"
                        >
                            <i class="fa fa-keyboard"></i>
                        </button>

                    </div>

                    <!-- CATEGORY PILLS -->
                    <div class="flex gap-2 overflow-x-auto whitespace-nowrap no-scrollbar flex-nowrap pb-1">

                        <!-- ALL -->
                        <button
                            @click="category = ''"
                            :class="category === ''
            ? 'bg-orange-600 text-white border-orange-600'
            : 'bg-white text-gray-600'"
                            class="px-3 py-1.5 rounded-full border text-sm font-semibold transition shrink-0"
                        >
                            Semua
                        </button>

                        @foreach($categories as $cat)
                            <button
                                @click="category = '{{ $cat }}'; search=''"
                                :class="category === '{{ $cat }}'
                ? 'bg-orange-600 text-white border-orange-600'
                : 'bg-white text-gray-600'"
                                class="px-3 py-1.5 rounded-full border text-sm font-semibold transition hover:bg-orange-300 shrink-0"
                            >
                                {{ strtoupper($cat) }}
                            </button>
                        @endforeach

                    </div>

                </div>

                {{-- MENU GRID --}}
                <div class="max-h-[75vh] overflow-y-auto scroll-smooth">

                    <template x-for="cat in categoryOrder" :key="cat">

                        <div x-show="groupedMenus()[cat] && groupedMenus()[cat].length">

                            <!-- CATEGORY TITLE -->
                            <h3 class="text-sm font-bold text-gray-600 uppercase tracking-wide mb-2"
                                x-text="cat">
                            </h3>

                            <!-- GRID -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                                <template x-for="menu in groupedMenus()[cat]" :key="menu.id">

                                    <button
                                        type="button"
                                        @click="addMenu(menu)"
                                        class="relative group border rounded-lg overflow-hidden hover:bg-orange-100 bg-white transition text-left"
                                    >

                                        <!-- IMAGE -->
                                        <div class="h-28 bg-gray-100 flex items-center justify-center">
                                            <img
                                                :src="menu.picture.url || '/images/placeholder.png'"
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

                                        <!-- ADD BUTTON -->
                                        <div class="absolute bottom-3 right-3 w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center opacity-0 scale-75 group-hover:opacity-100 group-hover:scale-100 transition-all duration-200">
                                            <i class="fa fa-plus text-xs"></i>
                                        </div>

                                    </button>

                                </template>

                            </div>

                        </div>

                    </template>

                    <!-- EMPTY -->
                    <div
                        x-show="filteredMenus.length === 0"
                        class="text-center py-10 text-gray-400"
                    >
                        Menu tidak ditemukan
                    </div>

                </div>
            </div>

            {{-- RIGHT : CART --}}
            <div class="col-span-4 flex flex-col h-[calc(100vh-120px)]">
                <div class="bg-white rounded-xl border mb-2 shrink-0">
                   {{-- ORDER META --}}
                   <div class="px-4 py-3 space-y-2">

                       <!-- TIPE PESANAN -->
                       <div>
                           <label class="text-xs font-semibold text-gray-600">
                               Tipe Pesanan
                           </label>

                           <select
                               x-model="orderType"
                               class="w-full border rounded-md px-2 py-1 text-sm"
                           >
                               <option value="">Pilih</option>

                               @foreach($orderTypes as $orderType)
                                   <option value="{{ $orderType->name }}">{{ $orderType->name }}</option>
                               @endforeach
                           </select>
                       </div>
                   </div>
                </div>

                <div class="bg-white rounded-xl border flex flex-col flex-1 overflow-hidden">

                    {{-- CART HEADER --}}
                    <div class="px-4 py-3 border-b flex items-center justify-between">
                        <h3 class="font-semibold">Keranjang</h3>
                        <span class="text-xs text-gray-500"
                              x-text="`${cart.length} item`">
                        </span>
                    </div>

                    {{-- CART ITEMS --}}
                    <div class="flex-1 overflow-hidden divide-y">
                        {{-- ITEM --}}
                        <div class="pr-2 pl-2 bg-white">

                            <!-- EMPTY STATE -->
                            <template x-if="cart.length === 0">
                                <div class="py-12 text-center text-gray-400 space-y-2">
                                    <i class="fa fa-cart-shopping text-3xl"></i>
                                    <p class="text-sm">Keranjang masih kosong</p>
                                    <p class="text-xs">Klik menu atau scan barcode</p>
                                </div>
                            </template>

                            <!-- CART ITEMS -->
                            <div class="max-h-[53vh] overflow-y-auto scroll-smooth space-y-2">
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
                    <div class="flex flex-col border-r border-gray-200 pr-6 pl-6 h-full">

                        <div class="space-y-4">
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

                                <input
                                    type="text"
                                    x-model="tableNumber"
                                    @focus="setActiveInput($event.target)"
                                    placeholder="Nomor Meja / Pager"
                                    class="w-full mt-2 text-gray-700 px-3 py-2 rounded-lg border border-gray-300
           focus:outline-none focus:ring-2 focus:ring-orange-500"
                                >


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
                        <div class="flex-1"></div>
                    </div>

                    <!-- RIGHT : PAYMENT -->
                    <div class="space-y-6 pr-6">
                        <div class="flex border-gray-200 bg-white border border-black rounded-xl p-2">
                            <button
                                @click="paymentType = 'PAY';  setPaymentMode('FULL')"
                                :class="paymentType === 'PAY'
                                            ? 'bg-orange-600 text-white'
                                            : 'border-transparent text-gray-500 hover:text-orange-600'"
                                class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
                                Bayar
                            </button>
                            <button
                                @click="paymentType = 'DRAFT'; setPaymentMode('DP')"
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

{{--                            <!-- PAYMENT METHOD -->--}}
{{--                            <div class="space-y-3">--}}
{{--                                <label class="text-sm font-medium">Metode Pembayaran</label>--}}

{{--                                <div class="grid grid-cols-3 gap-2">--}}
{{--                                    <button--}}
{{--                                        type="button"--}}
{{--                                        @click="paymentMethod='CASH'"--}}
{{--                                        :class="paymentMethod==='CASH'--}}
{{--                                    ? 'bg-orange-600 text-white'--}}
{{--                                    : 'border hover:bg-orange-100'"--}}
{{--                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"--}}
{{--                                    >--}}
{{--                                        <i class="fa fa-money-bill text-lg"></i>--}}
{{--                                        <span class="text-xs">Cash</span>--}}
{{--                                    </button>--}}

{{--                                    <button--}}
{{--                                        type="button"--}}
{{--                                        @click="paymentMethod='CARD'"--}}
{{--                                        :class="paymentMethod==='CARD'--}}
{{--                                    ? 'bg-orange-600 text-white'--}}
{{--                                    : 'border hover:bg-orange-100'"--}}
{{--                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"--}}
{{--                                    >--}}
{{--                                        <i class="fa fa-credit-card text-lg"></i>--}}
{{--                                        <span class="text-xs">Kartu</span>--}}
{{--                                    </button>--}}

{{--                                    <button--}}
{{--                                        type="button"--}}
{{--                                        @click="paymentMethod='QRIS'"--}}
{{--                                        :class="paymentMethod==='QRIS'--}}
{{--                                    ? 'bg-orange-600 text-white'--}}
{{--                                    : 'border hover:bg-orange-100'"--}}
{{--                                        class="rounded-md px-4 py-3 flex flex-col items-center gap-1"--}}
{{--                                    >--}}
{{--                                        <i class="fa fa-qrcode text-lg"></i>--}}
{{--                                        <span class="text-xs">QRIS</span>--}}
{{--                                    </button>--}}
{{--                                </div>--}}
{{--                            </div>--}}

{{--                            <!-- CASH INPUT -->--}}
{{--                            <div x-show="paymentMethod==='CASH'" class="space-y-4">--}}

{{--                                <div class="space-y-2">--}}
{{--                                    <label class="text-sm font-medium">--}}
{{--                                        <template x-if="paymentMode==='FULL'">--}}
{{--                                            <span>Uang Diterima</span>--}}
{{--                                        </template>--}}

{{--                                        <template x-if="paymentMode==='DP'">--}}
{{--                                            <span>Nominal DP</span>--}}
{{--                                        </template>--}}
{{--                                    </label>--}}
{{--                                    <input--}}
{{--                                        type="text"--}}
{{--                                        inputmode="numeric"--}}
{{--                                        @focus="setActiveInput($event.target)"--}}
{{--                                        @keydown="if(!/[0-9]|Backspace|Delete|ArrowLeft|ArrowRight|Tab/.test($event.key)) $event.preventDefault()"--}}
{{--                                        class="w-full text-gray-700 pl-2 pr-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"--}}
{{--                                        :value="payAmount ? payAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''"--}}
{{--                                        @input="--}}
{{--                                            const clean = $event.target.value.replace(/[^0-9]/g, '');--}}
{{--                                            payAmount = Number(clean);--}}
{{--                                            $event.target.value = payAmount ? payAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';--}}
{{--                                        "--}}
{{--                                    >--}}
{{--                                </div>--}}

{{--                                <!-- QUICK CASH -->--}}
{{--                                <div class="grid grid-cols-4 gap-2">--}}
{{--                                    <template x-for="n in [50000,100000,150000,200000]" :key="n">--}}
{{--                                        <button--}}
{{--                                            type="button"--}}
{{--                                            @click="payAmount=n"--}}
{{--                                            class="border rounded-md py-2 hover:bg-orange-100"--}}
{{--                                            x-text="formatRp(n)"--}}
{{--                                        ></button>--}}
{{--                                    </template>--}}
{{--                                </div>--}}

{{--                                <button--}}
{{--                                    type="button"--}}
{{--                                    @click="payAmount=grandTotal"--}}
{{--                                    class="w-full border rounded-md py-2 hover:bg-orange-100"--}}
{{--                                >--}}
{{--                                    Uang Pas--}}
{{--                                </button>--}}

{{--                            </div>--}}

{{--                            <!-- CHANGE -->--}}
{{--                            <div class="bg-gray-100 rounded-lg p-4 text-center">--}}
{{--                                <template x-if="paymentMode==='FULL'">--}}
{{--                                    <p class="text-sm text-gray-500">Kembalian</p>--}}
{{--                                </template>--}}

{{--                                <template x-if="paymentMode==='DP'">--}}
{{--                                    <p class="text-sm text-gray-500">Sisa Pembayaran</p>--}}
{{--                                </template>--}}

{{--                                <p class="text-2xl font-bold"--}}
{{--                                   :class="change < 0 ? 'text-red-500' : 'text-green-600'"--}}
{{--                                   x-text="formatRp(change)">--}}
{{--                                </p>--}}
{{--                            </div>--}}
                        </div>

                        <div x-show="paymentType==='PAY'" class="space-y-2">
                            <!-- PAYMENT MODE -->
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Mode Pembayaran</label>

                                <div class="grid grid-cols-3 gap-2">
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

                                    <button
                                        type="button"
                                        @click="setPaymentMode('SPLIT')"
                                        :class="paymentMode==='SPLIT' ? 'bg-orange-600 text-white' : 'border hover:bg-orange-100'"
                                        class="rounded-md py-2 text-sm font-semibold"
                                    >
                                        Split
                                    </button>
                                </div>
                            </div>

                            {{-- SPLIT UI --}}
                            <div x-show="paymentMode === 'SPLIT'" class="space-y-3 mt-4">
                                <template x-for="(split, i) in splits" :key="i">
                                    <div
                                        class="rounded-xl border p-3 space-y-2 transition-all"
                                        :class="split.confirmed ? 'bg-green-50 border-green-300' : 'bg-white border-gray-200'"
                                    >
                                        {{-- HEADER BARIS --}}
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-semibold text-gray-500" x-text="`Metode ${i + 1}`"></span>
                                            <div class="flex items-center gap-1">
                                                {{-- Badge status --}}
                                                <span
                                                    x-show="split.confirmed"
                                                    class="text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full flex items-center gap-1"
                                                >
                                                    <i class="fa fa-check"></i> Terkonfirmasi
                                                </span>
                                                {{-- Batal konfirmasi --}}
                                                <button
                                                    type="button"
                                                    x-show="split.confirmed"
                                                    @click="unconfirmSplit(i)"
                                                    class="text-xs text-orange-500 hover:text-orange-700 px-1"
                                                    title="Batalkan konfirmasi"
                                                >
                                                    <i class="fa fa-rotate-left"></i>
                                                </button>
                                                {{-- Hapus baris (hanya jika belum confirmed & > 1 baris) --}}
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
                                                class="border rounded-lg px-2 py-2 flex-1 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
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
                                                @focus="setActiveInput($event.target)"
                                                placeholder="Nominal"
                                                class="border rounded-lg px-2 py-2 text-sm w-32 focus:outline-none focus:ring-2 focus:ring-orange-400 text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
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

                                        {{-- INFO KEMBALIAN PER BARIS (hanya tunai yang dikonfirmasi) --}}
                                        <div
                                            x-show="split.confirmed && ['CASH','TUNAI'].includes(split.method.toUpperCase()) && splitRemainingAmount() < 0"
                                            class="text-xs text-green-700 bg-green-100 rounded px-2 py-1 text-center"
                                        >
                                            Kembalian: <span x-text="formatRp(Math.abs(splitRemainingAmount()))"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- SISA YANG HARUS DIBAYAR --}}
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

                                {{-- TAMBAH METODE —  hanya tampil jika masih ada sisa --}}
                                <button
                                    type="button"
                                    x-show="splitRemainingAmount() > 0"
                                    @click="addSplit()"
                                    class="text-sm text-orange-600 border border-orange-300 px-3 py-2 rounded-lg hover:bg-orange-50 w-full"
                                >
                                    <i class="fa fa-plus mr-1"></i> Tambah Metode Pembayaran
                                </button>
                            </div>


                            <!-- PAYMENT METHOD -->
                            <div class="space-y-3" x-show="paymentMode !== 'SPLIT'">
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
                            <div x-show="paymentMethod==='CASH' && paymentMode !== 'SPLIT'" class="space-y-4">

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
                                    <template x-if="paymentMode==='FULL'">
                                        <p class="text-sm text-gray-500">Kembalian</p>
                                    </template>

                                    <template x-if="paymentMode==='DP'">
                                        <p class="text-sm text-gray-500">Sisa Pembayaran</p>
                                    </template>

                                    <p class="text-2xl font-bold"
                                       :class="change < 0 ? 'text-red-500' : 'text-green-600'"
                                       x-text="formatRp(change)">
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ACTION PAY --}}
                        <button
                            x-show="paymentType==='PAY'"
                            :disabled="paymentMode === 'SPLIT' && !allSplitConfirmed()"
                            :class="paymentMode === 'SPLIT' && !allSplitConfirmed()
                                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                : 'bg-orange-600 text-white hover:bg-orange-500'"
                            class="w-full h-14 text-lg rounded-lg flex items-center justify-center gap-2 transition"
                            @click="processPayment()"
                        >
                            <i class="fa fa-print"></i>
                            <span x-text="
                                paymentMode === 'SPLIT'
                                    ? (allSplitConfirmed() ? 'Bayar & Cetak Struk' : 'Konfirmasi semua pembayaran dulu')
                                    : (paymentMode === 'DP' ? 'Bayar DP' : 'Bayar & Cetak Struk')
                            "></span>
                        </button>

                        <!-- ACTION DRAFT -->
                        <button
                            x-show="paymentType==='DRAFT'"
                            class="w-full h-14 text-lg bg-orange-600 text-white rounded-lg hover:bg-orange-500 flex items-center justify-center gap-2"
                            @click="processPayment()"
                        >
                            <i class="fa fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('components.virtual-keyboard')
    </div>
@endsection

@push('css')
    <style>

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@push('js')
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

                orderType: 'Dine In',
                orderChannel: '',
                tableNumber: '',

                paymentOpen: false,
                paymentMethod: 'CASH',
                payAmount: 0,

                paymentType: 'PAY', // PAY | DRAFT
                draftNote: '',
                customerName: '',
                customerPhone: '',

                paymentMode: 'FULL', // FULL | DP | SPLIT
                paymentMethod: 'CASH',
                payAmount: 0,
                remainingAmount: 0,
                splits: [{ method: 'CASH', amount: 0, confirmed: false }],

                addSplit() {
                    const sisa = this.splitRemainingAmount();
                    this.splits.push({ method: '', amount: sisa > 0 ? sisa : 0, confirmed: false });
                },
                removeSplit(i) {
                    if (this.splits[i].confirmed) return; // tidak bisa hapus yang sudah dikonfirmasi
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
                    // Auto-fill sisa ke baris berikutnya jika ada
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
                    // Sisa = final total - total confirmed
                    return Math.max(0, this.finalTotal() - this.splitConfirmedTotal());
                },
                splitRemaining() {
                    const total = this.splitTotal();
                    const remaining = this.finalTotal() - total;
                    return remaining; // Positif: kurang, Negatif: kembalian
                },
                allSplitConfirmed() {
                    return this.splits.length > 0 &&
                           this.splits.every(s => s.confirmed) &&
                           this.splitConfirmedTotal() >= this.finalTotal();
                },

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

                    if (!this.orderType) {
                        alert('Tipe Pesanan wajib dipilih');
                        return;
                    }

                    this.payAmount = this.finalTotal();
                    this.paymentMethod = 'CASH';

                    this.paymentOpen = true;

                    this.$nextTick(() => {
                        document.querySelector('[x-model="tableNumber"]')?.focus()
                    })
                },

                // 🔁 CLOSE PAYMENT
                closePayment() {
                    this.paymentOpen = false;
                },

                // 💰 KEMBALIAN
                get change() {
                    if (this.paymentMode === 'SPLIT') {
                        const rem = this.splitRemaining();
                        return rem < 0 ? Math.abs(rem) : 0;
                    }
                    if (this.paymentMode === 'DP'){
                        return this.payAmount - this.finalTotal();
                    };
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

                    if (mode === 'SPLIT') {
                        this.splits = [{ method: 'CASH', amount: this.finalTotal() }];
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
                    if (this.paymentType === 'DRAFT' && !this.customerName?.trim()) {
                        alert('Nama customer masih kosong');
                        return;
                    }

                    if (this.paymentType === 'PAY' && this.paymentMode === 'SPLIT') {
                        if (!this.allSplitConfirmed()) {
                            Swal.fire('Perhatian', 'Konfirmasi semua metode pembayaran terlebih dahulu', 'warning');
                            return;
                        }
                    }

                    try {

                        const res = await fetch('{{ route('transaction.order.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                type: this.orderType,
                                channel: this.orderChannel,
                                table_number: this.tableNumber || null,

                                items: this.cart.map(i => ({
                                    menu_id: i.menu_id,
                                    qty: i.qty,
                                    note: i.note || null
                                })),

                                payment_type: this.paymentType,

                                payment: this.paymentType === 'PAY'
                                    ? (this.paymentMode === 'SPLIT' 
                                        ? { mode: 'SPLIT', splits: this.splits }
                                        : { mode: this.paymentMode, method: this.paymentMethod, amount: this.payAmount }
                                    )
                                    : null,

                                customer_name: this.customerName || null,
                                customer_phone: this.customerPhone || null,
                                note: this.paymentType === 'DRAFT'
                                    ? this.draftNote || null
                                    : null
                            })
                        });

                        const result = await res.json();

                        // ===== HTTP ERROR =====
                        if (!res.ok) {
                            console.error('HTTP ERROR:', res.status, result);
                            alert(result.message || 'Server error');
                            return;
                        }

                        // ===== APP ERROR =====
                        if (!result.success) {
                            console.error('APP ERROR:', result);
                            alert(result.message || 'Terjadi kesalahan');
                            return;
                        }

                        console.log('SUCCESS:', result);
                        alert('Order berhasil');

                        // printReceipt(receipt);

                    } catch (err) {

                        console.error('FETCH ERROR:', err);
                        alert('Gagal memproses order');

                    } finally {

                        this.resetOrder();
                        this.paymentOpen = false;

                    }
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
                categoryOrder: [
                    'jajan pasar',
                    'roti',
                    'keripik',
                    'minuman',
                    'wedangan',
                    'paket nasi',
                    'makanan',
                    'jede sate',
                    'jede bakmi',
                    'packaging',
                    'lainnya'
                ],

                groupedMenus() {

                    const groups = {};

                    // init category sesuai order
                    this.categoryOrder.forEach(cat => {
                        groups[cat] = [];
                    });

                    // bucket untuk kategori lain
                    groups['lainnya'] = [];

                    this.filteredMenus.forEach(menu => {

                        const cat = (menu.category || '').toLowerCase();

                        if (groups.hasOwnProperty(cat)) {
                            groups[cat].push(menu);
                        } else {
                            groups['lainnya'].push(menu);
                        }

                    });

                    return groups;
                },

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

        function formatRp(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        }
    </script>
@endpush
