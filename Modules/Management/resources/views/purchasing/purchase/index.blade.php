@extends('layouts.app', [
    'activeModule' => 'management',
    'activeMenu' => 'purchasing',
    'activeSubmenu' => 'purchase',
])
@section('title', 'Pembelian')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Pembelian</h2>

        <div class="flex items-center space-x-3">
            <button
                @click="$dispatch('open-modal', 'modal-form-purchase')"
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
                <th class="px-4 py-3">ID Pembelian</th>
                <th class="px-4 py-3">Tanggal Pembelian</th>
                <th class="px-4 py-3">Vendor</th>
                <th class="px-4 py-3">Bahan</th>
                <th class="px-4 py-3">Total Pembelian</th>
                <th class="px-4 py-3 text-center">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($purchases as $key => $purchase)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ $purchase->code }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ parse_date_time($purchase->purchased_at) }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ @$purchase->vendor->name }}</td>
                        <td class="px-4 py-3 text-nowrap">{{ @$purchase->ingredientBatches->count() }} Bahan</td>
                        <td class="px-4 py-3 text-nowrap">{{ rp_format($purchase->total_cost) }}</td>
                        <td class="px-4 py-2 text-center">
                            <button
                                @click="$dispatch('open-modal', 'modal-form-detail')"
                                onclick="openDetail({{ $purchase->id }})"
                                class="px-3 py-2 bg-yellow-500 text-white rounded">
                                <i class="fa fa-magnifying-glass"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

<x-modal id="modal-form-purchase" title="Tambah Pembelian" icon="fa-plus" size="5xl">
    <form method="POST" action="{{ route('management.purchasing.purchase.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="p-5 text-gray-300">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Vendor</label>
                    <div class="relative">
                        <select name="vendor_id" data-placeholder="Pilih vendor" class="select2 w-full appearance-none p-2 pr-10 rounded-lg border border-gray-300 bg-white text-gray-700 text-sm">
                            <option></option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((old('vendor_id') ?? '') === $vendor->id)>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="border rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-800">Daftar Bahan</p>
                            <p class="text-xs text-gray-500">Bisa tambah lebih dari 1 komponen + qty per komponen</p>
                        </div>

                        <button type="button"
                                id="addItem"
                                class="px-3 py-2 rounded-lg bg-orange-600 text-white hover:bg-orange-500">
                            + Tambah Komponen
                        </button>
                    </div>
                    <div class="relative max-h-[320px] overflow-y-auto p-4 space-y-3 bg-white">
                        <div id="purchase-items" class="space-y-4">
                            <div class="purchase-item rounded-xl border border-gray-200 p-4">
                                <div class="grid grid-cols-12 gap-4 items-end">

                                    {{-- INGREDIENT --}}
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">
                                            Bahan
                                        </label>
                                        <select
                                            name="items[0][ingredient_id]"
                                            class="select2 ingredient-select w-full"
                                            data-placeholder="Pilih bahan"
                                        >
                                            <option></option>
                                        </select>
                                    </div>

                                    {{-- QTY --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">
                                            Qty
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="items[0][qty]"
                                            placeholder="0"
                                            class="qty-input w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                        >
                                    </div>

                                    {{-- UNIT --}}
                                    <div class="col-span-6 md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">
                                            Satuan
                                        </label>
                                        <select
                                            name="items[0][unit_id]"
                                            class="select2 unit-select w-full"
                                        >
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- TOTAL --}}
                                    <div class="col-span-10 md:col-span-2">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">
                                            Total Harga
                                        </label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            name="items[0][cost]"
                                            placeholder="0"
                                            class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                        >
                                    </div>

                                    {{-- REMOVE --}}
                                    <div class="col-span-2 md:col-span-1 flex justify-center">
                                        <button
                                            type="button"
                                            class="remove-item w-9 h-9 flex items-center justify-center rounded-full border border-red-200 text-red-500 hover:bg-red-500 hover:text-white transition"
                                            title="Hapus bahan"
                                        >
                                            <i class="fa fa-trash text-xs"></i>
                                        </button>
                                    </div>

                                </div>

                                {{-- CONVERSION RESULT --}}
                                <div class="mt-3 pl-1 text-xs text-gray-500 conversion-result italic">
                                    {{-- hasil konversi muncul di sini --}}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700">
                        Deskripsi
                    </label>

                    <textarea
                        name="description"
                        rows="2"
                        placeholder="Tambahkan keterangan pembelian, catatan vendor, atau info lainnya..."
                        class="w-full text-gray-700 px-3 py-2 rounded-lg
                                border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white
                                resize-none"
                    ></textarea>

                    <p class="text-xs text-gray-400">
                        Opsional, hanya untuk catatan internal.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700">Nota Pembelian</label>
                    <div class="flex items-center gap-4">
                        <label
                            class="flex items-center gap-2 px-4 py-2 rounded-lg
                                    border border-gray-300 bg-white cursor-pointer
                                    hover:bg-gray-50 transition text-sm text-gray-600"
                        >
                            <i class="fa fa-upload text-gray-400"></i>
                            <span>Pilih File</span>

                            <input
                                type="file"
                                name="attachment"
                                class="hidden"
                                accept="image/*,application/pdf"
                            >
                        </label>

                        <span class="text-xs text-gray-400">
                            JPG, PNG, atau PDF
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center px-5 py-4">
            <div class="flex gap-3 ml-auto">
                <button
                    type="button"
                    @click="$dispatch('close-modal')"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-orange-100 hover:text-orange-400">
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-500">
                    Simpan
                </button>
            </div>
        </div>
    </form>
