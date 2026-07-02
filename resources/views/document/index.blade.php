<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style type="text/css">
            @foreach ($document->fonts as $font)
            @font-face {
                font-family: {{ $font->name }};
                font-style: {{ $font->style }};
                font-weight: {{ $font->weight }};
                src: url({{ \Illuminate\Support\Facades\Storage::disk('fonts')->path($font->path) }});
            }
            @endforeach

            @page {
                margin: 0px 0px 0px 0px !important;
                padding: 0px 0px 0px 0px !important;
                size: {{ intval($page->width) }}mm {{ intval($page->height) }}mm;
                page-break-inside: auto;
            }
        </style>
    </head>

    <body>
        @foreach ($fields as $field)
            <div class="field" style="position:fixed;line-height:normal;font-family:sans-serif;font-size:12px;transform-origin: top left;{{ $field->css }}">
                {!! $field->content !!}
            </div>
        @endforeach
    </body>
</html>
