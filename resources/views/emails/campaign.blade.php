@extends('emails.layout')

@section('main-title')
{{ config('app.name') }}
@endsection

@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;">
    {!! $body !!}
</div>
@endsection
