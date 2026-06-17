<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #374151; background: #f9fafb; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #065f46; color: white; padding: 32px 40px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 32px 40px; }
        .body p { line-height: 1.6; margin: 0 0 16px; }
        .btn { display: inline-block; background: #065f46; color: white; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; font-size: 16px; margin: 16px 0; }
        .note { font-size: 13px; color: #6b7280; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; }
        .url { word-break: break-all; color: #059669; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BovWeight CR</h1>
        </div>
        <div class="body">
            <p>Hola, <strong>{{ $usuario->nombre }}</strong>.</p>
            <p>Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta en <strong>BovWeight CR</strong>.</p>
            <p>Hacé clic en el botón de abajo para crear una nueva contraseña:</p>

            <a href="{{ $resetUrl }}" class="btn">Restablecer contraseña</a>

            <p>Este enlace expira en <strong>60 minutos</strong>.</p>

            <div class="note">
                <p>Si no solicitaste este cambio, podés ignorar este correo. Tu contraseña no será modificada.</p>
                <p>Si el botón no funciona, copiá este enlace en tu navegador:</p>
                <p class="url">{{ $resetUrl }}</p>
            </div>
        </div>
    </div>
</body>
</html>
