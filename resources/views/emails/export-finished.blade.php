@extends('emails.layout')

@section('main-title')
Export terminé
@endsection

@section('icon')
{{ $icon }}
@endsection

@section('title')
{{ $title }}
@endsection

@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;margin-bottom: 50px;">
Bonjour, <br>
votre export est terminé!<br><br>
Veuillez télécharger les fichiers en PJ.
</div>
@endsection
