@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'purchasing',
    'activeSubmenu' => 'vendor',
])
@section('title', 'Vendor')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Vendor</h2>

        <div class="flex items-center gap-2">
            <button
                @click="$dispatch('open-modal', 'modal-import-vendor')"
                class="bg-green-600 text-white px-4 py-2 rounded-xl shadow hover:bg-green-500 transition flex items-center gap-2 hover:cursor-pointer">
                <i class="fa fa-file-import"></i>
                Import
            </button>
            <button
                @click="$dispatch('open-modal', 'modal-form-vendor')"
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
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">No. Telp</th>
                <th class="px-4 py-3">Alamat</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($vendors as $key => $vendor)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $vendor->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $vendor->phone_number }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $vendor->address }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $vendor->is_active ? 'checked' : '' }}
                                    onchange="toggleVendorStatus(this, '{{ route('management.purchasing.vendor.update', $vendor) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('management.purchasing.vendor.update', $vendor) }}"
                                    @click="$dispatch('open-edit-vendor', {
                                        vendor: {
                                            id: {{ $vendor->id }},
                                            name: @js($vendor->name),
                                            phone_number: @js($vendor->phone_number),
                                            address: @js($vendor->address),
                                            link_maps: @js($vendor->link_maps),
                                            components: @js($vendor->vendor_components),
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
@endsection

<x-modal id="modal-import-vendor" title="Import Vendor" size="md">
    <form method="POST" action="{{ route('management.purchasing.vendor.import') }}" enctype="multipart/form-data">
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
                                <li>Kolom wajib: <span class="font-medium bg-blue-100 px-1 rounded">Nama Vendor</span>, <span class="font-medium bg-blue-100 px-1 rounded">No Telp</span>, <span class="font-medium bg-blue-100 px-1 rounded">Nama Bahan</span></li>
                                <li>Kolom opsional: <span class="font-medium bg-blue-100 px-1 rounded">Alamat</span>, <span class="font-medium bg-blue-100 px-1 rounded">Link Maps</span></li>
                                <li>Satu vendor bisa punya <b>banyak baris</b> bahan suplai</li>
                                <li><b>Nama Bahan</b> harus sudah ada di sistem</li>
                                <li>Import akan <b>update</b> vendor jika nama sudah ada</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Step 1: Download Template</label>
                    <a href="{{ route('management.purchasing.vendor.download-template') }}" target="_blank"
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

<x-modal id="modal-form-vendor" title="Tambah Vendor" icon="fa-plus" size="5xl">
    <form method="POST" action="{{ route('management.purchasing.vendor.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama vendor" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">No Telp</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="No Telp" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Alamat</label>
                        <textarea name="address" placeholder="Alamat" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Link Maps</label>
                        <input type="text" name="link_maps" value="{{ old('link_maps') }}" required placeholder="Link Map" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                </div>
                <div
                    x-data="vendorComponent({
                                ingredients: @js($ingredients),
                            })"
                    class="space-y-2"
                >

                    <!-- INPUT BAR -->
                    <label class="block text-sm font-bold text-gray-700">Tambah Barang</label>
                    <div class="flex items-center gap-3">
                        <!-- Select Ingredient -->
                        <select x-model="selectedIngredient" class="flex-1 text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                            <option value="">Pilih barang...</option>
                            <template x-for="item in ingredients" :key="item.id">
                                <option :value="item.id" x-text="item.name"></option>
                            </template>
                        </select>

                        <!-- Add Button -->
                        <button @click="addComponent"
                                type="button"
                                class="w-12 h-12 rounded-xl border border-gray-300 flex items-center justify-center hover:bg-orange-500 hover:text-white transition">
                            +
                        </button>
                    </div>

                    <!-- LIST KOMPONEN -->
                    <div class="border divide-y">
                        <template x-for="(item, index) in components" :key="index">
                            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition">
                                <div>
                                    <p class="font-medium text-gray-700" x-text="item.name"></p>
                                </div>

                                <button @click="removeComponent(index)"
                                        class="text-red-500 hover:text-red-700">
                                    ✕
                                </button>
                            </div>
                        </template>

                        <div x-show="components.length === 0"
                             class="px-4 py-6 text-center text-gray-400 text-sm">
                            Belum ada komponen resep
                        </div>
                    </div>

                    <!-- HIDDEN INPUT (BUAT SUBMIT FORM) -->
                    <input type="hidden" name="components" :value="JSON.stringify(components)">
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
    x-data="editVendorModal({
        ingredients: @js($ingredients)
    })"
    x-show="open"
    @open-edit-vendor.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
