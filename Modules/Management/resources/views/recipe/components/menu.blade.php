<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nama Bahan</th>
            <th class="px-4 py-3">Qty</th>
            <th class="px-4 py-3">Satuan</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($menus as $key => $menu)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>

                <td class="px-4 py-3 text-nowrap">{{ $menu->name }}
                    <span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-700">
                                {{ count($menu->items) }} Bahan
                            </span>
                </td>
                <td class="px-4 py-3 text-nowrap">{{ $menu->quantity }}</td>
                <td class="px-4 py-3 text-nowrap">{{ @$menu->unit->name }}</td>
                <td class="px-4 py-3">
                    <label class="inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            class="sr-only peer"
                            {{ $menu->is_active ? 'checked' : '' }}
                            onchange="toggleRecipeStatus(this, '{{ route('management.recipe.update', $menu) }}')"
                        >
                        <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2">
                        <button
                            type="button"
                            data-route="{{ route('management.recipe.update', $menu) }}"
                            @click="$dispatch('open-edit-menu', {
                                                recipe: {
                                                        id: {{ $menu->id }},
                                                        name: @js($menu->name),
                                                        min_stock: {{ $menu->quantity }},
                                                        unit_id: @js($menu->unit_id),
                                                        outlet_id: {{ $menu->outlet_id }},
                                                        quantity: {{ $menu->quantity }},
                                                        components: @js($menu->recipe_components)
                                                    },
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

<x-modal id="modal-form-menu" title="Tambah Menu" icon="fa-plus" size="7xl">
    <form method="POST" action="{{ route('management.recipe.store') }}">
        @csrf
        <div class="p-5">
            <div
                x-data="recipeComponent({
                            ingredients: @js($menuIngredients),
                            units: @js($units)
                        })"
                class="grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-6"
            >
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama bahan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Qty</label>
                            <input type="number" step="0.1" name="quantity" value="{{ old('quantity') }}" required placeholder="Hasil olahan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Satuan</label>
                            <div class="relative">
                                <select name="unit_id" class="w-full select2 text-gray-300 appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" @selected((old('unit_id') ?? '') === $unit->id)>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-3 mt-3">
                        <span class="font-semibold text-gray-700">Total HPP Menu</span>
                        <span class="font-bold text-orange-600 text-lg"
                              x-text="formatMoney(calcTotalHpp())"></span>
                    </div>
                </div>
                <div class="border rounded-xl overflow-hidden">
                    <!-- HEADER -->
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

                    <!-- LIST -->
                    <div class="relative max-h-[600px] overflow-y-auto p-4 space-y-3 bg-white">
                        <template x-for="(row, idx) in components" :key="row.key ?? idx">
                            <div class="grid grid-cols-12 gap-3 items-start border rounded-lg p-3">

                                <!-- INGREDIENT -->
                                <div class="col-span-12 md:col-span-5">
                                    <select
                                        class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2"
                                        x-model.number="row.ingredient_id"
                                        @change="onPickIngredient(idx)"
                                    >
                                        <option value="">Pilih komponen...</option>
                                        <template x-for="it in ingredients" :key="it.id">
                                            <option :value="it.id"
                                                    x-text="`${it.name} (${it.base_unit?.symbol ?? ''})`"></option>
                                        </template>
                                    </select>

                                    <!-- UNIT COST (RESERVED SPACE) -->
                                    <div class="text-xs mt-1 min-h-[1.25rem] leading-5"
                                         :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">
                                        <template x-if="row.ingredient">
                                            <span>
                                                Unit cost:
                                                <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>
                                            </span>
                                        </template>
                                        <template x-if="!row.ingredient">
                                            <span>&nbsp;</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- QTY -->
                                <div class="col-span-4 md:col-span-2 flex flex-col">
                                    <input
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        placeholder="Qty"
                                        class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2 text-right"
                                        x-model.number="row.qty"
                                        @input="recalcRow(idx)"
                                    />
                                    <!-- spacer biar sejajar dengan unit cost -->
                                    <div class="min-h-[1.25rem] mt-1"></div>
                                </div>

                                <!-- UNIT -->
                                <div class="col-span-4 md:col-span-2 flex flex-col">
                                    <select
                                        class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2"
                                        x-model="row.unit_id"
                                        @change="recalcRow(idx)"
                                    >
                                        <option value="">Unit</option>
                                        <template x-for="u in row.available_units || []" :key="u.id">
                                            <option :value="u.id" x-text="u.symbol || u.name"></option>
                                        </template>
                                    </select>
                                    <!-- spacer -->
                                    <div class="min-h-[1.25rem] mt-1"></div>
                                </div>

                                <!-- SUBTOTAL -->
                                <div class="col-span-4 md:col-span-2 flex flex-col">
                                    <div
                                        class="w-full h-[42px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right flex items-center justify-end"
                                    >
                                    <span class="font-semibold text-gray-800"
                                          x-text="formatMoney(row.sub_hpp)"></span>
                                    </div>
                                    <!-- spacer -->
                                    <div class="min-h-[1.25rem] mt-1"></div>
                                </div>

                                <!-- REMOVE -->
                                <div class="col-span-12 md:col-span-1 flex items-start justify-center pt-[4px]">
                                    <button
                                        type="button"
                                        @click="removeComponent(idx)"
                                        class="w-10 h-10 flex items-center justify-center rounded-full
                                               border border-red-200 text-red-500
                                               hover:bg-red-500 hover:text-white transition"
                                        title="Hapus"
                                    >
                                        <i class="fa fa-trash text-xs"></i>
                                    </button>
                                </div>

                                <!-- hidden fields -->
                                <input type="hidden" :name="`components[${idx}][ingredient_id]`" :value="row.ingredient_id">
                                <input type="hidden" :name="`components[${idx}][qty]`" :value="row.qty">
                                <input type="hidden" :name="`components[${idx}][unit_id]`" :value="row.unit_id">

                            </div>
                        </template>

                        <div x-show="components.length === 0"
                             class="text-sm text-gray-400 text-center py-8">
                            Belum ada komponen.
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-5 py-4">
            <button
                type="button"
                @click="$dispatch('close-modal')"
                class="px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400">
                Batal
            </button>

            <button
                type="submit"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                Simpan
            </button>
        </div>
    </form>
