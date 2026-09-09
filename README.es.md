# Presence Platform — Escaparate del Marketplace

El escaparate de marketing/ventas de **Presence Platform**, el producto NFC
de eventos de presencia para escuelas/empresas (asistencia, seguimiento de
comidas PAE, incentivos de reciclaje, seguimiento de eventos
personalizados). Este sitio VENDE el producto — deliberadamente no ES el
producto, y no depende del backend ni del hardware de la plataforma core.
Consulta `.agent/PROJECT.md` para el contexto completo del proyecto.

- **Framework:** Laravel 13 + Blade (sin Livewire/Inertia/SPA, sin pipeline de build de Node)
- **Almacenamiento: NINGUNO — sin estado por decisión.** No hay base de
  datos de ningún tipo (ver `.agent/DECISIONS/ADR-013-stateless-no-database.md`).
  Las solicitudes de contacto se escriben en el log de la aplicación, no se
  persisten.
- **Idiomas:** inglés y español — alterna **EN / ES** en la cabecera
- **Sin auth, sin pagos, sin mecánicas multi-vendedor**

> Lee esto en: [English](README.md)

## Páginas

| Ruta | Página |
|---|---|
| `/` | Landing — problema, propuesta, cómo funciona, aplicaciones |
| `/product` | Vista general del producto — el pipeline de toque-a-reporte, anatomía del evento |
| `/pricing` | Paquetes — Starter / Campus / Enterprise |
| `/enterprise` | Enterprise — seguimiento de eventos personalizados |
| `/contact` | Solicitar una demo — formulario validado, envío registrado en el log (sin BD) |
| `/contact/thank-you` | Confirmación tras el envío (email con flash de sesión) |
| `/lang/{en\|es}` | El toggle EN/ES de la cabecera — guarda el idioma en la sesión y redirige dentro de la misma app (los referers cross-site se ignoran) |

## Inicio rápido

Requiere PHP >= 8.3 (con `mbstring`, `openssl`, `tokenizer`, `dom`) y
Composer. Nada más — **sin Node, sin servidor de base de datos, sin correo,
sin migraciones.**

```bash
composer install
cp .env.example .env        # el repo no incluye .env (ADR-013)
php artisan key:generate --force
php artisan serve
```

Luego abre `http://127.0.0.1:8000`.

### Por qué no hay base de datos

Esta decisión de producto es explícita y vinculante (ADR-013, sustituye al
ADR-001): el escaparate es un sitio de marketing cuyo único endpoint
dinámico es el formulario de contacto. Una base de datos añadiría una
superficie de persistencia, un paso de migración y una dependencia de
despliegue para datos que nadie opera. Los envíos validados se escriben en
el log de la aplicación (`storage/logs/laravel.log` localmente, stderr en
Vercel) para que el operador los recoja del log drain del despliegue. Si
alguna vez se necesita captura real de leads, añade un backend de
formularios alojado o una BD gestionada — como decisión separada y
explícita.

Nota de la auditoría: `LOG_LEVEL` en `.env.example` debe ser `info` (o
menor) — la ÚNICA constancia de un lead es una línea `Log::info`, y un
nivel superior la filtraría silenciosamente.

## Despliegue con Docker (Render)

El escaparate se entrega como imagen de producción autocontenida — Apache +
mod_php, fuentes autohospedadas; sin Node, sin base de datos, sin otros
servicios. El Dockerfile de Render/docker-compose es el `Dockerfile` por
defecto del repo:

```bash
docker build -t presence-platform-storefront .
docker run --rm -p 8080:80 presence-platform-storefront
# escaparate en http://localhost:8080
```

El entrypoint prepara los directorios escribibles, materializa `.env` desde
`.env.example`, genera un `APP_KEY` al arrancar cuando no se proporciona
por entorno, y entrega a Apache. **No se ejecutan migraciones — no hay base
de datos.** Las solicitudes de contacto aparecen en el log del contenedor
(`docker compose logs storefront`).

## Despliegue en Vercel (Container Service — FrankenPHP)

Vercel no tiene un runtime Laravel de primera clase, pero puede ejecutar
cualquier imagen Docker como **container service** vía `Dockerfile.vercel` +
un `vercel.json` que declara `runtime: "container"`. El Dockerfile usa
**FrankenPHP** (el runtime PHP recomendado oficialmente por Vercel, ver
`vercel.com/kb/guide/deploy-php-on-vercel-with-docker`) — un único binario
que combina el servidor web Caddy y el runtime PHP, lee variables de
entorno de forma nativa y se enlaza a `$PORT` vía configuración de
Caddyfile.

