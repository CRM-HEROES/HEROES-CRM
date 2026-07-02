@extends('emails.layout')

@section('main-title')
Nouveau message
@endsection

@section('icon'){{ $icon }}@endsection

@section('title'){{ $title }}@endsection

@section('body')
<div style="max-width: 650px; margin: auto; text-align: left;margin-bottom: 50px;">
    {!! $threadMessage !!}
</div>
@endsection