</x-modal>

<x-modal id="modal-form-detail" idModalTitle="modal-title-detail" idSubModalTitle="modal-sub-title-detail" icon="fa-receipt" title="Detail Pembelian" size="5xl">
    <div class="bg-white w-full rounded-lg shadow-lg p-6 space-y-6">
        <!-- HEADER INFO -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
            <div class="rounded-lg p-4 border border-gray-200 shadow-md">
                <div class="space-y-1">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <i class="fa fa-house"></i> Vendor
                    </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed" id="purchase-vendor"></span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow-md">
                <div class="space-y-1">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <i class="fa fa-user"></i> PIC
                    </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed" id="purchase-pic"></span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow-md">
                <div class="space-y-1">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <i class="fa fa-calendar"></i> Tanggal Pembelian
                    </span>
                    <span class="block text-sm font-bold text-gray-600 leading-relaxed" id="purchase-date"></span>
                </div>
            </div>

            <div class="rounded-lg p-4 border border-gray-200 shadow-md">
                <div class="space-y-1">
                    <span class="block text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <i class="fa fa-dollar-sign"></i> Total Pembelian
                    </span>
                    <span class="block text-sm font-bold text-orange-600 leading-relaxed" id="purchase-total"></span>
                </div>
            </div>
        </div>

        <div class="p-4 bg-gray-100 rounded-lg border border-gray-200 space-y-1">
            <span class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <span class="flex items-center gap-1">
                    <i class="fa fa-note-sticky"></i>
                    Keterangan
                </span>

                <span id="purchase-invoice"></span>
            </span>

            <span
                id="purchase-description"
                class="block text-sm text-gray-700 leading-relaxed"
            >
            </span>
        </div>

        <!-- TABLE -->
        <div class="overflow-hidden rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left">
                <thead class="bg-orange-700 text-white uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Nama Bahan</th>
                    <th class="px-4 py-3">Jenis Bahan</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3">Satuan</th>
                    <th class="px-4 py-3 text-right">HPP</th>
                    <th class="px-4 py-3 text-right">Total HPP</th>
                </tr>
                </thead>
                <tbody id="detailBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

    </div>
</x-modal>

