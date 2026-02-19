@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'purchasing',
    'activeSubmenu' => 'production',
])
@section('title', 'Produksi')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Produksi</h2>

        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form-production')"
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
                <th class="px-4 py-3">ID Produksi</th>
                <th class="px-4 py-3">Nama Bahan</th>
                <th class="px-4 py-3">Stok</th>
                <th class="px-4 py-3">Tanggal</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($batchs as $key => $batch)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $batch->code }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $batch->ingredient->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ number_format($batch->qty_in, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ parse_date_time($batch->created_at) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<x-modal id="modal-form-production" title="Tambah Produksi" icon="fa-plus" size="5xl">
    <form method="POST" action="{{ route('management.purchasing.production.store') }}"
          x-data="productionForm(@js($ingredients))">
        @csrf

        <div class="p-5 grid grid-cols-1 md:grid-cols-[1fr_2fr] gap-6">
            <div class="space-y-4">
                <!-- PILIH PRODUK -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        Bahan Diproduksi
                    </label>
                    <select
                        x-model="selectedIngredientId"
                        @change="loadRecipe"
                        name="ingredient_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Pilih bahan...</option>
                        <template x-for="item in ingredients" :key="item.id">
                            <option :value="item.id" x-text="`${item.name} (${item.recipe.quantity} ${item.base_unit.name})`"></option>
                        </template>
                    </select>
                </div>

                <!-- QTY PRODUKSI -->
                <div x-show="recipe">
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        Qty Produksi
                    </label>
                    <input
                        type="number"
                        min="0.01"
                        step="0.01"
                        x-model.number="productionQty"
                        @input="recalcQty"
                        name="production_qty"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2">
                </div>

                <div class="flex justify-between items-center px-4 py-3 border-t">
                    <span class="font-semibold text-gray-700">Total HPP Produksi</span>
                    <span class="font-bold text-orange-600"
                          x-text="formatMoney(calcTotalHpp())"></span>
                </div>
            </div>

            <div class="space-y-4">
                <!-- RECIPE ITEMS -->
                <div x-show="recipe" class="border rounded-lg divide-y">
                    <div class="px-4 py-2 bg-gray-100 font-semibold text-sm">
                        Bahan yang Dibutuhkan
                    </div>

                    <template x-for="(item, index) in recipeItems" :key="index">
                        <div class="flex items-center justify-between gap-4 px-4 py-3 border-b hover:bg-gray-50 transition">

                            <!-- LEFT INFO -->
                            <div class="flex-1 min-w-0 space-y-1">
                                <!-- NAMA -->
                                <p class="font-semibold text-gray-800 truncate" x-text="item.name"></p>

                                <!-- BASE QTY -->
                                <p class="text-xs text-gray-500">
                                    Resep:
                                    <span class="font-medium" x-text="item.base_qty"></span>
                                    <span x-text="item.unit"></span>
                                    / 1 produksi
                                </p>

                                <!-- META ROW -->
                                <div class="flex flex-wrap items-center gap-4 text-xs">
                                    <!-- STOK -->
                                    <div
                                        :class="item.stock_remaining < item.qty ? 'text-red-600' : 'text-green-600'"
                                        class="flex items-center gap-1"
                                    >
                                        <i class="fa fa-box text-[10px]"></i>
                                        <span>Stok:</span>
                                        <span class="font-semibold" x-text="item.stock_remaining"></span>
                                        <span x-text="item.unit"></span>
                                    </div>

                                    <!-- HPP -->
                                    <div class="text-gray-500 flex items-center gap-1">
                                        <i class="fa fa-tag text-[10px]"></i>
                                        <span>HPP:</span>
                                        <span class="font-medium" x-text="formatMoney(item.unit_cost)"></span>
                                        <span>/ unit</span>
                                    </div>

                                    <!-- SUB HPP -->
                                    <div class="text-orange-600 flex items-center gap-1">
                                        <i class="fa fa-calculator text-[10px]"></i>
                                        <span>Sub:</span>
                                        <span class="font-semibold" x-text="formatMoney(item.sub_hpp)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT ACTION -->
                            <div class="flex flex-col items-end gap-1">
                                <label class="text-[11px] text-gray-400">Qty pakai</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="w-24 rounded-lg border px-2 py-1 text-right focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    x-model.number="item.qty"
                                    @input="item.manual = true; recalcQty()"
                                >
                            </div>
                        </div>
                    </template>
                </div>

                <div class="bg-gray-50 border rounded-lg p-3">
                    <p class="text-sm text-gray-600">Total Hasil Produksi</p>

                    <p class="text-xl font-bold text-orange-600">
                        <span x-text="productionTotal"></span>
                        <span x-text="productionUnit"></span>
                    </p>
                </div>
            </div>

            <!-- HIDDEN INPUT -->
            <input type="hidden" name="components"
                   :value="JSON.stringify(recipeItems)">
        </div>

        <!-- FOOTER -->
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
                Produksi
            </button>
        </div>
    </form>
</x-modal>


@push('js')
    <script>
        function productionForm(ingredients) {
            return {
                ingredients,
                selectedIngredientId: '',
                recipe: null,
                recipeItems: [],
                productionQty: 1,
                productionTotal: 0,
                productionUnit: null,

                loadRecipe() {
                    const ingredient = this.ingredients.find(
                        i => i.id == this.selectedIngredientId
                    );

                    if (!ingredient || !ingredient.recipe) {
                        this.recipe = null;
                        this.recipeItems = [];
                        return;
                    }

                    this.recipe = ingredient.recipe;
                    this.productionTotal = this.recipe.quantity * 1;
                    this.productionUnit = ingredient.base_unit.name;

                    console.log(ingredient);

                    this.recipeItems = this.recipe.items.map(item => {
                        const ing = item.ingredient;

                        const unitCost = Number(ing?.avg_cost || 0);
                        const stock = Number(ing?.total_stock || 0);
                        const baseQty = Number(item.quantity || 0);

                        return {
                            ingredient_id: item.ingredient_id,
                            name: ing.name,
                            unit: item.unit.symbol,
                            base_qty: baseQty,
                            qty: baseQty * this.productionQty,
                            stock_remaining: stock,
                            unit_cost: unitCost,
                            sub_hpp: baseQty * this.productionQty * unitCost,
                            manual: false
                        };
                    });

                    this.recalcQty();
                },

                recalcQty() {
                    this.productionTotal = this.recipe.quantity * this.productionQty;

                    this.recipeItems.forEach(item => {
                        if (!item.manual) {
                            item.qty = item.base_qty * this.productionQty;
                        }

                        const qty = Number(item.qty || 0);
                        const cost = Number(item.unit_cost || 0);
                        item.sub_hpp = qty * cost;
                    });
                },

                calcTotalHpp() {
                    return this.recipeItems.reduce((sum, i) => {
                        return sum + Number(i.sub_hpp || 0);
                    }, 0);
                },

                formatMoney(n) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(n || 0);
                },

                markManual(index) {
                    this.recipeItems[index].manual = true;
                }
            };
        }

        function numberFormat(num) {
            return Number(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    </script>
@endpush
