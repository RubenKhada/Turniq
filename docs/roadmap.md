# Roadmap — Turniq v0.1 → v1.0

## Principio del roadmap

El núcleo local (lo que se vende como Starter y Pro) es la prioridad absoluta. El módulo Cloud es una fase posterior, deliberadamente separada, para no bloquear la venta de licencias locales mientras se diseña e implementa la parte de red.

## Fase 0 — Fundamentos (actual)

> Ver docs/arquitectura.md §10 (H0) para dependencias, riesgos y estimación de semanas.

- [x] Identidad y branding
- [x] Documentación base (README, licencias, arquitectura, modelo de datos, flujos)
- [ ] Modelo de datos implementado en SQLite (esquema real, no solo documentado)
- [ ] Desarrollo del núcleo: `core/` (auth local por PIN, carga de módulos según licencia)

## Fase 1 — Núcleo v0.1 (vendible como Starter)

> Ver docs/arquitectura.md §10 (H1) para dependencias, riesgos y estimación de semanas.

- [ ] Módulo `clients/` — CRM básico funcional
- [ ] Módulo `payments/` — registro de cobros y métodos de pago
- [ ] Módulo `appointments/` o punto de venta según el giro objetivo del primer cliente real
- [ ] Sistema de licencias local: validación de clave, activación de módulos según licencia
- [ ] Instalador básico (aunque sea manual/documentado, no necesita ser un `.exe` pulido todavía)

**Meta de esta fase:** tener un negocio real (el primer cliente fundador) operando el núcleo local, sin nube, cobrando la licencia Starter o Pro.

## Fase 2 — Núcleo completo v0.2 (vendible como Pro)

> Ver docs/arquitectura.md §10 (H2) para dependencias, riesgos y estimación de semanas.

- [ ] Módulo `staff/` — empleados, roles, PIN por usuario
- [ ] Módulo `cashregister/` — cortes de caja
- [ ] Módulo `reports/` — dashboard y reportes locales
- [ ] Módulo `alerts/` — alertas de inventario y otras notificaciones internas
- [ ] Empaquetado real del instalador (definir tecnología: Electron, instalador nativo, u otro)

**Meta de esta fase:** licencia Pro completa y vendible sin ninguna dependencia de red.

## Fase 3 — Módulo Cloud, versión mínima (vendible como add-on Nube)

> Ver docs/arquitectura.md §10 (H3) para dependencias, riesgos y estimación de semanas.

Esta fase no empieza hasta que la Fase 2 esté vendida y validada con clientes reales — construir la nube antes de tener negocios usando el núcleo es invertir tiempo en la parte equivocada primero.

- [ ] Servidor mínimo: 1 VPS, base de datos con `negocio_id` en cada tabla (ver `docs/modelo-datos.md`)
- [ ] Flujo de activación del add-on (`docs/flujos.md`, Flujo 3)
- [ ] Respaldo automático cifrado (`docs/flujos.md`, Flujo 4)
- [ ] Acceso remoto de solo lectura (`docs/flujos.md`, Flujo 5)

**Meta de esta fase:** poder cobrar el add-on de $99/mes a clientes que ya tienen licencia local.

## Fase 3.5 — Acceso remoto

> Ver docs/arquitectura.md §10 (H4) para dependencias, riesgos y estimación de semanas.

- [ ] Endpoint de reportes remotos de solo lectura (`docs/flujos.md`, Flujo 5)
- [ ] Autenticación de sesión remota (token rotable, alcance `solo_lectura`)

## Fase 4 — Multisucursal

> Ver docs/arquitectura.md §10 (H5) para dependencias, riesgos y estimación de semanas.

- [ ] Sincronización de catálogo e inventario entre sucursales (`docs/flujos.md`, Flujo 6)
- [ ] Resolución de conflictos de sincronización definida e implementada
- [ ] Dashboard consolidado multisucursal
- [ ] Cobro y facturación de la licencia Multi ($6,500 + $390/mes)

## Fase 5 — v1.0

- [ ] Empaquetado pulido y distribución (sitio de descargas o portal de licencias)
- [ ] Documentación de usuario final (no solo técnica)
- [ ] Sistema de soporte formalizado más allá de WhatsApp manual
- [ ] Revisión de seguridad del módulo Cloud antes de escalar más allá de los primeros clientes Multi

## Qué NO está en este roadmap todavía

- Escritura remota (registrar ventas desde fuera del negocio) — decisión explícitamente pospuesta, ver `docs/flujos.md` Flujo 5.
- Facturación electrónica / CFDI — evaluar como módulo Custom cuando haya demanda real.
- App móvil nativa — el acceso remoto de la Fase 3 cubre esa necesidad vía navegador antes de justificar una app dedicada.
