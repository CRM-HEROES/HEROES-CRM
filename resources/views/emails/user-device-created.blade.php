@extends('emails.layout')

@section('main-title')
Nouvel appareil de connexion
@endsection
 
@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;">
    Bonjour {{ $user->name }},<br>
    <br>
    Nous venons d’identifier un nouvel appareil qui est connecté sur votre compte.<br>
    Veuillez trouver ci-dessous les details à propos de l’appareil:<br>
    <br>

    <ul style="font-size: 15px;">
        <li><b style="color: black;">Modèle:</b> {{ $device->model }}</li>
        <li><b style="color: black;">SE:</b> {{ $device->platform }} {{ $device->platform_version }}</li>

        @if (isset($session) && $session && isset($session->latitude) && isset($session->longitude))
        <li><b style="color: black;">Navigateur:</b> {{ $session->browser }} {{ $session->browser_version }}</li>
        <li><b style="color: black;">Location:</b> {{ implode(' ', array_filter([$session->postal_code, $session->city, $session->region, $session->country_name])) }}, <a href="https://www.google.com/maps?q={{ $session->latitude }},{{ $session->longitude }}" target="_blank">Voir sur Google MAP</a><br />
        Si vous avez un souci avec l'URL Google MAP, copiez et utilisez l'URL suivant: <br />
        https://www.google.com/maps?q={{ $session->latitude }},{{ $session->longitude }}</li>
        @endif

    </ul>
</div>
@endsection
