<table>
    <thead>
    <tr>
        <th rowspan="4" colspan="6" style="font-weight: bold">
            {{ strtoupper($optional['outlet']) }} <br>
            {{ $optional['title'] }}
        </th>
    </tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr>
        <th>#</th>
        <th>No. Nota</th>
        <th>Tanggal Transaksi</th>
        <th>Total HPP</th>
        <th>Total Harga Jual</th>
        <th>Total Omzet</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $key => $sale)
        @php
            $hpp = @$sale->calculateHpp();
            $pay = @$sale->paid_amount;
        @endphp
        <tr>
            <td>{{ $key + 1 + (((request('page') ?? 1) - 1) * 15) }}</td>
            <td>{{ $sale->code }}</td>
            <td>{{ parse_date_time($sale->created_at) }}</td>
            <td>{{ rp_format($hpp) }}</td>
            <td>{{ rp_format($pay) }}</td>
            <td>{{ rp_format($pay - $hpp) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
