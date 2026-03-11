@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'menu',
    'activeSubmenu' => 'single',
])
@section('title', 'Menu')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Menu</h2>

        <div class="flex items-center gap-2">
            <button
                @click="$dispatch('open-modal', 'modal-import-menu-single')"
                class="bg-green-600 text-white px-4 py-2 rounded-xl shadow hover:bg-green-500 transition flex items-center gap-2 hover:cursor-pointer">
                <i class="fa fa-file-import"></i>
                Import
            </button>
            <button
                @click="$dispatch('open-modal', 'modal-form-single')"
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
                <th class="px-4 py-3">Resep</th>
                <th class="px-4 py-3">HPP</th>
                <th class="px-4 py-3">Harga Jual</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($menus as $key => $menu)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 10) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $menu->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $menu->sku }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ strtoupper($menu->category) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ count($menu->components) }} Bahan</td>
                        <td class="px-4 py-3 text-nowrap text-orange-600 font-bold">{{ rp_format($menu->calculateHppDynamic()) }}</td>
                        <td class="px-4 py-3 text-nowrap text-green-600 font-bold">{{ rp_format($menu->sell_price) }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $menu->is_active ? 'checked' : '' }}
                                    onchange="toggleMenuStatus(this, '{{ route('management.purchasing.menu.single.update', $menu) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('management.purchasing.menu.single.update', $menu) }}"
                                    @click="$dispatch('open-edit-menu', {
                                                menu: @js(
                                                    $menu->load('components.componentable')
                                                ),
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
        @if($menus->hasPages())
            <div class="px-5 py-4 border-t border-gray-200">
                {{ $menus->links() }}
            </div>
        @endif
    </div>
@endsection

<x-modal id="modal-import-menu-single" title="Import Menu" size="md">
    <form method="POST" action="{{ route('management.purchasing.menu.single.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="p-6">
            <div class="space-y-6">
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i class="fa fa-info-circle text-blue-600 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Panduan Import:</p>
                            <ul class="list-disc list-inside space-y-1 text-blue-700">
                                <li>Gunakan format <b>.xlsx</b> atau <b>.csv</b></li>
                                <li>Kolom wajib: <span class="font-medium bg-blue-100 px-1 rounded">Nama Menu</span>, <span class="font-medium bg-blue-100 px-1 rounded">SKU</span>, <span class="font-medium bg-blue-100 px-1 rounded">Kategori</span>, <span class="font-medium bg-blue-100 px-1 rounded">Harga Jual</span>, <span class="font-medium bg-blue-100 px-1 rounded">Nama Bahan</span>, <span class="font-medium bg-blue-100 px-1 rounded">Tipe Bahan</span>, <span class="font-medium bg-blue-100 px-1 rounded">Qty</span></li>
                                <li>Satu menu bisa <b>banyak baris</b> (banyak komponen)</li>
                                <li>Import akan <b>update</b> menu jika SKU sudah ada</li>
                                <li>Kategori bebas (contoh: makanan, minuman, snack). Tipe Bahan: <b>raw</b> / <b>semi</b> / <b>finished</b></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Step 1: Download Template</label>
                    <a href="{{ route('management.purchasing.menu.single.download-template') }}" target="_blank"
                        class="flex items-center justify-center gap-2 w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-600 hover:border-orange-500 hover:text-orange-600 hover:bg-orange-50 transition cursor-pointer group">
                        <i class="fa fa-file-excel text-green-600 text-lg group-hover:scale-110 transition"></i>
                        <span class="font-medium">Download Format.xlsx</span>
                    </a>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Step 2: Upload File</label>
                    <input
                        type="file"
                        name="file"
                        required
                        accept=".csv, .xlsx, .xls"
                        class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2.5 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-orange-50 file:text-orange-700
                            hover:file:bg-orange-100
                            cursor-pointer border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                    />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-xl">
            <button
                type="button"
                @click="$dispatch('close-modal')"
                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-100 transition">
                Batal
            </button>
            <button
                type="submit"
                class="px-5 py-2.5 bg-orange-600 text-white rounded-xl font-medium hover:bg-orange-700 shadow-lg shadow-orange-200 transition">
                <i class="fa fa-upload mr-2"></i>
                Mulai Import
            </button>
        </div>
    </form>
