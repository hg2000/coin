<h3>Coin Rate Alert</h3>

@if (!$increasesBtc->isEmpty())
<p>
The following coins have increased at least by {{ $rate }} % since {{ $date }}:
</p>

<table>
    <thead>
        <th>Currency</th>
        <th>Increase BTC</th>
        <th>Increase Fiat</th>
    </thead>
    <tbody>
        @foreach ($increasesBtc as $item)
            <tr>
                <td>{{ $item['currency'] }}</td>
                <td>{{ $item['diffBtc'] }} %</td>
                <td>{{ $item['diffFiat'] }} %</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if (!$decreasesBtc->isEmpty())
<p>
The following coins have decreased at least by {{ $rate }} % since {{ $date }}:
</p>

<table>
    <thead>
        <th>Currency</th>
        <th>Decrease BTC</th>
        <th>Decrease Fiat</th>
    </thead>
    <tbody>
        @foreach ($decreasesBtc as $item)
            <tr>
                <td>{{ $item['currency'] }}</td>
                <td>{{ $item['diffBtc'] }} %</td>
                <td>{{ $item['diffFiat'] }} %</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
