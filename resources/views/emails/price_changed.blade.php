<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>OLX Price Change Notification</title>
</head>
<body>
<p>The price of the advert at <a href="{{ $url }}">{{ $url }}</a> has changed.</p>
<p>
    Previous Price: {{ $prevPrice ?? 'Unknown' }} [UAH] <br/>
    New Price: {{ $newPrice ?? 'Unavailable' }} [UAH].
</p>
<div>Price information retrieved at {{$parsedAt}}</div>
<br/>
<footer>Sincerely, {{config('app.name')}} Team</footer>
</body>
</html>
