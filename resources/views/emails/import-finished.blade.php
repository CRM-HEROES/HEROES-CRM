@extends('emails.layout')

@section('main-title')
Import terminé
@endsection

@section('icon')
{{ $icon }}
@endsection

@section('title')
{{ $title }}
@endsection

@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;margin-bottom: 50px;">
    L'import <b>{{ $import->name }}</b> est terminé.<br>
    Vous pouvez consulter les prospects importés ici: <a href="{{ route('project.prospect.index', ['project' => $import->project->slug]) }}?filters={{ urlencode('{"withImports":[' .  $import->id. ']}') }}">{{ $import->name }}</a>
</div>
@endsection
