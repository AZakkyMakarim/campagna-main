@extends('layouts.app', [
    'activeModule' => 'core',
    'activeMenu' => 'order-type'
])
@section('title', 'Daftar Jenis Order')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Jenis Order</h2>

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
                <th class="px-4 py-3">Nama Jenis</th>
                <th class="px-4 py-3">Deskripsi</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($orderTypes as $key => $orderType)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $orderType->name }}</td>
                        <td class="px-4 py-3">{{ $orderType->description }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $orderType->is_active ? 'checked' : '' }}
                                    onchange="toggleOutletStatus(this, '{{ route('core.order-type.update', $orderType) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    type="button"
                                    data-route="{{ route('core.order-type.update', $orderType->id) }}"
                                    @click="$dispatch('open-edit', {
                                        orderType: @js($orderType),
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
<x-modal id="modal-form" title="Tambah Jenis Order" size="md">
    <form method="POST" action="{{ route('core.order-type.store') }}">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Jenis Order</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Dine-In Biasa, GoFood, Booking Meeting" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" placeholder="Deskripsi singkat..." class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">{{ old('description') }}</textarea>
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
    x-data="editOutletModal()"
    x-show="open"
    @open-edit.window="fill($event.detail)"
    x-transition
    x-cloak
    class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
>

    <div class="relative w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-300">

        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-300">
            <h3 class="font-semibold text-lg">
                Edit Jenis Order
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
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Jenis Order</label>
                            <input
                                type="text"
                                name="name"
                                x-model="form.name"
                                placeholder="Contoh: Dine-In Biasa, GoFood, Booking Meeting"
                                class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi (Opsional)</label>
                            <textarea x-model="form.description" name="description" placeholder="Deskripsi singkat..." class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">{{ old('description') }}</textarea>
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
                open: false,
                action: '',
                form: {
                    name: '',
                    code: '',
                    type:'',
                    description: '',
                },

                fill(payload) {
                    const orderType = payload.orderType

                    this.open = true
                    this.action = payload.action

                    this.form = {
                        name: orderType.name,
                        code: orderType.code,
                        type: orderType.type,
                        description: orderType.description,
                    }
                }
            }
        }
    </script>
@endpush
