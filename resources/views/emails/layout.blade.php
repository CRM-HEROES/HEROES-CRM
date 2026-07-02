<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <style type="text/css" rel="stylesheet" media="all">
        /* Media Queries */
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>
</head>

<?php

$color1 = "#898989";
$color2 = "#000000";

$style = [
    /* Layout ------------------------------ */

    'body' => "margin: 0; padding: 0; width: 100%; background-color: #FFF; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;",

    'header' => 'text-align: center; border-bottom: 1px solid #EEEEEE;',
    'title1' => "line-height: 40px; font-size: 14px; font-weight: 600; color: $color1;",
    'project-icon' => 'width: 100px; vertical-align: middle;',
    'icon' => 'width: 40px; vertical-align: middle; margin-right: 10px;',

    'content' => "text-align: center; padding: 50px 0;",
    'title2' => "color: $color2; font-size: 40px; font-weight: 600; margin-top: 0; margin-bottom: 40px; width: 100%;",
    'main-content' => "color: $color1; font-size: 18px; line-height: 28px; margin-bottom: 20px; width: 100%;",

    'footer' => 'height: 60px; line-height: 60px; text-align: center; border-top: 1px solid #EEEEEE; font-size: 14px;',
    'anchor' => 'color: #3869D4;',
];
?>

<body style="{{ $style['body'] }}">

	<div style="{{ $style['header'] }}">

		<div style="{{ $style['title1'] }}">
			<img src="@yield('icon', url('favicon.ico'))" style="{{ $style['project-icon'] }}" />
			<div>@yield('title', config('app.name'))</div>
		</div>

	</div>

	<div style="{{ $style['content'] }}">

		<h1 style="{{ $style['title2'] }}">
			@section('main-title')
			{{ config('app.name') }}
			@show
		</h1>

		<div style="{{ $style['main-content'] }}">
			@section('body')
			@show
		</div>

	</div>

	<div style="{{ $style['footer'] }}">

		<span>
			<img src="{{ url('images/logo.jpg') }}" style="{{ $style['icon'] }}" />
			&copy; {{ date('Y') }} <a style="{{ $style['anchor'] }}" href="{{ url('/') }}" target="_blank">{{ config('app.name') }}</a>. Tous droits réservés.
		</span>

	</div>

</body>
</html>