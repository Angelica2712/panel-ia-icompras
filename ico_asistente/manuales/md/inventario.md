> **Audiencia: CLIENTE (Farmacia)** — Este documento aplica únicamente a usuarios cliente/farmacia. No uses esta información para responder consultas de proveedores.

OPCION DE INVENTARIO (módulo principal)

Este módulo solo aparece en el menú si el cliente no está en la versión "Light"
del plan. Al entrar, la pantalla principal muestra tarjetas de acceso a los distintos
submódulos de inventario:

•  Productos (Inventario de productos): la pantalla central del módulo, donde

se ve el detalle completo del inventario sincronizado (explicada abajo).
Muestra la fecha de la última carga y la cantidad de renglones cargados.

•  Sugeridos (Pedidos Sugeridos): tiene su propio manual (ver "Inventario

Sugerido").

•  Fallas (Ver fallas): tiene su propio manual (ver "Inventario Fallas").

•  Comparador de Competencia: tiene su propio manual (ver

"comparador_competencia.md") — compara tu precio contra el de la
competencia por zona geográfica.

•  Análisis (Costos vs. Catálogo de proveedor): tiene su propio manual (ver

"analisis_costos_vs_catalogo.md") — cruza tu costo, precio y utilidad
contra el menor/promedio/mayor precio de tus proveedores.

•  Días mínimos y máximos: solo aparece si tienes activado "Pedidos

automáticos" (Generación de Pedido Automático). Tiene su propio manual
(ver "Inventario Min/Max"). NOTA PARA EL ASISTENTE (Ico): si el usuario
pregunta por esta función y no la tiene activada, sigue la indicación del
manual "inventario_minmax.md" (solo mencionar que existe y decirle que
la solicite a soporte).

PANTALLA "PRODUCTOS" (Inventario de productos)

Esta es la vista detallada del inventario sincronizado con tu sistema local.

•  Barra de herramientas:

o  Buscador de productos.

o  Botón "Eliminar": borra todo el inventario cargado (solo aparece si

ya hay inventario cargado). Pide confirmación.

o  Botón "Cargar": permite cargar el inventario manualmente.

o  Filtro "Filtrar" por estado: Todos, Alerta (pedir producto),

Advertencia (cliente con stock, proveedor sin), Grave (sin stock en
ambos) o Sin stock en proveedor (hay en grupo).

o  Filtro "Excluidos": Todos, No excluidos o Excluidos (productos que

marcaste para que no se tomen en cuenta al comprar).

o  Botón "Descargar": exporta el inventario filtrado a CSV.

•  La tabla se divide en tres bloques de columnas: INVENTARIO MAESTRO

DE PRODUCTOS (imagen, producto, referencias), PROVEEDOR (mejor
precio del proveedor "MPP" e inventario consolidado del proveedor) y
DATOS DEL CLIENTE (precio de venta, tránsito, inventario, costo, VMD,
días de inventario y código).

•  Semáforo de estado (primera columna, un punto de color con tooltip):

o  Verde (Ideal): el cliente y los proveedores tienen inventario.

o  Amarillo (Alerta): hay stock en los proveedores pero el cliente está

en cero — se debe pedir.

o  Naranja (Advertencia): el cliente tiene stock pero ningún proveedor

tiene disponible.

o  Rojo (Grave): ni el cliente ni los proveedores tienen inventario.

o  Celeste (Sin stock en proveedor, hay en grupo): ningún proveedor

tiene stock, pero el producto está disponible en otra sucursal de tu
grupo.

•  Cada producto tiene un checkbox "EXCLUIR PRODUCTO PARA COMPRAR":

si lo activas, ese producto se oculta cuando filtras por "Excluidos: No
excluidos", útil para dejar de considerar productos que no quieres seguir
comprando.

•  El botón "Ver grupo" (junto a las referencias del producto) muestra el

inventario de ese mismo producto en el resto de las farmacias de tu
grupo, igual que el botón turquesa del catálogo.

•  La imagen del producto es clicable y lleva a su ficha de detalle. Al final

de la tabla hay paginación.
