# BovWeight CR — Documentación de arquitectura

> Curso: IF7100 Ingeniería del Software — I Ciclo 2026
> Universidad de Costa Rica, Sede de Guanacaste

## 1. Visión general

BovWeight CR es un sistema de información para asistir a ganaderos de la Región
Chorotega en la estimación de peso bovino a partir de fotografías, sin depender
de báscula industrial. El sistema **no** sustituye la báscula oficial para
transacciones comerciales legales (limitación documentada en la UI).

## 2. Diagrama de contexto (C4 nivel 1)

```
                   ┌─────────────────┐
                   │   Ganadero      │
                   │   (móvil)       │
                   └────────┬────────┘
                            │ HTTPS + token Sanctum
                            ▼
   ┌─────────────────────────────────────────────────────┐
   │                                                     │
   │  Sistema BovWeight CR                               │
   │                                                     │
   │  ┌─────────────────────┐    POST /estimar           │
   │  │  API Laravel        │ ──────────────┐            │
   │  │  (PHP 8.3)          │               ▼            │
   │  └─────────┬───────────┘    ┌────────────────────┐  │
   │            │                │  ml_service        │  │
   │            │                │  Python 3.11       │  │
   │            ▼                │  Flask + YOLOv8    │  │
   │     ┌────────────┐          └────────────────────┘  │
   │     │  SQLite/   │                                  │
   │     │  MySQL     │                                  │
   │     └────────────┘                                  │
   │                                                     │
   └─────────────────────────────────────────────────────┘
                            ▲
                            │
                   ┌────────┴────────┐
                   │  Administrador  │
                   │  (web admin)    │
                   └─────────────────┘
```

