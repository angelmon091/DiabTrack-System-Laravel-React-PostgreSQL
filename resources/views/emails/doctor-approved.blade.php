<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil médico aprobado</title>
</head>
<body style="margin:0;background:#f2f8fb;font-family:Arial,sans-serif;color:#12233f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#fff;border-radius:16px;padding:32px;">
                <tr><td style="font-size:24px;font-weight:700;color:#00a8c6;">DiabTrack</td></tr>
                <tr><td style="padding-top:24px;font-size:20px;font-weight:700;">Tu perfil médico fue aprobado</td></tr>
                <tr><td style="padding-top:16px;line-height:1.6;">Hola, {{ $name }}. El equipo administrador verificó tu información profesional. Ya puedes vincular pacientes mediante sus códigos de invitación.</td></tr>
                <tr><td align="center" style="padding-top:28px;"><a href="{{ $dashboardUrl }}" style="display:inline-block;background:#00a8c6;color:#fff;text-decoration:none;font-weight:700;padding:14px 24px;border-radius:10px;">Ir al panel médico</a></td></tr>
                <tr><td style="padding-top:24px;font-size:13px;color:#64748b;">La aprobación no sustituye las obligaciones profesionales ni regulatorias correspondientes.</td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
