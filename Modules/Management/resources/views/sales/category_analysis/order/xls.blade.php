<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="4" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {{ $optional['title'] }}
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
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $sale)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ config('array.order.type')[$sale->jenis_order]['display_name'] }}</td>
            <td>{{ rp_format($sale->jumlah_transaksi) }}</td>
            <td>{{ rp_format($sale->total_nominal) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
