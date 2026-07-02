@extends('emails.layout')

@section('main-title')
Se connecter sur<br>
Heroes CRM
@endsection

@section('body')
<div style="max-width: 650px; margin: auto; margin-bottom: 50px;">
Bonjour {{ $prospect->full_name }},<br>
<br>
Pour vous connecter, <br>
nous vous prions de cliquer sur le lien ci-après:<br>
</div>
<div style="max-width: 650px; margin: auto; text-align: center;">
    <a href="{{ $url }}" style="padding: 10px 30px; display: inline-block; border-radius: 5px; color: white; background-color: #2500B7; font-size: 15px; font-weight: bold; text-decoration: none;">Se connecter</a>
</div>
@endsection