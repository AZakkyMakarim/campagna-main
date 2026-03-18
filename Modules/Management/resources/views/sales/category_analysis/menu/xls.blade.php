<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="8" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {!! $optional['title'] !!}
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
    @php
        $total_qty = 0;
        $total_hpp = 0;
        $total_harga_jual = 0;
        $total_omzet = 0;
    @endphp

    @foreach($data as $key => $sale)
        @php
            $total_qty += $sale->qty_terjual;
            $total_hpp += $sale->total_hpp;
            $total_harga_jual += $sale->total_harga_jual;
            $total_omzet += $sale->total_omzet;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->menu->name }}</td>
            <td>{{ $sale->menu->sku }}</td>
            <td>{{ strtoupper($sale->menu->category) }}</td>
            <td>{{ $sale->qty_terjual }}</td>
            <td>{{ $sale->total_hpp }}</td>
            <td>{{ $sale->total_harga_jual }}</td>
            <td>{{ $sale->total_omzet }}</td>
        </tr>
    @endforeach
    <tr class="font-bold bg-gray-100">
        <td colspan="4" class="text-right">TOTAL</td>
        <td>{{ $total_qty }}</td>
        <td>{{ $total_hpp }}</td>
        <td>{{ $total_harga_jual }}</td>
        <td>{{ $total_omzet }}</td>
    </tr>
    </tbody>
</table>
