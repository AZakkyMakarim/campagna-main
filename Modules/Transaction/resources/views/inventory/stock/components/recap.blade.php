<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nama Bahan</th>
            <th class="px-4 py-3">Tipe</th>
            <th class="px-4 py-3">Satuan</th>
            <th class="px-4 py-3">Stok Saat Ini</th>
            <th class="px-4 py-3">Total Harga</th>
            <th class="px-4 py-3">Stok Minimum</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($ingredients as $key => $recap)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
                <td class="px-4 py-3 text-nowrap">{{ $recap->name }}</td>
                <td class="px-4 py-3 text-nowrap uppercase">{{ $recap->type }}</td>
                <td class="px-4 py-3 text-nowrap">{{ $recap->baseUnit->name }}</td>
                <td class="px-4 py-3 text-nowrap">{{ number_format($recap->stock, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-nowrap text-green-600 font-bold">{{ rp_format($recap->stock_value) }}</td>
                <td class="px-4 py-3 text-nowrap">{{ number_format($recap->min_stock, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-nowrap">{{ $recap->is_active ? 'Aktif' : 'Non Aktif' }}</td>
                <td class="px-4 py-2 text-center">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-stock-recap')"
                        onclick="openStockRecap({{ $recap->id }})"
                        class="px-3 py-2 bg-yellow-500 text-white rounded">
                        <i class="fa fa-magnifying-glass"></i>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<x-modal id="modal-form-stock-recap" title="Datail Rekap" icon="fa-magnifying-glass" size="7xl">
    <div class="bg-white w-full rounded-lg shadow-lg p-4">
        <div class="relative max-h-[620px] overflow-y-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm text-left">
                <thead class="bg-orange-700 text-white uppercase text-xs sticky top-0 z-10 shadow-md">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Nama Bahan</th>
                        <th class="px-4 py-3">Qty</th>
                        <th class="px-4 py-3">Satuan</th>
                        <th class="px-4 py-3">Harga Per Satuan</th>
                        <th class="px-4 py-3">Total Harga</th>
                        <th class="px-4 py-3">Tanggal Input</th>
                        <th class="px-4 py-3">PIC</th>
                        <th class="px-4 py-3">Vendor</th>
                    </tr>
                </thead>
                <tbody id="stockRecapBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</x-modal>

@push('js')
    <script>
        const stockRecapUrl = "{{ route('transaction.inventory.stock.recap', ':id') }}";

        function openStockRecap(id) {
            document.getElementById('stockRecapBody').innerHTML =
                '<tr><td colspan="9" class="px-4 py-3 text-center"><i class="fa fa-spin fa-spinner"></i> Loading...</td></tr>';

            const url = stockRecapUrl.replace(':id', id);

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    document.getElementById('modal-title').innerText =
                        `Detail Rekap - ${res.ingredient}`;
                    let html = '';
                    let no = 1;
                    res.rows.forEach(r => {
                        html += `
                            <tr>
                                <td class="px-4 py-3">${no++}</td>
                                <td class="px-4 py-3">${res.ingredient}</td>
                                <td class="px-4 py-3">${r.qty}</td>
                                <td class="px-4 py-3">${res.unit}</td>
                                <td class="px-4 py-3">${r.cost_per_unit}</td>
                                <td class="px-4 py-3">${r.total_price}</td>
                                <td class="px-4 py-3">${r.input_date}</td>
                                <td class="px-4 py-3">${r.pic}</td>
                                <td class="px-4 py-3">${r.vendor}</td>
                            </tr>
                        `;
                    });

                    document.getElementById('stockRecapBody').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('stockRecapBody').innerHTML =
                        '<tr><td colspan="9" class="px-4 py-3 text-center">Gagal memuat data</td></tr>';
                });
        }
    </script>
@endpush
