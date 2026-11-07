# Sesión 4 · UX/UI en Figma · sistema de diseño blck.co

## Identidad
Todo negro. El blanco y un solo acento caramelo cuentan la historia; no hay gradientes ni sombras: las separaciones se hacen con líneas finas.

## Variables (crea estos *styles* en Figma; coinciden con `public/css/blckco.css`)
| Nombre | Hex | Uso |
|---|---|---|
| Negro | `#000000` | Fondo de toda la app |
| Superficie | `#0E0E0E` | Tarjetas, inputs |
| Borde | `#2A2A2A` | Líneas y contornos |
| Texto | `#F5F5F5` | Texto principal |
| Texto secundario | `#A3A3A3` | Etiquetas y ayudas |
| Acento caramelo | `#C8A97E` | Botón principal, precios, enlaces |
| Peligro | `#E5736B` | Errores, stock bajo, eliminar |
| Éxito | `#8FBF9A` | Confirmaciones |

## Tipografía (máximo 2)
- **Space Grotesk** 500/700 → títulos, cifras y la marca «blck.co».
- **Inter** 400/500/600 → todo el texto de interfaz.

## Reglas
- Esquinas de 2 px, bordes de 1 px.
- Botón principal: contorno caramelo que se rellena al pasar el mouse. Botón peligroso: contorno rojo.
- El menú público es una lista de precios con puntos guía entre nombre y precio. Ese es el elemento memorable; el resto queda sobrio.
- Contraste mínimo 4,5:1 y foco visible en todos los controles.

## Pantallas (cada una en 1440 px y 390 px)
1. Login · 2. Panel · 3. Menú lateral (en móvil, panel deslizante) · 4. Listado de productos · 5. Formulario de producto · 6. Detalle de pedido · 7. Modal de confirmación · 8. Reportes · 9. Mensajes de error · 10. Menú público responsive

## Componentes reutilizables
Botón (acento, contorno, peligro) · Input con error · Select · Tabla · Tarjeta de cifra · Etiqueta de estado · Alerta · Modal

Conecta las pantallas con un *Prototype* (login → panel → pedidos → detalle). Exporta `docs/figma.pdf` y guarda el enlace.
