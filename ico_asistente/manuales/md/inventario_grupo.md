> **Audiencia: CLIENTE (Farmacia)** — Este documento aplica únicamente a usuarios cliente/farmacia. No uses esta información para responder consultas de proveedores.

NOTA PARA EL ASISTENTE (Ico): al igual que en "grupo.md", verifica si el usuario
que pregunta pertenece a una cuenta tipo Grupo antes de explicar este módulo
en detalle. Si NO lo es, solo menciona que existe esta funcionalidad (comparar
el inventario de varias sucursales lado a lado) y dile que consulte con soporte si
su cuenta califica para configurarse como Grupo.

OPCION DE GRUPO > INVENTARIO GRUPO

Este módulo solo aparece para clientes tipo Grupo (cadenas de varias
farmacias). Muestra, para cada producto, el inventario de TODAS las sucursales
del grupo lado a lado en una misma tabla, para comparar de un vistazo dónde
hay stock y a qué precio.

•  Buscador de productos, y un botón para descargar el listado en Excel/CSV

(con overlay de progreso mientras se genera el archivo).

•  La tabla tiene un bloque fijo "INVENTARIO MAESTRO DE PRODUCTOS"

(imagen, producto, categoría, marca, bulto, IVA) y un bloque "GRUPO" con
el TRÁNSITO y el INVENTARIO CONSOLIDADO de todas las sucursales
sumado.

•  A la derecha, se repite un bloque de columnas por cada sucursal del

grupo (identificada con su color propio): PRECIO, COSTO, INV/TRAN.
(inventario y tránsito de esa sucursal) y VMD/DÍAS (venta media diaria y
días de inventario), y CÓDIGO interno del producto en esa sucursal.

•  Marcas visuales de comparación: la sucursal con el precio más bajo entre

todas muestra un ícono de check junto al PRECIO, y la sucursal con
mayor cantidad de inventario disponible muestra un check junto al
INVENTARIO, para identificar rápidamente dónde conviene comprar o
trasladar mercancía.

•  Si un producto está marcado como excluido en alguna sucursal, se

muestra el badge de "Excluido" en esa columna.

•  Si no hay productos que mostrar, aparece el mensaje "Catálogo de

productos vacío". Al final de la tabla hay paginación.
