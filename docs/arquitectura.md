# Arquitectura del Sistema
## Turniq v0.1

**Versión:** 0.1
**Fecha:** 2025
**Autor:** [Tu nombre]

---

## 1. Visión arquitectónica

Turniq es un sistema de escritorio **local-first** construido sobre una arquitectura modular por capas. Cada capa tiene una responsabilidad única y se comunica únicamente con la capa adyacente. Los módulos funcionales son componentes independientes que se activan o desactivan según la licencia del cliente, sin afectar el núcleo del sistema.

---

## 2. Principios de diseño

| Principio | Descripción |
|---|---|
| **Local-first** | El sistema funciona sin internet. Los datos viven en la máquina del cliente |
| **Modularidad** | Cada módulo es independiente. Se activa por licencia sin modificar el núcleo |
| **Simplicidad operativa** | Un empleado sin capacitación técnica debe poder operar el sistema |
| **Bajo acoplamiento** | Los módulos no dependen entre sí directamente, sino a través del núcleo |
| **Rendimiento en hardware básico** | El sistema debe correr en equipos de gama baja comunes en México |

---

## 3. Stack tecnológico

| Capa | Tecnología | Justificación |
|---|---|---|
| Backend / Lógica | PHP 8.x | Experiencia del desarrollador, amplia comunidad, liviano |
| Frontend / UI | HTML5 + CSS3 + JavaScript | Sin frameworks pesados, compatible con cualquier equipo |
| Base de datos | SQLite | Sin servidor, archivo único, ideal para instalación local |
| Base de datos alternativa | MySQL 8.x | Para negocios con mayor volumen de datos (v0.2+) |
| Servidor local | PHP Built-in Server / Apache (XAMPP) | Empaquetado simple para distribución en Windows |
| Empaquetado | XAMPP portable | Permite distribuir el sistema como carpeta ejecutable |

### ¿Por qué no una app de escritorio nativa?
En v0.1 se elige una arquitectura web local (PHP + navegador) porque:
- El desarrollador ya conoce el stack
- No requiere compilar para diferentes sistemas operativos
- La interfaz corre en el navegador del cliente (Chrome/Edge), que ya está instalado
- El mantenimiento y actualizaciones son más simples

---

## 4. Arquitectura por capas

```
┌─────────────────────────────────────────────────────┐
│                  CAPA DE PRESENTACIÓN                │
│         HTML5 + CSS3 + JavaScript (UI)               │
│   Dashboard │ Formularios │ Reportes │ POS           │
└──────────────────────┬──────────────────────────────┘
                       │ HTTP (local)
┌──────────────────────▼──────────────────────────────┐
│                  CAPA DE APLICACIÓN                  │
│                    PHP 8.x                           │
│  Controladores │ Validaciones │ Lógica de negocio    │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                  CAPA DE MÓDULOS                     │
│  appointments │ clients │ staff │ payments │ pos     │
│  inventory │ reports │ cashregister │ alerts         │
│  (cada módulo: controlador + modelo + vistas)        │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                  CAPA DE NÚCLEO                      │
│  Auth │ Config │ License Manager │ Router │ DB       │
└──────────────────────┬──────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────┐
│                  CAPA DE DATOS                       │
│              SQLite / MySQL                          │
│   turniq.db (archivo local en la máquina)            │
└─────────────────────────────────────────────────────┘
```

---

## 5. Estructura de directorios

```
turniq/
│
├── index.php                  # Punto de entrada
├── .htaccess                  # Reescritura de rutas
│
├── core/                      # Núcleo del sistema
│   ├── Auth.php               # Autenticación y sesiones
│   ├── Router.php             # Enrutador de peticiones
│   ├── Database.php           # Conexión y queries base
│   ├── Config.php             # Configuración global
│   └── LicenseManager.php     # Activación de módulos
│
├── modules/                   # Módulos funcionales
│   ├── appointments/          # Citas y turnos
│   │   ├── AppointmentController.php
│   │   ├── AppointmentModel.php
│   │   └── views/
│   ├── clients/               # CRM básico
│   ├── staff/                 # Empleados
│   ├── payments/              # Pagos y facturación
│   ├── pos/                   # Punto de venta
│   ├── inventory/             # Inventario
│   ├── products/              # Catálogo de productos
│   ├── reports/               # Dashboard y reportes
│   ├── cashregister/          # Cortes de caja
│   └── alerts/                # Alertas internas
│
├── ui/                        # Assets de interfaz
│   ├── css/
│   │   ├── main.css
│   │   └── components/
│   ├── js/
│   │   ├── app.js
│   │   └── modules/
│   └── assets/
│       ├── logo/
│       └── icons/
│
├── database/                  # Base de datos
│   ├── turniq.db              # Archivo SQLite
│   ├── schema.sql             # Esquema inicial
│   └── migrations/            # Cambios de versión
│
├── config/                    # Configuración por cliente
│   ├── app.php                # Configuración general
│   ├── license.php            # Módulos activos
│   └── business.php           # Datos del negocio
│
├── storage/                   # Archivos generados
│   ├── backups/               # Respaldos de la DB
│   ├── logs/                  # Logs del sistema
│   └── exports/               # Reportes exportados
│
└── docs/                      # Documentación
    ├── README.md
    ├── PRD.md
    ├── arquitectura.md
    ├── modelo-datos.md
    ├── flujos.md
    ├── licencias.md
    └── roadmap.md
```

