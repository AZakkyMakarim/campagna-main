<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="4" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {!! $optional['title'] !!}
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
    @php
        $total_transaksi = 0;
        $total_nominal = 0;
    @endphp

    @foreach($data as $key => $sale)
        @php
            $total_transaksi += $sale->jumlah_transaksi;
            $total_nominal += $sale->total_nominal;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->method }}</td>
            <td>{{ $sale->jumlah_transaksi }}</td>
            <td>{{ $sale->total_nominal }}</td>
        </tr>
    @endforeach
    <tr class="font-bold bg-gray-100">
        <td colspan="2" class="text-right">TOTAL</td>
        <td>{{ $total_transaksi }}</td>
        <td>{{ $total_nominal }}</td>
    </tr>
    </tbody>
</table>
