# Frontend — rediseño Datum (TASK-013) y el libro de brechas del mockup

Esta tienda ahora usa el sistema de diseño v3 **"Datum"** (ADR-015): la
paleta tonal Material-3 de salvia clara, el sistema tipográfico
Epilogue/Manrope/Space Grotesk/IBM Plex Mono, geometría de esquinas
afiadas de 2px, tarjetas de 8px y el par de acentos casi-negro/dorado
compartido con los paneles de Core. Signal v2 (oscuro, ADR-014) queda
sustituido pero sus registros permanecen intactos, como siempre.

El rediseño es **solo CSS**. No cambió ni una vista Blade, controlador,
ruta, prueba, ni una línea de `public/js/app.js` — la capa de animación
de anime.js sigue funcionando porque su contrato en tiempo de ejecución
(los tokens `--data / --data-solid / --data-tint / --surface /
--border-strong / --accent-soft` y las clases de estado `.is-revealed /
.is-new / .is-hit / .is-on`) se conserva textualmente en la nueva hoja
de estilos. El copy bilingüe EN/ES quedó intacto (auditoría de
contenido: 86/86 elementos presentes).

## Referencia de diseño (datos rápidos para trabajo futuro)

- Fondo `#f6fbed` salvia; texto `#181d15`; acción casi-negro `#0e0f0e`;
  dorado eco/puntos `#ffdf93`/`#ebc254` (tinta `#241a00`, tinta de texto
  dorado `#594400`); error `#ba1a1a`; familia verde-dato `#416e4a /
  #2f5238 / #35573b` para etiquetas de eventos y estados "en vivo";
  pie invertido `#2d3229`.
- Tipografía: Epilogue (títulos), Manrope (cuerpo), Space Grotesk
  (etiquetas/chips), IBM Plex Mono (eventos/datos). Todo autoalojado —
  `fonts.css` + `public/fonts/`, cero peticiones externas en ejecución.
- Geometría: radios 2/4/8px (chips/interior/tarjetas), shell 1140px,
  barra superior 68px fija con desenfoque, puntos de corte 940px/620px
  (coinciden con los mediaQueries de `app.js`).
- Los tokens viven en UN solo lugar (`app.css :root`). Los componentes
  solo referencian alias; los pares WCAG se auditan con
  `scripts/contrast_audit.py` (37 pares, todos AA en esta ejecución).
- Residuo cosmético conocido: el lavado verde-azulado de `flashRow`
  (rgba(128,179,167)) está codificado dentro de `app.js` y aparece como
  un destello verde suave en la fila más nueva del registro — armonioso
  sobre salvia, documentado aquí en lugar de tocar la capa de animación
  congelada.

## El libro de brechas — partes que necesitan funcionalidad que aún no existe

La directiva del propietario para esta familia de rediseños dice:
construye el diseño, conserva cada función real y **documenta las partes
aspiracionales en lugar de fingirlas**. Abajo, cada elemento de tienda
que un diseño como este invita a tener, que hoy no tiene funcionalidad
detrás, por qué, y qué construiría. Nada de esta lista es un botón
muerto en la interfaz — donde un elemento habría requerido funcionalidad
inexistente, la interfaz lo omite o dice la verdad (ver notas del suelo
de honestidad).

### C — Comercio (la brecha literal del "marketplace")

- **C1 — Carrito.** No existe carrito alguno; los CTA de los paquetes
  llevan al formulario de contacto, no a un carrito. *Por qué:* tienda
  de un solo vendedor por paquetes que se vende por conversación
  (no-meta explícita en `.agent/PROJECT.md`: sin carrito/checkout).
  *Necesita:* una capa de datos de carrito, su interfaz y un modelo de
  precios por artículo en vez de por paquete.
- **C2 — Pago / procesamiento.** Sin integración de PSP, sin tokens de
  pago, sin direcciones de facturación. *Por qué:* no-meta explícita —
  los paquetes se cotizan individualmente. *Necesita:* cuenta PSP +
  webhooks + un dominio `payments` (nuevo también en Core).
- **C3 — Compra autónoma de paquetes.** Campus/Enterprise no se pueden
  comprar sin humano. *Necesita:* C1+C2 más aprovisionamiento: hoy
  "desplegar un paquete" significa que el equipo configura lectores,
  tarjetas y clases — no existe aprovisionamiento automático.