>

    <div class="relative w-full max-w-5xl bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300">
            <h3 class="font-semibold text-lg">
                Edit Vendor
            </h3>
            <button @click="open = false" class="text-gray-600 hover:text-gray-400 hover:cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="bg-white w-full rounded-xl">

            <form :action="action" method="POST">
                @csrf
                <div class="p-5 text-gray-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                                <input
                                    type="text"
                                    name="name"
                                    x-model="form.name"
                                    placeholder="Nama Vendor"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">No Telp</label>
                                <input
                                    type="text"
                                    name="phone_number"
                                    x-model="form.phone_number"
                                    placeholder="No Telp"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat</label>
                                <textarea x-model="form.address" name="address" placeholder="Masukan alamat" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Link Maps</label>
                                <input
                                    type="text"
                                    name="link_maps"
                                    x-model="form.link_maps"
                                    placeholder="Link Map"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-gray-700">Komponen Resep</label>

                            <!-- INPUT BAR -->
                            <div class="flex gap-2">
                                <select x-model="selectedIngredient" class="flex-1 text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                                    <option value="">Pilih bahan</option>
                                    <template x-for="r in ingredients">
                                        <option :value="r.id" x-text="r.name"></option>
                                    </template>
                                </select>

                                <button type="button" @click="addComponent"
                                        class="w-12 h-12 rounded-xl border border-gray-300 flex items-center justify-center hover:bg-orange-500 hover:text-white transition">+</button>
                            </div>

                            <!-- LIST -->
                            <div class="border rounded divide-y">
                                <template x-for="(item, index) in form.components" :key="index">
                                    <div class="flex justify-between px-4 py-2 border-b">
                                        <div>
                                            <p class="font-medium text-gray-700" x-text="item.name"></p>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeComponent(index)"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <input type="hidden" name="components" :value="JSON.stringify(form.components)">
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
        </div>
    </div>


</div>

@push('js')
    <script>
        function toggleVendorStatus(el, url) {
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

        function editVendorModal({ ingredients = [] }) {
            return {
                open: false,
                action: '',
                ingredients,
                form: {
                    name: '',
                    phone_number: '',
                    address:'',
                    components: '',
                },
                selectedIngredient: '',
                ingredientId: '',

                fill(payload) {
                    const vendor = payload.vendor

                    this.open = true
                    this.action = payload.action

                    this.form = {
                        name: vendor.name,
                        phone_number: vendor.phone_number,
                        address: vendor.address,
                        link_maps: vendor.link_maps,
                        components: vendor.components ?? []
                    }
                },

                addComponent() {
                    if (!this.selectedIngredient) {
                        alert('Lengkapi bahan')
                        return
                    }

                    if (this.form.components.find(i => i.ingredient_id == this.selectedIngredient)) {
                        alert('Komponen sudah ada')
                        return
                    }

                    const ingredient = this.ingredients.find(i => i.id == this.selectedIngredient)

                    this.form.components.push({
                        ingredient_id: ingredient.id,
                        name: ingredient.name,
                    })

                    this.selectedIngredient = ''
                    this.ingredientId = ''
                },

                // 🔥 INI YANG KURANG
                removeComponent(index) {
                    this.form.components.splice(index, 1)
                }
            }
        }

        function vendorComponent({ ingredients = [] }) {
            return {
                ingredients,

                selectedIngredient: '',
                qty: '',
                unitId: '',
                components: [],

                addComponent() {
                    console.log(this.selectedIngredient);
                    if (!this.selectedIngredient) {
                        alert('Lengkapi bahan');
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

                    this.components.push({
                        ingredient_id: ingredient.id,
                        name: ingredient.name,
                    });

                    this.selectedIngredient = '';
                },

                removeComponent(index) {
                    this.components.splice(index, 1);
                }
            }
        }
    </script>
@endpush
