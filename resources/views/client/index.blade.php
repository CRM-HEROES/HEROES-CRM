<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <title>Heroes CRM</title>
        <link rel="icon" href="/images/logo.jpg" />
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Cache-control" content="public">
        <title>{{ env('APP_NAME') }}</title>
        <link href="https://fonts.cdnfonts.com/css/inter" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <script>
            window.APP_URL = @json(env('APP_URL'));
        </script>
        <script src="{{ mix('js/client/app.js') }}" defer></script>
    </head>
    <body>
        <div id="app" class="" style="overflow: auto; position: absolute; width: 100%; height: 100%">
            <router-view></router-view>
        </div>
    </body>
</html>