### K — Contacto / leads

- **K1 — Leads persistentes.** Los envíos se validan, se registran en el
  log y desaparecen (ADR-013: sin estado, sin base de datos).
  *Necesita:* una capa de persistencia (tabla o CRM externo) — reversión
  directa de la ADR-013, decisión del propietario.
- **K2 — Notificación por correo.** No hay transporte de correo; nadie
  recibe email al enviarse el formulario. *Necesita:* credenciales
  SMTP/proveedor + Mailable + cola (la cola requiere la tabla de jobs →
  también toca la decisión de estado).
- **K3 — Integración CRM.** Sin HubSpot/Salesforce/etc. *Necesita:*
  cliente de integración + credenciales; hoy la promesa "te
  responderemos" depende de humanos leyendo logs.

### P — Prueba social

- **P1 — Testimonios / citas de clientes.** Ninguno. *Por qué:* aún no
  hay clientes en producción; inventar citas viola el suelo de
  honestidad. *Necesita:* clientes reales + permiso escrito.
- **P2 — Logos de clientes / franja "confían en nosotros".** Misma
  regla: no existen logos reales, así que la franja no se renderiza.
- **P3 — Cifras de caso de éxito ("40% menos trabajo admin").** No hay
  resultados medidos. *Necesita:* un piloto instrumentado con métricas
  antes/después de los reportes del propio Core.

### L — Datos "en vivo"

- **L1 — Flujo real de eventos.** El terminal del hero y la insignia
  "LIVE" animan **filas de demostración guionizadas** — son el discurso
  del producto, no un flujo real. *Por qué:* la tienda no se integra con
  el backend de Core (arquitectura de dos apps, ARCH-001).
  *Necesita:* una API pública de solo lectura en Core + consumidor
  SSE/WebSocket aquí; antes, revisión de privacidad (los ids de tarjeta
  son datos personales).
- **L2 — Estado de la plataforma.** Sin página de estado, sin chip de
  disponibilidad. *Necesita:* monitor externo de uptime + ruta de
  estado; inventar un "todos los sistemas operativos" verde sin nada
  detrás rompe el suelo de honestidad.

### D — Demo y onboarding

- **D1 — Demo interactiva / sandbox.** "Solicitar demo" lleva al
  formulario. *Necesita:* instancia Core de demo con datos sembrados
  anonimizados + credenciales bajo petición. Nada de esto está
  desplegado.
- **D2 — Agendamiento de demo (calendario).** Sin reservas estilo
  Calendly. *Necesita:* integración de calendario; hoy un humano
  responde por correo y acuerda la hora.

### A — Analítica y SEO

- **A1 — Analítica web.** Sin analítica de páginas/eventos (ni GA, ni
  Plausible, ni autoalojada). *Necesita:* una elección que respete la
  restricción de cero CDN + manejo de consentimiento.
- **A2 — Sitemap / datos estructurados.** Sin `sitemap.xml`, sin schema
  JSON-LD. *Necesita:* generación de ambos (fácil — listado porque no
  existe, no porque sea difícil).

### I — Internacionalización

- **I1 — Idiomas más allá de EN/ES.** El selector ofrece exactamente EN
  y ES porque son los únicos árboles `lang/` existentes. *Necesita:*
  archivos de traducción nuevos + auditoría RTL si entra un idioma no
  latino.

### G — Páginas de gobernanza

- **G1 — Política de privacidad y términos.** La línea legal del pie
  solo lleva el copyright; no existe /privacy ni /terms. El formulario
  hasta dice a dónde van los datos (honrado: "al log, no a una base").
  *Necesita:* texto legal de la compañía operadora — ingeniería puede
  añadir rutas/vistas en minutos, pero el contenido debe venir de
  humanos con autoridad para escribirlo.

## Suelo de honestidad (qué hicimos en lugar de fingir)

- Los CTA de precios dicen lo que hacen (llevar a contacto) — sin
  botones "Comprar ahora" que no llevan a nada.
- El terminal del registro se presenta como dispositivo de demostración
  ("LIVE" = el ciclo anima); no se afirman capacidades en tiempo real.
- No existen marcadores de posición de testimonios/logos/cifras que
  alguien pudiera confundir con contenido real.
- El flujo de contacto dice en su propio copy a dónde van los envíos.
