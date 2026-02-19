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

        {{-- ORDER META --}}
        <div class="bg-white rounded-xl border p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- TIPE PESANAN --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Tipe Pesanan
                    </label>
                    <select
                        x-model="orderType"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                    >
                        <option value="">Tipe Pesanan</option>
                        @foreach(config('array.order.type') as $key => $type)
                            <option value="{{ $key }}">{{ $type['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- JENIS ORDER --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Jenis Order
                    </label>
                    <select
                        x-model="orderChannel"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                    >
                        <option value="">Jenis Order</option>
                        @foreach(config('array.order.channel') as $key => $channel)
                            <option value="{{ $key }}">{{ $channel['display_name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- NOMOR MEJA (DINE IN) --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        Nomor Meja
                    </label>
                    <input
                        type="text"
                        x-model="tableNumber"
                        placeholder="Contoh: A1 / 5 / VIP"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                    >
                </div>

            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            {{-- LEFT : MENU LIST --}}
            <div x-data="menuSearch({{ $menus->toJson() }})" class="col-span-8 space-y-4">

                {{-- SEARCH & CATEGORY --}}
                <div class="bg-white rounded-xl border p-4 flex items-center gap-4">
                    <input
                        type="text"
                        placeholder="Cari menu / SKU…"
                        x-model="search"
                        @keydown.enter.prevent="enterAdd()"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                    >

                    <select class="border rounded-lg px-3 py-2">
                        <option>Semua</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
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

                <!-- KEYBOARD WRAPPER -->
                <div
                    x-show="keyboardOpen"
                    x-transition
                    class="bg-white rounded-xl border p-4"
                >
                    <div class="space-y-3 text-center">

                        <!-- ROW 1: 1-0 -->
                        <div class="grid grid-cols-10 gap-2">
                            <template x-for="key in ['1','2','3','4','5','6','7','8','9','0']">
                                <button
                                    @click="pressKey(key)"
                                    class="key-btn w-full"
                                    x-text="key"
                                ></button>
                            </template>
                        </div>

                        <!-- ROW 2: Q-P -->
                        <div class="grid grid-cols-10 gap-2">
                            <template x-for="key in ['Q','W','E','R','T','Y','U','I','O','P']">
                                <button
                                    @click="pressKey(key)"
                                    class="key-btn w-full"
                                    x-text="key"
                                ></button>
                            </template>
                        </div>

                        <!-- ROW 3: A-L (9 tombol, center) -->
                        <div class="grid grid-cols-11 gap-2 justify-center">
                            <!-- spacer kiri -->
                            <div></div>

                            <template x-for="key in ['A','S','D','F','G','H','J','K','L']">
                                <button
                                    @click="pressKey(key)"
                                    class="key-btn w-full"
                                    x-text="key"
                                ></button>
                            </template>

                            <!-- spacer kanan -->
                            <div></div>
                        </div>

                        <!-- ROW 4: Z-M (7 tombol, center) -->
                        <div class="grid grid-cols-11 gap-2 justify-center">
                            <!-- spacer kiri -->
                            <div></div>
                            <div></div>

                            <template x-for="key in ['Z','X','C','V','B','N','M']">
                                <button
                                    @click="pressKey(key)"
                                    class="key-btn w-full"
                                    x-text="key"
                                ></button>
                            </template>

                            <!-- spacer kanan -->
                            <div></div>
                            <div></div>
                        </div>

                        <!-- ROW 5: ACTIONS -->
                        <div class="grid grid-cols-10 gap-2">
                            <button @click="clearSearch()" class="key-btn col-span-2">CLR</button>
                            <button @click="pressKey(' ')" class="key-btn col-span-6">Space</button>
                            <button @click="backspace()" class="key-btn col-span-2">⌫</button>
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
                                            x-model="item.note"
                                            class="w-full text-xs border rounded-md px-2 py-1 focus:ring-1 focus:ring-orange-400 focus:outline-none"
                                        >
                                    </div>

                                </div>
                            </template>
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
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div class="absolute inset-0 bg-black/60" @click="closePayment()"></div>

            <div class="relative bg-white w-full max-w-4xl rounded-xl shadow-xl">

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
                                Draft
                            </button>
                        </div>

                        <!-- DRAFT INPUT -->
                        <div x-show="paymentType==='DRAFT'" class="space-y-4">

                            <div class="space-y-1">
                                <label class="text-sm font-medium">Nama Customer</label>
                                <input
                                    type="text"
                                    x-model="customerName"
                                    placeholder="Opsional"
                                   class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">No. HP</label>
                                <input
                                    type="text"
                                    x-model="customerPhone"
                                    placeholder="Opsional"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Catatan Order</label>
                                <textarea
                                    x-model="draftNote"
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
                                    
                                     <button
                                        type="button"
                                        @click="setPaymentMode('SPLIT')"
                                        :class="paymentMode==='SPLIT' ? 'bg-orange-600 text-white' : 'border hover:bg-orange-100'"
                                        class="rounded-md py-2 text-sm font-semibold col-span-2"
                                    >
                                        Split Payment (Min 2x)
                                    </button>
                                </div>
                            </div>
                            
                            <!-- SPLIT INFO -->
                            <div x-show="paymentMode==='SPLIT'" class="bg-gray-50 border rounded-lg p-3 space-y-2">
                                <template x-for="(hist, idx) in splitHistory">
                                    <div class="flex justify-between text-xs text-gray-600">
                                        <span x-text="`Pembayaran #${idx+1} (${hist.method})`"></span>
                                        <span x-text="formatRp(hist.amount)"></span>
                                    </div>
                                </template>
                                
                                <hr x-show="splitHistory.length > 0">
                                
                                <div class="flex justify-between text-sm font-bold">
                                    <span>Sisa Tagihan</span>
                                    <span class="text-orange-600" x-text="formatRp(remainingAmount)"></span>
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

                                        <template x-if="paymentMode==='SPLIT'">
                                            <span>Bayar Sebagian</span>
                                        </template>
                                    </label>
                                    <input
                                        type="number"
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
                            <span x-show="paymentMode==='FULL'">Bayar & Cetak Struk</span>
                            <span x-show="paymentMode==='DP'">Bayar DP</span>
                            <span x-show="paymentMode==='SPLIT'">
                                <span x-text="remainingAmount > payAmount ? 'Bayar Sebagian' : 'Bayar Lunas & Cetak'"></span>
                            </span>
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

                paymentMode: 'FULL', // FULL | DP | SPLIT
                paymentMethod: 'CASH',
                payAmount: 0,
                // SPLIT STATE
                splitOrderId: null,
                splitHistory: [],
                
                get remainingAmount() {
                    if (this.paymentMode === 'SPLIT') {
                        return Math.max(0, this.finalTotal() - this.totalPaidSplit);
                    }
                    return 0;
                },

                get totalPaidSplit() {
                    return this.splitHistory.reduce((sum, p) => sum + Number(p.amount), 0);
                },

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

                    if (!this.orderChannel) {
                        alert('Jenis Order wajib dipilih');
                        return;
                    }

                    // optional: kalau dine-in wajib isi meja
                    if (this.orderType === 'dine_in' && !this.tableNumber) {
                        alert('Nomor meja wajib diisi untuk Dine In');
                        return;
                    }

                    this.payAmount = this.finalTotal();
                    this.paymentMethod = 'CASH';
                    this.paymentOpen = true;
                    this.splitOrderId = null;
                    this.splitHistory = [];
                },

                // 🔁 CLOSE PAYMENT
                closePayment() {
                    // Prevent close if split payment is in progress but not finished
                    if (this.paymentMode === 'SPLIT' && this.splitOrderId && this.remainingAmount > 0) {
                        if(!confirm('Transaksi split belum selesai. Yakin ingin tutup? (Data tersimpan di server)')) {
                            return;
                        }
                    }
                    this.paymentOpen = false;
                },

                // 💰 KEMBALIAN
                get change() {
                    if (this.paymentMode === 'DP') return 0;
                    if (this.paymentMode === 'SPLIT') return 0; // handled logic elsewhere
                    return Math.max(0, this.payAmount - this.finalTotal());
                },

                setPaymentMode(mode) {
                    // Disable changing mode if split transaction already started
                    if (this.splitOrderId) return;

                    this.paymentMode = mode;

                    if (mode === 'FULL') {
                        this.payAmount = this.finalTotal();
                    }

                    if (mode === 'DP') {
                        this.payAmount = 0;
                    }
                    
                    if (mode === 'SPLIT') {
                        this.payAmount = 0; // User input manually
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
                    if (this.paymentMode === 'SPLIT') {
                        await this.processSplitPayment();
                        return;
                    }

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

                        this.printAndComplete(res.order, {
                            method: this.paymentMethod,
                            paid: this.payAmount,
                            change: this.change
                        });
                    })
                    .catch(() => {
                        alert('Gagal memproses order');
                    });
                },

                async processSplitPayment() {
                    if (this.payAmount <= 0) {
                        alert('Nominal pembayaran tidak valid');
                        return;
                    }

                    // FIRST SPLIT (CREATE ORDER)
                    if (!this.splitOrderId) {
                        let data = JSON.stringify({
                            type: this.orderType,
                            channel: this.orderChannel,
                            table_number: this.tableNumber || null,
                            items: this.cart.map(i => ({
                                menu_id: i.menu_id,
                                qty: i.qty,
                                note: i.note || null
                            })),
                            payment_type: 'PAY', 
                            payment: {
                                mode: 'SPLIT',
                                method: this.paymentMethod,
                                amount: this.payAmount
                            },
                            customer_name: this.customerName || null,
                            customer_phone: this.customerPhone || null,
                            note: null
                        });

                        try {
                            const res = await fetch('{{ route('transaction.order.store') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: data
                            }).then(r => r.json());

                            if (!res.success) {
                                alert(res.message);
                                return;
                            }

                            // Success create order
                            this.splitOrderId = res.order.id;
                            this.splitHistory.push({
                                method: this.paymentMethod,
                                amount: this.payAmount,
                                date: new Date().toLocaleTimeString()
                            });

                            // Calculate remaining logic with current payAmount is not needed as history updated.
                            // But we need to reset payAmount input
                            this.payAmount = 0;

                            // Check if completed (unlikely for first split unless full amount)
                            if (this.remainingAmount <= 0) {
                                this.printAndComplete(res.order, this.getCombinedPaymentInfo());
                            } else {
                                this.payAmount = this.remainingAmount; // Auto suggest remaining
                            }

                        } catch (e) {
                            alert('Gagal memproses pembayaran split pertama');
                        }
                    } 
                    // SUBSEQUENT SPLIT (ADD PAYMENT)
                    else {
                        let data = JSON.stringify({
                            order_id: this.splitOrderId,
                            method: this.paymentMethod,
                            amount: this.payAmount
                        });

                        try {
                            const res = await fetch('{{ route('transaction.list-order.pay') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: data
                            }).then(r => r.json());

                            if (!res.success) {
                                alert(res.message);
                                return;
                            }

                             this.splitHistory.push({
                                method: this.paymentMethod,
                                amount: this.payAmount,
                                date: new Date().toLocaleTimeString()
                            });
                            
                            this.payAmount = 0;

                            if (this.remainingAmount <= 0) {
                                // Fetch updated order to get full details matching printAndComplete expectation? 
                                // Actually we can construct it partially or just use what we have. 
                                // Need 'order' object. Since we don't have fresh order object here, 
                                // we can construct a mock or fetch it. 
                                // For simplicity let's construct mock order object based on known totals.
                                const order = {
                                    code: '-', // We might need to save code from first response
                                    date: new Date().toLocaleString('id-ID'),
                                    table: this.tableNumber,
                                    sub_total: this.subTotal,
                                    adjustment_total: this.adjustmentTotal,
                                    grand_total: this.grandTotal
                                };
                                this.printAndComplete(order, this.getCombinedPaymentInfo());
                            } else {
                                this.payAmount = this.remainingAmount;
                            }

                        } catch (e) {
                            alert('Gagal memproses pembayaran split lanjutan');
                        }
                    }
                },

                getCombinedPaymentInfo() {
                    const totalPaid = this.totalPaidSplit;
                    const change = Math.max(0, totalPaid - this.finalTotal());
                    
                    return {
                        method: 'SPLIT (' + this.splitHistory.map(h => h.method).join(',') + ')',
                        paid: totalPaid,
                        change: change
                    };
                },

                printAndComplete(order, paymentInfo) {
                    const items = this.cart.map(item => ({
                        name: item.name,
                        qty: item.qty,
                        price: item.price,
                        subtotal: item.subtotal
                    }));
                    
                    // Re-calc change if needed
                    // paymentInfo.change = Math.max(0, paymentInfo.paid - this.grandTotal);

                    const receipt = buildReceipt({
                        outlet: {
                            name: window.outletName,
                            address: window.outletAddress || ''
                        },
                        cashier: window.cashierName,
                        order: {
                            ...order,
                            grand_total: this.grandTotal // ensure consistent
                        },
                        items,
                        payment: paymentInfo
                    });

                    // printReceipt(receipt);

                    // 🔄 RESET
                    this.resetOrder();
                    this.paymentOpen = false;
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
                menus,
                keyboardOpen: false,

                get filteredMenus() {
                    if (!this.search) return this.menus;

                    const q = this.search.toLowerCase();

                    return this.menus.filter(m =>
                        m.name.toLowerCase().includes(q) ||
                        (m.sku && m.sku.toLowerCase().includes(q)) ||
                        (m.barcode && m.barcode === this.search)
                    );
                },

                pressKey(key) {
                    this.search += key;
                },

                backspace() {
                    this.search = this.search.slice(0, -1);
                },

                clearSearch() {
                    this.search = '';
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
            if (order.table) text += `Meja   : ${order.table}\n`;
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
