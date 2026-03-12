@extends('layouts.app', [
    'activeModule' => 'core',
    'activeMenu' => 'outlet'
])
@section('title', 'Daftar Outlet')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Daftar Outlet</h2>

        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form')"
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
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Nama Outlet</th>
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3">Alamat</th>
                <th class="px-4 py-3">Jam Operasional</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($outlets as $key => $outlet)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $outlet->code }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $outlet->name }}</td>
                        <td class="px-4 py-3">{{ $outlet->type }}</td>
                        <td class="px-4 py-3">{{ $outlet->address }}</td>
                        <td class="px-4 py-3">{{ parse_time_hm($outlet->opening_hours).' - '.parse_time_hm($outlet->closing_hours) }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $outlet->is_active ? 'checked' : '' }}
                                    onchange="toggleOutletStatus(this, '{{ route('core.outlet.update', $outlet) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('core.outlet.update', $outlet->id) }}"
                                    @click="$dispatch('open-edit', {
                                        outlet: @js($outlet),
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
<x-modal id="modal-form" title="Tambah Outlet" icon="fa-plus" size="5xl">
    <form action="{{ route('core.outlet.store') }}" method="POST">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Kolom 1: Outlet Details -->
                <div class="space-y-4">
                    <!-- Nama Outlet -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Outlet</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama outlet" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <!-- Kode Outlet -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kode Outlet</label>
                        <input type="text" name="code" value="{{ old('code') }}" placeholder="Masukkan kode outlet" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <!-- Alamat -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Alamat</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Masukkan alamat" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <!-- Tipe Outlet -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telp</label>
                        <div class="relative">
                            <input
                                type="text"
                                name="phone_number"
                                placeholder="Masukkan Nomor Telp"
                                class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                            >
                        </div>
                    </div>


                    <!-- Jam Buka -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jam Buka</label>
                            <input type="time" name="opening_hours" value="{{ old('opening_hours') }}" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>

                        <!-- Jam Tutup -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jam Tutup</label>
                            <input type="time" name="closing_hours" value="{{ old('closing_hours') }}" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Transaction Settings -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Prefix Nomor Transaksi</label>
                            <input
                                type="text"
                                name="prefix_transaction"
                                placeholder="Prefix transaksi"
                                class="w-full text-gray-700 px-3 py-2 mb-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                            >
                            <small class="text-gray-600 text-xs">Contoh : CMP-</small>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Reset Nomor
                            </label>
                            <div class="relative">
                                <select
                                    name="reset_transaction"
                                    class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm"
                                >
                                    @foreach(['harian', 'bulanan', 'tahunan'] as $type)
                                        <option value="{{ $type }}">
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Shift Kasir -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Modal Kas Awal per Shift</label>
                        <input
                            type="text"
                            name="initial_cash"
                            placeholder="Masukkan modal awal"
                            class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Modal Petty Awal per Shift</label>
                        <input
                            type="text"
                            name="petty_cash"
                            placeholder="Masukkan modal petty"
                            class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        >
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 px-5 py-4">
                <button
                    type="button"
                    @click="$dispatch('close-modal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-400">
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</x-modal>

<div
    x-data="editOutletModal()"
    x-show="open"
    @open-edit.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
