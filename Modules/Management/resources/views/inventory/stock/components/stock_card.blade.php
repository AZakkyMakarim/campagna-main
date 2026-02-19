<div class="overflow-hidden rounded-lg shadow-lg border border-gray-200 bg-white">
    <table class="w-full text-sm text-left">
        <thead class="bg-orange-700 text-white uppercase text-xs">
        <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3">Nama Bahan</th>
            <th class="px-4 py-3">Tipe</th>
            <th class="px-4 py-3">Stok</th>
            <th class="px-4 py-3">Satuan</th>
            <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
        @foreach($ingredients as $i => $item)
            <tr>
                <td class="px-4 py-2">{{ $i+1 }}</td>
                <td class="px-4 py-2 font-medium">{{ $item->name }}</td>
                <td class="px-4 py-2 uppercase text-xs">{{ $item->type }}</td>
                <td class="px-4 py-2">{{ number_format($item->stock, 2, ',', '.') }}</td>
                <td class="px-4 py-2">{{ $item->baseUnit->name }}</td>
                <td class="px-4 py-2 text-center">
                    <button
                        @click="$dispatch('open-modal', 'modal-form-stock-card')"
                        onclick="openStockCard({{ $item->id }})"
                        class="px-3 py-2 bg-yellow-500 text-white rounded">
                        <i class="fa fa-magnifying-glass"></i>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<x-modal id="modal-form-stock-card" idModalTitle="modal-title-stock-card" idSubModalTitle="modal-sub-title-stock-card" title="Kartu Stok" icon="fa-magnifying-glass" size="7xl">
    <div class="bg-white w-full rounded-lg shadow-lg p-4">
        <div class="relative max-h-[620px] overflow-y-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm text-left">
                <thead class="bg-orange-700 text-white uppercase text-xs sticky top-0 z-10 shadow-md">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Masuk</th>
                    <th class="px-4 py-3">Keluar</th>
                    <th class="px-4 py-3">Akhir</th>
                    <th class="px-4 py-3">Harga Satuan</th>
                    <th class="px-4 py-3">Total Harga</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">Waktu Update</th>
                    <th class="px-4 py-3">PIC</th>
                </tr>
                </thead>

                <tbody id="stockCardBody" class="divide-y divide-gray-200 bg-white"></tbody>
            </table>
        </div>
    </div>
</x-modal>

@push('js')
    <script>
        const stockCardUrl = "{{ route('management.inventory.stock.card', ':id') }}";

        function openStockCard(id) {
            document.getElementById('stockCardBody').innerHTML =
                '<tr><td colspan="6">Loading...</td></tr>';

            const url = stockCardUrl.replace(':id', id);

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    document.getElementById('modal-sub-title-stock-card').innerText = res.ingredient ?? '-';
                    let no = 1;
                    let html = '';
                    res.rows.forEach(r => {
                        html += `
                            <tr>
                                <td class="px-4 py-3">${no++}</td>
                                <td class="px-4 py-3">${r.date}</td>
                                <td class="px-4 py-3">${r.type}</td>
                                <td class="px-4 py-3 text-green-600">${r.in || '-'}</td>
                                <td class="px-4 py-3 text-red-600">${r.out || '-'}</td>
                                <td class="px-4 py-3">${r.closing}</td>
                                <td class="px-4 py-3">${r.cost_per_unit}</td>
                                <td class="px-4 py-3">${r.total_price}</td>
                                <td class="px-4 py-3">${r.code}</td>
                                <td class="px-4 py-3">${r.updated_at}</td>
                                <td class="px-4 py-3">${r.pic}</td>
                            </tr>
                        `;
                    });

                    document.getElementById('stockCardBody').innerHTML = html;
                })
                .catch(() => {
                    document.getElementById('stockCardBody').innerHTML =
                        '<tr><td colspan="6">Gagal memuat data</td></tr>';
                });
        }
    </script>
@endpush
