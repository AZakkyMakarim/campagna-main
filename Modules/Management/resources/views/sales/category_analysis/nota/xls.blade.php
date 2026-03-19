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
        <th>No. Nota</th>
        <th>Metode Pembayaran</th>
        <th>Jenis Order</th>
        <th>Tanggal Transaksi</th>
        <th>Total HPP</th>
        <th>Total Harga Jual</th>
        <th>Total Omzet</th>
    </tr>
    </thead>
    <tbody>
    @php
        $total_hpp = 0;
        $total_pay = 0;
        $total_profit = 0;
    @endphp
    @foreach($data as $key => $sale)
        @php
            $hpp = $sale->calculateHpp();
            $pay = $sale->grand_total;
            $profit = $pay - $hpp;

            $total_hpp += $hpp;
            $total_pay += $pay;
            $total_profit += $profit;
        @endphp
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $sale->code }}</td>
            <td>{{ $sale->payment->method }}</td>
            <td>{{ $sale->type }}</td>
            <td>{{ parse_date_time($sale->created_at) }}</td>
            <td>{{ $hpp}}</td>
            <td>{{ $pay}}</td>
            <td>{{ $pay - $hpp}}</td>
        </tr>
    @endforeach

    <tr class="font-bold bg-gray-100">
        <td colspan="5" class="text-right">TOTAL</td>
        <td>{{ $total_hpp }}</td>
        <td>{{ $total_pay }}</td>
        <td>{{ $total_profit }}</td>
    </tr>
    </tbody>
</table>