</x-modal>

<div
    x-data="editMenuModal({
            ingredients: @js($menuIngredients),
            units: @js($units)
        })"
    x-show="open"
    @open-edit-menu.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-7xl bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-semibold text-lg">Edit Resep</h3>
            <button @click="open = false"><i class="fa fa-times"></i></button>
        </div>

        <form :action="action" method="POST">
            @csrf

            <div class="p-5 grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-6">

                <!-- LEFT -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" x-model="form.name"
                               class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Qty</label>
                            <input type="number" step="0.1" name="quantity" x-model="form.quantity"  required placeholder="Hasil olahan" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Satuan</label>
                            <div class="relative">
                                <select name="unit_id" x-model="form.base_unit_id"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                                    <template x-for="u in units">
                                        <option :value="u.id" :selected="u.id == form.unit_id" x-text="u.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-3 mt-3">
                        <span class="font-semibold text-gray-700">Total HPP Menu</span>
                        <span class="font-bold text-orange-600 text-lg"
                              x-text="formatMoney(calcTotalHpp())"></span>
                    </div>
                </div>

                <!-- RIGHT (RESEP) -->
                <div class="space-y-3">
                    <div class="border rounded-xl overflow-hidden">
                        <!-- HEADER -->
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

                        <!-- LIST -->
                        <div class="relative max-h-[600px] overflow-y-auto p-4 space-y-3 bg-white">
                            <template x-for="(row, idx) in components" :key="row.ingredient_id ?? idx">
                                <div class="grid grid-cols-12 gap-3 items-start border rounded-lg p-3">

                                    <!-- INGREDIENT -->
                                    <div class="col-span-12 md:col-span-5">
                                        <select
                                            class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2"
                                            @change="row.ingredient_id = $event.target.value; onPickIngredient(idx)"
                                        >
                                            <option value="">Pilih komponen...</option>

                                            <template x-for="it in ingredients" :key="it.id">
                                                <option
                                                    :value="String(it.id)"
                                                    :selected="String(row.ingredient_id) === String(it.id)"
                                                    x-text="`${it.name} (${it.base_unit?.symbol ?? ''})`"
                                                ></option>
                                            </template>
                                        </select>

                                        <!-- UNIT COST -->
                                        <div class="text-xs mt-1 min-h-[1.25rem] leading-5"
                                             :class="row.unit_cost > 0 ? 'text-gray-500' : 'text-red-500'">
                                            <template x-if="row.ingredient">
                                                <span>
                                                    Unit cost:
                                                    <span class="font-semibold" x-text="formatMoney(row.unit_cost)"></span>
                                                </span>
                                            </template>
                                            <template x-if="!row.ingredient">
                                                <span>&nbsp;</span>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- QTY -->
                                    <div class="col-span-4 md:col-span-2 flex flex-col">
                                        <input
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            placeholder="Qty"
                                            class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2 text-right"
                                            x-model.number="row.qty"
                                            @input="recalcRow(idx)"
                                        />
                                        <div class="min-h-[1.25rem] mt-1"></div>
                                    </div>

                                    <!-- UNIT -->
                                    <div class="col-span-4 md:col-span-2 flex flex-col">
                                        <select
                                            class="w-full h-[42px] rounded-lg border border-gray-300 px-3 py-2"
                                            @change="components[idx].unit_id = $event.target.value; recalcRow(idx)"
                                        >
                                            <option value="">Unit</option>

                                            <template x-for="u in components[idx].available_units || []" :key="u.id">
                                                <option
                                                    :value="String(u.id)"
                                                    :selected="String(components[idx].unit_id) === String(u.id)"
                                                    x-text="u.symbol || u.name"
                                                ></option>
                                            </template>
                                        </select>
                                        <div class="min-h-[1.25rem] mt-1"></div>
                                    </div>

                                    <!-- SUBTOTAL -->
                                    <div class="col-span-4 md:col-span-2 flex flex-col">
                                        <div
                                            class="w-full h-[42px] rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-right flex items-center justify-end"
                                        >
                                        <span class="font-semibold text-gray-800"
                                              x-text="formatMoney(row.sub_hpp)"></span>
                                        </div>
                                        <div class="min-h-[1.25rem] mt-1"></div>
                                    </div>

                                    <!-- REMOVE -->
                                    <div class="col-span-12 md:col-span-1 flex items-start justify-center pt-[4px]">
                                        <button
                                            type="button"
                                            @click="removeComponent(idx)"
                                            class="w-10 h-10 flex items-center justify-center rounded-full
                               border border-red-200 text-red-500
                               hover:bg-red-500 hover:text-white transition"
                                            title="Hapus"
                                        >
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>

                                    <!-- hidden fields -->
                                    <input type="hidden" :name="`components[${idx}][ingredient_id]`" :value="row.ingredient_id">
                                    <input type="hidden" :name="`components[${idx}][quantity]`" :value="row.qty">
                                    <input type="hidden" :name="`components[${idx}][unit_id]`" :value="row.unit_id">
                                </div>
                            </template>

                            <div x-show="components.length === 0"
                                 class="text-sm text-gray-400 text-center py-8">
                                Belum ada komponen.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 px-5 py-4 border-t">
                <button type="button" @click="open=false" class="px-4 py-2 border rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</div>

