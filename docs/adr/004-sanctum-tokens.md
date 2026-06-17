# ADR-004 — Sanctum para autenticación de la API móvil

**Estado:** Aceptada
**Fecha:** 2026-06-11
**Decisores:** equipo BovWeight CR

## Contexto

La app móvil (Ionic + Vue 3 + Capacitor) debe autenticarse contra la API REST
de Laravel. Necesitamos un mecanismo seguro, simple de implementar en ambos
lados, y que no requiera infraestructura externa (Auth0, Cognito, etc.).

## Decisión

Adoptar **Laravel Sanctum** con autenticación por **personal access tokens**:

- Login: `POST /api/login` con cédula + contraseña devuelve un token Bearer.
- Cliente: el móvil guarda el token en `Capacitor Preferences` y lo manda en
  el header `Authorization: Bearer {token}` de cada request siguiente.
- Logout: `POST /api/logout` revoca solo el token usado (no todos).

## Alternativas consideradas

1. **JWT** (firebase/php-jwt o tymon/jwt-auth).
   ⚠️ Más flexibles pero más complejos. Para nuestro caso, Sanctum es suficiente.

2. **OAuth2 (Laravel Passport)**.
   ❌ Overkill para una app cliente única y sin third-party clients.

3. **Sesiones server-side**.
   ❌ Cookies + CSRF se vuelven incómodos en clientes móviles nativos.

## Consecuencias

✅ Setup mínimo: `php artisan install:api` lo configura automáticamente.
✅ Los tokens se guardan en la tabla `personal_access_tokens` (revocables).
✅ Mismo backend sirve a web (sesión) y móvil (token) sin duplicar código.

⚠️ Los tokens **no expiran** por default. Para producción agregar expiración
   con `Sanctum::usePersonalAccessTokenModel(...)` y revisar `tokens()->where('last_used_at', ...)`.
