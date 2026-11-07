# Sesión 2 · Problema, actores y requisitos (ISO/IEC/IEEE 29148:2018)

**Descripción del problema.** Las cafeterías pequeñas anotan los pedidos a mano, controlan el inventario por conteo y no saben qué venden más. Eso genera errores de cobro, productos agotados sin aviso y decisiones sin datos.
*(Complementa con cifras de tu encuesta.)*

**Pregunta problema.** ¿Cómo puede un sistema web mejorar la gestión de pedidos e inventario en una cafetería digital?

**Justificación.** Un sistema propio reduce errores, ahorra tiempo en caja y entrega indicadores para decidir.

**Objetivo general.** Desarrollar un sistema web para gestionar pedidos, productos e inventario de la cafetería blck.co.

**Objetivos específicos**
1. Registrar pedidos con cálculo automático del total.
2. Controlar el stock con descuento automático y alertas de inventario bajo.
3. Administrar productos, categorías y clientes con validaciones.
4. Generar un panel y reportes de ventas por mes, producto y método de pago.
5. Publicar un menú digital público con estilo visual propio.

**Alcance.** Autenticación con roles, menú público, categorías, productos, clientes, pedidos, pagos, inventario, panel y reportes.
**Limitaciones.** Sin pasarela de pago real, sin app móvil nativa, sin pedidos hechos por el cliente desde su cuenta (los registra el barista), un solo local.

## Actores
| Actor | Responsabilidad | Interacción con el sistema |
|---|---|---|
| Administrador | Catálogos, precios, usuarios y reportes | Todos los módulos |
| Barista/Cajero | Registrar pedidos, cobrar, cambiar estados | Pedidos, clientes, consulta de productos |
| Cliente | Consultar el menú | Menú público |

Casos de uso (dibújalos en draw.io): Iniciar sesión · Gestionar categorías · Gestionar productos · Gestionar clientes · Registrar pedido · Cobrar pedido · Cambiar estado · Cancelar pedido · Ver panel · Ver reportes · Consultar menú.

## Requisitos funcionales
| ID | Nombre | Descripción | Prioridad | Criterio de aceptación |
|---|---|---|---|---|
| RF-01 | Iniciar sesión | Acceso con correo y contraseña | Alta | Con datos correctos entra al panel; con datos incorrectos ve un error |
| RF-02 | Cerrar sesión | Termina la sesión | Alta | Tras salir, el panel exige iniciar sesión |
| RF-03 | Gestionar categorías | Crear, listar, editar y eliminar categorías | Alta | La categoría queda en el listado; no se borra si tiene productos |
| RF-04 | Gestionar productos | CRUD con precio, stock y categoría | Alta | El producto aparece en el listado y en el menú |
| RF-05 | Buscar productos | Filtrar por nombre | Media | Al buscar solo se muestran coincidencias |
| RF-06 | Gestionar clientes | CRUD con teléfono y correo | Alta | El cliente queda disponible al crear pedidos |
| RF-07 | Registrar pedido | Elegir cliente y productos con cantidades | Alta | El pedido aparece en el listado con estado pendiente |
| RF-08 | Calcular total | El servidor suma cantidad × precio | Alta | El total coincide con el detalle |
| RF-09 | Controlar stock | Descontar al vender y devolver al cancelar | Alta | El stock baja con la venta y vuelve al cancelar |
| RF-10 | Cambiar estado | pendiente → preparando → entregado | Alta | El estado cambia solo al siguiente paso |
| RF-11 | Registrar pago | Efectivo, tarjeta o transferencia | Alta | El pedido muestra el pago registrado |
| RF-12 | Cancelar pedido | Cancela pedidos sin pago | Media | El pedido queda cancelado y el stock vuelve |
| RF-13 | Panel | Ventas del día, pendientes, más vendidos, stock bajo | Alta | Las cifras coinciden con los datos |
| RF-14 | Reportes | Ingresos por mes, clientes frecuentes, métodos de pago, CSV | Media | Las cifras coinciden con consultas SQL |
| RF-15 | Menú público | Lista de productos por categoría sin iniciar sesión | Media | Cualquiera ve productos activos y los agotados marcados |

## Requisitos no funcionales
| ID | Descripción | Criterio |
|---|---|---|
| RNF-01 | Rendimiento | Cada página responde en menos de 3 segundos |
| RNF-02 | Seguridad de claves | Contraseñas cifradas con `password_hash` |
| RNF-03 | Seguridad de datos | Consultas preparadas PDO, escape de salida y token CSRF |
| RNF-04 | Responsive | Se usa en pantallas de 390 px y de 1440 px |
| RNF-05 | Compatibilidad | Funciona en Chrome y Firefox actuales |
| RNF-06 | Accesibilidad | Contraste de texto de al menos 4,5:1 y foco visible |
| RNF-07 | Mantenibilidad | Arquitectura MVC y código comentado |

## Reglas de negocio
| ID | Regla |
|---|---|
| RN-01 | No se vende un producto sin stock suficiente |
| RN-02 | Un pedido entregado o cancelado no se puede modificar |
| RN-03 | El total se calcula siempre en el servidor desde el detalle |
| RN-04 | No se entrega un pedido sin pago registrado |

## Historias de usuario
| ID | Historia | Requisito |
|---|---|---|
| HU-01 | Como barista, quiero iniciar sesión para acceder a mi panel | RF-01 |
| HU-02 | Como administrador, quiero gestionar categorías para organizar el menú | RF-03 |
| HU-03 | Como administrador, quiero gestionar productos para mantener precios y stock | RF-04 |
| HU-04 | Como barista, quiero buscar un producto para encontrarlo rápido | RF-05 |
| HU-05 | Como barista, quiero registrar clientes para asociarles pedidos | RF-06 |
| HU-06 | Como barista, quiero registrar un pedido para que el cliente reciba su café sin errores | RF-07, RF-08 |
| HU-07 | Como barista, quiero que el stock se descuente solo para no vender lo que no hay | RF-09 |
| HU-08 | Como barista, quiero cambiar el estado del pedido para avisar que va avanzando | RF-10 |
| HU-09 | Como cajero, quiero registrar el pago para cerrar la venta | RF-11 |
| HU-10 | Como administrador, quiero ver el panel para conocer cómo va el día | RF-13 |
| HU-11 | Como administrador, quiero reportes de ventas para decidir qué comprar | RF-14 |
| HU-12 | Como cliente, quiero ver el menú desde mi celular para elegir antes de llegar | RF-15 |

**Entrega:** diagrama de casos de uso + este documento en `docs/requisitos.pdf` · commit `docs: agregar requisitos`
