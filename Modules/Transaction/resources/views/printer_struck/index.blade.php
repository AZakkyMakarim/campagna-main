@extends('layouts.app', [
    'activeModule' => 'transaction',
    'activeMenu' => 'printer-struck'
])
@section('title', 'Printer & Struk')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Printer & Struk</h2>

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
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3">Nama Printer</th>
                <th class="px-4 py-3">Section</th>
                <th class="px-4 py-3">Jenis Koneksi</th>
                <th class="px-4 py-3">IP Address</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-center"><i class="fa fa-spin fa-cog"></i> Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($printers as $key => $printer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3">{{ strtoupper($printer->role) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $printer->device_name }}</td>
                        <td class="px-4 py-3">
                            @foreach(json_decode($printer->section) as $section)
                                {{ strtoupper($section) }},
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-nowrap">{{ strtoupper($printer->connection_type) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $printer->connection_type == 'lan' ? $printer->ip_address.':'.$printer->port : '' }}</td>
                        <td class="px-4 py-3">
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    class="sr-only peer"
                                    {{ $printer->is_active ? 'checked' : '' }}
                                    onchange="togglePrinterStatus(this, '{{ route('transaction.printer.update', $printer) }}')"
                                >
                                <div class=" relative w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                            </label>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $printerPayload = [
                                    'id'              => $printer->id,
                                    'connection_type' => $printer->connection_type,
                                    'device_name'     => $printer->device_name,
                                    'ip_address'      => $printer->ip_address ?: null,
                                    'port'            => $printer->port ?: null,
                                    'paper_width'     => $printer->paper_width ?? 58,
                                    'type'            => $printer->type ?? 'cashier',
                                ];
                            @endphp
                            <a class="test-print-btn px-4 py-2 rounded-lg border border-gray-300 hover:cursor-pointer hover:bg-orange-100 hover:text-orange-400 flex items-center gap-2"
                               href="{{ route('transaction.printer-struck.test', $printer) }}">
                                <i class="fa fa-print"></i>
                                Test Print
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
<x-modal id="modal-form" title="Tambah Printer" icon="fa-plus" size="xl">
    <form method="POST" action="{{ route('transaction.printer.store') }}">
        @csrf
        <div x-data="{ connectionType: '{{ old('type') }}' }" class="p-5 text-gray-300">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- DEVICE NAME (2 KOLOM) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Nama Printer
                        </label>

                        <select
                            name="device_name"
                            id="printerSelect"
                            class="select2 w-full"
                            data-placeholder="Pilih atau ketik nama printer"
                            required
                        >
                            @if(old('device_name'))
                                <option value="{{ old('device_name') }}" selected>
                                    {{ old('device_name') }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <!-- OUTLET (1 KOLOM) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Outlet
                        </label>

                        <select
                            name="outlet_id"
                            required
                            class="w-full appearance-none p-2 pr-10 rounded-lg
                   border border-gray-300 bg-white text-gray-700 text-sm
                   focus:outline-none focus:ring-2 focus:ring-orange-500"
                        >
                            @foreach(\App\Models\Outlet::where('business_id', auth()->user()->business_id)->get() as $outlet)
                                <option
                                    value="{{ $outlet->id }}"
                                    @selected(old('outlet_id') == $outlet->id)
                                >
                                    {{ strtoupper($outlet->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Koneksi
                        </label>
                        <div class="relative">
                            <select
                                name="connection_type" required
                                x-model="connectionType"
                                class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm"
                            >
                                @foreach(config('array.printer.connection_type') as $connectionType)
                                    <option value="{{ $connectionType }}" @selected((old('type') ?? '') === $connectionType)>
                                        {{ strtoupper($connectionType) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Role
                        </label>
                        <div class="relative">
                            <select name="role" required class="w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                                @foreach(config('array.printer.role') as $role)
                                    <option value="{{ $role }}" @selected((old('role') ?? '') === $role)>
                                        {{ strtoupper($role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Section
                        </label>
                        <div class="relative">
                            <select
                                name="section[]"
                                id="printerSection"
                                class="select2 w-full"
                                data-placeholder="Pilih section printer"
                                required
                                multiple
                            >
                                @foreach($sections as $section)
                                    <option value="{{ $section }}">{{ strtoupper($section) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div
                    x-show="connectionType === 'lan'"
                    x-transition
                    class="grid grid-cols-1 md:grid-cols-2 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            IP Address
                        </label>
                        <input type="text" name="ip_address" value="{{ old('ip_address') }}" placeholder="192.168.x.x" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Port
                        </label>
                        <input type="text" name="port" value="{{ old('port') }}" placeholder="9100" class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white">
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

@push('js')
    <script>
        document.addEventListener('open-modal', async (e) => {
            if (e.detail !== 'modal-form') return;

            const selectPrinter = $('#printerSelect');
            selectPrinter.empty();

            selectPrinter.select2({
                placeholder: 'Pilih atau ketik nama printer',
                tags: true,
                width: '100%'
            });

            const selectSection = $('#printerSection');

            selectSection.select2({
                placeholder: 'Pilih atau ketik section printer',
                tags: true,
                width: '100%'
            });
        });

        function togglePrinterStatus(el, url) {
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
    </script>
@endpush
