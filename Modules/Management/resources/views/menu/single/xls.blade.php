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
        <th>Harga Jual</th>
        <th>Nama Bahan</th>
        <th>Tipe Bahan</th>
        <th>Qty</th>
    </tr>
    </thead>
    <tbody>
    @php
        $no = 1;
    @endphp
    @foreach($data as $key => $menu)
        @foreach($menu->components as $component)
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $menu->name }}</td>
                <td>{{ $menu->sku }}</td>
                <td>{{ strtoupper($menu->category) }}</td>
                <td>{{ $menu->sell_price }}</td>
                <td>{{ $component->componentable->name }}</td>
                <td>{{ $component->componentable->type }}</td>
                <td>{{ $component->qty }}</td>
            </tr>
        @endforeach
        @php $no++ @endphp
    @endforeach
    </tbody>
</table>
