@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'ingredient-receipt',
    'activeSubmenu' => 'receipt'
])
@section('title', 'Bahan & Resep')

@section('content')
<div x-data="{ tab: 'menu' }">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Resep</h2>
        <div class="flex items-center gap-2">

            <button
                @click="$dispatch('open-modal', 'modal-import-semi')"
                x-show="tab === 'semi'"
                class="bg-green-600 text-white px-4 py-2 rounded-xl shadow hover:bg-green-500 transition flex items-center gap-2 hover:cursor-pointer">
                <i class="fa fa-file-import"></i>
                Import
            </button>

            <div x-show="tab === 'menu'">
                <div class="flex items-center space-x-3">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-menu')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
                </div>
            </div>

            <div x-show="tab === 'semi'">
                <div class="flex items-center space-x-3">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-semi')"
                        class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                        <i class="fa fa-plus"></i>
                        Tambah
                    </button>
                </div>
            </div>

        </div>
    </div>

    <div class="flex shadow-lg border border-gray-200 bg-white rounded-xl p-2">
        <button
            @click="tab = 'menu'"
            :class="tab === 'menu'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
            class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
            Menu
        </button>

        <button
            @click="tab = 'semi'"
            :class="tab === 'semi'
                ? 'bg-orange-600 text-white'
                : 'border-transparent text-gray-500 hover:text-orange-600'"
            class="flex-1 text-center py-3 rounded-xl text-sm font-medium transition">
            ½ Jadi
        </button>
    </div>

    <div class="mt-4">
        <div x-show="tab === 'menu'">
            @include('management::recipe.components.menu')
        </div>
        <div x-show="tab === 'semi'">
            @include('management::recipe.components.semi')
        </div>
    </div>

</div>
@endsection

@push('js')
    <script>
        function toggleRecipeStatus(el, url) {
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

        function recipeComponent({ ingredients = [], units = [] }) {
            return {
                ingredients,
                units,

                selectedIngredient: '',
                qty: '',
                unitId: '',
                components: [],

                addComponent() {
                    this.components.push({
                        key: Date.now() + Math.random(), // biar aman buat :key
                        ingredient_id: '',
                        ingredient: null,
                        available_units: [],
                        unit_id: '',
                        unit_cost: 0,
                        qty: 1,
                        sub_hpp: 0,
                    });
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

                    // set default unit = base_unit kalau ada
                    row.unit_id = units.length ? units[0].id : null;

                    // asumsi ingredient punya unit_cost / hpp
                    row.unit_cost = Number(ing.avg_cost || ing.hpp || 0);

                    this.recalcRow(idx);
                },

                onChangeUnit(idx) {
                    const row = this.components[idx];
                    if (!row.ingredient || !row.unit_id) return;

                    this.recalc();
                },

                recalcRow(idx) {
                    const row = this.components[idx];
                    if (!row.ingredient) return;

                    const baseCost = Number(row.ingredient.avg_cost || 0);

                    // cari unit yg dipilih
                    const unit = (row.available_units || []).find(u => u.id == row.unit_id);

                    const multiplier = unit ? Number(unit.multiplier || 1) : 1;

                    row.unit_cost = baseCost * multiplier;

                    const qty = Number(row.qty || 0);
                    row.sub_hpp = row.unit_cost * qty;

                    this.recalc(); // kalau ada total HPP global
                },

                calcTotalHpp() {
                    return this.components.reduce((sum, row) => {
                        return sum + Number(row.sub_hpp || 0);
                    }, 0);
                },

                recalc() {
                    this.components.forEach(row => {
                        const qty = Number(row.qty || 0);
                        const cost = Number(row.unit_cost || 0);

                        row.sub_hpp = qty * cost;
                    });
                },

                removeComponent(index) {
                    this.components.splice(index, 1);
                },

                formatMoney(n) {
                    const num = Number(n || 0);
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(num);
                },
            }
        }
    </script>
@endpush
