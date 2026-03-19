<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
            <tr>
                <th class="px-4 py-3">#</th>
                <th class="px-4 py-3">Nama Bahan</th>
                <th class="px-4 py-3">Satuan</th>
                <th class="px-4 py-3">Stok Min</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Unlimited Stok</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($raws as $key => $raw)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">{{ $key + 1 + (((request('raw_page') ?? 1) - 1) * 10) }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $raw->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $raw->baseUnit->name }}</td>
                    <td class="px-4 py-3 text-nowrap">{{ $raw->min_stock }}</td>
                    <td class="px-4 py-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ $raw->is_active ? 'checked' : '' }}
                                onchange="toggleOutletStatus(this, '{{ route('management.ingredient.update', $raw) }}')">
                            <div
                                class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5">
                            </div>
                        </label>
                    </td>
                    <td class="px-4 py-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ $raw->is_unlimited_stock ? 'checked' : '' }}
                                onchange="toggleOutletUnlimited(this, '{{ route('management.ingredient.update', $raw) }}')">
                            <div
                                class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5">
                            </div>
                        </label>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button type="button" data-route="{{ route('management.ingredient.update', $raw) }}" @click="$dispatch('open-edit-raw', {
                                                    raw: @js($raw),
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
    @if($raws->hasPages())
        <div class="px-5 py-4 border-t border-gray-200">
            {{ $raws->appends(Request::except('page'))->links() }}
        </div>
    @endif
</div>

<x-modal id="modal-form-raw" title="Tambah Bahan Baku" size="md">
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

        <input type="hidden" name="type" value="raw">
    </form>
</x-modal>

<div x-data="editRawModal()" x-show="open" @open-edit-raw.window="fill($event.detail)" x-transition x-cloak
    class="fixed inset-0 flex items-center justify-center z-50">

    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300">
            <h3 class="font-semibold text-lg">
                Edit Bahan Baku
            </h3>
            <button @click="open = false" class="text-gray-600 hover:text-gray-400 hover:cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="bg-white w-full max-w-md rounded-xl">

            <form :action="action" method="POST">
                @csrf
                <div class="p-5 text-gray-300">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama</label>
                            <input type="text" x-model="form.name" name="name" required
                                placeholder="Masukkan nama karyawan"
                                class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Stok Min</label>
                            <input type="number" x-model="form.min_stock" step="0.1" name="min_stock" required
                                placeholder="Masukkan Stok Minimal"
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

        function toggleOutletUnlimited(el, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_unlimited_stock: el.checked ? 1 : 0
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

        function editRawModal() {
            return {
                open: false,
                action: '',
                form: {
                    name: '',
                    min_stock: '',
                    base_unit_id: '',
                    outlet_id: '',
                },

                fill(payload) {
                    const raw = payload.raw

                    this.open = true
                    this.action = payload.action

                    this.form = {
                        name: raw.name,
                        min_stock: raw.min_stock,
                        base_unit_id: raw.base_unit_id,
                        outlet_id: raw.outlet_id,
                    }
                }
            }
        }
    </script>
@endpush