@push('js')
    <script !src="">
        function editMenuModal({ ingredients = [], units = [] }) {
            return {
                open: false,
                action: '',
                ingredients,
                units,

                // komponen resep (row-based)
                components: [],

                form: {
                    name: '',
                    quantity: '',
                    unit_id: '',
                },

                fill(payload) {
                    this.open = true;
                    this.action = payload.action;

                    this.form = {
                        name: payload.recipe.name,
                        quantity: payload.recipe.quantity,
                        unit_id: Number(payload.recipe.unit_id),
                        components: (payload.recipe.components || []).map(c => ({
                            ...c,
                            ingredient_id: Number(c.ingredient_id),
                            unit_id: Number(c.unit_id),
                        }))
                    };

                    this.components = (payload.recipe.components || []).map(c => ({
                        ingredient_id: String(c.ingredient_id), // ⬅️ cukup ini
                        unit_id: String(c.unit_id),
                        ingredient: null,
                        qty: Number(c.quantity || c.qty || 0),
                        unit_cost: 0,
                        available_units: [],
                        sub_hpp: 0,
                    }));

                    this.$nextTick(() => {
                        this.components.forEach((row, idx) => {
                            this.onPickIngredient(idx, true);
                            this.recalcRow(idx);
                        });
                    });
                },

                addComponent() {
                    this.components.push({
                        ingredient_id: '',
                        ingredient: null,
                        qty: 1,
                        unit_id: '',
                        unit_cost: 0,
                        available_units: [],
                        sub_hpp: 0,
                    });
                },

                removeComponent(index) {
                    this.components.splice(index, 1);
                },

                onPickIngredient(idx) {
                    const row = this.components[idx];
                    const ing = this.ingredients.find(i => i.id == row.ingredient_id);
                    if (!ing) return;

                    if (!row.ingredient_id) {
                        row.ingredient = null;
                        row.available_units = [];
                        row.unit_id = '';
                        row.unit_cost = 0;
                        row.sub_hpp = 0;
                        return;
                    }

                    row.ingredient = ing;

                    // gabungin: base_unit + converted_units
                    const units = [];

                    if (ing.base_unit) {
                        units.push({
                            id: ing.base_unit.id,
                            name: ing.base_unit.name,
                            symbol: ing.base_unit.symbol,
                            multiplier: 1
                        });
                    }

                    if (ing.unit_conversions && ing.unit_conversions.length) {
                        ing.unit_conversions.forEach(c => {
                            if (c.to_unit) {
                                units.push({
                                    id: c.to_unit.id,
                                    name: c.to_unit.name,
                                    symbol: c.to_unit.symbol,
                                    multiplier: Number(c.multiplier)
                                });
                            }
                        });
                    }

                    // isi unit list (sesuaikan dengan struktur data lu)
                    row.available_units = units;

                    // kalau unit_id dari backend ADA dan masih valid di available_units → PAKAI ITU
                    if (row.unit_id && units.some(u => String(u.id) === String(row.unit_id))) {
                        // keep existing unit_id
                    } else {
                        // fallback ke base unit
                        row.unit_id = units.length ? String(units[0].id) : '';
                    }

                    // asumsi ingredient punya unit_cost / hpp
                    row.unit_cost = Number(ing.avg_cost || ing.hpp || 0);

                    this.recalcRow(idx);
                },

                onChangeUnit(idx) {
                    const row = this.components[idx];
                    if (!row.ingredient || !row.unit_id) return;

                    // kalau belum ada konversi unit, sementara pakai unit_cost default
                    // nanti bisa ditambah logic: cost = base_cost * conversion_rate
                    this.recalc();
                },

                getMultiplier(row) {
                    if (!row.ingredient) return 1;

                    // kalau unit sama dengan base unit → 1
                    if (row.unit_id == row.ingredient.base_unit_id) return 1;

                    // cari di conversions
                    const conv = (row.ingredient.unit_conversions || []).find(c =>
                        c.to_unit_id == row.unit_id
                    );

                    return conv ? Number(conv.multiplier) : 1;
                },

                recalcRow(idx) {
                    const row = this.components[idx];
                    const qty = Number(row.qty || 0);
                    const unitCost = Number(row.unit_cost || 0);
                    const multiplier = this.getMultiplier(row);

                    row.sub_hpp = qty * unitCost * multiplier;
                },

                calcTotalHpp() {
                    return this.components.reduce((sum, r) => {
                        return sum + Number(r.sub_hpp || 0);
                    }, 0);
                },

                formatMoney(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(n || 0);
                }
            }
        }
    </script>
@endpush