</x-modal>
<x-modal id="modal-form-single" title="Tambah Menu" icon="fa-plus" size="7xl">
    <form method="POST" action="{{ route('management.purchasing.menu.single.store') }}"
          x-data="menuForm(@js($ingredients))"
          class="flex flex-col max-h-[60vh] overflow-hidden"
    >
        @csrf

        <div class="p-5 space-y-5 overflow-y-auto shrink">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    {{-- INFO MENU --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama</label>
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
                                    id="selectCategory"
                                    class="select2 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    data-placeholder="Pilih atau ketik kategori">
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ strtoupper($category) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- PRICE SECTION --}}
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
                <div>
                    {{-- MULTI KOMPONEN --}}
                    <div class="border rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-800">Resep / Bahan Jadi</p>
                                <p class="text-xs text-gray-500">Bisa tambah lebih dari 1 komponen + qty per komponen</p>
                            </div>

                            <button type="button"
                                    @click="addComponent()"
                                    class="px-3 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-500">
                                + Tambah Komponen
                            </button>
                        </div>

                        <div class="relative overflow-y-auto p-4 space-y-3" style="max-height: 250px;">
                            <template x-for="(row, idx) in components" :key="row.key">
                                <div class="grid grid-cols-12 gap-3 items-center border rounded-lg p-3">
                                    {{-- SELECT INGREDIENT --}}
                                    <div class="col-span-12 md:col-span-6">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Komponen</label>
                                        <select class="w-full rounded-lg border border-gray-300 px-3 py-2"
                                                x-model="row.ingredient_id"
                                                @change="onPickIngredient(idx)">
                                            <option value="">Pilih komponen...</option>
                                            <template x-for="it in ingredients" :key="it.id">
                                                <option :value="it.id"
                                                        x-text="`${it.name} (${it.unit?.symbol ?? ''})`"></option>
                                            </template>
                                        </select>

                                        <p class="text-xs mt-1"
                                           x-show="row.ingredient"
                                           :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">
                                            Unit cost:
                                            <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>
                                        </p>
                                    </div>

                                    {{-- QTY --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Qty</label>
                                        <input type="number" min="0.01" step="0.01"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right"
                                               x-model.number="row.qty"
                                               @input="recalc()">
                                    </div>

                                    {{-- SUBTOTAL HPP --}}
                                    <div class="col-span-6 md:col-span-3">
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sub HPP</label>
                                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right">
                                            <span class="font-semibold text-gray-800" x-text="formatMoney(row.sub_hpp)"></span>
                                        </div>
                                    </div>

                                    {{-- REMOVE --}}
                                    <div class="col-span-12 md:col-span-1 flex items-center justify-center">
                                        <button type="button"
                                                @click="removeComponent(idx)"
                                                class="w-9 h-9 flex items-center justify-center rounded-full border border-red-200 text-red-500 hover:bg-red-500 hover:text-white transition"
                                                title="Hapus">
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>

                                    {{-- hidden fields for submit --}}
                                    <input type="hidden" :name="`components[${idx}][recipe_id]`" :value="row.ingredient_id">
                                    <input type="hidden" :name="`components[${idx}][qty]`" :value="row.qty">
                                </div>
                            </template>

                            <div x-show="components.length === 0" class="text-sm text-gray-400 text-center py-8">
                                Belum ada komponen.
                            </div>
                        </div>
                    </div>
                </div>

                <input name="type" type="hidden" value="single">
            </div>
        </div>

        <div class="flex justify-end gap-3 px-5 py-4 border-t bg-gray-50 shrink-0">
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
    x-data="menuEditForm(@js($ingredients))"
    x-show="open"
    @open-edit-menu.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open=false"></div>

    <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-xl border flex flex-col max-h-[60vh]">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-4 border-b shrink-0 bg-white rounded-t-xl z-10">
            <h3 class="font-semibold text-lg">Edit Menu</h3>
            <button @click="open=false"><i class="fa fa-times"></i></button>
        </div>

        <form
            method="POST"
            :action="action"
            x-data="menuForm(@js($ingredients))"
            @open-edit-menu.window="fill($event.detail)"
            class="flex flex-col shrink overflow-hidden"
        >
            @csrf

            <div class="p-5 space-y-5 overflow-y-auto shrink">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        {{-- INFO MENU --}}
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
                                        id="selectCategoryEdit"
                                        class="select2 w-full rounded-lg border border-gray-300 px-3 py-2"
                                        data-placeholder="Pilih atau ketik kategori">
                                    <option value="">Pilih kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}">{{ strtoupper($category) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- PRICE SECTION --}}
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

                    {{-- MULTI KOMPONEN --}}
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

                            <div class="relative overflow-y-auto p-4 space-y-3" style="max-height: 250px;">
                                <template x-for="(row, idx) in components" :key="row.key">
                                    <div class="grid grid-cols-12 gap-3 items-center border rounded-lg p-3">
                                        {{-- SELECT INGREDIENT --}}
                                        <div class="col-span-12 md:col-span-6">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Komponen</label>
                                            <select class="w-full rounded-lg border border-gray-300 px-3 py-2"
                                                    x-model="row.ingredient_id"
                                                    @change="onPickIngredient(idx)">
                                                <option value="">Pilih komponen...</option>
                                                <template x-for="it in ingredients" :key="it.id">
                                                    <option
                                                        :value="String(it.id)"
                                                        :selected="row.ingredient_id === String(it.id)"
                                                        x-text="`${it.name} (${it.unit?.symbol ?? ''})`">
                                                    </option>
                                                </template>
                                            </select>

                                            <p class="text-xs mt-1"
                                               x-show="row.ingredient"
                                               :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">
                                                Unit cost:
                                                <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>
                                            </p>
                                        </div>

                                        {{-- QTY --}}
                                        <div class="col-span-6 md:col-span-2">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Qty</label>
                                            <input type="number" min="0.01" step="0.01"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-right"
                                                   x-model.number="row.qty"
                                                   @input="recalc()">
                                        </div>

                                        {{-- SUB HPP --}}
                                        <div class="col-span-6 md:col-span-3">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1">Sub HPP</label>
                                            <div class="w-full rounded-lg border bg-gray-50 px-3 py-2 text-right">
                                                <span x-text="formatMoney(row.sub_hpp)"></span>
                                            </div>
                                        </div>

                                        {{-- REMOVE --}}
                                        <div class="col-span-12 md:col-span-1 flex justify-center">
                                            <button type="button"
                                                    @click="removeComponent(idx)"
                                                    class="w-9 h-9 rounded-full border border-red-200 text-red-500 hover:bg-red-500 hover:text-white">
                                                <i class="fa fa-trash text-xs"></i>
                                            </button>
                                        </div>

                                        <input type="hidden" :name="`components[${idx}][recipe_id]`" :value="row.ingredient_id">
                                        <input type="hidden" :name="`components[${idx}][qty]`" :value="row.qty">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="type" value="single">
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
        document.addEventListener('open-modal', async (e) => {
            if (e.detail !== 'modal-form-single') return;

            const selectCategory = $('#selectCategory');

            selectCategory.select2({
                placeholder: 'Pilih atau ketik kategori',
                tags: true,
                width: '100%'
            });
        });

        document.addEventListener('open-edit-menu', async (e) => {
            const selectCategory = $('#selectCategoryEdit');

            selectCategory.select2({
                placeholder: 'Pilih atau ketik kategori',
                tags: true,
                width: '100%'
            });
        });

        function toggleMenuStatus(el, url) {
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

        function menuForm(ingredientsRaw) {
            const ingredients = (ingredientsRaw || []).map(r => ({
                id: r.id,
                name: r.name,

                unit: r.base_unit
                    ? {
                        id: r.base_unit.id,
                        name: r.base_unit.name,
                        symbol: r.base_unit.symbol,
                    }
                    : null,

                stock: Number(r.ingredient_stock?.qty || 0),
                avg_cost: Number(r.ingredient_stock?.avg_cost || 0),

                quantity: Number(r.quantity || 1),

                items: (r.items || []).map(it => ({
                    ingredient_id: it.ingredient_id,
                    name: it.ingredient?.name ?? '-',
                    qty: Number(it.quantity || 0),

                    // 🔥 sekarang ambil dari stock snapshot
                    avg_cost: Number(it.ingredient?.ingredient_stock?.avg_cost || 0),
                    stock: Number(it.ingredient?.ingredient_stock?.qty || 0),

                    unit: it.unit
                        ? {
                            id: it.unit.id,
                            name: it.unit.name,
                            symbol: it.unit.symbol,
                        }
                        : null,
                })),
            }));

            return {
                ingredients,
                components: [],
                totalHpp: 0,

                init() {
                    this.addComponent();
                    this.recalc();
                },

                addComponent() {
                    this.components.push({
                        key: crypto.randomUUID
                            ? crypto.randomUUID()
                            : (Date.now() + Math.random()),
                        ingredient_id: '',
                        ingredient: null,
                        qty: 1,
                        unit_cost: 0,
                        sub_hpp: 0,
                    });
                },

                removeComponent(idx) {
                    this.components.splice(idx, 1);
                    this.recalc();
                },

                onPickIngredient(idx) {
                    const row = this.components[idx];

                    const picked = this.ingredients.find(
                        r => String(r.id) === String(row.ingredient_id)
                    );

                    row.ingredient = picked || null;

                    if (!picked) {
                        row.unit_cost = 0;
                        this.recalc();
                        return;
                    }

                    // 🔥 kalau tidak punya komponen (bukan semi / bukan resep)
                    if (!picked.items || !picked.items.length) {
                        row.unit_cost = Number(picked.avg_cost || picked.ingredient_stock?.avg_cost || 0);
                        this.recalc();
                        return;
                    }

                    // 🔥 hitung total cost dari komponen
                    let totalRecipeCost = 0;

                    picked.items.forEach(it => {
                        const avgCost = Number(it.ingredient?.ingredient_stock?.avg_cost || 0);
                        const qty = Number(it.qty || 0);

                        totalRecipeCost += qty * avgCost;
                    });

                    row.unit_cost = picked.quantity > 0
                        ? totalRecipeCost / Number(picked.quantity)
                        : 0;

                    this.recalc();
                },

                recalc() {
                    let total = 0;

                    this.components.forEach(row => {
                        const qty = Number(row.qty || 0);
                        const unitCost = Number(row.unit_cost || 0);

                        row.sub_hpp = qty * unitCost;
                        total += row.sub_hpp;
                    });

                    this.totalHpp = total;
                },

                formatMoney(n) {
                    const num = Number(n || 0);
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(num);
                },
            };
        }

        function menuEditForm(ingredientsRaw) {
            const ingredients = ingredientsRaw.map(i => ({
                id: i.id,
                name: i.name,
                avg_cost: Number(i.avg_cost || 0),
            }));

            return {
                open: false,
                action: '',
                ingredients,
                components: [],
                totalHpp: 0,

                form: {
                    name: '',
                    sku: '',
                    category: '',
                    barcode: '',
                    sell_price: 0,
                },

                fill({ menu, action }) {
                    console.log('MENU COMPONENTS:', menu.components);

                    this.open = true;
                    this.action = action;

                    this.form = {
                        name: menu.name,
                        sku: menu.sku,
                        category: menu.category,
                        barcode: menu.barcode,
                        sell_price: menu.sell_price,
                    };

                    this.$nextTick(() => {
                        this.components = menu.components.map(c => ({
                            key: crypto.randomUUID
                                ? crypto.randomUUID()
                                : (Date.now() + Math.random()),
                            ingredient_id: String(c.componentable_id),
                            qty: Number(c.qty),
                            ingredient: null,
                            unit_cost: 0,
                            sub_hpp: 0,
                        }));

                        // 🔥 PAKSA HITUNG ULANG UNIT COST
                        this.components.forEach((_, idx) => {
                            this.onPickIngredient(idx);
                        });

                        this.recalc();
                    });
                },

                addComponent() {
                    this.components.push({
                        key: crypto.randomUUID(),
                        ingredient_id: '',
                        qty: 1,
                        unit_cost: 0,
                        sub_hpp: 0,
                    });
                },

                removeComponent(idx) {
                    this.components.splice(idx, 1);
                    this.recalc();
                },

                onPickIngredient(idx) {
                    const row = this.components[idx];
                    const ing = this.ingredients.find(i => i.id == row.ingredient_id);
                    row.unit_cost = ing ? ing.avg_cost : 0;
                    this.recalc();
                },

                recalc() {
                    this.totalHpp = 0;
                    this.components.forEach(r => {
                        r.sub_hpp = r.qty * r.unit_cost;
                        this.totalHpp += r.sub_hpp;
                    });
                },

                formatMoney(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(n || 0);
                }
            };
        }

        function numberFormat(num) {
            return Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
@endpush