@push('js')
    <script>
        const detailUrl = "{{ route('management.purchasing.purchase.detail', ':id') }}";

        function openDetail(id) {
            document.getElementById('detailBody').innerHTML =
                '<tr><td class="px-4 py-3 text-center" colspan="7">Loading...</td></tr>';

            const url = detailUrl.replace(':id', id);

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    console.log(res);
                    document.getElementById('modal-sub-title-detail').innerText = res.code ?? '-';
                    document.getElementById('purchase-pic').innerText = res.created_by ?? '-';
                    document.getElementById('purchase-date').innerText = res.purchased_at ?? '-';
                    document.getElementById('purchase-vendor').innerText = res.vendor ?? '-';
                    document.getElementById('purchase-total').innerText = res.total_price ?? '-';
                    document.getElementById('purchase-description').innerText = res.description ?? '-';

                    const invoiceEl = document.getElementById('purchase-invoice');
                    if (res.nota) {
                        invoiceEl.innerHTML = `
                                                <a href="${res.nota}" target="_blank"
                                                   class="inline-flex items-center gap-1 px-3 py-1 rounded-lg
                                                          bg-orange-100 text-orange-700 text-xs font-semibold hover:bg-orange-200">
                                                    <i class="fa fa-file-invoice"></i>
                                                    Lihat Nota
                                                </a>
                                            `;
                    } else {
                        invoiceEl.innerHTML = `<span class="text-gray-400 italic text-xs">Tidak ada nota</span>`;
                    }

                    let no = 1;
                    let html = '';
                    res.rows.forEach(r => {
                        html += `
                            <tr>
                                <td class="px-4 py-3">${no++}</td>
                                <td class="px-4 py-3">${r.ingredient}</td>
                                <td class="px-4 py-3">${r.ingredient_type}</td>
                                <td class="px-4 py-3">${r.qty}</td>
                                <td class="px-4 py-3">${r.unit}</td>
                                <td class="px-4 py-3">${r.cost_per_unit}</td>
                                <td class="px-4 py-3">${r.total_price}</td>
                            </tr>
                        `;
                    });

                    document.getElementById('detailBody').innerHTML = html;
                })
                .catch((e) => {
                    console.log(e);
                    document.getElementById('detailBody').innerHTML =
                        '<tr><td class="px-4 py-3 text-center" colspan="7">Gagal memuat data</td></tr>';
                });
        }

        window.ingredientData = @json($ingredientPayload);
        window.ingredientGroups = @json($ingredients);

        document.addEventListener('DOMContentLoaded', () => {

            let itemIndex = 1;

            const vendorSelect = $('select[name="vendor_id"]');

            /** ===============================
             * INIT SELECT2
             * =============================== */
            function initSelect2(el) {
                if (el.hasClass('select2-hidden-accessible')) return;
                el.select2({ width: '100%' });
            }

            $('.select2').each(function () {
                initSelect2($(this));
            });

            // Populate baris pertama
            populateIngredientSelect($('select[name="items[0][ingredient_id]"]'), window.ingredientGroups);

            /** ===============================
             * ADD ROW
             * =============================== */
            $('#addItem').on('click', function () {

                const html = `
                    <div class="purchase-item rounded-xl border border-gray-200 p-4">

                        <div class="grid grid-cols-12 gap-4 items-end">

                            <!-- INGREDIENT -->
                            <div class="col-span-12 md:col-span-5">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Bahan
                                </label>
                                <select
                                    name="items[${itemIndex}][ingredient_id]"
                                    class="ingredient-select w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                    data-placeholder="Pilih bahan"
                                ><option></option></select>
                            </div>

                            <!-- QTY -->
                            <div class="col-span-6 md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Qty
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[${itemIndex}][qty]"
                                    placeholder="0"
                                    class="qty-input w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <!-- UNIT -->
                            <div class="col-span-6 md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Satuan
                                </label>
                                <select
                                    name="items[${itemIndex}][unit_id]"
                                    class="unit-select w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                                    @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- TOTAL -->
                            <div class="col-span-10 md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Total Harga
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[${itemIndex}][cost]"
                                    placeholder="0"
                                    class="w-full text-gray-700 px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-white"
                                >
                            </div>

                            <!-- REMOVE -->
                            <div class="col-span-2 md:col-span-1 flex justify-center">
                                <button
                                    type="button"
                                    class="remove-item w-9 h-9 flex items-center justify-center
                                           rounded-full border border-red-200
                                           text-red-500 hover:bg-red-500 hover:text-white transition"
                                    title="Hapus bahan"
                                >
                                    <i class="fa fa-trash text-xs"></i>
                                </button>
                            </div>

                        </div>

                        <!-- CONVERSION RESULT -->
                        <div class="mt-3 pl-1 text-xs text-gray-500 conversion-result italic"></div>

                    </div>
                    `;

                $('#purchase-items').append(html);

                const row = $('#purchase-items .purchase-item').last();

                // init select2
                initSelect2(row.find('select'));

                // auto load all ingredients
                populateIngredientSelect(
                    row.find('.ingredient-select'),
                    window.ingredientGroups
                );

                itemIndex++;
            });

            /** ===============================
             * REMOVE ROW
             * =============================== */
            $(document).on('click', '.remove-item', function () {
                $(this).closest('.purchase-item').remove();
            });

            /** ===============================
             * POPULATE INGREDIENT
             * =============================== */
            function populateIngredientSelect(select, groups) {
                select.empty().append('<option value="">Pilih bahan</option>');

                Object.entries(groups).forEach(([label, items]) => {
                    const optgroup = $('<optgroup>', { label });

                    items.forEach(item => {
                        optgroup.append(
                            new Option(
                                `${item.name} (${item.base_unit.name})`,
                                item.id
                            )
                        );
                    });

                    select.append(optgroup);
                });

                // select.trigger('change.select2');
            }

            function populateUnitSelect(row, ingredient) {
                const unitSelect = row.find('.unit-select');
                unitSelect.empty();

                if (!ingredient) {
                    unitSelect.append('<option value="">Pilih satuan</option>');
                    return;
                }

                // Base unit
                unitSelect.append(
                    new Option(
                        ingredient.base_unit.name,
                        ingredient.base_unit.id
                    )
                );

                // Conversion units
                if (ingredient.conversions && ingredient.conversions.length) {
                    ingredient.conversions.forEach(c => {
                        if (c.to_unit) {
                            unitSelect.append(
                                new Option(
                                    c.to_unit.name,
                                    c.to_unit.id
                                )
                            );
                        }
                    });
                }

                unitSelect.trigger('change.select2'); // kalau pakai select2
            }

            /** ===============================
             * KONVERSI REALTIME
             * =============================== */
            $(document).on('change', '.ingredient-select', function () {
                const row = $(this).closest('.purchase-item');
                const ingredientId = $(this).val();

                if (!ingredientId) {
                    populateUnitSelect(row, null);
                    row.find('.conversion-result').text('');
                    return;
                }

                const ingredient = window.ingredientData[ingredientId];
                if (!ingredient) return;

                populateUnitSelect(row, ingredient);
            });

            $('.select2').select2({
                placeholder: function () {
                    return $(this).data('placeholder');
                },
                allowClear: true,
                width: '100%'
            });
        });

        document.querySelector('input[name="attachment"]')?.addEventListener('change', function () {
            const fileName = this.files[0]?.name;
            if (!fileName) return;

            this.closest('div').querySelector('span.text-xs').innerText = fileName;
        });
    </script>
@endpush