---

## 6. Módulos del sistema

### 6.1 Núcleo (siempre activo)

| Componente | Responsabilidad |
|---|---|
| `Auth` | Login, sesiones, roles de usuario |
| `Router` | Direcciona peticiones HTTP al controlador correcto |
| `Database` | Abstracción de conexión SQLite/MySQL |
| `Config` | Lee y expone configuración global del sistema |
| `LicenseManager` | Verifica qué módulos están activos para esta instalación |

### 6.2 Módulos activables por licencia

| Módulo | Clave | Descripción |
|---|---|---|
| Citas y turnos | `appointments` | Agenda, reservaciones, historial |
| Clientes | `clients` | CRM básico, historial por cliente |
| Empleados | `staff` | Registro, roles, horarios, acceso |
| Pagos | `payments` | Cobros, métodos de pago, recibos |
| Punto de venta | `pos` | Venta rápida en mostrador |
| Inventario | `inventory` | Stock, alertas de bajo inventario |
| Catálogo | `products` | Productos, precios, categorías |
| Dashboard | `reports` | Métricas, gráficas, reportes |
| Corte de caja | `cashregister` | Apertura, cierre, reconciliación |
| Alertas | `alerts` | Notificaciones internas |

---

## 7. Sistema de autenticación y roles

### Roles del sistema

| Rol | Permisos |
|---|---|
| `admin` | Acceso total. Configuración, reportes, cortes, empleados |
| `cashier` | POS, pagos, clientes, citas |
| `receptionist` | Citas, clientes, alertas |
| `viewer` | Solo lectura de dashboard y reportes |

### Flujo de autenticación
1. El usuario accede al sistema desde el navegador (`localhost`)
2. Ingresa usuario y contraseña
3. El sistema valida contra la base de datos local
4. Se crea una sesión PHP con el rol del usuario
5. El `Router` verifica permisos en cada petición
6. El `LicenseManager` verifica si el módulo solicitado está activo

---

## 8. Integración de pagos con tarjeta

Para la v0.1 se contempla integración con terminales físicas del ecosistema mexicano:

| Proveedor | Integración | Viabilidad v0.1 |
|---|---|:---:|
| **Clip** | Terminal física + API REST | ✅ Recomendado |
| **Mercado Pago Point** | Terminal física + SDK | ✅ Alternativa |
| **Conekta** | API REST (cobro en línea) | ⚠️ Requiere internet |

### Decisión v0.1
Se implementará integración con **Clip** por ser la terminal más común entre pequeños negocios en México. El flujo es:
1. El cajero registra el monto en Turniq
2. Turniq envía el cobro a la terminal Clip vía API local
3. El cliente paga en la terminal física
4. Clip confirma el pago
5. Turniq registra la transacción automáticamente

> Nota: Esta integración requiere conexión a internet únicamente en el momento del cobro con tarjeta. El resto del sistema sigue siendo local-first.

---

## 9. Modelo de licencias (técnico)

El archivo `config/license.php` define los módulos activos para cada instalación:

```php
// Ejemplo: Licencia Pro para Barbería
return [
    'business_type' => 'barberia',
    'license_type'  => 'pro',
    'modules' => [
        'appointments' => true,
        'clients'      => true,
        'staff'        => true,
        'payments'     => true,
        'pos'          => true,
        'inventory'    => true,
        'products'     => true,
        'reports'      => true,
        'cashregister' => true,
        'alerts'       => true,
    ]
];
```

El `LicenseManager` lee este archivo al iniciar el sistema y el menú de navegación solo muestra los módulos activos. Intentar acceder a una ruta de módulo inactivo redirige a una pantalla de "módulo no disponible en tu licencia".

---

## 10. Decisiones técnicas y justificaciones

| Decisión | Alternativa considerada | Razón de la elección |
|---|---|---|
| PHP sobre Node.js | Node.js + Express | Mayor experiencia del desarrollador, menor curva de entrada |
| SQLite sobre MySQL | MySQL desde el inicio | Sin necesidad de servidor de BD, instalación más simple |
| Web local sobre app nativa | Electron, Tauri | Menor complejidad, sin compilación, actualizable fácilmente |
| XAMPP portable | Instalador custom | Conocido, probado, fácil de distribuir en USB o descarga |
| Sin framework PHP | Laravel, CodeIgniter | Menor peso, mayor control, aprendizaje más profundo |

---

## 11. Consideraciones de seguridad

- Contraseñas almacenadas con `password_hash()` de PHP (bcrypt)
- Sesiones PHP con tiempo de expiración configurable
- Validación de entradas en backend, nunca solo en frontend
- El sistema no expone puertos al exterior — solo accesible en `localhost`
- Respaldos automáticos de la base de datos en `storage/backups/`
- Log de acciones críticas (cortes, pagos, modificaciones) en `storage/logs/`

---

## 12. Diagrama de componentes

```
[Navegador local]
      │
      │ HTTP localhost
      ▼
[index.php] ──► [Router] ──► [LicenseManager]
                   │
          ┌────────┼────────┐
          ▼        ▼        ▼
    [Auth]   [Módulos]  [Config]
               │
    ┌──────────┼──────────┐
    ▼          ▼          ▼
[Modelo]  [Controlador] [Vista]
    │
    ▼
[Database] ──► [SQLite / MySQL]
```

---

*Turniq — Arquitectura v0.1 | Documento vivo sujeto a cambios durante el desarrollo*
