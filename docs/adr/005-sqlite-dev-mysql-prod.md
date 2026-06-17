# ADR-005 — SQLite en desarrollo, MySQL en producción

**Estado:** Aceptada
**Fecha:** 2026-06-11
**Decisores:** equipo BovWeight CR

## Contexto

El enunciado del curso (sección 5) exige **MySQL como gestor de BD**. Sin
embargo durante el desarrollo varios integrantes del equipo tuvieron
problemas con servicios locales de MySQL/MariaDB (conflictos de puerto entre
instancias, configuraciones de XAMPP/Laragon, contraseñas perdidas, etc.).

Estos problemas bloqueaban a integrantes que solo necesitaban probar features
de UI/lógica sin tocar BD.

## Decisión

Usar **SQLite** en entornos de desarrollo local (config via `.env`) y **MySQL**
en producción y CI (cuando aplique).

Ambos motores funcionan con el mismo set de migraciones Laravel — el switch
es solo cambiar `DB_CONNECTION=sqlite` → `DB_CONNECTION=mysql` y ajustar
host/usuario/password.

`.env.example` se entrega configurado para MySQL (default del proyecto).
Cada developer puede cambiar localmente su `.env` a SQLite sin afectar al resto.

## Consecuencias

✅ Cero fricción para developers que no toquen BD directamente.
✅ Tests del CI corren en SQLite en memoria (mucho más rápido que levantar
   MySQL en cada job).
✅ El switch a MySQL para la entrega final no requiere cambios de código,
   solo de configuración.

⚠️ Algunas features de MySQL (tipos JSON específicos, fulltext) no están
   en SQLite. Solucionamos manteniéndonos en el subset común — no usamos
   features exclusivas de un motor.

⚠️ El despliegue final SÍ debe correr sobre MySQL para cumplir el enunciado.
   Esta condición está documentada en `architecture.md` sección 9.
