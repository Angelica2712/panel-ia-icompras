> **Audiencia: CLIENTE (Farmacia)** — Este documento aplica únicamente a usuarios cliente/farmacia. No uses esta información para responder consultas de proveedores.

REPORTE DE SOBRESTOCK

Este panel es una herramienta de salud financiera y logística. Su objetivo es
identificar el capital estancado o "inventario muerto". Le muestra a la farmacia (el
cliente) exactamente en qué productos tiene un exceso de mercancía acumulada
en sus almacenes que no se está vendiendo al ritmo esperado, rompiendo sus
propias reglas de inventario. Es vital para evitar que la mercancía caduque o que
ocupe espacio que debería destinarse a productos de alta rotación.

Para orientar al cliente en la lectura de este reporte, debes guiarlo a través del
cruce de métricas en la tabla:

•  Buscador y Alertas Visuales: En la parte superior puede filtrar por marca
o nombre. En la tabla, debajo del nombre de cada PRODUCTO, el sistema
estampa una alerta roja inconfundible: SOBRESTOCK!!. La imagen del
producto es clicable y lleva a su ficha de detalle. Al final de la tabla hay
paginación para recorrer todo el listado.

•  Importar Redistribución (solo para usuarios de grupo/gerente): Si el

usuario es de tipo "G" (grupo), junto al botón de Excel aparece un
formulario para subir un archivo. NOTA PARA EL ASISTENTE (Ico): si el
usuario pregunta por esta función y no es de tipo Grupo, no expliques el
paso a paso; solo menciona que existe (redistribuir inventario entre
sucursales) y dile que consulte con soporte si su cuenta califica para
configurarse como Grupo (ver también "grupo.md"). El flujo es: primero se descarga el Excel
de este mismo módulo (que incluye la hoja "Redistribución grupo"), se
completa ese archivo respetando exactamente el formato y los nombres de
columna, y luego se vuelve a subir aquí para importar la redistribución de
inventario entre las sucursales del grupo.

•  El Origen del Problema (CANTIDAD ACTUAL): Es el stock físico real que

tiene la farmacia en sus estantes (ej. 49 Tapabocas).

•  La Columna VMD (La Política de Inventario): Esta columna no solo

muestra el promedio matemático, sino que revela las reglas configuradas
por el cliente.

o  El Promedio Diario: El primer número (ej. 0.0111) es la Venta

Media Diaria pura. Refleja cuántas unidades salen realmente por día
(en un caso de sobrestock, suele ser bajísimo).

o  MIN (Días Mínimos de Inventario): Es el "stock de seguridad".

Indica la cantidad mínima de días de venta que el cliente quiere tener
cubiertos en su almacén.

o  MAX (Días Máximos de Inventario): Aquí está la clave del

reporte. Le dice al sistema el límite que la farmacia estableció como
regla (ej. solo deseo tener mercancía suficiente para cubrir 20 días
de ventas).

•  La Proyección y Magnitud (SUG y DIAS):

o  SUG (Sugeridos): El sistema proyecta cuánto comprar a 15, 30 y 60
días. Lógicamente, en un caso de sobrestock todos marcan -> 0,
indicándole al cliente que no debe comprar ni una unidad más.

o  DIAS (Días de Inventario Alto): Esta columna indica exactamente
cuántos días le va a durar la CANTIDAD ACTUAL si se sigue
moviendo al ritmo del VMD. Cuantifica el tamaño del problema.

Ejemplo: Si el cliente tiene una regla de MAX-> 20 días, pero en la
columna DIAS marca 4.414, el sistema le está traduciendo los
números a una realidad directa: "A tu ritmo de ventas actual, esas 49
unidades te van a durar 4.414 días en agotarse. Tienes un inventario
alto que rompe drásticamente tu límite configurado".

El botón verde de Descargar Excel es el cierre perfecto de la herramienta. El
encargado de compras o el gerente puede descargar este listado y pasárselo al
equipo de piso de ventas o mercadeo para que ejecuten las promociones de
liquidación de forma inmediata.

