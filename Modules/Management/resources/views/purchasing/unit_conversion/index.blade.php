@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'purchasing',
    'activeSubmenu' => 'unit-conversion'
])
@section('title', 'Konversi')

@section('content')
<div x-data="{ tab: 'raw' }">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Konversi</h2>
        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form-conversion')"
                class="bg-orange-600 text-white px-4 py-2 rounded-xl shadow hover:bg-orange-500 transition flex items-center gap-2 hover:cursor-pointer">
                <i class="fa fa-plus"></i>
                Tambah
            </button>
        </div>
    </div>

    <div class="mt-4">
        <div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
            <table class="w-full text-sm text-left">
                <thead class="bg-orange-700 text-white uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Dari</th>
                    <th class="px-4 py-3">Ke</th>
                    <th class="px-4 py-3">Multiplier</th>
                    <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @foreach($conversions as $key => $conversion)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>

                        <td class="px-4 py-3 text-nowrap">{{ $conversion->fromUnit->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $conversion->toUnit->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $conversion->multiplier }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('management.purchasing.unit-conversion.update', $conversion) }}"
                                    @click="$dispatch('open-edit-conversion', {
                                                conversion: {
                                                        id: {{ $conversion->id }},
                                                        from_unit_id: {{ $conversion->from_unit_id }},
                                                        to_unit_id: {{ $conversion->to_unit_id }},
                                                        multiplier: {{ $conversion->multiplier }},
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
    </div>
</div>

<x-modal id="modal-form-conversion" title="Tambah Konversi" icon="fa-plus" size="md">
    <form method="POST" action="{{ route('management.purchasing.unit-conversion.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Dari</label>
                        <div class="relative">
                            <select name="from_unit_id" class="w-full select2 appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((old('from_unit_id') ?? '') === $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ke</label>
                        <div class="relative">
                            <select name="to_unit_id" class="w-full select2 appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((old('to_unit_id') ?? '') === $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Multiplier</label>
                    <input type="number" step="0.0001" name="multiplier" value="{{ old('multiplier') }}" required placeholder="Multiplier" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
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
    x-data="editConversionModal({ units: @js($units) })"
    x-show="open"
    @open-edit-conversion.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 flex items-center justify-center z-50"
>
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- HEADER -->
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h3 class="font-semibold text-lg">Edit Konversi</h3>
            <button @click="open = false"><i class="fa fa-times"></i></button>
        </div>

        <form :action="action" method="POST">
            @csrf
            <div class="p-5 text-gray-300">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Dari</label>
                            <div class="relative">
                                <select name="from_unit_id" x-model="form.from_unit_id"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                                    <template x-for="u in units">
                                        <option :value="u.id" x-text="u.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Ke</label>
                            <div class="relative">
                                <select name="to_unit_id" x-model="form.to_unit_id"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                                    <template x-for="u in units">
                                        <option :value="u.id" x-text="u.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Multipler</label>
                        <input type="text" name="multiplier" x-model="form.multiplier" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 px-5 py-4">
                <button type="button" @click="open=false" class="px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400">Batal</button>
                <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">Update</button>
            </div>
        </form>
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

        function editConversionModal({ units = [] }) {
            return {
                open: false,
                action: '',
                units,
                form: {
                    id: '',
                    from_unit_id: '',
                    to_unit_id: '',
                    multiplier: '',
                },

                fill(payload) {
                    this.open = true;
                    this.action = payload.action;

                    this.form = {
                        id: payload.conversion.id,
                        from_unit_id: payload.conversion.from_unit_id,
                        to_unit_id: payload.conversion.to_unit_id,
                        multiplier: payload.conversion.multiplier,
                    };
                },
            }
        }
    </script>
@endpush