## 3. Diagrama de contenedores (C4 nivel 2)

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│   ┌──────────────────┐         ┌──────────────────┐                  │
│   │ App móvil Ionic  │         │ Web admin Blade  │                  │
│   │ Vue 3 + Capacitor│         │ (Laravel views)  │                  │
│   └────────┬─────────┘         └────────┬─────────┘                  │
│            │ JSON+Bearer                │ Cookie+Sesión              │
│            └────────────┬───────────────┘                            │
│                         ▼                                            │
│            ┌────────────────────────┐                                │
│            │  routes/api.php +      │   Sanctum                      │
│            │  routes/web.php        │   Form Requests                │
│            │  + middleware rol      │   API Resources                │
│            └────────────┬───────────┘                                │
│                         ▼                                            │
│            ┌──────────────────────────────────────┐                  │
│            │  Controllers (Web + Api/*)           │                  │
│            │  + Strategy (Manual/Foto)            │                  │
│            │  + Observers (Auditoría/Alerta)      │                  │
│            └────────────┬─────────────────────────┘                  │
│                         ▼                                            │
│            ┌──────────────────────────┐                              │
│            │  Modelos Eloquent        │   scopes visibleFor()        │
│            │  (Usuario, Finca, Animal,│                              │
│            │   Pesaje, Catálogos...)  │                              │
│            └────────────┬─────────────┘                              │
│                         ▼                                            │
│                  ┌─────────────┐                                     │
│                  │  Base datos │  SQLite (dev) / MySQL (prod)        │
│                  └─────────────┘                                     │
│                                                                      │
│   ┌──────────────────────────────────────────────────────────────┐   │
│   │  ml_service (proceso aparte, puerto 5000)                    │   │
│   │  Flask  → YOLOv8 → heurística de bounding box → peso (kg)    │   │
│   └──────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
```

## 4. Stack

| Capa | Tecnología | Versión |
|---|---|---|
| Backend API | Laravel | 13 |
| Lenguaje backend | PHP | 8.3 |
| Autenticación API | Sanctum | 4 |
| Frontend web | Blade + Tailwind | 4 |
| Frontend móvil | Ionic + Vue 3 + Capacitor | latest |
| Servicio ML | Flask + Ultralytics YOLOv8 | 3.0 + 8.3 |
| BD desarrollo | SQLite | 3 |
| BD producción | MySQL/MariaDB | 10.4+ |
| Control de versiones | Git + GitHub | — |
| CI/CD | GitHub Actions | — |

## 5. Patrones de diseño aplicados

| Patrón | Dónde | Por qué |
|---|---|---|
| **Strategy** (GoF) | `App\Strategies\*` + `CalculadorPesoContext` | Permite cambiar el método de estimación (manual / foto / futuro modelo entrenado) sin tocar el controller. |
| **Observer** (GoF) | `App\Observers\*` attached a `Pesaje` | Cada vez que se crea un Pesaje, se dispara auditoría, notificación y verificación de peso bajo, sin acoplar lógica al controller. |
| **Factory Method** | `App\Factories\ReporteFactory` | Selecciona el formato de reporte (PDF/Excel) en runtime. |
| **Repository implícito** | Scopes Eloquent (`scopeVisibleFor`) | Centraliza las reglas de visibilidad por rol; el controller no las repite. |
| **API Resource** (Laravel) | `App\Http\Resources\*` | Transforma modelos a JSON con nombres limpios y oculta columnas internas (`id_finca` → `id`). |

## 6. Control de acceso por rol

3 roles definidos en `Tipo_usuario`:

| Rol | id | Puede ver | Puede modificar |
|---|---:|---|---|
| Administrador | 1 | Todo | Todo |
| Ganadero | 2 | Sus fincas, animales y pesajes | Sus fincas, animales y pesajes |
| Veterinario | 3 | Fincas asignadas + animales/pesajes ahí | Solo lectura |

Implementado en 3 capas:
1. **Middleware de ruta** (`rol:admin,ganadero,...`) — corta el acceso al controller.
2. **Scope del modelo** (`visibleFor($user)`) — filtra queries a la BD.
3. **Authorize() en FormRequest** — bloquea escritura por rol.
4. **`@if` en Blade** — esconde botones/menús.

## 7. Decisiones arquitectónicas — ver `docs/adr/`

Cada decisión importante tiene su propio Architecture Decision Record (ADR):

- [ADR-001](adr/001-laravel-y-mysql.md) — Stack backend Laravel + MySQL
- [ADR-002](adr/002-microservicio-ml-aparte.md) — Microservicio ML separado
- [ADR-003](adr/003-strategy-para-estimacion.md) — Patrón Strategy para estimación
- [ADR-004](adr/004-sanctum-tokens.md) — Sanctum para auth móvil
- [ADR-005](adr/005-sqlite-dev-mysql-prod.md) — SQLite en dev, MySQL en prod

## 8. Estructura del repositorio

```
.
├── .github/
│   ├── workflows/ci.yml          ← CI/CD GitHub Actions
│   └── pull_request_template.md
├── app/
│   ├── Contracts/                ← Interfaces (ICalculadorPeso, IReporte)
│   ├── Factories/                ← Factory Method para reportes
│   ├── Http/
│   │   ├── Controllers/          ← Web (FincaController, AnimalController, ...)
│   │   │   └── Api/              ← API REST equivalentes
│   │   ├── Middleware/           ← RoleMiddleware
│   │   ├── Requests/             ← Form Requests (validación + autorización)
│   │   └── Resources/            ← API Resources (modelo → JSON)
│   ├── Models/                   ← Eloquent (Usuario, Finca, Animal, ...)
│   ├── Observers/                ← 3 observers sobre Pesaje
│   ├── Services/                 ← CalculadorPesoContext
│   └── Strategies/               ← FormulaManual + FotoIA
├── database/
│   ├── factories/                ← Factories de Eloquent (para tests)
│   ├── migrations/               ← 16 migraciones
│   └── seeders/                  ← Catálogos + usuarios de prueba
├── docs/
│   ├── architecture.md           ← este archivo
│   ├── adr/                      ← Architecture Decision Records
│   └── declaracion-uso-ia.md     ← Requisito académico (sección 9.2 del PDF)
├── ml_service/                   ← Microservicio Python
│   ├── app.py
│   ├── requirements.txt
│   ├── Dockerfile
│   └── README.md
├── resources/views/              ← Blade templates
└── routes/
    ├── api.php
    └── web.php
```

## 9. Cómo correrlo end-to-end

```powershell
# Backend Laravel
cp .env.example .env
php artisan key:generate
composer install
npm install
php artisan migrate --seed
php artisan storage:link

# En 2 terminales paralelas:
npm run dev
php artisan serve

# (Opcional) Microservicio ML, en otra terminal:
cd ml_service
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

## 10. Limitaciones conocidas

1. **Estimación de peso por foto es heurística** — ver `ml_service/README.md`.
2. **Tabla `Notificacion`** depende de `Recordatorio`, módulo no implementado todavía.
   Los observers son defensivos: si no hay recordatorio, registran solo en log.
3. **No hay tests automáticos exhaustivos** todavía. CI corre solo PHPUnit base.
4. **MySQL requerido para producción** (PDF lo exige). SQLite es solo para desarrollo.
