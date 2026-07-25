# Flujos del sistema — Turniq

## Flujo 1 — Instalación y activación de licencia (siempre local)

1. El cliente instala Turniq en su equipo (instalador por definir en v0.2).
2. Durante el primer arranque, ingresa su clave de licencia (Starter, Pro o Custom).
3. El sistema valida la clave **localmente**, contra un archivo de licencia firmado que se generó al momento de la venta — no requiere internet para validar en este paso, salvo la primera activación si se decide requerir verificación única en línea (a definir).
4. Se crean las tablas locales según los módulos incluidos en la licencia.
5. El negocio queda operativo sin necesidad de ningún paso adicional de red.

Este flujo nunca depende del módulo Cloud. Si el módulo Cloud no está contratado, el sistema debe funcionar exactamente igual, sin mensajes de error relacionados a conexión.

## Flujo 2 — Operación diaria (100% local)

1. Empleado inicia sesión con PIN (`modules/staff`, validado contra la base local).
2. Registra ventas o citas según el giro (`modules/appointments` o punto de venta).
3. El sistema descuenta inventario automáticamente si aplica (`modules/reports` + inventario).
4. Al cierre, se genera el corte de caja (`modules/cashregister`).
5. Todo esto ocurre sin tocar el módulo Cloud, incluso si está contratado — el Cloud solo actúa en segundo plano (ver Flujo 3).

## Flujo 3 — Activación del módulo Cloud (opcional)

1. El cliente contrata el add-on Nube (por WhatsApp o portal, a definir).
2. Se genera un `negocio_id` único para esa instalación en el servidor central.
3. El cliente ingresa una clave de vinculación en su instalación local (`modules/cloud`), que asocia esa instalación con su `negocio_id` en el servidor.
4. A partir de aquí, el módulo Cloud empieza a operar en segundo plano:
   - Respaldo automático según el intervalo configurado.
   - Acceso remoto de solo lectura habilitado a través de un enlace único.
5. Si el cliente cancela el add-on, el módulo Cloud deja de sincronizar pero el núcleo local sigue funcionando sin cambios. El servidor conserva los respaldos existentes según la política de retención (a definir), pero deja de recibir nuevos.

## Flujo 4 — Respaldo automático (dentro de Cloud)

1. En el intervalo configurado (diario por defecto), el módulo Cloud local empaqueta y cifra la base de datos.
2. Sube el archivo cifrado al servidor, asociado al `negocio_id`.
3. El servidor almacena el archivo sin poder leer su contenido (cifrado de extremo a extremo con clave que solo el cliente posee, o cifrado en tránsito + en reposo con clave gestionada — decisión de seguridad pendiente de definir en detalle técnico).
4. En caso de pérdida del equipo local, el cliente puede solicitar la restauración del último respaldo válido a una nueva instalación con la misma licencia.

## Flujo 5 — Acceso remoto de solo lectura (dentro de Cloud)

1. El empleado autorizado (dueño/gerente) solicita acceso remoto desde su celular u otro dispositivo.
2. Se autentica contra el servidor Cloud (no contra la base local) con credenciales separadas del PIN local.
3. El servidor devuelve reportes y dashboard basados en los últimos datos sincronizados — nunca en tiempo real exacto si el respaldo no ha corrido recientemente; la interfaz debe mostrar la fecha/hora del último dato disponible.
4. No existe, en v1, ningún camino desde este flujo hacia una operación de escritura (no se puede registrar una venta ni modificar inventario remotamente).

## Flujo 6 — Sincronización multisucursal (solo licencia Multi)

1. Cada sucursal corre su propia instalación local del núcleo, con el módulo Cloud activo y el mismo `negocio_id`.
2. Periódicamente (intervalo a definir, más frecuente que el respaldo completo), cada instalación envía al servidor:
   - Cambios en su catálogo de productos.
   - Su inventario actual (para el consolidado).
   - Totales agregados de venta del día (sin detalle de línea de venta ni datos de cliente).
3. El servidor consolida y expone un dashboard multisucursal vía el mismo mecanismo de acceso remoto del Flujo 5.
4. Si dos sucursales modifican el mismo producto del catálogo compartido antes de sincronizar, el sistema debe tener una regla de resolución de conflicto (por ejemplo: gana el cambio más reciente por timestamp) — la regla exacta queda pendiente de definir antes de implementar este flujo.

## Flujo 7 — Negocio sin módulo Cloud que luego lo contrata

Este flujo existe para dejar claro que el sistema debe soportar la transición sin fricción:

1. Negocio opera meses solo con licencia local.
2. Contrata el add-on Nube.
3. Se ejecuta el Flujo 3 (activación) tomando como punto de partida el estado actual de su base de datos local — no requiere reinstalar ni perder historial.
