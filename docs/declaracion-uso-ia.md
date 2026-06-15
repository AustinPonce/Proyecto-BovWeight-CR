# Declaración de uso de IA generativa

> Requisito académico — sección 9.2 del enunciado del proyecto IF7100.

Este documento declara explícitamente el uso que el equipo hizo de herramientas
de IA generativa durante el desarrollo de BovWeight CR.

## Herramientas utilizadas

| Herramienta | Quién la usó | Para qué |
|---|---|---|
| Claude (Anthropic) | Equipo completo | Generación de scaffolding inicial (controllers, vistas Blade, FormRequests), revisión y documentación de patrones de diseño, ayuda con debugging de Laravel/Eloquent, redacción de documentación técnica (este archivo, ADRs). |
| GitHub Copilot | (si aplica — completar) | Autocomplete dentro del IDE. |

## Cómo se validó cada salida

Para cada generación de IA, el integrante responsable:

1. **Leyó** el código generado línea por línea antes de pegarlo.
2. **Adaptó** los identificadores, mensajes en español y rutas a las
   convenciones del proyecto (PascalCase en tablas, snake_case en columnas,
   verbos en imperativo, etc.).
3. **Probó** que el código corriera localmente con datos de prueba.
4. **Refactorizó** cuando el resultado generado era genérico o no encajaba
   con decisiones arquitectónicas del equipo (ej. patrones de diseño).

## Áreas donde NO se usó IA

- **Diseño del esquema de la base de datos** y relaciones entre tablas:
  trabajo de modelado conceptual del equipo (Dari).
- **Patrones SOLID** (Strategy, Observer, Factory Method): implementación
  manual por el sub-equipo asignado.
- **Decisiones arquitectónicas** (ADRs): discutidas y acordadas por el equipo,
  redactadas con asistencia de IA pero firmadas por el equipo.

## Responsabilidad

Por la sección 9.2 del enunciado:

> "El equipo es responsable de todo el contenido entregado, independientemente
> de si fue generado con IA."

El equipo asume la responsabilidad total sobre el código y la documentación
entregados, y está en condición de explicar cualquier parte del proyecto en
preguntas orales del docente.

---

**Equipo:**
- Nathalie (Scrum Master)
- Austin (Frontend lead — móvil Ionic + web)
- Dari (Base de datos, modelos)
- Francela (Pruebas, manual de usuario, documentación)
