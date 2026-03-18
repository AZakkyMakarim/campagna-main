<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="11" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {!! $optional['title'] !!}
        </th>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr>
        <th>#</th>
        <th>Jenis Order</th>
        <th>Jumlah Transaksi</th>
        <th>Total Nominal</th>
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
        $total_transaksi = 0;
        $total_nominal = 0;
        $total_qty = 0;
        $total_hpp = 0;
        $total_subtotal = 0;
        $total_profit = 0;

        $no = 1;
    @endphp


    @foreach($data as $key => $sale)
        @foreach($sale->items as $item)

            @php
                $total_transaksi += $sale->jumlah_transaksi;
                $total_nominal += $sale->total_nominal;
                $total_qty += $item->qty;
                $total_hpp += $item->hpp;
                $total_subtotal += $item->subtotal;
                $total_profit += ($item->subtotal - $item->hpp);
            @endphp

            <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $sale->jenis_order }}</td>
                <td>{{ $sale->jumlah_transaksi }}</td>
                <td>{{ $sale->total_nominal }}</td>
                <td>{{ $item->menu->name }}</td>
                <td>{{ $item->menu->sku }}</td>
                <td>{{ strtoupper($item->menu->category) }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->hpp }}</td>
                <td>{{ $item->subtotal }}</td>
                <td>{{ $item->subtotal - $item->hpp }}</td>
            </tr>
        @endforeach
    @endforeach

    <tr class="font-bold bg-gray-100">
        <td colspan="2" class="text-right">TOTAL</td>
        <td>{{ $total_transaksi }}</td>
        <td>{{ $total_nominal }}</td>
        <td colspan="3"></td>
        <td>{{ $total_qty }}</td>
        <td>{{ $total_hpp }}</td>
        <td>{{ $total_subtotal }}</td>
        <td>{{ $total_profit }}</td>
    </tr>

    </tbody>
</table>
