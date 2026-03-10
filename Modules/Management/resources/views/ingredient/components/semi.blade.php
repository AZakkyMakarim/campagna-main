<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Nama Bahan</th>
                <th class="px-4 py-3">Satuan</th>
                <th class="px-4 py-3">Stok Min</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($semis as $key => $semi)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('semi_page') ?? 1) - 1) * 10) }}</td>

                    <td class="px-4 py-3 text-nowrap">{{ $semi->name }}
                        {{-- <span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-700">--}}
                            {{-- {{ count($semi->recipe->items) }} Bahan--}}
                            {{-- </span>--}}
                    </td>
                    <td class="px-4 py-3 text-nowrap">{{ $semi->baseUnit->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $semi->min_stock }}</td>
                    <td class="px-4 py-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ $semi->is_active ? 'checked' : '' }}
                                onchange="toggleOutletStatus(this, '{{ route('management.ingredient.update', $semi) }}')">
                            <div
                                class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5">
                            </div>
                        </label>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" data-route="{{ route('management.ingredient.update', $semi) }}" @click="$dispatch('open-edit-semi', {
                                                    semi: {
                                                            id: {{ $semi->id }},
                                                            name: @js($semi->name),
                                                            min_stock: {{ $semi->min_stock }},
                                                            base_unit_id: {{ $semi->base_unit_id }},
                                                            outlet_id: {{ $semi->outlet_id }}
                                                        },
                                                    action: $el.dataset.route
                                                })" class="px-3 py-2 bg-yellow-500 text-white rounded">
                                <i class="fa fa-pen"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($semis->hasPages())
        <div class="px-5 py-4 border-t border-gray-200">
            {{ $semis->links() }}
        </div>
    @endif
</div>

<x-modal id="modal-form-semi" title="Tambah Bahan 1/2 Jadi" size="md">
    <form method="POST" action="{{ route('management.ingredient.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Masukkan nama bahan"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Stok Min</label>
                    <input type="number" step="0.1" name="min_stock" value="{{ old('min_stock') }}" required
                        placeholder="Masukkan stok min"
                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Satuan
                    </label>
                    <div class="relative">
                        <select name="base_unit_id"
                            class="w-full select2 appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((old('base_unit_id') ?? '') === $unit->id)>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 px-5 py-4">
            <button type="button" @click="$dispatch('close-modal')"
                class="px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400">
                Batal
            </button>

            <button type="submit"
                class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                Simpan
            </button>
        </div>

        <input type="hidden" name="type" value="semi">
    </form>
</x-modal>

<div x-data="editSemiModal({
        ingredients: @js($raws),
        units: @js($units)
    })" x-show="open" @open-edit-semi.window="fill($event.detail)" x-transition x-cloak
    class="fixed inset-0 flex items-center justify-center z-50">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-semibold text-lg">Edit Bahan 1/2 Jadi</h3>
            <button @click="open = false"><i class="fa fa-times"></i></button>
        </div>

        <form :action="action" method="POST">
            @csrf

            <div class="p-5 text-gray-300">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" x-model="form.name"
                            class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Stok Min</label>
                        <input type="number" step="0.1" name="min_stock" x-model="form.min_stock"
                            class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Satuan
                        </label>
                        <div class="relative">
                            <select name="base_unit_id" x-model="form.base_unit_id"
                                class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((old('base_unit_id') ?? '') === $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 px-5 py-4">
                <button type="button" @click="$dispatch('close-modal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400">
                    Batal
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
    <script>
        function toggleOutletStatus(el, url) {
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

        function editSemiModal({ ingredients = [], units = [] }) {
            return {
                open: false,
                action: '',
                ingredients,
                units,
                form: {
                    name: '',
                    min_stock: '',
                    base_unit_id: '',
                    outlet_id: '',
                    components: []
                },
                selectedIngredient: '',
                qty: '',
                unitId: '',

                fill(payload) {
                    this.open = true;
                    this.action = payload.action;

                    this.form = {
                        name: payload.semi.name,
                        min_stock: payload.semi.min_stock,
                        base_unit_id: payload.semi.base_unit_id,
                        outlet_id: payload.semi.outlet_id,
                        quantity: payload.semi.quantity,
                    };
                },
            }
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
                    if (!this.selectedIngredient || !this.qty || !this.unitId) {
                        alert('Lengkapi komponen, qty, dan unit');
                        return;
                    }

                    // cegah ingredient dobel
                    if (this.components.find(i => i.ingredient_id == this.selectedIngredient)) {
                        alert('Komponen sudah ditambahkan');
                        return;
                    }

                    const ingredient = this.ingredients.find(
                        i => i.id == this.selectedIngredient
                    );
                    const unit = this.units.find(
                        u => u.id == this.unitId
                    );

                    this.components.push({
                        ingredient_id: ingredient.id,
                        name: ingredient.name,
                        qty: parseFloat(this.qty),
                        unit_id: unit.id,
                        unit_name: unit.name,
                        unit_symbol: unit.symbol
                    });

                    this.selectedIngredient = '';
                    this.qty = '';
                    this.unitId = '';
                },

                removeComponent(index) {
                    this.components.splice(index, 1);
                }
            }
        }
    </script>
@endpush