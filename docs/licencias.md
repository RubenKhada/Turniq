# Sistema de Licencias
## Turniq v0.1

**Versión:** 0.1
**Fecha:** 2025
**Autor:** Ruben Fuentes

---

## 1. Filosofía del sistema de licencias

Turniq no es un software de talla única. Cada negocio tiene necesidades distintas — una barbería no necesita los mismos módulos que un restaurante o una tienda de abarrotes.

El sistema de licencias resuelve esto con tres principios:

- **Pago único** — el cliente compra su licencia una sola vez, sin suscripciones mensuales
- **Activación por módulo** — solo se activan las funciones que el negocio necesita
- **Flexibilidad** — si el negocio crece, puede actualizar su licencia

---

## 2. Tipos de licencia

### 🔹 Starter
Orientada a negocios pequeños que necesitan lo esencial para ordenarse.

| Módulo | Incluido |
|---|:---:|
| Gestión de citas y turnos | ✅ |
| Clientes (CRM básico) | ✅ |
| Pagos y facturación | ✅ |
| Alertas internas | ✅ |
| Dashboard básico | ✅ |
| Empleados / Staff | ❌ |
| Punto de venta (POS) | ❌ |
| Inventario | ❌ |
| Catálogo de productos | ❌ |
| Corte de caja | ❌ |
| Reportes avanzados | ❌ |

**Ideal para:** Barbería de un solo empleado, consultorio pequeño, estética familiar.

---

### 🔸 Pro
La licencia completa para negocios que quieren control total de su operación.

| Módulo | Incluido |
|---|:---:|
| Gestión de citas y turnos | ✅ |
| Clientes (CRM básico) | ✅ |
| Pagos y facturación | ✅ |
| Alertas internas | ✅ |
| Dashboard completo | ✅ |
| Empleados / Staff | ✅ |
| Punto de venta (POS) | ✅ |
| Inventario | ✅ |
| Catálogo de productos | ✅ |
| Corte de caja | ✅ |
| Reportes avanzados | ✅ |

**Ideal para:** Spa, gimnasio, restaurante, tienda de abarrotes, barbería con varios empleados.

---

### ⚙️ Custom
Para negocios con necesidades específicas que no encajan exactamente en Starter ni Pro. El distribuidor configura los módulos activos de forma manual.

**Ideal para:** Negocios mixtos, franquicias pequeñas, negocios con flujos de trabajo inusuales.

---

## 3. Comparativa por tipo de negocio

| Tipo de negocio | Licencia recomendada | Módulos clave |
|---|---|---|
| Barbería (1 empleado) | Starter | Citas, Clientes, Pagos |
| Barbería (varios empleados) | Pro | Todo + Empleados + Corte |
| Spa / Centro de bienestar | Pro | Todo + Inventario (productos) |
| Restaurante | Pro | POS + Inventario + Corte |
| Tienda de abarrotes | Pro | POS + Inventario + Corte |
| Consultorio médico/dental | Starter o Pro | Citas + Clientes + Pagos |
| Gimnasio | Pro | Citas + Clientes + POS + Reportes |
| Taller mecánico | Custom | Citas + Inventario + Pagos |

---

## 4. Cómo funciona técnicamente

La licencia se almacena en el archivo `config/license.php` de cada instalación. Este archivo es generado por el distribuidor al momento de instalar el sistema.

### Ejemplo — Licencia Starter para barbería

```php
return [
    'business_name' => 'Barbería El Estilo',
    'business_type' => 'barberia',
    'license_type'  => 'starter',
    'licensed_to'   => 'Juan Pérez',
    'issued_at'     => '2025-01-01',
    'modules' => [
        'appointments' => true,
        'clients'      => true,
        'payments'     => true,
        'alerts'       => true,
        'reports'      => true,
        'staff'        => false,
        'pos'          => false,
        'inventory'    => false,
        'products'     => false,
        'cashregister' => false,
    ]
];
```

### Ejemplo — Licencia Pro para tienda de abarrotes

```php
return [
    'business_name' => 'Abarrotes Don Chuy',
    'business_type' => 'abarrotes',
    'license_type'  => 'pro',
    'licensed_to'   => 'Jesús Ramírez',
    'issued_at'     => '2025-01-01',
    'modules' => [
        'appointments' => true,
        'clients'      => true,
        'payments'     => true,
        'alerts'       => true,
        'reports'      => true,
        'staff'        => true,
        'pos'          => true,
        'inventory'    => true,
        'products'     => true,
        'cashregister' => true,
    ]
];
```

---

## 5. Flujo de activación de licencia

```
1. Distribuidor llega al negocio del cliente
2. Instala Turniq en la computadora del cliente
3. Ejecuta el script de configuración inicial
4. Selecciona el tipo de licencia del cliente
5. El sistema genera config/license.php automáticamente
6. El LicenseManager lee el archivo al iniciar
7. Solo los módulos activos aparecen en el menú
8. Módulos inactivos redirigen a pantalla "No disponible en tu licencia"
```

---

## 6. Actualización de licencia

Si un cliente con Starter quiere pasar a Pro:

1. El distribuidor visita al cliente
2. Modifica el archivo `config/license.php` con los nuevos módulos
3. No se reinstala el sistema — solo se actualiza el archivo de configuración
4. El cliente paga la diferencia entre licencias

---

## 7. Protección de la licencia (v0.2+)

En v0.1 el sistema de licencias es por archivo de configuración — simple y funcional para la distribución directa.

En versiones futuras se contempla:
- Licencia con clave cifrada para evitar modificaciones manuales
- Verificación opcional en línea para validar la licencia
- Panel del distribuidor para gestionar todas sus instalaciones activas

---

*Turniq — Sistema de licencias v0.1*
