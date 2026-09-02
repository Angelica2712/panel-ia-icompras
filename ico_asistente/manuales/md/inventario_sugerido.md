OPCION DE INVENTARIO > SUGERIDOS (Pedidos Sugeridos)

Este módulo analiza tu inventario y te sugiere qué productos y en qué cantidad
deberías reponer, basándose en la venta media diaria (VMD) y los días de
reposición que definas.

CREAR SUGERIDO

Al presionar "Crear" se abre un formulario con estos campos:

•  Días de reposición: cuántos días de stock quieres cubrir con la compra

sugerida (por defecto 7).

•  Descripción: filtro de texto libre por nombre de producto.

•  Marca: filtro por marca (o "Todas las marcas disponibles").

•  Categoría: filtro por categoría (o "Todas las categorías disponibles").

Al confirmar, el sistema "analiza el inventario" y genera el listado. Si pides más
de 30 días de reposición y tu cuenta tiene activada la verificación en dos pasos
(Google Authenticator), el sistema te pedirá el código de autenticación antes de
procesar la búsqueda. Si no se encuentran productos sugeridos, te avisa con un
mensaje y puedes intentar de nuevo con otros filtros.

LISTADO DE SUGERIDOS

Una vez generado, la pantalla muestra:

•  Buscador, y botones "Eliminar" (borra todo el sugerido, pide confirmación)

y "Descargar" (exporta el listado).

•  Botón "Crear": vuelve a abrir el formulario para generar un nuevo sugerido

(esto reemplaza al anterior).

•  Botón "Procesar": abre una pantalla para convertir el sugerido en un

pedido real. Solo aparece si hay productos en la lista. Tiene un selector
"Modo de generación" con 5 opciones:

o  ACTUAL: usa el Criterio (Precio / Inventario / Días / Tolerancia) y la

Preferencia (Ninguna / Primer proveedor) de siempre, sin cambios.

o  EXISTENCIA MÍNIMA POR PROVEEDOR: solo considera un proveedor

si tiene al menos la existencia mínima que indiques. Si nadie la
cubre, sigue repartiendo por mejor precio entre los que sí califican.

o  COBERTURA TOTAL (un solo proveedor): busca un único proveedor

que pueda cubrir el 100% de la cantidad al mejor precio; si ninguno
cubre el total, no se pide nada de ese producto.

o  MEJOR PRECIO ÚNICO: elige el proveedor más barato y le pide lo

que tenga disponible, sin completar con otros aunque falte cantidad.

o  SOLO PRODUCTOS EN MIN/MAX: procesa únicamente los productos

con mínimos y máximos configurados; puedes marcar la casilla "Solo
productos con cendis > 0" para limitarlo aún más.

Cada modo muestra una descripción explicativa en pantalla al seleccionarlo.

•  Cada fila muestra: imagen del producto (clicable, va a la ficha del

producto), la cantidad sugerida a "Pedir" (editable, con un botón de check
para confirmar el cambio y un botón de papelera para eliminar ese
producto puntual), un checkbox para selección múltiple, PRODUCTO
(con badge de "Excluido" si aplica), CANT (inventario actual), TRAN
(tránsito), VMD, MIN y MAX (tus límites configurados), un indicador
"CENDIS" si el producto está marcado por Cendis, CODIGO, BARRA,
MARCA y CATEGORIA.

•  Selección múltiple: puedes marcar varios productos con el checkbox de

cada fila (o "Marcar/desmarcar todos") y eliminarlos en bloque con el
botón de papelera de la cabecera.

•  Al final de la lista hay paginación.
