<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <title>Verify Your Email</title>
</head>
<body>
<h1>Verify Your Email Address</h1>
<p>Hello, {{$login}}</p>
<p>In order to use our OLX adverts price change service, we need to confirm your subscription email.</p>
<p>Please, click the link below to verify your email:</p>
<a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
<p>If you did not subscribe to <a href="{{ route('home') }}" target="_blank">OLX price monitoring</a>, please ignore this email.</p>
<br/>
<footer>Sincerely, {{ config('app.name') }} Team</footer>
</body>
</html>
