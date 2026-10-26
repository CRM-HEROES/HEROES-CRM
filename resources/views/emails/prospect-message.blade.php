<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        @media only screen and (max-width: 620px) {
            .email-shell { width: 100% !important; }
            .email-padding { padding: 28px 18px !important; }
            .email-card { border-radius: 0 !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f3f6f8; color:#24323d; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6f8;">
        <tr>
            <td align="center" class="email-padding" style="padding:42px 18px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" class="email-shell" style="width:600px; max-width:600px;">
                    <tr>
                        <td style="background:#163b52; border-radius:12px 12px 0 0; padding:25px 34px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:21px; font-weight:bold; color:#ffffff;">
                                        {{ $project->name }}
                                    </td>
                                    <td align="right" style="font-size:12px; letter-spacing:1px; color:#b9d6df; text-transform:uppercase;">
                                        Heroes CRM
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="email-card" style="background:#ffffff; padding:38px 42px; border-radius:0 0 12px 12px; box-shadow:0 8px 24px rgba(22,59,82,.10);">
                            <div style="font-size:12px; font-weight:bold; letter-spacing:1.4px; color:#4a8796; text-transform:uppercase; margin-bottom:14px;">
                                {{ $category }}
                            </div>
                            <h1 style="margin:0 0 24px; color:#163b52; font-size:28px; line-height:36px; font-weight:700;">
                                {{ $subject }}
                            </h1>
                            <div style="border-top:1px solid #e7edf0; padding-top:26px; color:#42515b; font-size:16px; line-height:27px;">
                                {!! nl2br(e($body)) !!}
                            </div>
                            <div style="margin-top:30px; padding-top:22px; border-top:1px solid #e7edf0; color:#6d7b83; font-size:14px; line-height:22px;">
                                Bien cordialement,<br>
                                <strong style="color:#163b52;">{{ $project->name }}</strong>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 20px 0; color:#84929a; font-size:12px; line-height:20px;">
                            <div style="margin-bottom:5px;">Un message envoyé par {{ $project->name }}.</div>
                            <div>
                                &copy; {{ date('Y') }} {{ $project->name }} · Tous droits réservés.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>