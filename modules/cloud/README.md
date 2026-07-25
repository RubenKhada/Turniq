# Módulo Cloud (en diseño)

Este módulo es el único punto del sistema que se comunica con un servidor externo. Cubre tres funciones, activables de forma independiente:

1. **Respaldo automático** — sube una copia cifrada de la base de datos local a intervalos configurables.
2. **Acceso remoto de solo lectura** — expone reportes y el dashboard vía un endpoint autenticado, sin exponer operaciones de escritura (ventas, cortes de caja) fuera del negocio.
3. **Sincronización multisucursal** — replica un subconjunto de tablas (inventario, catálogo, ventas agregadas) entre instalaciones del mismo negocio_id.

Ninguna de las tres funciones es requisito para que el núcleo local opere. Este módulo no existe aún en código — este README es un marcador de intención hasta llegar al Hito H3 (ver `docs/arquitectura.md` §10).

## Regla de aislamiento

Todo dato que pase por este módulo debe llevar `negocio_id` en cada tabla y en cada query, sin excepción — ver `docs/arquitectura.md`, ADR-005, para el razonamiento completo detrás de esta regla.
