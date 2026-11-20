@php
    /*
     * Email of an automation, drawn with the design chosen in the builder
     * (App\Workflows\Actions\EmailDesign): the colours and the font are
     * validated there, the texts are escaped here, the body is HTML already cleaned.
     */
    $d = $design;
    $branded = $d['style'] === 'branded';
    $title = $d['category'] !== '' ? $d['category'] : null;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { width: 100% !important; }
            .email-padding { padding: 24px 12px !important; }
            .email-card { padding: 26px 18px !important; border-radius: 0 !important; }
        }
        .email-body img { max-width: 100%; height: auto; }
        .email-body a { color: {{ $d['accent_color'] }}; }
    </style>
</head>
@if ($branded)
<body style="margin:0; padding:0; background:{{ $d['background_color'] }}; color:{{ $d['text_color'] }}; font-family:{!! $fontStack !!};">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:{{ $d['background_color'] }};">
        <tr>
            <td align="center" class="email-padding" style="padding:36px 16px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" class="email-shell" style="width:600px; max-width:600px;">
                    @if ($d['brand_name'] !== '' || $d['brand_label'] !== '' || ($d['logo'] === 'project' && $logo))
                    <tr>
                        <td style="background:{{ $d['header_color'] }}; border-radius:12px 12px 0 0; padding:22px 34px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:21px; font-weight:bold; color:#ffffff;">
                                        @if ($d['logo'] === 'project' && $logo)
                                            <img src="{{ $logo }}" alt="{{ $d['brand_name'] }}" height="36" style="height:36px; vertical-align:middle; border:0;">
                                        @endif
                                        {{ $d['brand_name'] }}
                                    </td>
                                    @if ($d['brand_label'] !== '')
                                    <td align="right" style="font-size:12px; letter-spacing:1px; color:#ffffff; opacity:.8; text-transform:uppercase;">
                                        {{ $d['brand_label'] }}
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="email-card" style="background:#ffffff; padding:36px 42px; border-radius:{{ ($d['brand_name'] !== '' || $d['brand_label'] !== '' || ($d['logo'] === 'project' && $logo)) ? '0 0 12px 12px' : '12px' }};">
                            @if ($title)
                            <div style="font-size:12px; font-weight:bold; letter-spacing:1.4px; color:{{ $d['accent_color'] }}; text-transform:uppercase; margin-bottom:14px;">
                                {{ $title }}
                            </div>
                            @endif
                            @if ($d['show_title'] && $subject !== '')
                            <h1 style="margin:0 0 22px; color:{{ $d['header_color'] }}; font-size:26px; line-height:34px; font-weight:700;">
                                {{ $subject }}
                            </h1>
                            @endif
                            <div class="email-body" style="color:{{ $d['text_color'] }}; font-size:16px; line-height:26px; text-align:left;">
                                {!! $body !!}
                            </div>
                            @if ($d['show_signature'] && ($d['closing'] !== '' || $d['signature_name'] !== ''))
                            <div style="margin-top:28px; padding-top:20px; border-top:1px solid #e7edf0; color:{{ $d['text_color'] }}; font-size:14px; line-height:22px;">
                                {{ $d['closing'] }}<br>
                                <strong style="color:{{ $d['header_color'] }};">{{ $d['signature_name'] }}</strong>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @if ($d['show_footer'] && ($d['sent_by'] !== '' || $d['rights'] !== ''))
                    <tr>
                        <td align="center" style="padding:22px 20px 0; color:#84929a; font-size:12px; line-height:20px;">
                            @if ($d['sent_by'] !== '')<div style="margin-bottom:5px;">{{ $d['sent_by'] }}</div>@endif
                            @if ($d['rights'] !== '')<div>{{ $d['rights'] }}</div>@endif
                        </td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>
</body>
@else
<body style="margin:0; padding:0; background:#ffffff; color:{{ $d['text_color'] }}; font-family:{!! $fontStack !!};">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td align="left" class="email-padding" style="padding:24px 16px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" class="email-shell" style="width:600px; max-width:600px;">
                    <tr>
                        <td>
                            @if ($d['logo'] === 'project' && $logo)
                                <img src="{{ $logo }}" alt="{{ $d['brand_name'] }}" height="40" style="height:40px; border:0; margin-bottom:18px;">
                            @endif
                            <div class="email-body" style="color:{{ $d['text_color'] }}; font-size:15px; line-height:24px; text-align:left;">
                                {!! $body !!}
                            </div>
                            @if ($d['show_signature'] && ($d['closing'] !== '' || $d['signature_name'] !== ''))
                            <div style="margin-top:22px; color:{{ $d['text_color'] }}; font-size:15px; line-height:24px;">
                                {{ $d['closing'] }}<br>
                                <strong style="color:{{ $d['header_color'] }};">{{ $d['signature_name'] }}</strong>
                            </div>
                            @endif
                            @if ($d['show_footer'] && ($d['sent_by'] !== '' || $d['rights'] !== ''))
                            <div style="margin-top:26px; padding-top:14px; border-top:1px solid #e7edf0; color:#84929a; font-size:12px; line-height:19px;">
                                @if ($d['sent_by'] !== '')<div>{{ $d['sent_by'] }}</div>@endif
                                @if ($d['rights'] !== '')<div>{{ $d['rights'] }}</div>@endif
                            </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
@endif
</html>
