<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Heroes CRM</title>
        <link rel="icon" href="/images/logo.jpg" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Cache-control" content="public">
        <link href="https://fonts.cdnfonts.com/css/inter" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <script src="{{ mix('js/app.js') }}" defer></script>
        <!--script src="{{ url('js/chart.js') }}" defer></script-->
    </head>
    <body>
        <div id="app" class="" style="overflow: auto; position: absolute; width: 100%; height: 100%">
            <router-view></router-view>
        </div>
    </body>
</html>