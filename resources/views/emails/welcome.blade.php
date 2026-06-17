<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #15803d; padding: 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #bbf7d0; margin: 8px 0 0; font-size: 14px; }
        .body { padding: 32px; color: #374151; }
        .body h2 { color: #15803d; margin-top: 0; }
        .info-box { background: #f0fdf4; border-left: 4px solid #15803d; padding: 16px; border-radius: 4px; margin: 24px 0; }
        .info-box p { margin: 4px 0; font-size: 14px; }
        .btn { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #15803d; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐄 BovWeight CR</h1>
            <p>Sistema de estimación de peso bovino</p>
        </div>
        <div class="body">
            <h2>¡Bienvenido, {{ $usuario->nombre }}!</h2>
            <p>Tu cuenta en BovWeight CR ha sido creada exitosamente. Ya podés ingresar al sistema con tus credenciales.</p>

            <div class="info-box">
                <p><strong>Cédula:</strong> {{ $usuario->cedula }}</p>
                <p><strong>Correo:</strong> {{ $usuario->correo }}</p>
                <p><strong>Rol:</strong>
                    @if($usuario->id_tipo_usuario === 2) Ganadero
                    @elseif($usuario->id_tipo_usuario === 3) Veterinario
                    @else Administrador
                    @endif
                </p>
                <p><strong>Fecha de registro:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <a href="{{ url('/login') }}" class="btn">Iniciar sesión</a>

            <p style="margin-top: 24px; font-size: 13px; color: #6b7280;">
                Si no creaste esta cuenta, ignorá este correo o contactá al administrador del sistema.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BovWeight CR — IF7100 Ingeniería del Software
        </div>
    </div>
</body>
</html>
