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
        <th>Metode Pembayaran</th>
        <th>Jumlah Transaksi</th>
        <th>Total Nominal</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $sale)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->method }}</td>
            <td>{{ rp_format($sale->jumlah_transaksi) }}</td>
            <td>{{ rp_format($sale->total_nominal) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
