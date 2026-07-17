<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación de DiabTrack</title>
</head>
<body style="margin:0;background:#f2f8fb;font-family:Arial,sans-serif;color:#12233f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border-radius:16px;padding:32px;">
                    <tr><td style="font-size:24px;font-weight:700;color:#00a8c6;">DiabTrack</td></tr>
                    <tr><td style="padding-top:24px;font-size:18px;font-weight:700;">Verifica tu correo electrónico</td></tr>
                    <tr><td style="padding-top:16px;line-height:1.6;">Hola, {{ $name }}. Utiliza el siguiente código para completar tu registro:</td></tr>
                    <tr>
                        <td align="center" style="padding:28px 0;">
                            <span style="display:inline-block;letter-spacing:10px;font-size:34px;font-weight:700;color:#12233f;background:#e8f7fb;border-radius:12px;padding:18px 20px 18px 30px;">{{ $code }}</span>
                        </td>
                    </tr>
                    <tr><td style="line-height:1.6;">El código vence en {{ $expiresInMinutes }} minutos. Si no solicitaste esta cuenta, puedes ignorar este mensaje.</td></tr>
                    <tr><td style="padding-top:24px;font-size:13px;color:#64748b;">DiabTrack nunca te pedirá este código fuera de la pantalla de verificación.</td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
