@extends('emails.layout')

@section('main-title')
Compte bloqué
@endsection

@section('body')
<span>
    Bonjour {{ $user->name }},<br><br>
    Votre compte a été bloqué suite à {{ $max }} tentatives de connexion échouées.<br>
    Pour le débloquer, veuillez demander à l'administrateur de votre projet de réactiver votre accès
    @if(count($adminEmails))
        :<br><strong>{{ implode(', ', $adminEmails) }}</strong>
    @else
        .
    @endif
    <br><br>Une fois votre accès débloqué, vous recevrez un email pour choisir un nouveau mot de passe.
</span>
@endsection
