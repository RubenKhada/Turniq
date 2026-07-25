# Modelo de datos — Turniq

## Regla general

Toda entidad vive en la base de datos local por defecto. Solo las entidades explícitamente marcadas como "sincronizada" en este documento tienen una copia o referencia en el módulo Cloud, y solo cuando ese módulo está activo para el negocio.

## Entidades del núcleo (100% locales)

Estas entidades nunca salen del equipo del cliente salvo como parte de un respaldo cifrado (que el negocio no puede leer directamente, solo restaurar).

### Negocio
Configuración de la instalación: nombre, giro, licencia activa, módulos habilitados.
- No lleva `negocio_id` — en el núcleo local, la base de datos completa ES un negocio.

### Empleado
`id, nombre, rol, pin_hash, activo`
- El PIN se valida contra esta tabla local. Nunca viaja al módulo Cloud en texto plano ni hasheado salvo como parte del respaldo cifrado completo.

### Cliente (CRM)
`id, nombre, contacto, notas, historial`

### Producto / Servicio
`id, nombre, categoria, precio, tipo (producto|servicio)`

### Inventario
`id, producto_id, cantidad_actual, umbral_alerta`

### Venta
`id, empleado_id, cliente_id (opcional), fecha, total, metodo_pago`

### Línea de venta
`id, venta_id, producto_id, cantidad, precio_unitario, comisión_calculada`

### Corte de caja
`id, empleado_id, fecha_apertura, fecha_cierre, total_esperado, total_contado, diferencia`

### Cita / Turno (según giro)
`id, cliente_id, empleado_id, fecha, estado`

## Entidades del módulo Cloud (solo si el add-on está activo)

Estas SÍ llevan `negocio_id` porque conviven con otros negocios en la misma infraestructura.

### Respaldo
`id, negocio_id, fecha, ubicación_archivo_cifrado, tamaño`
- Es un blob cifrado del estado completo de la base local. El servidor no interpreta su contenido, solo lo almacena y lo puede devolver.

### Sesión de acceso remoto
`id, negocio_id, empleado_id, token, expiración, alcance (solo_lectura)`
- El `alcance` siempre es `solo_lectura` en v1. No existe todavía un alcance de escritura remota — si se agrega en el futuro, requiere su propia revisión de seguridad, no se asume aquí.

### Producto sincronizado (solo para negocios con Multi)
`id, negocio_id, sucursal_id, producto_id_local, nombre, precio, categoria`
- Es la proyección compartida del catálogo entre sucursales del mismo negocio. Cambios aquí se propagan a cada instalación local; no reemplazan la tabla `Producto` local, la alimentan.

### Inventario consolidado (solo Multi)
`id, negocio_id, sucursal_id, producto_id_local, cantidad_reportada, fecha_reporte`
- Es un espejo de solo agregación para el dashboard consolidado. La fuente de verdad del inventario operativo sigue siendo la tabla `Inventario` local de cada sucursal.

### Venta agregada (solo Multi, para reportes consolidados)
`id, negocio_id, sucursal_id, fecha, total, num_tickets`
- Nunca incluye líneas de venta individuales ni datos de clientes — es agregación diaria, suficiente para comparar sucursales sin exponer el detalle operativo de cada una fuera de su propia instalación.

## Diagrama de relación entre zonas

```
[Instalación local — Sucursal A]          [Instalación local — Sucursal B]
   Empleado, Cliente, Producto,               Empleado, Cliente, Producto,
   Inventario, Venta, Línea de venta,          Inventario, Venta, Línea de venta,
   Corte de caja                               Corte de caja
        │                                           │
        │  (solo si módulo Cloud activo)            │  (solo si módulo Cloud activo)
        ▼                                           ▼
   ┌─────────────────────── Módulo Cloud (negocio_id) ───────────────────────┐
   │  Respaldo · Sesión de acceso remoto                                     │
   │  Producto sincronizado · Inventario consolidado · Venta agregada        │
   │  (todo filtrado por negocio_id, aislado de otros negocios en el mismo   │
   │   servidor)                                                             │
   └──────────────────────────────────────────────────────────────────────┘
```

## Pendiente de definir (no bloquea v0.1 del núcleo)

- Formato exacto de cifrado del respaldo.
- Protocolo de resolución de conflictos si dos sucursales editan el mismo producto sincronizado casi al mismo tiempo.
- Diagrama ER detallado por módulo (appointments, payments, etc.) — este documento cubre el modelo de alto nivel y la separación local/nube, no el ER completo de cada módulo.
