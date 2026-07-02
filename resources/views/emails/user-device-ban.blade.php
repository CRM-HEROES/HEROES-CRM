@extends('emails.layout')

@section('main-title')
Nouvel appareil de connexion
@endsection
 
@section('body')
<span>
    Pour confirmer le bannisement du nouvel appareil connecté,<br>
    veuillez utiliser le code à 6 chiffres suivant
</span><br>
<div style="font-size: 36px; color: #2500B7; width: 100%; text-align: center; margin-top: 30px; letter-spacing: 12px;">
    {{ $code }}
</div>
@endsection
