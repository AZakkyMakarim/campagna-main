@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'menu',
    'activeSubmenu' => 'bundle',
])
@section('title', 'Menu')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Menu</h2>

        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form-bundle')"
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
                <th class="px-4 py-3">Nama Menu</th>
                <th class="px-4 py-3">SKU</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Barcode ID</th>
                <th class="px-4 py-3">Resep</th>
                <th class="px-4 py-3">HPP</th>
                <th class="px-4 py-3">Harga Jual</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($bundles as $key => $bundle)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $bundle->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $bundle->sku }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $bundle->category }}</td>
                        <td class="px-4 py-3 text-nowrap"></td>
                        <td class="px-4 py-3 text-nowrap">{{ count($bundle->components) }} Bahan</td>
                        <td class="px-4 py-3 text-nowrap text-orange-600 font-bold">{{ rp_format($bundle->hpp) }}</td>
                        <td class="px-4 py-3 text-nowrap text-green-600 font-bold">{{ rp_format($bundle->sell_price) }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $bundle->is_active ? 'checked' : '' }}
                                    onchange="toggleBundleStatus(this, '{{ route('management.purchasing.menu.single.update', $bundle) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('management.purchasing.menu.single.update', $bundle) }}"
                                    @click="$dispatch('open-edit-bundle', {
                                                menu: @js($bundle->load('components.componentable')),
                                                action: $el.dataset.route
                                            })"
                                    class="px-3 py-2 bg-yellow-500 text-white rounded"
                                >
                                    <i class="fa fa-pen"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<x-modal id="modal-form-bundle" title="Tambah Paket" icon="fa-plus" size="7xl">
    <form method="POST"
          action="{{ route('management.purchasing.menu.bundle.store') }}"
          x-data="bundleForm(@js($menus))">

        @csrf

        <div class="p-5 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- ================= LEFT : INFO MENU ================= --}}
                <div class="space-y-2">

                    {{-- INFO MENU --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Bundle</label>
                        <input type="text" name="name" required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                            <select name="category" required
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2">
                                <option value="">Pilih kategori</option>
                                <option value="makanan">Makanan</option>
                                <option value="minuman">Minuman</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">HPP (Auto)</label>
                            <input type="text" readonly
                                   :value="formatMoney(totalHpp)"
                                   class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 font-semibold text-orange-600">
                            <input type="hidden" name="hpp" :value="totalHpp">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Harga Jual</label>
                            <input type="number" step="1" min="0" name="sell_price" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                </div>

                {{-- ================= RIGHT : MULTI KOMPONEN ================= --}}
                <div>
                    <div class="border rounded-xl overflow-hidden">

                        {{-- HEADER --}}
                        <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-800">Isi Bundle</p>
                                <p class="text-xs text-gray-500">
                                    Pilih menu + qty per menu
                                </p>
                            </div>

                            <button type="button"
                                    @click="addRow"
                                    class="px-3 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-500">
                                + Tambah Menu
                            </button>
                        </div>

                        {{-- BODY --}}
                        <div class="relative max-h-[600px] overflow-y-auto p-4 space-y-3">

                            <template x-for="(row, idx) in rows" :key="row.key">
                                <div class="grid grid-cols-12 gap-3 items-center border rounded-lg p-3">

                                    {{-- MENU --}}
                                    <div class="col-span-12 md:col-span-6">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            Menu
                                        </label>
                                        <select x-model="row.menu_id"
                                                @change="onPickMenu(idx)"
                                                class="w-full rounded-lg border border-gray-300 px-3 py-2">
                                            <option value="">Pilih menu...</option>
                                            <template x-for="m in menus" :key="m.id">
                                                <option :value="m.id"
                                                        x-text="`${m.name} (${formatMoney(m.hpp)})`">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- QTY --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            Qty
                                        </label>
                                        <input type="number" min="1"
                                               x-model.number="row.qty"
                                               @input="recalc"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right">
                                    </div>

                                    {{-- SUB HPP --}}
                                    <div class="col-span-6 md:col-span-3">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                                            Sub HPP
                                        </label>
                                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right">
                                    <span class="font-semibold text-gray-800"
                                          x-text="formatMoney(row.sub_hpp)"></span>
                                        </div>
                                    </div>

                                    {{-- REMOVE --}}
                                    <div class="col-span-12 md:col-span-1 flex items-center justify-center">
                                        <button type="button"
                                                @click="removeRow(idx)"
                                                class="w-9 h-9 flex items-center justify-center rounded-full
                                               border border-red-200 text-red-500
                                               hover:bg-red-500 hover:text-white transition"
                                                title="Hapus">
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>

                                    {{-- hidden submit --}}
                                    <input type="hidden"
                                           :name="`components[${idx}][menu_id]`"
                                           :value="row.menu_id">
                                    <input type="hidden"
                                           :name="`components[${idx}][qty]`"
                                           :value="row.qty">
                                </div>
                            </template>

                            <div x-show="rows.length === 0"
                                 class="text-sm text-gray-400 text-center py-8">
                                Belum ada menu dalam bundle.
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="type" value="bundle">
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

<div
    x-data="bundleEditForm(@js($menus))"
    x-show="open"
    @open-edit-bundle.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open=false"></div>

    <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-xl border">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-semibold text-lg">Edit Menu</h3>
            <button @click="open=false"><i class="fa fa-times"></i></button>
        </div>

        <form
            method="POST"
            :action="action"
        >
            @csrf

            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama</label>
                            <input type="text" name="name" required
                                   x-model="form.name"
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">SKU</label>
                                <input type="text" name="sku" required
                                       x-model="form.sku"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                                <select name="category" required
                                        x-model="form.category"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2">
                                    <option value="makanan">Makanan</option>
                                    <option value="minuman">Minuman</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">HPP (Auto)</label>
                                <input type="text" readonly
                                       :value="formatMoney(totalHpp)"
                                       class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 font-semibold text-orange-600">
                                <input type="hidden" name="hpp" :value="totalHpp">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Harga Jual</label>
                                <input type="number" step="1" min="0" name="sell_price" required
                                       x-model.number="form.sell_price"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="border rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-800">Resep / Bahan Jadi</p>
                                    <p class="text-xs text-gray-500">Bisa tambah lebih dari 1 komponen</p>
                                </div>

                                <button type="button"
                                        @click="addComponent()"
                                        class="px-3 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-500">
                                    + Tambah Komponen
                                </button>
                            </div>

                            <div class="relative max-h-[600px] overflow-y-auto p-4 space-y-3">
                                <template x-for="(row, idx) in components" :key="row.key">
                                    <div class="grid grid-cols-12 gap-3 items-center border rounded-lg p-3">
                                        <div class="col-span-12 md:col-span-6">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Komponen</label>
                                            <select
                                                class="w-full rounded-lg border px-3 py-2"
                                                x-model="row.menu_id"
                                                @change="onPickMenu(idx)"
                                            >
                                                <option value="">Pilih menu...</option>

                                                <template x-for="m in menus" :key="m.id">
                                                    <option
                                                        :value="String(m.id)"
                                                        :selected="row.menu_id === String(m.id)"
                                                    x-text="`${m.name} (${formatMoney(m.hpp)})`"
                                                    ></option>
                                                </template>
                                            </select>

                                            <p class="text-xs mt-1"
                                               x-show="row.ingredient"
                                               :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">
                                                Unit cost:
                                                <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>
                                            </p>
                                        </div>

                                        <div class="col-span-6 md:col-span-2">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Qty</label>
                                            <input type="number" min="1" step="1"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right"
                                                   x-model.number="row.qty"
                                                   @input="recalc()">
                                        </div>

                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sub HPP</label>
                                            <div class="w-full rounded-lg border bg-gray-50 px-3 py-2 text-right">
                                                <span x-text="formatMoney(row.sub_hpp)"></span>
                                            </div>
                                        </div>

                                        <div class="col-span-12 md:col-span-1 flex justify-center">
                                            <button type="button"
                                                    @click="removeComponent(idx)"
                                                    class="w-9 h-9 rounded-full border border-red-200 text-red-500 hover:bg-red-500 hover:text-white">
                                                <i class="fa fa-trash text-xs"></i>
                                            </button>
                                        </div>

                                        <input type="hidden" :name="`components[${idx}][menu_id]`" :value="row.menu_id">
                                        <input type="hidden" :name="`components[${idx}][qty]`" :value="row.qty">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="type" value="bundle">
                </div>
            </div>

            <div class="flex justify-end gap-3 px-5 py-4 border-t">
                <button type="button"
                        @click="$dispatch('close-modal')"
                        class="px-4 py-2 border rounded-lg">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
    <script>
        function toggleBundleStatus(el, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_active: el.checked ? 1 : 0
                })
            })
                .then(res => {
                    if (!res.ok) {
                        throw res;
                    }
                    return res.json();
                })
                .then(res => {
                    // console.log(res.payload); // data utama
                })
                .catch(async err => {
                    el.checked = !el.checked; // rollback toggle

                    let message = 'Terjadi kesalahan';

                    if (err.json) {
                        const e = await err.json();
                        message = e.message ?? message;
                    }

                    alert(message);
                });
        }

        function bundleForm(menusRaw) {
            const menus = menusRaw.map(m => ({
                id: m.id,
                name: m.name,
                hpp: Number(m.hpp || 0),
            }));

            return {
                menus,
                rows: [],
                totalHpp: 0,

                init() {
                    this.addRow();
                },

                addRow() {
                    this.rows.push({
                        key: crypto.randomUUID
                            ? crypto.randomUUID()
                            : (Date.now() + Math.random()),
                        menu_id: '',
                        menu: null,
                        qty: 1,
                        sub_hpp: 0,
                    });
                },

                removeRow(i) {
                    this.rows.splice(i, 1);
                    this.recalc();
                },

                onPickMenu(i) {
                    const row = this.rows[i];
                    row.menu = this.menus.find(m => String(m.id) === String(row.menu_id));
                    this.recalc();
                },

                recalc() {
                    let total = 0;
                    this.rows.forEach(r => {
                        r.sub_hpp = (r.menu ? r.menu.hpp : 0) * r.qty;
                        total += r.sub_hpp;
                    });
                    this.totalHpp = total;
                },

                formatMoney(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(n || 0);
                }
            };
        }

        function numberFormat(num) {
            return Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>

    <script>
        function bundleEditForm(menusRaw) {
            const menus = menusRaw.map(m => ({
                id: String(m.id),
                name: m.name,
                hpp: Number(m.hpp || 0),
            }));

            return {
                open: false,
                action: '',
                menus,
                components: [],
                totalHpp: 0,

                form: {
                    name: '',
                    sku: '',
                    category: '',
                    sell_price: 0,
                },

                fill({ menu, action }) {
                    this.open = true;
                    this.action = action;

                    this.form.name = menu.name;
                    this.form.sku = menu.sku;
                    this.form.category = menu.category;
                    this.form.sell_price = Number(menu.sell_price || 0);

                    // reset dulu
                    this.components = [];

                    this.$nextTick(() => {
                        this.components = (menu.components || []).map(c => ({
                            key: crypto.randomUUID
                                ? crypto.randomUUID()
                                : (Date.now() + Math.random()),

                            menu_id: '',          // ⛔ kosong dulu
                            menu: null,
                            qty: Number(c.qty),
                            unit_cost: 0,
                            sub_hpp: 0,

                            __target_id: String(c.componentable_id) // simpan target
                        }));

                        // ⏳ tunggu option selesai render
                        this.$nextTick(() => {
                            this.components.forEach((row, idx) => {
                                row.menu_id = row.__target_id;
                                this.onPickMenu(idx);
                            });

                            this.recalc();
                        });
                    });
                },

                addComponent() {
                    this.components.push({
                        key: crypto.randomUUID
                            ? crypto.randomUUID()
                            : (Date.now() + Math.random()),
                        menu_id: '',
                        menu: null,
                        qty: 1,
                        unit_cost: 0,
                        sub_hpp: 0,
                    });
                },

                removeComponent(idx) {
                    this.components.splice(idx, 1);
                    this.recalc();
                },

                onPickMenu(idx) {
                    const row = this.components[idx];
                    const picked = this.menus.find(
                        m => m.id === String(row.menu_id)
                    );

                    row.menu = picked || null;
                    row.unit_cost = picked ? picked.hpp : 0;

                    this.recalc();
                },

                recalc() {
                    let total = 0;
                    this.components.forEach(row => {
                        row.sub_hpp = row.qty * row.unit_cost;
                        total += row.sub_hpp;
                    });
                    this.totalHpp = total;
                },

                formatMoney(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(n || 0);
                },
            };
        }
    </script>
@endpush
