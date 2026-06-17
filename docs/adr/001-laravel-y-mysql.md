# ADR-001 — Stack backend Laravel + MySQL

**Estado:** Aceptada
**Fecha:** 2026-06-10
**Decisores:** equipo BovWeight CR

## Contexto

Necesitamos elegir el stack backend para implementar la API REST y el panel
admin web del sistema BovWeight CR.

## Restricciones del curso

El enunciado del proyecto (sección 5 del PDF) exige:
- API REST en Laravel (PHP 8.x).
- MySQL como gestor de base de datos.

## Decisión

Adoptar **Laravel 13** sobre **PHP 8.3** con **MySQL/MariaDB** como motor relacional.

## Consecuencias

✅ Cumplimos restricción del curso.
✅ Laravel ofrece scaffolding maduro: Eloquent ORM, sistema de migraciones,
   Sanctum para auth API, Blade para vistas, routing declarativo.
✅ Comunidad amplia → resolución rápida de dudas.

⚠️ El equipo tiene experiencia heterogénea en Laravel — riesgo mitigado con
   pair programming y revisión de PRs.

⚠️ En desarrollo local usamos SQLite para evitar requerir servicios externos
   (ver ADR-005). El switch a MySQL es solo cambio de `.env`.
