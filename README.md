# Turniq

[#turniq](#turniq)

![Version](https://img.shields.io/badge/versi%C3%B3n-0.1.0--alpha-7B2FBE?style=flat-square)
![Estado](https://img.shields.io/badge/estado-en%20desarrollo-C084FC?style=flat-square)
![Plataforma](https://img.shields.io/badge/plataforma-escritorio-2A0E55?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Licencia](https://img.shields.io/badge/licencia-comercial-0D0618?style=flat-square)
![País](https://img.shields.io/badge/mercado-M%C3%A9xico-green?style=flat-square)

> Software de gestión de negocios, instalable en escritorio, con un módulo opcional en la nube para respaldo, acceso remoto y sincronización multisucursal.

---

## ¿Qué es Turniq?

[#qué-es-turniq](#qué-es-turniq)

Turniq es un sistema de gestión de negocios **híbrido**: el núcleo corre instalado en el equipo del cliente (sin depender de internet para operar día a día), y quien lo necesite puede activar un **módulo en la nube** para respaldo automático, acceso remoto a reportes y sincronización entre sucursales.

Su núcleo es completamente modular: dependiendo del tipo de negocio y la licencia adquirida, se activan los módulos que ese cliente necesita — sin pagar por funciones que no usa.

---

## ¿Por qué Turniq?

[#por-qué-turniq](#por-qué-turniq)

La mayoría de los negocios pequeños y medianos en México operan sin un sistema de gestión formal. Los que sí usan software, generalmente pagan por soluciones genéricas que no se adaptan a su giro, o por plataformas 100% en la nube con costos recurrentes que no siempre se justifican para un negocio de una sola sucursal.

Turniq resuelve esto con:

- **Licencia de pago único** para el núcleo local — sin depender de internet para vender, cobrar o cerrar caja
- **Módulos activables** según el tipo de negocio
- **Nube opcional**, solo donde aporta valor real: respaldo, acceso remoto y multisucursal
- **Soporte directo** por WhatsApp, incluido en los planes con nube

---

## Módulos del núcleo (v0.1)

[#módulos-del-núcleo-v01](#módulos-del-núcleo-v01)

| Módulo                    | Descripción                             | Incluido en v0.1 |
| ------------------------- | --------------------------------------- | ---------------- |
| Gestión de citas y turnos | Agenda, reservaciones, control de flujo | ✅                |
| Clientes (CRM básico)     | Historial, datos de contacto, notas     | ✅                |
| Empleados / Staff         | Registro, roles, horarios               | ✅                |
| Pagos y facturación       | Cobros, métodos de pago, registro       | ✅                |
| Dashboard y reportes      | Métricas clave del negocio              | ✅                |
| Cortes de caja            | Apertura, cierre, resumen del día       | ✅                |
| Alertas                   | Notificaciones internas del sistema     | ✅                |
| Módulo Nube (opcional)    | Respaldo, acceso remoto, sincronización | 🔄 en diseño      |

---

## Tipos de negocio compatibles

[#tipos-de-negocio-compatibles](#tipos-de-negocio-compatibles)

Turniq no es un software para un solo giro. Su arquitectura modular permite configurarlo para:

- Barberías y estéticas
- Spas y centros de bienestar
- Restaurantes, bares y cafeterías
- Gimnasios y estudios de yoga
- Consultorios (médico, dental, psicología)
- Talleres y servicios técnicos
- Cualquier negocio que maneje citas, ventas y pagos

---

## Arquitectura general

[#arquitectura-general](#arquitectura-general)

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
│   ├── alerts/         # Alertas del sistema
│   └── cloud/          # Módulo opcional: respaldo, acceso remoto, sync multisucursal
├── ui/                 # Interfaz de usuario
├── database/           # Esquema y migraciones
├── config/             # Configuración por cliente
└── docs/               # Documentación del proyecto
```

El módulo `cloud/` es el único componente que se comunica con un servidor. El resto del sistema opera completamente offline contra la base de datos local.

---

## Stack tecnológico

[#stack-tecnológico](#stack-tecnológico)

| Capa                    | Tecnología                                  |
| ------------------------ | -------------------------------------------- |
| Backend (local)          | PHP 8.x                                       |
| Frontend                 | HTML5 + CSS3 + JavaScript                     |
| Base de datos local      | SQLite (v0.1) / MySQL (opcional)              |
| Backend nube (opcional)  | PHP 8.x + MySQL, servidor único con aislamiento por `negocio_id` |
| Empaquetado              | Por definir (v0.2)                            |

---

## Sistema de licencias

[#sistema-de-licencias](#sistema-de-licencias)

Turniq opera bajo un modelo híbrido: **licencia local de pago único** + **módulo nube opcional por suscripción**, solo donde el negocio realmente lo necesita.

| Licencia | Módulos activos | Modalidad | Orientada a |
| -------- | ---------------- | --------- | ----------- |
| Starter | Citas/ventas + clientes + pagos | Local, pago único | Barberías, estéticas pequeñas |
| Pro | Todo el núcleo v0.1 | Local, pago único | Spas, gimnasios, restaurantes de una sucursal |
| Nube (add-on) | Respaldo automático + acceso remoto a reportes + soporte prioritario | Mensual, sobre cualquier licencia local | Cualquier negocio que quiera respaldo fuera del sitio |
| Multi | Licencia Pro + sincronización entre sucursales + nube | Pago único (licencia) + mensual (sincronización) | Negocios con 2 o más sucursales |
| Custom | A definir por cliente | Según necesidad | Cualquier giro con requerimientos específicos |

La suscripción mensual **nunca es obligatoria** para operar el negocio en una sola sucursal: el núcleo funciona completo sin internet. Solo se vuelve necesaria cuando el negocio necesita que dos o más puntos compartan información en tiempo real, o cuando el cliente quiere respaldo automático fuera de su equipo.

> La documentación detallada del sistema de licencias está en [`/docs/licencias.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/licencias.md)

---

## Documentación

[#documentación](#documentación)

| Documento | Descripción |
| --------- | ----------- |
| [`/docs/arquitectura.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/arquitectura.md) | Arquitectura del sistema y decisiones técnicas |
| [`/docs/modelo-datos.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/modelo-datos.md) | Diagrama ER y descripción de entidades |
| [`/docs/flujos.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/flujos.md) | Diagramas de flujo por módulo |
| [`/docs/licencias.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/licencias.md) | Sistema de licencias y módulos |
| [`/docs/roadmap.md`](https://github.com/RubenKhada/Turniq/blob/main/docs/roadmap.md) | Roadmap v0.1 → v1.0 |

---

## Estado del proyecto

[#estado-del-proyecto](#estado-del-proyecto)

```
v0.1.0-alpha  ← en desarrollo
│
├── Identidad y branding      ✅
├── Documentación base        🔄 en progreso
├── Arquitectura del sistema  🔄 en progreso
├── Modelo de datos           ⬜ pendiente
├── Desarrollo del núcleo     ⬜ pendiente
└── Módulo nube (add-on)      ⬜ pendiente
```

---

## Autor

[#autor](#autor)

**Desarrollado por:** Ruben Fuentes
**Contacto:** rubenfuentes416@gmail.com
**Ubicación:** México

---

*Turniq v0.1 — 2026. Todos los derechos reservados.*