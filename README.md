# Turniq

![Version](https://img.shields.io/badge/versión-0.1.0--alpha-7B2FBE?style=flat-square)
![Estado](https://img.shields.io/badge/estado-en%20desarrollo-C084FC?style=flat-square)
![Plataforma](https://img.shields.io/badge/plataforma-escritorio-2A0E55?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Licencia](https://img.shields.io/badge/licencia-comercial-0D0618?style=flat-square)
![País](https://img.shields.io/badge/mercado-México-green?style=flat-square)

> Software modular de gestión de negocios diseñado para adaptarse a cualquier giro comercial — restaurantes, spas, barberías, gimnasios y más.

---

## ¿Qué es Turniq?

Turniq es un sistema de gestión de negocios instalable en escritorio, pensado para el mercado mexicano. Su núcleo es completamente modular: dependiendo del tipo de negocio y la licencia adquirida, se activan los módulos que ese cliente necesita — sin pagar por funciones que no usa.

La distribución es directa (puerta a puerta), lo que permite una atención personalizada y una configuración inicial adaptada a cada cliente.

---

## ¿Por qué Turniq?

La mayoría de los negocios pequeños y medianos en México operan sin un sistema de gestión formal. Los que sí usan software, generalmente pagan por soluciones genéricas que no se adaptan a su giro, o por plataformas en la nube con costos recurrentes fuera de su presupuesto.

Turniq resuelve esto con:

- **Un pago único** por licencia, sin suscripciones mensuales obligatorias
- **Módulos activables** según el tipo de negocio
- **Instalación local** sin dependencia de internet para operar
- **Soporte directo** del distribuidor en persona

---

## Módulos del núcleo (v0.1)

| Módulo | Descripción | Incluido en v0.1 |
|---|---|:---:|
| Gestión de citas y turnos | Agenda, reservaciones, control de flujo | ✅ |
| Clientes (CRM básico) | Historial, datos de contacto, notas | ✅ |
| Empleados / Staff | Registro, roles, horarios | ✅ |
| Pagos y facturación | Cobros, métodos de pago, registro | ✅ |
| Dashboard y reportes | Métricas clave del negocio | ✅ |
| Cortes de caja | Apertura, cierre, resumen del día | ✅ |
| Alertas | Notificaciones internas del sistema | ✅ |

---

## Tipos de negocio compatibles

Turniq no es un software para un solo giro. Su arquitectura modular permite configurarlo para:

- Barberías y estéticas
- Spas y centros de bienestar
- Restaurantes y cafeterías
- Gimnasios y estudios de yoga
- Consultorios (médico, dental, psicología)
- Talleres y servicios técnicos
- Cualquier negocio que maneje citas, clientes y pagos

---

## Arquitectura general

```
turniq/
├── core/               # Núcleo del sistema (auth, config, módulos)
├── modules/            # Módulos activables por licencia
│   ├── appointments/   # Citas y turnos
│   ├── clients/        # CRM básico
│   ├── staff/          # Empleados
│   ├── payments/       # Pagos y facturación
│   ├── reports/        # Dashboard y reportes
│   ├── cashregister/   # Cortes de caja
│   └── alerts/         # Alertas del sistema
├── ui/                 # Interfaz de usuario
├── database/           # Esquema y migraciones
├── config/             # Configuración por cliente
└── docs/               # Documentación del proyecto
```

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.x |
| Frontend | HTML5 + CSS3 + JavaScript |
| Base de datos | SQLite (local) / MySQL (opcional) |
| Empaquetado | Por definir (v0.2) |

---

## Sistema de licencias

Turniq opera bajo un modelo de licencias por módulo. Cada cliente adquiere una licencia que define qué módulos tiene disponibles en su instalación.

| Licencia | Módulos activos | Orientada a |
|---|---|---|
| Starter | Citas + Clientes + Pagos | Barberías, estéticas pequeñas |
| Pro | Todo el núcleo v0.1 | Spas, gimnasios, restaurantes |
| Custom | A definir por cliente | Cualquier giro con necesidades específicas |

> La documentación detallada del sistema de licencias está en [`/docs/licencias.md`](./docs/licencias.md)

---

## Documentación

| Documento | Descripción |
|---|---|
| [`/docs/arquitectura.md`](./docs/arquitectura.md) | Arquitectura del sistema y decisiones técnicas |
| [`/docs/modelo-datos.md`](./docs/modelo-datos.md) | Diagrama ER y descripción de entidades |
| [`/docs/flujos.md`](./docs/flujos.md) | Diagramas de flujo por módulo |
| [`/docs/licencias.md`](./docs/licencias.md) | Sistema de licencias y módulos |
| [`/docs/roadmap.md`](./docs/roadmap.md) | Roadmap v0.1 → v1.0 |

---

## Estado del proyecto

```
v0.1.0-alpha  ← en desarrollo
│
├── Identidad y branding     ✅
├── Documentación base       🔄 en progreso
├── Arquitectura del sistema 🔄 en progreso
├── Modelo de datos          ⬜ pendiente
└── Desarrollo del núcleo    ⬜ pendiente
```

---

## Autor

**Desarrollado por:** [Tu nombre]
**Contacto:** [tu@email.com]
**Ubicación:** México

---

*Turniq v0.1 — 2025. Todos los derechos reservados.*