Los intentos anteriores usaban Apache+mod_php, que fallaba porque la imagen
`php:apache` NO pasa las variables de entorno del SO a PHP por defecto
(requeriría directivas `SetEnv` que nunca se añadieron). FrankenPHP elimina
el problema por completo. Ver
`.agent/OBSERVATIONS/OBS-012-apache-mod-php-env-var-passing.md` y
`.agent/DECISIONS/ADR-011-switch-to-frankenphp.md` para el análisis completo.

Archivos específicos de Vercel:

| Archivo | Propósito |
|---|---|
| `Dockerfile.vercel` | Imagen FrankenPHP multi-etapa (Vercel detecta este nombre) |
| `vercel.json` | Declara el container service con `runtime: "container"` + rewrite catch-all |
| `docker/caddy/Caddyfile.vercel` | Config Caddy: escucha en `:{$PORT:80}`, sirve `/app/public`, enruta por `index.php` |
| `docker/entrypoint.frankenphp.sh` | Entrypoint: crea directorios escribibles en `/tmp`, carga el entorno, genera `APP_KEY`, exec FrankenPHP |

### Despliegue rápido

1. Empuja este commit a `main` (ya hecho si estás leyendo el repo).
2. Importa el repo en Vercel — Vercel detecta `Dockerfile.vercel` y la
   declaración `services` de `vercel.json`, construye la imagen y la sirve
   como función de contenedor.
3. No se requieren variables de entorno para una demo funcional — `APP_KEY`
   se autogenera en el arranque en frío si falta (pasa `APP_KEY` en los
   ajustes del proyecto Vercel para mantener claves estables entre arranques;
   una clave nueva solo reinicia las sesiones de cookie, de las que este
   sitio no depende).
4. El `vercel.json` es **obligatorio** — sin la declaración `services` +
   `runtime: "container"`, Vercel vuelve a la autodetección de framework y
   el Dockerfile nunca se usa.

### ⚠️ Notas de despliegue

El filesystem de contenedor de Vercel es **efímero**. El entrypoint reubica
el árbol `storage/` de Laravel a `/tmp/`, así que la app funciona
correctamente durante la vida de un contenedor.

Como el escaparate **no tiene estado** (ADR-013), esto ya no cuesta datos:
no hay base de datos que perder. Las solicitudes de contacto van al log
stderr del contenedor, que Vercel captura en su log drain — los envíos
sobreviven en los logs aunque el filesystem (inexistente) no.

El endpoint de diagnóstico `/__debug` que existió durante el arranque en
Vercel (RUN-005..RUN-011) se **eliminó** en TASK-011 — no estaba
autenticado y filtraba detalles del entorno. El diagnóstico de despliegue
usa ahora los logs de build y runtime de Vercel.

### Prueba local de la imagen de Vercel

```bash
docker build -f Dockerfile.vercel -t storefront-vercel .
docker run --rm -p 8080:8080 -e PORT=8080 storefront-vercel
# escaparate en http://localhost:8080
```

## Pruebas

```bash
php artisan test
```

No requiere configuración local — `phpunit.xml` incluye un `APP_KEY` de
prueba desechable, así que un clon fresco puede ejecutar la suite de
inmediato.

## Diseño

La dirección visual es **"Datum" v3** (ADR-015): familia tonal Material-3
clara sobre fondo salvia (`#f6fbed`, primario casi negro, acentos dorados
tertiary-fixed), tipografía display Epilogue sobre cuerpo Manrope, Space
Grotesk para etiquetas e IBM Plex Mono para datos (todas autohospedadas en
`public/fonts`, la demo funciona sin conexión), secciones con reglas finas,
y un único momento de movimiento orquestado en el hero (tarjeta → luz go →
fila escrita) respetando `prefers-reduced-motion`. La referencia completa
de componentes vive en `docs/FRONTEND.md` / `docs/FRONTEND.es.md`. No lo
reviertas a estéticas de plantilla por defecto.

## Estructura del repositorio para agentes

La memoria persistente del proyecto vive en `.agent/` (solo se añade):
registros de tareas, reportes de corridas, ADRs, observaciones y
snapshots de estado. Lee `.agent/PROJECT.md` y
`.agent/TASKS/TASK-001-marketplace-mvp.md` antes de cambiar nada, y añade —
nunca reescribas — los registros históricos.
