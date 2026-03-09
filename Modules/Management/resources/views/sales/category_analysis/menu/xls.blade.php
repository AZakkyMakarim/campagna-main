<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="8" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {{ $optional['title'] }}
        </th>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr>
        <th>#</th>
        <th>Nama Menu</th>
        <th>SKU</th>
        <th>Kategori</th>
        <th>Qty</th>
        <th>Total HPP</th>
        <th>Total Harga Jual</th>
        <th>Total Omzet</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $sale)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->name }}</td>
            <td>{{ $sale->sku }}</td>
            <td>{{ strtoupper($sale->category) }}</td>
            <td>{{ $sale->qty_terjual }}</td>
            <td>{{ rp_format($sale->total_hpp) }}</td>
            <td>{{ rp_format($sale->total_harga_jual) }}</td>
            <td>{{ rp_format($sale->total_omzet) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
