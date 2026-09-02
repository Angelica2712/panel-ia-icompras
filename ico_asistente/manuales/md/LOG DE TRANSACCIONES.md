LOG DE TRANSACCIONES

Este panel funciona como la "caja negra" de Icompras360. Su objetivo es registrar
y estampar con fecha y hora absolutamente todas las acciones que ocurren dentro
de la plataforma. Es la herramienta definitiva para auditorías de seguridad, rastreo
de errores (debugging) y para resolver discrepancias cuando un cliente reporta un
comportamiento inusual o afirma no haber realizado una acción específica.

•  Buscador Multicampo: En la parte superior, la barra de "Buscar" es el
mejor aliado del equipo. Permite rastrear un problema introduciendo
directamente el ID de un pedido, el correo del cliente (email), el nombre de
la farmacia o el texto de la operación, filtrando miles de registros en
segundos.

•  Filtro por Fecha (Desde / Hasta): Junto al buscador hay dos campos de
fecha para acotar la búsqueda a un rango específico, útil cuando ya se
sabe aproximadamente cuándo ocurrió el hecho a auditar.

•  Filtro Rápido en Pantalla: Debajo de los filtros anteriores hay un campo
de "Filtro rápido" que no consulta el servidor, sino que filtra al instante
los registros que ya están cargados en la página actual.

•  Descargar Excel: Exporta a un archivo .xlsx todo el log resultante de los
filtros aplicados (Desde, Hasta y Buscar), útil para compartir evidencia de
auditoría fuera de la plataforma.

•  La Columna Clave - OPERACION: Esta es la columna más importante

para soporte. A diferencia de las demás que muestran datos básicos, esta
columna revela el detalle técnico de la acción.

•  Ejemplo de carga: Muestra rastros legibles como PRODUCTO AGREGADO
MANUALMENTE, seguido del código de barras (ej. 7591616000667), el ID
interno y la cantidad agregada.

•  Ejemplo de sistema: Muestra registros en formato JSON (como se ve en el
ID 1946499 con pedido_enviar_click). Esto le permite a soporte ver la
dirección IP desde donde se envió el pedido y el navegador exacto ("User
Agent"), lo cual es indispensable para detectar fraudes o errores de
compatibilidad.

•  Trazabilidad Completa: Las columnas de FECHA (marca de tiempo exacta
al segundo), USUARIO (correo) y CLIENTE completan el rompecabezas
para saber quién, cuándo y dónde ejecutó la acción. Además la tabla
incluye:

o  CODCLI: código interno del cliente/farmacia.

o  TIPO: tipo de usuario que ejecutó la operación.

o  VISITAS: contador de visitas asociado a ese registro.