>

    <div class="relative w-full max-w-5xl bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300">
            <h3 class="font-semibold text-lg">
                Edit Outlet
            </h3>
            <button @click="open = false" class="text-gray-600 hover:text-gray-400 hover:cursor-pointer">
                <i class="fa fa-times"></i>
            </button>
        </div>

        <div class="bg-white w-full rounded-xl">
            <form :action="action" method="POST">
                @csrf
                <div class="p-5 text-gray-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6"> <!-- Divided into 2 columns -->

                        <!-- Kolom 1: Outlet Details -->
                        <div class="space-y-4">
                            <!-- Nama Outlet -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Outlet</label>
                                <input
                                    type="text"
                                    name="name"
                                    x-model="form.name"
                                    placeholder="Masukkan nama outlet"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <!-- Kode Outlet -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Kode Outlet</label>
                                <input
                                    type="text"
                                    name="code"
                                    x-model="form.code"
                                    placeholder="Masukkan kode outlet"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <!-- Alamat -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat</label>
                                <input
                                    type="text"
                                    name="address"
                                    x-model="form.address"
                                    placeholder="Masukkan alamat"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <!-- Tipe Outlet -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telp</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="phone_number"
                                        x-model="form.phone_number"
                                        placeholder="Masukkan Nomor Telp"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                    >
                                </div>
                            </div>

                            <!-- Jam Buka -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Jam Buka</label>
                                    <input
                                        type="time"
                                        name="opening_hours"
                                        x-model="form.opening_hours"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                    >
                                </div>

                                <!-- Jam Tutup -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Jam Tutup</label>
                                    <input
                                        type="time"
                                        name="closing_hours"
                                        x-model="form.closing_hours"
                                        class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Kolom 2: Transaction Settings -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Prefix Nomor Transaksi</label>
                                    <input
                                        type="text"
                                        name="prefix_transaction"
                                        x-model="form.prefix_transaction"
                                        placeholder="Prefix transaksi"
                                        class="w-full text-gray-700 px-3 py-2 mb-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                    >
                                    <small class="text-gray-600 text-xs">Contoh : CMP-</small>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">
                                        Reset Nomor
                                    </label>
                                    <div class="relative">
                                        <select
                                            name="reset_transaction"
                                            x-model="form.reset_transaction"
                                            class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm"
                                        >
                                            @foreach(['harian', 'bulanan', 'tahunan'] as $type)
                                                <option value="{{ $type }}">
                                                    {{ ucfirst($type) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Pengaturan Shift Kasir -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Modal Kas Awal per Shift</label>
                                <input
                                    type="text"
                                    name="initial_cash"
                                    x-model="form.initial_cash"
                                    placeholder="Masukkan modal awal"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Modal Petty Awal per Shift</label>
                                <input
                                    type="text"
                                    name="petty_cash"
                                    x-model="form.petty_cash"
                                    placeholder="Masukkan modal petty"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 px-5 py-4">
                        <button
                            type="button"
                            @click="$dispatch('close-modal')"
                            class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-400">
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:cursor-pointer hover:bg-orange-500">
                            Simpan
                        </button>
                    </div>
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

        function editOutletModal() {
            return {
                formAction: '',
                form: {
                    name: '',
                    code: '',
                    address: '',
                    opening_hours: '',
                    closing_hours: '',
                }
            }
        }
    </script>

    <script>
        function editOutletModal() {
            return {
                open: false,
                action: '',
                form: {
                    name: '',
                    code: '',
                    address: '',
                    type:'',
                    opening_hours: '',
                    closing_hours: '',
                },

                fill(payload) {
                    const outlet = payload.outlet

                    const prefixTransaction = outlet.settings.find(setting => setting.name === 'prefix transaction')?.value;
                    const resetTransaction = outlet.settings.find(setting => setting.name === 'reset transaction')?.value;
                    const prefixQueue = outlet.settings.find(setting => setting.name === 'prefix queue')?.value;

                    this.open = true
                    this.action = payload.action

                    this.form = {
                        name: outlet.name,
                        phone_number: outlet.phone_number,
                        code: outlet.code,
                        address: outlet.address,
                        type: outlet.type,
                        opening_hours: outlet.opening_hours,
                        closing_hours: outlet.closing_hours,
                        prefix_transaction: prefixTransaction,
                        reset_transaction: resetTransaction,
                        queue_number: prefixQueue,
                        initial_cash: outlet.initial_cash,
                        petty_cash: outlet.petty_cash,
                    }
                }
            }
        }
    </script>
@endpush
