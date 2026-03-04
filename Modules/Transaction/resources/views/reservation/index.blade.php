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
                <th class="px-4 py-3">Nama Customer</th>
                <th class="px-4 py-3">No. HP</th>
                <th class="px-4 py-3">DP</th>
                <th class="px-4 py-3">Pre-Order</th>
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
                        <td class="px-4 py-3">{{ $reservation->customer_name }}</td>
                        <td class="px-4 py-3">{{ $reservation->phone }}</td>
                        <td class="px-4 py-3">{{ rp_format(@$reservation->payments->where('type', 'DP')->first()->amount) }}</td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3">{{ $reservation->status }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
{{--                                <button--}}
{{--                                    type="button"--}}
{{--                                    data-route="{{ route('management.purchasing.menu.single.update', $menu) }}"--}}
{{--                                    @click="$dispatch('open-edit-menu', {--}}
{{--                                                menu: @js(--}}
{{--                                                    $menu->load('components.componentable')--}}
{{--                                                ),--}}
{{--                                                action: $el.dataset.route--}}
{{--                                            })"--}}
{{--                                    class="px-3 py-2 bg-yellow-500 text-white rounded"--}}
{{--                                >--}}
{{--                                    <i class="fa fa-pen"></i>--}}
{{--                                </button>--}}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
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

{{--<div--}}
{{--    x-data="menuEditForm(@js($recipes))"--}}
{{--    x-show="open"--}}
{{--    @open-edit-menu.window="fill($event.detail)"--}}
{{--    x-transition--}}
{{--    x-cloak--}}
{{--    class="fixed inset-0 flex items-center justify-center z-50"--}}
{{-->--}}
{{--    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open=false"></div>--}}

{{--    <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-xl border">--}}

{{--        <!-- HEADER -->--}}
{{--        <div class="flex items-center justify-between px-5 py-4 border-b">--}}
{{--            <h3 class="font-semibold text-lg">Edit Menu</h3>--}}
{{--            <button @click="open=false"><i class="fa fa-times"></i></button>--}}
{{--        </div>--}}

{{--        <form--}}
{{--            method="POST"--}}
{{--            :action="action"--}}
{{--            x-data="menuForm(@js($recipes))"--}}
{{--            @open-edit-menu.window="fill($event.detail)"--}}
{{--        >--}}
{{--            @csrf--}}

{{--            <div class="p-5 space-y-5">--}}
{{--                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                    <div class="space-y-2">--}}
{{--                        --}}{{-- INFO MENU --}}
{{--                        <div>--}}
{{--                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama</label>--}}
{{--                            <input type="text" name="name" required--}}
{{--                                   x-model="form.name"--}}
{{--                                   class="w-full rounded-lg border border-gray-300 px-3 py-2">--}}
{{--                        </div>--}}

{{--                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                            <div>--}}
{{--                                <label class="block text-sm font-bold text-gray-700 mb-1">SKU</label>--}}
{{--                                <input type="text" name="sku" required--}}
{{--                                       x-model="form.sku"--}}
{{--                                       class="w-full rounded-lg border border-gray-300 px-3 py-2">--}}
{{--                            </div>--}}

{{--                            <div>--}}
{{--                                <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>--}}
{{--                                <select name="category" required--}}
{{--                                        x-model="form.category"--}}
{{--                                        class="w-full rounded-lg border border-gray-300 px-3 py-2">--}}
{{--                                    <option value="makanan">Makanan</option>--}}
{{--                                    <option value="minuman">Minuman</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        --}}{{-- PRICE SECTION --}}
{{--                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">--}}
{{--                            <div>--}}
{{--                                <label class="block text-sm font-bold text-gray-700 mb-1">HPP (Auto)</label>--}}
{{--                                <input type="text" readonly--}}
{{--                                       :value="formatMoney(totalHpp)"--}}
{{--                                       class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 font-semibold text-orange-600">--}}
{{--                                <input type="hidden" name="hpp" :value="totalHpp">--}}
{{--                            </div>--}}

{{--                            <div>--}}
{{--                                <label class="block text-sm font-bold text-gray-700 mb-1">Harga Jual</label>--}}
{{--                                <input type="number" step="1" min="0" name="sell_price" required--}}
{{--                                       x-model.number="form.sell_price"--}}
{{--                                       class="w-full rounded-lg border border-gray-300 px-3 py-2">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    --}}{{-- MULTI KOMPONEN --}}
{{--                    <div>--}}
{{--                        <div class="border rounded-xl overflow-hidden">--}}
{{--                            <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">--}}
{{--                                <div>--}}
{{--                                    <p class="font-semibold text-gray-800">Resep / Bahan Jadi</p>--}}
{{--                                    <p class="text-xs text-gray-500">Bisa tambah lebih dari 1 komponen</p>--}}
{{--                                </div>--}}

{{--                                <button type="button"--}}
{{--                                        @click="addComponent()"--}}
{{--                                        class="px-3 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-500">--}}
{{--                                    + Tambah Komponen--}}
{{--                                </button>--}}
{{--                            </div>--}}

{{--                            <div class="relative max-h-[600px] overflow-y-auto p-4 space-y-3">--}}
{{--                                <template x-for="(row, idx) in components" :key="row.key">--}}
{{--                                    <div class="grid grid-cols-12 gap-3 items-center border rounded-lg p-3">--}}
{{--                                        --}}{{-- SELECT INGREDIENT --}}
{{--                                        <div class="col-span-12 md:col-span-6">--}}
{{--                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Komponen</label>--}}
{{--                                            <select class="w-full rounded-lg border border-gray-300 px-3 py-2"--}}
{{--                                                    x-model="row.ingredient_id"--}}
{{--                                                    @change="onPickIngredient(idx)">--}}
{{--                                                <option value="">Pilih komponen...</option>--}}
{{--                                                <template x-for="it in ingredients" :key="it.id">--}}
{{--                                                    <option--}}
{{--                                                        :value="String(it.id)"--}}
{{--                                                        :selected="row.ingredient_id === String(it.id)"--}}
{{--                                                        x-text="`${it.name} (${it.unit?.symbol ?? ''})`">--}}
{{--                                                    </option>--}}
{{--                                                </template>--}}
{{--                                            </select>--}}

{{--                                            <p class="text-xs mt-1"--}}
{{--                                               x-show="row.ingredient"--}}
{{--                                               :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">--}}
{{--                                                Unit cost:--}}
{{--                                                <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>--}}
{{--                                            </p>--}}
{{--                                        </div>--}}

{{--                                        --}}{{-- QTY --}}
{{--                                        <div class="col-span-6 md:col-span-2">--}}
{{--                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Qty</label>--}}
{{--                                            <input type="number" min="0.01" step="0.01"--}}
{{--                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right"--}}
{{--                                                   x-model.number="row.qty"--}}
{{--                                                   @input="recalc()">--}}
{{--                                        </div>--}}

{{--                                        --}}{{-- SUB HPP --}}
{{--                                        <div class="col-span-6 md:col-span-3">--}}
{{--                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sub HPP</label>--}}
{{--                                            <div class="w-full rounded-lg border bg-gray-50 px-3 py-2 text-right">--}}
{{--                                                <span x-text="formatMoney(row.sub_hpp)"></span>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}

{{--                                        --}}{{-- REMOVE --}}
{{--                                        <div class="col-span-12 md:col-span-1 flex justify-center">--}}
{{--                                            <button type="button"--}}
{{--                                                    @click="removeComponent(idx)"--}}
{{--                                                    class="w-9 h-9 rounded-full border border-red-200 text-red-500 hover:bg-red-500 hover:text-white">--}}
{{--                                                <i class="fa fa-trash text-xs"></i>--}}
{{--                                            </button>--}}
{{--                                        </div>--}}

{{--                                        <input type="hidden" :name="`components[${idx}][recipe_id]`" :value="row.ingredient_id">--}}
{{--                                        <input type="hidden" :name="`components[${idx}][qty]`" :value="row.qty">--}}
{{--                                    </div>--}}
{{--                                </template>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <input type="hidden" name="type" value="single">--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="flex justify-end gap-3 px-5 py-4 border-t">--}}
{{--                <button type="button"--}}
{{--                        @click="$dispatch('close-modal')"--}}
{{--                        class="px-4 py-2 border rounded-lg">--}}
{{--                    Batal--}}
{{--                </button>--}}
{{--                <button type="submit"--}}
{{--                        class="px-4 py-2 bg-orange-600 text-white rounded-lg">--}}
{{--                    Update--}}
{{--                </button>--}}
{{--            </div>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}

@push('js')
    <script>
        function reservation() {
            return {
                paymentMethod: 'CASH',
            }
        }
    </script>
@endpush
