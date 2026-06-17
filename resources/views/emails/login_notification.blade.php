<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #1d4ed8; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #bfdbfe; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px; color: #374151; }
        .body h2 { color: #1d4ed8; margin-top: 0; }
        .info-box { background: #eff6ff; border-left: 4px solid #1d4ed8; padding: 16px; border-radius: 4px; margin: 24px 0; }
        .info-box p { margin: 4px 0; font-size: 14px; }
        .alert-box { background: #fef9c3; border-left: 4px solid #ca8a04; padding: 16px; border-radius: 4px; margin: 24px 0; font-size: 13px; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐄 BovWeight CR</h1>
            <p>Notificación de acceso</p>
        </div>
        <div class="body">
            <h2>Nuevo inicio de sesión detectado</h2>
            <p>Hola <strong>{{ $usuario->nombre }}</strong>, se registró un ingreso a tu cuenta.</p>

            <div class="info-box">
                <p><strong>Fecha y hora:</strong> {{ $fechaHora }}</p>
                <p><strong>Dirección IP:</strong> {{ $ip }}</p>
                <p><strong>Cédula:</strong> {{ $usuario->cedula }}</p>
            </div>

            <div class="alert-box">
                ⚠️ Si no fuiste vos quien inició sesión, cambiá tu contraseña de inmediato
                y contactá al administrador del sistema.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BovWeight CR — IF7100 Ingeniería del Software
        </div>
    </div>
</body>
</html>
