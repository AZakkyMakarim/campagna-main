@extends('layouts.app', [
    'activeModule' => 'core',
    'activeMenu' => 'tax-rule'
])
@section('title', 'Pajak')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Pajak & Service</h2>
    </div>

    <div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white p-4">
        <form method="POST" id="taxForm" action="{{ route('core.tax-rule.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- PPN --}}
                <div class="space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="enable_ppn"
                            class="w-5 h-5 border border-gray-300 rounded
                               flex items-center justify-center
                               peer-checked:bg-orange-600
                               peer-checked:border-orange-600"
                            @checked($ppn->is_active)
                        >
                        <span class="font-medium">Aktifkan PPN</span>
                    </label>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Persentase PPN (%)
                        </label>
                        <input
                            type="number"
                            name="ppn_percentage"
                            value="{{ $ppn->value }}"
                            min="0"
                            max="100"
                            class="w-24 text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        >
                    </div>
                </div>

                {{-- SERVICE --}}
                <div class="space-y-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="enable_service"
                            class="w-5 h-5 border border-gray-300 rounded
                               flex items-center justify-center
                               peer-checked:bg-orange-600
                               peer-checked:border-orange-600"
                            @checked($service->is_active)
                        >
                        <span class="font-medium">Aktifkan Service Charge</span>
                    </label>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Persentase Service (%)
                        </label>
                        <input
                            type="number"
                            name="service_percentage"
                            value="{{ $service->value }}"
                            min="0"
                            max="100"
                            class="w-24 text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                        >
                    </div>
                </div>
            </div>

            {{-- ACTION --}}
            <div class="mt-6 flex justify-end">
                <button
                    type="submit"
                    class="px-5 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500 transition"
                >
                    Simpan Pengaturan Pajak
                </button>
            </div>
        </form>
    </div>
@endsection


@push('js')
    <script>
        document.getElementById('taxForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(async res => {
                    if (!res.ok) {
                        const err = await res.json();
                        throw err;
                    }
                    return res.json();
                })
                .then(res => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: res.message ?? 'Pengaturan pajak berhasil disimpan',
                        showConfirmButton: false,
                        timer: 2500
                    });
                })
                .catch(err => {
                    let message = 'Terjadi kesalahan';

                    if (err?.errors) {
                        message = Object.values(err.errors)[0][0];
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
        });
    </script>
@endpush
