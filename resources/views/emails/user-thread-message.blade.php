@extends('emails.layout')

@section('main-title')
Nouveau message
@endsection

@section('icon')
{{ $icon }}
@endsection

@section('title')
{{ $title }}
@endsection

@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;margin-bottom: 50px;">
    Ce message attend votre retour
    [<a href="{{ route('project.prospect.show', ['project' => $prospect->project->slug, 'prospect' => $prospect->id]) }}">Fiche prospect: {{ $prospect->full_name }}</a>]<br><br>
    {!! $threadMessage !!}
</div>
@endsection
