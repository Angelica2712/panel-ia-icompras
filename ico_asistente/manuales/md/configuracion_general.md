> **Audiencia: CLIENTE (Farmacia)** — Este documento aplica únicamente a usuarios cliente/farmacia. No uses esta información para responder consultas de proveedores.

NOTA PARA EL ASISTENTE (Ico): esta pantalla es solo para usuarios
administradores, y varias de sus pestañas tienen su propia condición de acceso
(tipo de usuario o permiso habilitado), indicada en el título de cada una. Antes
de explicar el detalle de una pestaña puntual, verifica si el usuario que pregunta
cumple esa condición:

•  Si la cumple: explica la pestaña con el detalle de abajo.

•  Si NO la cumple: no expliques el paso a paso de esa pestaña en particular,

solo menciona que existe esa funcionalidad y dile que debe solicitar a
soporte que se la activen (o que confirme con soporte si su tipo de
usuario permite verla). Esto aplica especialmente a "USUARIO", "GESTOR
OFERTAS", "CÓDIGO QR" y "PEDIDO DIRECTO", que dependen de tipo de
cuenta o de banderas específicas (el permiso de Pedido Directo ya tiene su propia nota
detallada en el manual "pedido_directo.md").

Las pestañas "BÁSICA", "PARÁMETROS", "INVENTARIO", "UBICACIÓN" y "COND.
COM. AVANZADA" son visibles para cualquier administrador, sin condición
adicional.

SEGURIDAD: si el usuario tiene activada la verificación en dos pasos (Google
Authenticator), al entrar a esta pantalla de Configuración se le pedirá el código
de autenticación antes de poder ver o modificar nada (igual que en "Días
mínimos y máximos" de Inventario).

OPCION DE CONFIGURACIÓN (pantalla general)

Este módulo solo es visible para usuarios administradores. Es la pantalla central
de ajustes de la cuenta, organizada en pestañas. Algunas pestañas solo
aparecen según el tipo de usuario o permisos habilitados.

PESTAÑA "BÁSICA"

Datos de identificación del cliente: Código, Rif, Contacto, Estado (activo/
inactivo), Nombre, Dirección, Abreviatura, y personalización visual (Color de
fondo y Color de letra que se usan para identificar a este cliente en pantallas de
grupo, como la lista de sucursales).

PESTAÑA "PARÁMETROS"

Datos operativos y de contacto: Cadena, Link de página, Aplicación, Sector,
campos reservados, Prioridad, Correo, Usuario, Clave, Zona, Teléfono, Fecha de
registro, Tipo de Cliente, "Usa precio" (define cuál de los precios 1 a 3 se usa
como precio de venta), Moneda del Inventario (sincronización), Tasa/Factor
cambiario, Días en Tránsito por defecto, Meses mínimo de notificación de
vencimiento, y el Modo de Notificación de productos en tránsito. También incluye:

•  Casilla "Mostrar gráfica de Ahorro Inicio": muestra u oculta el gráfico de

ahorro en la pantalla principal.

•  Casilla "Activar búsqueda extendida de productos": permite ver en el

catálogo productos ofrecidos por proveedores no afiliados o inactivos
(con su información de contacto, para gestionar la afiliación).

PESTAÑA "USUARIO" (solo tipo Cliente, Grupo o Admin)

•  Casilla "Activar botón de Envío de pedidos".

PESTAÑA "GESTOR OFERTAS" (solo usuarios tipo Oferta)

Configura el tipo de precio, la utilidad mínima, el DA mínimo/máximo (oferta), y
los descuentos DC (comercial), DI (internet) y PP (pronto pago) por defecto.

PESTAÑA "INVENTARIO"

Aquí se configuran los umbrales y reglas que alimentan varios reportes ya
documentados (sobrestock, categorización, pedidos automáticos):

•  Valor bajo / Valor alto (días de inventario): umbrales usados para

detectar sobrestock y productos sin movimiento.

•  Campo para Marca en el Inventario: define si se usa la MARCA o una

marca alterna (subgrupo) en las pantallas de inventario.

•  Criterio y Preferencia del pedido automático, y Porcentaje de redondeo

al múltiplo para pedidos automáticos.

•  Tolerancia (%), casilla "Sinc. Inv. con Frecuencia del Cliente", casilla

"Activar Pedido Automático" a una hora específica todos los días, casilla
"Aplicar redondeo por Múltiplos de compras" y casilla "Utilizar como
Múltiplo de compra el Bulto del inventario del cliente".

•  Porcentajes de categorización ABC: Tipo A, Tipo B (el Tipo C se calcula

automáticamente como el resto hasta 100%). Estos porcentajes son los
que usa el "REPORTE DE CATEGORIZACION DE PRODUCTOS".

•  Porcentajes de categorización ORO/PLATA/BRONCE/DIAMANTE (el

Bronce se calcula como el resto), y el "Campo prioritario para
categorización de productos" (Costo o Precio de Venta).

PESTAÑA "CÓDIGO QR" (solo administradores de Grupo con el permiso de QR
habilitado)

Muestra un código QR para vincular o dar de alta rápidamente algo relacionado
con la cuenta del grupo.

PESTAÑA "PEDIDO DIRECTO" (solo administradores de Grupo con Pedido Directo)

•  Casilla "Cerrar automáticamente el pedido directo grupal al enviarlo": si

la activas, al enviar un pedido directo grupal las sucursales que queden
desmarcadas se cierran de forma PERMANENTE (no se pueden editar ni
reenviar después) y el pedido no queda en estado PARCIAL. Si la dejas
desactivada (comportamiento habitual), las sucursales no enviadas
quedan disponibles para reenviar después y el pedido puede quedar en
estado PARCIAL hasta completarse.

PESTAÑA "UBICACIÓN"

Estado, Municipio, Parroquia, Ciudad, Correo para notificaciones, Teléfono de
contacto, y un mapa interactivo (opcional) para marcar la ubicación exacta del
negocio.

PESTAÑA "COND. COM. AVANZADA"

Es un acceso directo al módulo de Condiciones Comerciales Avanzadas (ver el
manual "configuracion.md" para el detalle completo de cómo funciona). Desde
aquí puedes ver una vista previa de las reglas ya creadas para este cliente
(Proveedor, Condición, Valor, %, % × Día, Efecto, Activo), crear una regla nueva,
o ir a la "Gestión completa" (el listado con todas las reglas, editar y eliminar).
