{{--
    Partial: modal_ayuda
    Uso: @include('partials.modal_ayuda', ['modulo' => 'ventas'])
    Módulos disponibles: ventas | preventa | cierre_preventa | comandas
--}}

@php
    $ayuda = [
        'ventas' => [
            'titulo' => 'Generar Venta',
            'icono'  => 'fa-shopping-cart',
            'color'  => '#0b5ed7',
            'pasos'  => [
                ['icono' => 'fa-barcode',       'titulo' => 'Agregar productos',        'desc' => 'Escribe el código de barras en el campo superior y presiona Enter. Para agregar varias unidades de una vez usa el formato <strong>cantidad*código</strong> — por ejemplo: <code>3*0045</code> agrega 3 unidades del producto 0045. También puedes buscar por nombre en el panel derecho y hacer clic en el producto.'],
                ['icono' => 'fa-edit',           'titulo' => 'Ajustar cantidad',         'desc' => 'En el carrito usa los botones <strong>+</strong> y <strong>−</strong> para cambiar la cantidad, o edita el número directamente haciendo clic sobre él.'],
                ['icono' => 'fa-credit-card',    'titulo' => 'Seleccionar forma de pago','desc' => 'Elige entre Efectivo, Débito, Crédito, Transferencia, Cheque o <strong>Mixto</strong> (para combinar dos o más formas). Si eliges Mixto se abrirá un panel para ingresar los montos por cada medio.'],
                ['icono' => 'fa-check-circle',   'titulo' => 'Pagar',                    'desc' => 'Presiona <strong>PAGAR</strong>. El sistema validará el stock disponible de todos los productos antes de procesar. Si algún producto no tiene stock suficiente se te avisará antes de continuar.'],
                ['icono' => 'fa-save',           'titulo' => 'Guardar borrador',         'desc' => 'Si necesitas pausar una venta sin perderla, usa <strong>Guardar Borrador</strong>. Puedes recuperarla más tarde desde la pestaña <i class="fa fa-file"></i> del panel derecho.'],
            ],
            'tips' => [
                'El subtotal de cada línea se actualiza automáticamente al cambiar la cantidad.',
                'Si un producto tiene precio por tramo de cantidad, el precio se ajusta automáticamente según lo que agregues.',
                'La pestaña <i class="fa fa-exclamation-triangle"></i> muestra alertas de stock bajo o productos sin precio.',
                'La pestaña <i class="fa fa-money"></i> muestra el resumen de la caja del turno actual.',
            ],
        ],

        'preventa' => [
            'titulo' => 'Generar Preventa',
            'icono'  => 'fa-receipt',
            'color'  => '#198754',
            'pasos'  => [
                ['icono' => 'fa-barcode',      'titulo' => 'Agregar productos',   'desc' => 'Escribe el código de barras y presiona Enter. Para múltiples unidades usa <strong>cantidad*código</strong> — por ejemplo: <code>2*0012</code>. También puedes buscar por nombre en el panel derecho.'],
                ['icono' => 'fa-edit',         'titulo' => 'Ajustar cantidades',  'desc' => 'Usa los botones <strong>+</strong> y <strong>−</strong> del carrito o edita la cantidad directamente.'],
                ['icono' => 'fa-receipt',      'titulo' => 'Generar Preventa',    'desc' => 'Presiona <strong>Generar Preventa</strong>. El sistema validará el stock y generará un comprobante con número y código de barras. <strong>La preventa no descuenta stock</strong> — solo reserva el pedido para cerrarlo después.'],
                ['icono' => 'fa-list',         'titulo' => 'Preventas pendientes','desc' => 'La pestaña <i class="fa fa-list"></i> del panel derecho muestra todas las preventas que aún no han sido cobradas. Puedes cargar una preventa anterior al carrito haciendo clic en ella.'],
                ['icono' => 'fa-save',         'titulo' => 'Borradores',          'desc' => 'Igual que en ventas, puedes guardar un borrador para continuar después sin perder los productos agregados.'],
            ],
            'tips' => [
                'La preventa genera un ticket con código de barras para que el cliente lo presente al momento del pago.',
                'El stock se descuenta recién cuando se cierra la preventa en el módulo <strong>Cierre Preventa</strong>.',
                'Si un producto no tiene stock suficiente al momento de generar, el sistema te avisará pero igual puedes continuar si el negocio permite stock negativo.',
                'Puedes imprimir o mostrar el ticket de preventa al cliente desde el modal que aparece tras generarla.',
            ],
        ],

        'cierre_preventa' => [
            'titulo' => 'Cierre de Preventa',
            'icono'  => 'fa-check-square',
            'color'  => '#6f42c1',
            'pasos'  => [
                ['icono' => 'fa-search',       'titulo' => 'Buscar la preventa',   'desc' => 'Ingresa el <strong>código de preventa</strong> (está en el ticket impreso) en el campo de búsqueda y presiona <strong>Buscar</strong> o la tecla Enter. Se cargarán automáticamente los productos y el total.'],
                ['icono' => 'fa-eye',          'titulo' => 'Revisar el detalle',   'desc' => 'Verifica que los productos y el total coincidan con lo que el cliente presenta. Si hay diferencias, vuelve al módulo Generar Preventa para anular y crear una nueva.'],
                ['icono' => 'fa-credit-card',  'titulo' => 'Elegir forma de pago', 'desc' => 'Selecciona la forma de pago en el selector inferior. Si el cliente paga con múltiples medios, elige <strong>Mixto</strong> y se abrirá un panel para desglosar los montos.'],
                ['icono' => 'fa-check',        'titulo' => 'Generar Venta',        'desc' => 'Presiona <strong>Generar Venta</strong>. El sistema validará el stock, descontará las unidades del inventario y emitirá el ticket de venta definitivo. La preventa quedará marcada como cerrada.'],
            ],
            'tips' => [
                'Es en este paso donde el stock se descuenta efectivamente del inventario.',
                'Si el stock de algún producto es insuficiente al momento del cierre, el sistema mostrará un error. Coordina con el encargado de bodega.',
                'El ticket que se genera al cerrar es el comprobante de venta definitivo (no el de preventa).',
                'Puedes limpiar el formulario en cualquier momento con el botón <strong>Limpiar</strong> para buscar otra preventa.',
            ],
        ],

        'almacen_productos' => [
            'titulo' => 'Productos',
            'icono'  => 'fa-cube',
            'color'  => '#0b5ed7',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',   'titulo' => 'Crear producto',          'desc' => 'Haz clic en <strong>Nuevo Producto</strong>. Completa el código (puede ser el código de barras), descripción, precio de compra, impuesto y precio de venta. El precio de venta se puede calcular automáticamente ingresando el margen deseado.'],
                ['icono' => 'fa-edit',           'titulo' => 'Editar producto',         'desc' => 'En la tabla, haz clic en el ícono de lápiz <i class="fa fa-edit"></i> del producto que quieres modificar. Se abrirá el formulario de edición con todos sus datos actuales.'],
                ['icono' => 'fa-trash',          'titulo' => 'Eliminar producto',       'desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i>. El producto queda desactivado (eliminación lógica) y se puede recuperar desde el módulo <strong>Reactivaciones</strong>.'],
                ['icono' => 'fa-upload',         'titulo' => 'Carga masiva Excel',      'desc' => 'Para cargar muchos productos a la vez, primero descarga la <strong>Plantilla Excel</strong>, complétala con tus productos y luego usa <strong>Carga Masiva Excel</strong> para importarla. Útil para el ingreso inicial de inventario.'],
                ['icono' => 'fa-image',          'titulo' => 'Foto del producto',       'desc' => 'En el formulario de creación o edición puedes subir una foto del producto. Se recomienda usar imágenes cuadradas de al menos 200×200 px en formato JPG o PNG.'],
            ],
            'tips' => [
                'El código del producto es el que se usa para buscarlo rápidamente al momento de generar una venta.',
                'Si dejas el precio de compra en 0, el sistema igual creará el producto pero no podrá calcular márgenes de ganancia en reportes.',
                'Puedes filtrar y buscar productos en la tabla usando el campo de búsqueda que aparece sobre la tabla.',
                'Si eliminas un producto por error, recupéralo desde <strong>Reactivaciones → Productos eliminados</strong>.',
            ],
        ],

        'almacen_categorias' => [
            'titulo' => 'Categorías',
            'icono'  => 'fa-tags',
            'color'  => '#6f42c1',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Crear categoría',   'desc' => 'Haz clic en <strong>Nueva Categoría</strong>, ingresa el nombre y confirma. Las categorías sirven para agrupar productos y facilitar la búsqueda al momento de vender.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar categoría',  'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> de la categoría. Puedes cambiar el nombre; los productos ya asociados se actualizan automáticamente.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar categoría','desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i>. Solo se puede eliminar una categoría si <strong>no tiene productos asociados</strong>. Si tiene productos, primero muévelos a otra categoría o elimínalos.'],
            ],
            'tips' => [
                'En la tabla puedes ver cuántos productos tiene cada categoría en la columna <strong>Productos Asociados</strong>.',
                'Usa nombres de categorías cortos y claros — aparecen como filtro en el punto de venta.',
                'Si eliminas una categoría por error, recupérala desde <strong>Reactivaciones → Categorías eliminadas</strong>.',
                'Las categorías también se usan como filtro en los reportes de ventas por categoría.',
            ],
        ],

        'almacen_crear_recetas' => [
            'titulo' => 'Crear Receta',
            'icono'  => 'fa-magic',
            'color'  => '#198754',
            'pasos'  => [
                ['icono' => 'fa-search',        'titulo' => 'Buscar insumos',          'desc' => 'En el campo <strong>Ingrese código</strong>, escribe el código o nombre del insumo (materia prima) y selecciónalo de la lista. Ingresa la cantidad que requiere la receta y presiona Enter o el botón <strong>+</strong> para agregarlo a la lista.'],
                ['icono' => 'fa-list',          'titulo' => 'Revisar ingredientes',    'desc' => 'En la tabla inferior verás todos los insumos agregados con su costo unitario y total. Puedes eliminar una línea haciendo clic en el ícono de basura si te equivocaste.'],
                ['icono' => 'fa-percent',       'titulo' => 'Precio y margen',         'desc' => 'Completa el <strong>Código</strong> y <strong>Nombre</strong> de la receta. Puedes ingresar el <strong>Margen (%)</strong> y el precio se calculará automáticamente, o bien ingresar directamente el <strong>Precio de Venta</strong> y el margen se calculará solo.'],
                ['icono' => 'fa-camera',        'titulo' => 'Foto (opcional)',         'desc' => 'Puedes subir una foto de la receta terminada. Usa <strong>Subir</strong> para cargar la imagen antes de grabar.'],
                ['icono' => 'fa-save',          'titulo' => 'Grabar receta',           'desc' => 'Haz clic en <strong>Grabar nueva receta</strong>. La receta quedará disponible como producto en el punto de venta. Al venderse, el sistema descontará automáticamente el stock de cada insumo según las cantidades definidas.'],
            ],
            'tips' => [
                'Los insumos son los productos del almacén; asegúrate de tenerlos creados antes de armar la receta.',
                'El costo total de la receta es la suma de los insumos. Úsalo como referencia para definir el precio de venta.',
                'Si cambias la cantidad de un insumo, borra la línea y agrégala de nuevo con la cantidad correcta.',
                'Una vez grabada la receta, puedes editarla desde <strong>Almacén → Recetas</strong>.',
            ],
        ],

        'almacen_recetas' => [
            'titulo' => 'Ver / Editar Recetas',
            'icono'  => 'fa-book',
            'color'  => '#d97706',
            'pasos'  => [
                ['icono' => 'fa-filter',        'titulo' => 'Filtrar por categoría',   'desc' => 'Usa las pastillas de categoría en la parte superior para ver solo las recetas de una categoría específica. Haz clic en <strong>Todas</strong> para ver el listado completo.'],
                ['icono' => 'fa-eye',           'titulo' => 'Ver detalle',             'desc' => 'Haz clic en el ícono de ojo <i class="fa fa-eye"></i> para ver el detalle completo de una receta: ingredientes, cantidades y costos.'],
                ['icono' => 'fa-edit',          'titulo' => 'Editar receta',           'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i>. Puedes modificar el nombre, precio, margen, categoría, descripción, foto y los insumos que la componen.'],
                ['icono' => 'fa-trash',         'titulo' => 'Eliminar receta',         'desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i>. La receta queda desactivada y se puede recuperar desde <strong>Reactivaciones → Recetas eliminadas</strong>.'],
            ],
            'tips' => [
                'Si modificas las cantidades de ingredientes de una receta ya vendida, los reportes anteriores no se alteran.',
                'Una receta eliminada deja de aparecer en el punto de venta pero no afecta el historial de ventas pasadas.',
                'Puedes buscar una receta por nombre usando el buscador que aparece sobre la tabla.',
                'Las recetas aparecen en el punto de venta exactamente igual que los productos normales.',
            ],
        ],

        'almacen_rango_precios' => [
            'titulo' => 'Precio Según Cantidad',
            'icono'  => 'fa-sliders',
            'color'  => '#dc3545',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Nuevo rango',             'desc' => 'Haz clic en <strong>Nuevo Rango</strong>. Escribe el código del producto en el campo código y selecciónalo de la lista desplegable. Luego define la <strong>cantidad mínima</strong>, <strong>cantidad máxima</strong> y el <strong>precio de venta</strong> para ese tramo.'],
                ['icono' => 'fa-info-circle',  'titulo' => 'Cantidad máxima vacía',   'desc' => 'Si dejas la <strong>cantidad máxima en 0 o vacía</strong>, el rango aplica desde la cantidad mínima hacia arriba sin límite superior. Útil para el tramo de mayor volumen.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar rango',            'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> para modificar las cantidades o el precio de un rango existente.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar rango',          'desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i> para eliminar un rango. El producto volverá a usar su precio de venta estándar para ese intervalo de cantidad.'],
            ],
            'tips' => [
                'Ejemplo: si defines 1–5 unidades a $1.000 y 6+ unidades a $800, al agregar 6 unidades en caja el sistema automáticamente aplica $800 por unidad.',
                'Los rangos se aplican automáticamente al punto de venta — el vendedor no necesita hacer nada especial.',
                'Puedes crear varios tramos para un mismo producto (ej: 1-10, 11-50, 51+).',
                'El precio del rango reemplaza al precio estándar del producto solo cuando la cantidad está dentro del rango.',
            ],
        ],

        'almacen_crear_promocion' => [
            'titulo' => 'Crear Promoción',
            'icono'  => 'fa-tags',
            'color'  => '#6f42c1',
            'pasos'  => [
                ['icono' => 'fa-barcode',      'titulo' => 'Buscar producto',         'desc' => 'Escribe el código o nombre del producto en el campo <strong>Ingrese código</strong> y selecciónalo de la lista desplegable que aparece. Si el código es conocido puedes ingresarlo directamente.'],
                ['icono' => 'fa-plus-circle',  'titulo' => 'Agregar producto',         'desc' => 'Define la <strong>cantidad</strong> que se incluirá de ese producto en la promoción y haz clic en <strong>Grabar nueva promocion</strong>. Repite el proceso para agregar todos los artículos del combo.'],
                ['icono' => 'fa-pencil',       'titulo' => 'Nombre, precio y fechas',  'desc' => 'Completa el <strong>nombre</strong> de la promoción, el <strong>precio de venta</strong> y elige la <strong>categoría</strong>. Si la promo tiene vigencia, ingresa las fechas de inicio y término. Si no tiene fecha límite, activa la opción <strong>Esta promoción estará siempre activa</strong>.'],
                ['icono' => 'fa-save',         'titulo' => 'Guardar',                  'desc' => 'Haz clic en <strong>Guardar</strong> para crear la promoción. Quedará disponible de inmediato en el punto de venta con el precio y los artículos definidos.'],
            ],
            'tips' => [
                'El precio de costo se calcula automáticamente sumando el costo de todos los productos del combo.',
                'Puedes asignar una categoría a la promoción para que aparezca agrupada en el punto de venta.',
                'Si no defines fechas de vigencia, la promoción estará activa indefinidamente hasta que la elimines.',
                'Usa nombres descriptivos como "Combo Almuerzo" o "Pack 3x2" para que sean fáciles de reconocer al vender.',
            ],
        ],

        'almacen_editar_promocion' => [
            'titulo' => 'Editar / Eliminar Promoción',
            'icono'  => 'fa-edit',
            'color'  => '#fd7e14',
            'pasos'  => [
                ['icono' => 'fa-search',       'titulo' => 'Buscar la promoción',      'desc' => 'En el listado de promociones, ubica la que deseas modificar. Puedes buscarla por nombre o filtrar por categoría usando las pestañas superiores.'],
                ['icono' => 'fa-edit',         'titulo' => 'Abrir edición',            'desc' => 'Haz clic en el ícono de edición <i class="fa fa-edit"></i> de la fila correspondiente. Se abrirá el formulario con todos los datos actuales de la promoción.'],
                ['icono' => 'fa-pencil',       'titulo' => 'Modificar datos',          'desc' => 'Puedes cambiar el <strong>nombre</strong>, <strong>precio de venta</strong>, <strong>categoría</strong>, las <strong>fechas de vigencia</strong> o los productos del combo. Edita los campos necesarios y haz clic en <strong>Guardar cambios</strong>.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar promoción',       'desc' => 'Para eliminar, haz clic en el ícono de basura <i class="fa fa-trash"></i>. Se pedirá confirmación antes de eliminar. La promoción pasará al módulo <strong>Reactivaciones</strong> donde podrá recuperarse si fue un error.'],
            ],
            'tips' => [
                'Puedes agregar o quitar productos del combo directamente desde el formulario de edición.',
                'Al cambiar las fechas de vigencia, el cambio aplica de inmediato en el punto de venta.',
                'Las promociones eliminadas no se pierden definitivamente — ve a <strong>Reactivaciones → Promociones eliminadas</strong> para recuperarlas.',
                'Si modificas el precio de venta, el nuevo precio se aplicará en la próxima transacción que incluya esa promoción.',
            ],
        ],

        'compras_ingresos' => [
            'titulo' => 'Compras (Facturas / Boletas)',
            'icono'  => 'fa-shopping-cart',
            'color'  => '#0d6efd',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Nueva factura de compra',  'desc' => 'Haz clic en <strong>Nueva factura de compra</strong>. Selecciona el proveedor, ingresa el número de documento, fecha, condición de pago y agrega los productos con su cantidad y precio de compra neto. Luego confirma para registrar la compra.'],
                ['icono' => 'fa-plus-circle',  'titulo' => 'Nueva boleta de compra',   'desc' => 'Igual que la factura pero para compras con boleta. Usa el botón <strong>Nueva boleta de compra</strong>. Útil para compras de proveedores informales o sin RUT.'],
                ['icono' => 'fa-calendar',     'titulo' => 'Vencimientos por mes',     'desc' => 'Haz clic en <strong>Vencimientos por mes</strong> para ver qué facturas tienen fecha de pago próxima. Permite planificar los pagos a proveedores según su vencimiento.'],
                ['icono' => 'fa-eye',          'titulo' => 'Ver detalle de compra',    'desc' => 'En la tabla, haz clic en el ícono de ojo <i class="fa fa-eye"></i> para ver el detalle completo de una compra: productos, cantidades, totales y estado de pago.'],
                ['icono' => 'fa-dollar',       'titulo' => 'Registrar pago',           'desc' => 'En las acciones de la compra puedes registrar el pago total o parcial. Al marcar como pagada, la compra queda cerrada en el sistema.'],
            ],
            'tips' => [
                'El stock de los productos se actualiza automáticamente al registrar una compra con ingresos.',
                'Las facturas sin pagar aparecen destacadas — usa el filtro <strong>Mostrar solo facturas sin pagar</strong> para revisarlas rápido.',
                'El campo condición de pago permite registrar si es crédito 30, 60, 90 días — útil para controlar vencimientos.',
                'Puedes editar o eliminar una compra mientras no esté cerrada. Una vez pagada no es editable.',
            ],
        ],

        'compras_proveedores' => [
            'titulo' => 'Proveedores',
            'icono'  => 'fa-truck',
            'color'  => '#6610f2',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Crear proveedor',   'desc' => 'Haz clic en <strong>Nuevo Proveedor</strong>. Completa los datos obligatorios (*): RUT (con dígito verificador), Razón Social, Giro, Forma de pago, Dirección, Región, Comuna. Opcionalmente puedes ingresar nombre de fantasía, teléfono, correo y datos del contacto.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar proveedor',  'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> del proveedor. Puedes actualizar cualquier campo excepto el RUT (identificador único). Los cambios aplican de inmediato a las compras futuras.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar proveedor','desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i>. Solo se puede eliminar un proveedor que <strong>no tenga compras registradas</strong>. Si tiene historial, usa la edición para desactivarlo o cambiar su nombre.'],
            ],
            'tips' => [
                'El RUT se valida automáticamente — ingresa el formato 12345678-9.',
                'La <strong>Forma de pago</strong> del proveedor se usa como valor por defecto al registrar cada compra, puedes cambiarlo en el momento.',
                'Mantén el correo del contacto actualizado para recibir facturas por email directamente al proveedor.',
                'Si eliminas un proveedor por error, recupéralo desde <strong>Reactivaciones → Proveedores eliminados</strong>.',
            ],
        ],

        'compras_entradas_salidas' => [
            'titulo' => 'Entradas y Salidas de Stock',
            'icono'  => 'fa-exchange',
            'color'  => '#198754',
            'pasos'  => [
                ['icono' => 'fa-arrow-down',    'titulo' => 'Registrar una entrada/salida', 'desc' => 'Selecciona el <strong>Tipo de movimiento</strong>: Entrada (+), Salida (-) o Merma (-). Luego busca el producto por código o nombre, ingresa la <strong>Cantidad</strong> y presiona <strong>Cargar</strong>. El producto se agrega a la tabla de movimientos pendientes.'],
                ['icono' => 'fa-list',          'titulo' => 'Agregar varios movimientos',   'desc' => 'Puedes cargar varios productos antes de grabar. Cada producto se va sumando a la tabla. Revisa que los tipos y cantidades sean correctos. Puedes eliminar una fila si te equivocaste.'],
                ['icono' => 'fa-save',          'titulo' => 'Grabar movimientos',           'desc' => 'Cuando tengas todos los movimientos listos, presiona <strong>Grabar movimientos</strong>. El stock de cada producto se actualizará inmediatamente y quedará el registro en el historial de movimientos.'],
            ],
            'tips' => [
                '<strong>Entrada (+)</strong>: aumenta el stock. Úsala para correcciones de inventario o reposición sin factura.',
                '<strong>Salida (-)</strong>: disminuye el stock. Úsala para consumo interno, ajustes o retiros.',
                '<strong>Merma (-)</strong>: disminuye el stock por pérdida, vencimiento o daño. Queda registrado como merma en los reportes.',
                'Estos movimientos quedan registrados en el módulo <strong>Reportes → Movimientos de productos</strong> para trazabilidad completa.',
                'Para compras con documento (factura o boleta), usa el módulo <strong>Compras</strong> en lugar de este módulo.',
            ],
        ],

        'tickets_emitidos' => [
            'titulo' => 'Tickets Emitidos',
            'icono'  => 'fa-receipt',
            'color'  => '#01338d',
            'pasos'  => [
                ['icono' => 'fa-filter',         'titulo' => 'Filtrar por fecha',        'desc' => 'Usa los campos <strong>Fecha Desde</strong> y <strong>Fecha Hasta</strong> para buscar tickets en un rango de fechas. Presiona <strong>Filtrar</strong> para aplicar el filtro o <strong>Limpiar</strong> para ver todos.'],
                ['icono' => 'fa-print',          'titulo' => 'Ver e imprimir ticket',    'desc' => 'En la columna <strong>Acciones</strong>, haz clic en el ícono de ojo <i class="fa fa-eye"></i> para abrir el ticket en formato PDF. Desde esa ventana puedes imprimirlo o descargarlo.'],
                ['icono' => 'fa-ban',            'titulo' => 'Anular un ticket',         'desc' => 'Haz clic en el ícono de anulación <i class="fa fa-ban"></i> para anular un ticket. Se mostrará el detalle del ticket con la opción de <strong>Anular todo</strong> o seleccionar productos específicos para anular parcialmente.'],
                ['icono' => 'fa-exclamation-triangle', 'titulo' => 'Qué ocurre al anular', 'desc' => 'Al anular un ticket: se restituye el stock de los productos, se descuenta el monto del total de ventas de esa caja y el ticket queda marcado como <strong>Anulado</strong> en el historial. Esta acción <strong>no se puede deshacer</strong>.'],
            ],
            'tips' => [
                'La columna <strong>Estado</strong> muestra si el ticket está Pagado o Anulado.',
                'Puedes buscar por número de ticket, vendedor o forma de pago usando el buscador de la tabla.',
                'Si un ticket fue pagado con múltiples formas de pago, aparece el detalle en la columna Forma Pago.',
                'Los tickets anulados no se eliminan del historial — quedan visibles para auditoría.',
                'Para anular tickets de una caja ya cerrada, se requieren permisos de administrador.',
            ],
        ],

        'cierres_caja' => [
            'titulo' => 'Historial de Cierres de Caja',
            'icono'  => 'fa-archive',
            'color'  => '#495057',
            'pasos'  => [
                ['icono' => 'fa-table',          'titulo' => 'Tabla de cierres',         'desc' => 'La tabla muestra todos los cierres de caja registrados, con el usuario que los realizó, hora de apertura y cierre, monto inicial, total de ventas, monto esperado, monto declarado y diferencia.'],
                ['icono' => 'fa-info-circle',    'titulo' => 'Ver detalle del cierre',   'desc' => 'Haz clic en el ícono de detalle <i class="fa fa-eye"></i> para ver el resumen completo del cierre: ventas por forma de pago, total de tickets, montos de apertura y cierre, y la diferencia entre lo esperado y lo declarado.'],
                ['icono' => 'fa-print',          'titulo' => 'Imprimir ticket de cierre','desc' => 'Haz clic en el ícono de impresión <i class="fa fa-print"></i> para abrir el ticket de cierre en PDF. Sirve como comprobante físico del cierre del turno.'],
                ['icono' => 'fa-calculator',     'titulo' => 'Columna Diferencia',       'desc' => 'Esta columna muestra la diferencia entre el <strong>Monto Esperado</strong> (calculado por el sistema) y el <strong>Monto Declarado</strong> (ingresado por el cajero). Un valor negativo indica faltante; positivo indica sobrante.'],
            ],
            'tips' => [
                'El <strong>Monto Inicial</strong> es el dinero con que se abrió la caja ese turno.',
                'El <strong>Monto Esperado</strong> = Inicial + Total Ventas en efectivo y otros medios.',
                'Una diferencia de 0 indica que el cajero declaró exactamente lo que el sistema espera.',
                'Puedes exportar el historial a Excel desde el botón de exportación que aparece sobre la tabla.',
                'Los cierres de caja son por usuario — si hay varios cajeros, cada uno tiene su propio historial.',
            ],
        ],

        'usuarios' => [
            'titulo' => 'Usuarios del Sistema',
            'icono'  => 'fa-users',
            'color'  => '#01338d',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Crear usuario',          'desc' => 'Haz clic en <strong>Nuevo Usuario</strong>. Ingresa el nombre de usuario (login), el nombre completo, la contraseña y el <strong>Rol</strong> que tendrá dentro del sistema. El rol define a qué módulos podrá acceder.'],
                ['icono' => 'fa-edit',          'titulo' => 'Editar usuario',          'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i>. Puedes cambiar el nombre completo, la contraseña (deja en blanco para no cambiarla) y el rol asignado. El nombre de usuario (login) no se puede modificar.'],
                ['icono' => 'fa-trash',         'titulo' => 'Eliminar usuario',        'desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i> para desactivar el usuario. El usuario eliminado no podrá iniciar sesión hasta ser reactivado desde <strong>Reactivaciones</strong>.'],
                ['icono' => 'fa-key',           'titulo' => 'Cambiar contraseña',      'desc' => 'Edita el usuario y escribe la nueva contraseña en el campo correspondiente. Si dejas el campo en blanco, la contraseña actual no cambia.'],
            ],
            'tips' => [
                'Cada usuario debe tener un rol asignado — sin rol el sistema puede restringir el acceso a todos los módulos.',
                'El nombre de usuario es único y se usa para el inicio de sesión.',
                'Puedes ver los usuarios activos e inactivos filtrando en la tabla.',
                'Si un empleado cambia de puesto, basta con cambiarle el rol — no necesitas crear un nuevo usuario.',
            ],
        ],

        'roles' => [
            'titulo' => 'Roles del Sistema',
            'icono'  => 'fa-id-badge',
            'color'  => '#6f42c1',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Crear rol',               'desc' => 'Haz clic en <strong>Nuevo Rol</strong> e ingresa el nombre del rol (ej: Cajero, Bodeguero, Supervisor). El rol es el perfil de acceso que se asigna a los usuarios.'],
                ['icono' => 'fa-list',         'titulo' => 'Ver menús del rol',        'desc' => 'En la columna <strong>Menús Asociados</strong> puedes hacer clic para ver qué módulos y submódulos tiene habilitados ese rol. Muestra el menú completo al que tiene acceso el rol.'],
                ['icono' => 'fa-users',        'titulo' => 'Ver usuarios del rol',    'desc' => 'En la columna <strong>Usuarios Asociados</strong> puedes ver qué usuarios tienen ese rol asignado actualmente.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar rol',            'desc' => 'Solo se puede eliminar un rol si <strong>no tiene usuarios asignados</strong>. Si tiene usuarios, primero cámbiales el rol o elíminalos.'],
            ],
            'tips' => [
                'Crea roles según los puestos de trabajo: Vendedor, Cajero, Supervisor, Bodeguero, etc.',
                'Un rol sin permisos de menú no puede acceder a ningún módulo — asigna los permisos desde <strong>Permisos Menú</strong>.',
                'Puedes tener tantos roles como necesites — no hay límite.',
                'Cambiar el rol de un usuario surte efecto inmediatamente al iniciar sesión la próxima vez.',
            ],
        ],

        'permisos_menu' => [
            'titulo' => 'Permisos de Menú',
            'icono'  => 'fa-sitemap',
            'color'  => '#0d6efd',
            'pasos'  => [
                ['icono' => 'fa-mouse-pointer', 'titulo' => 'Seleccionar rol',         'desc' => 'Usa el selector <strong>Seleccione rol</strong> para elegir el rol al que quieres asignar o quitar acceso a módulos. Los menús y submenús disponibles aparecerán como tarjetas con casillas de verificación.'],
                ['icono' => 'fa-check-square',  'titulo' => 'Marcar/desmarcar accesos','desc' => 'Marca las casillas de los <strong>menús y submenús</strong> a los que el rol debería tener acceso. Desmarca los que no debe ver. Puedes usar <strong>Seleccionar todos</strong> para marcar o desmarcar todo a la vez.'],
                ['icono' => 'fa-save',          'titulo' => 'Actualizar permisos',    'desc' => 'Haz clic en <strong>Actualizar permisos</strong> para guardar los cambios. Los usuarios con ese rol verán el menú actualizado la próxima vez que inicien sesión o recarguen la página.'],
            ],
            'tips' => [
                'Este módulo controla a qué secciones del menú lateral puede navegar cada rol.',
                'Si un usuario no ve un módulo en el menú, verifica que su rol tenga el acceso marcado aquí.',
                'Los cambios de permisos aplican de forma inmediata — no se necesita reiniciar el sistema.',
                'Un rol puede tener acceso a solo los submenús que necesita, sin exponer el resto del sistema.',
            ],
        ],

        'permisos_roles' => [
            'titulo' => 'Permisos por Rol (Acciones)',
            'icono'  => 'fa-shield',
            'color'  => '#198754',
            'pasos'  => [
                ['icono' => 'fa-mouse-pointer', 'titulo' => 'Seleccionar rol',         'desc' => 'Usa el selector <strong>Seleccione rol</strong> para ver los permisos de acción del rol. Se mostrarán tarjetas con los permisos disponibles por módulo (crear, editar, eliminar, exportar, etc.).'],
                ['icono' => 'fa-check-square',  'titulo' => 'Asignar permisos',       'desc' => 'Marca las acciones que el rol puede realizar. Por ejemplo: puede <em>ver</em> productos pero no <em>eliminarlos</em>. Usa <strong>Seleccionar todos</strong> para activar todos los permisos del rol rápidamente.'],
                ['icono' => 'fa-save',          'titulo' => 'Actualizar permisos',    'desc' => 'Haz clic en <strong>Actualizar permisos</strong> para guardar. Los cambios aplican de inmediato para todos los usuarios con ese rol.'],
            ],
            'tips' => [
                'La diferencia con <strong>Permisos Menú</strong>: ese módulo controla qué <em>secciones</em> ve el rol; este controla qué <em>acciones</em> puede hacer dentro de cada sección.',
                'Un rol puede ver un módulo pero no tener permiso para eliminar o exportar dentro de él.',
                'Revisa ambos módulos (Permisos Menú y Permisos Rol) cuando configures un rol nuevo.',
                'Si un botón no aparece para un usuario, es porque su rol no tiene el permiso de acción correspondiente.',
            ],
        ],

        'config_datos_corp' => [
            'titulo' => 'Datos Corporativos',
            'icono'  => 'fa-building',
            'color'  => '#495057',
            'pasos'  => [
                ['icono' => 'fa-pencil',       'titulo' => 'Editar datos de la empresa',  'desc' => 'Completa o actualiza los campos: <strong>Nombre de la Empresa</strong>, <strong>Nombre Fantasía</strong>, <strong>Dirección</strong>, <strong>Comuna</strong> y <strong>Teléfono</strong>. Estos datos aparecen en los tickets de venta y documentos impresos.'],
                ['icono' => 'fa-image',        'titulo' => 'Logo de la empresa',          'desc' => 'En la sección Logo haz clic en <strong>Seleccionar archivo</strong>, elige la imagen (JPG, PNG o GIF) y luego presiona <strong>Subir</strong> para cargarla. El logo se mostrará en los tickets impresos.'],
                ['icono' => 'fa-save',         'titulo' => 'Guardar cambios',             'desc' => 'Haz clic en <strong>Guardar</strong> al final del formulario para confirmar todos los cambios. Los datos actualizados se verán reflejados en los próximos documentos generados.'],
            ],
            'tips' => [
                'El nombre de la empresa aparece en el encabezado de cada ticket de venta.',
                'Si cambias el logo, el cambio aplica de inmediato en los nuevos tickets.',
                'La comuna seleccionada debe coincidir con la dirección real del negocio — aparece en las boletas.',
                'Mantén el teléfono actualizado ya que aparece en los documentos impresos para contacto.',
            ],
        ],

        'config_datos_glob' => [
            'titulo' => 'Variables Globales',
            'icono'  => 'fa-sliders',
            'color'  => '#0d6efd',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver variables del sistema',   'desc' => 'La tabla muestra todas las variables configurables del negocio: nombre, valor actual y descripción. Estas variables controlan comportamientos generales del sistema.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar una variable',         'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> en la columna Acciones. Se habilitará el campo para ingresar el nuevo valor. Confirma con el botón de guardar.'],
            ],
            'tips' => [
                '<strong>STOCK_NEGATIVO</strong>: si está en 1, permite vender productos aunque el stock sea 0 o negativo.',
                'Los cambios en variables globales aplican de inmediato sin necesidad de reiniciar el sistema.',
                'Algunas variables son de uso interno y no deben modificarse sin conocer su efecto.',
            ],
        ],

        'config_impuestos' => [
            'titulo' => 'Impuestos',
            'icono'  => 'fa-percent',
            'color'  => '#dc3545',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver impuestos configurados',  'desc' => 'La tabla muestra los impuestos registrados en el sistema (ej: IVA 19%), con su nombre, porcentaje y descripción.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar un impuesto',          'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> para modificar el porcentaje o descripción de un impuesto. El cambio aplica a los cálculos de precio de los productos que usen ese impuesto.'],
            ],
            'tips' => [
                'El impuesto que usas al crear un producto determina el precio de venta final con IVA incluido.',
                'Si cambias el valor de un impuesto, los productos que lo usan actualizarán su precio de venta automáticamente.',
                'En Chile el IVA estándar es 19% — no lo modifiques a menos que sea necesario por normativa.',
                'Si necesitas crear un impuesto nuevo (ej: 0% para productos exentos), contacta al administrador del sistema.',
            ],
        ],

        'config_mesas' => [
            'titulo' => 'Configurar Mesas',
            'icono'  => 'fa-table',
            'color'  => '#d97706',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Nueva mesa',                  'desc' => 'Haz clic en <strong>Nueva Mesa</strong>. Ingresa el <strong>nombre</strong> de la mesa (ej: Mesa 1, VIP, Terraza) y la <strong>capacidad</strong> en personas. Confirma con <strong>Guardar</strong>.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar mesa',                 'desc' => 'Haz clic en el ícono de lápiz sobre la tarjeta de la mesa. Puedes cambiar el nombre, la capacidad y el estado (Activa/Inactiva). Una mesa inactiva no aparece en el módulo de Comandas.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar mesa',               'desc' => 'Haz clic en el ícono de basura sobre la tarjeta para eliminar una mesa. Solo se puede eliminar si <strong>no tiene comandas abiertas</strong>. Si tiene historial, se puede desactivar en lugar de eliminar.'],
            ],
            'tips' => [
                'Las mesas se muestran como tarjetas en el plano del módulo de Comandas.',
                'Puedes crear tantas mesas como necesites — no hay límite.',
                'Una mesa inactiva no desaparece del historial, solo deja de mostrarse en el POS de comandas.',
                'El nombre de la mesa aparece en el ticket de comanda impreso.',
            ],
        ],

        'config_garzones' => [
            'titulo' => 'Configurar Garzones',
            'icono'  => 'fa-user-circle',
            'color'  => '#0891b2',
            'pasos'  => [
                ['icono' => 'fa-plus-circle',  'titulo' => 'Nuevo garzón',                'desc' => 'Haz clic en <strong>Nuevo Garzón</strong>. Completa los campos obligatorios (*): Nombre, Apellido y RUT (con formato 12345678-9). Los campos Teléfono y Email son opcionales.'],
                ['icono' => 'fa-edit',         'titulo' => 'Editar garzón',               'desc' => 'Haz clic en el ícono de lápiz <i class="fa fa-edit"></i> de la fila correspondiente. Puedes actualizar cualquier dato del garzón, incluyendo cambiar su estado a Activo o Inactivo.'],
                ['icono' => 'fa-ban',          'titulo' => 'Desactivar garzón',           'desc' => 'Si un garzón ya no trabaja en el local, cámbia su estado a <strong>Inactivo</strong> desde el formulario de edición. El garzón dejará de aparecer como opción al asignarlo a una comanda.'],
                ['icono' => 'fa-trash',        'titulo' => 'Eliminar garzón',             'desc' => 'Haz clic en el ícono de basura <i class="fa fa-trash"></i>. Solo elimina garzones que no tengan historial de comandas. Para casos con historial, usa la opción Inactivo.'],
            ],
            'tips' => [
                'El RUT del garzón se valida automáticamente — usa el formato 12345678-9.',
                'Los garzones activos aparecen en el selector al abrir o gestionar una comanda.',
                'Los reportes de ventas por garzón se generan en <strong>Reportes → Ventas por Garzón</strong>.',
                'Un garzón inactivo sigue apareciendo en el historial de comandas anteriores, pero no en nuevas asignaciones.',
            ],
        ],

        'reactiv_categorias' => [
            'titulo' => 'Categorías Eliminadas',
            'icono'  => 'fa-tags',
            'color'  => '#6f42c1',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver categorías eliminadas', 'desc' => 'La tabla muestra todas las categorías que han sido eliminadas, con su nombre, fecha de eliminación y el usuario que la eliminó.'],
                ['icono' => 'fa-undo',         'titulo' => 'Reactivar categoría',      'desc' => 'Haz clic en el botón de reactivar <i class="fa fa-undo"></i> en la columna Acciones. La categoría volverá a estar activa y sus productos asociados quedarán nuevamente disponibles en el sistema.'],
            ],
            'tips' => [
                'Al reactivar una categoría, los productos que tenía asociados también vuelven a estar vinculados a ella.',
                'Solo se pueden ver aquí las categorías eliminadas con el botón de eliminar del módulo <strong>Almacén → Categorías</strong>.',
            ],
        ],

        'reactiv_productos' => [
            'titulo' => 'Productos Eliminados',
            'icono'  => 'fa-cube',
            'color'  => '#0b5ed7',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver productos eliminados',  'desc' => 'La tabla lista todos los productos que fueron eliminados: código, descripción, categoría, fecha de eliminación y usuario que lo eliminó.'],
                ['icono' => 'fa-undo',         'titulo' => 'Reactivar producto',        'desc' => 'Haz clic en el botón de reactivar <i class="fa fa-undo"></i>. El producto volverá a estar disponible en el punto de venta, en el almacén y en los reportes.'],
            ],
            'tips' => [
                'Reactivar un producto no restaura su stock anterior — el stock quedará en 0 y deberás actualizarlo manualmente.',
                'Si eliminaste un producto por error durante una venta activa, reactívalo cuanto antes para evitar inconsistencias.',
            ],
        ],

        'reactiv_recetas' => [
            'titulo' => 'Recetas Eliminadas',
            'icono'  => 'fa-book',
            'color'  => '#b45309',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver recetas eliminadas',    'desc' => 'La tabla muestra las recetas eliminadas con su código, nombre, categoría, fecha de eliminación y el usuario que la eliminó.'],
                ['icono' => 'fa-undo',         'titulo' => 'Reactivar receta',          'desc' => 'Haz clic en el botón de reactivar <i class="fa fa-undo"></i>. La receta volverá a aparecer en el punto de venta del restaurant y podrá ser vendida nuevamente.'],
            ],
            'tips' => [
                'Al reactivar una receta, sus ingredientes y cantidades se mantienen tal como estaban al momento de eliminarla.',
                'Una receta reactivada no afecta el historial de ventas anteriores.',
            ],
        ],

        'reactiv_promociones' => [
            'titulo' => 'Promociones Eliminadas',
            'icono'  => 'fa-tag',
            'color'  => '#dc3545',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver promociones eliminadas','desc' => 'La tabla muestra las promociones que fueron eliminadas: código, nombre, categoría, fecha de eliminación y usuario responsable.'],
                ['icono' => 'fa-undo',         'titulo' => 'Reactivar promoción',       'desc' => 'Haz clic en el botón de reactivar <i class="fa fa-undo"></i>. La promoción volverá a estar disponible en el punto de venta con todos sus productos componentes.'],
            ],
            'tips' => [
                'Al reactivar una promoción, verifica que todos sus productos componentes sigan activos en el sistema.',
                'Una promoción reactivada mantiene su precio y configuración original.',
            ],
        ],

        'reactiv_proveedores' => [
            'titulo' => 'Proveedores Eliminados',
            'icono'  => 'fa-truck',
            'color'  => '#198754',
            'pasos'  => [
                ['icono' => 'fa-table',        'titulo' => 'Ver proveedores eliminados','desc' => 'La tabla muestra los proveedores eliminados con su RUT, razón social, región-comuna, fecha de eliminación y usuario que lo eliminó.'],
                ['icono' => 'fa-undo',         'titulo' => 'Reactivar proveedor',       'desc' => 'Haz clic en el botón de reactivar <i class="fa fa-undo"></i>. El proveedor volverá a estar disponible para asociarlo en nuevas compras.'],
            ],
            'tips' => [
                'Al reactivar un proveedor, su historial de compras previas se mantiene intacto.',
                'Solo reactiva proveedores que realmente vayan a volver a ser usados para evitar confusiones en el selector de compras.',
            ],
        ],

        'comandas' => [
            'titulo' => 'Atención de Mesas',
            'icono'  => 'fa-utensils',
            'color'  => '#d97706',
            'pasos'  => [
                ['icono' => 'fa-map',          'titulo' => 'Ver el plano de mesas', 'desc' => 'Usa el botón <strong>Ver plano de mesas</strong> para ver la distribución del local. Las mesas muestran su estado: <span style="color:#28a745">●</span> Libre, <span style="color:#e67e22">●</span> Ocupada, <span style="color:#c0392b">●</span> Pendiente de pago.'],
                ['icono' => 'fa-hand-pointer-o','titulo' => 'Abrir una mesa',       'desc' => 'Haz clic sobre cualquier mesa libre para abrirla. Se abrirá el panel de pedido donde podrás agregar productos, asignar un garzón y registrar el número de comensales.'],
                ['icono' => 'fa-plus',         'titulo' => 'Agregar productos',     'desc' => 'Busca el producto por nombre o código. Puedes ajustar la cantidad antes de agregarlo. Los productos se van acumulando en el pedido de la mesa.'],
                ['icono' => 'fa-print',        'titulo' => 'Imprimir comanda',      'desc' => 'Usa el botón <strong>Imprimir Comanda</strong> para enviar el pedido a cocina. Puedes imprimir varias veces si el cliente agrega más productos.'],
                ['icono' => 'fa-money',        'titulo' => 'Cobrar la mesa',        'desc' => 'Cuando el cliente pide la cuenta, usa <strong>Solicitar Cuenta</strong>. La mesa pasará a estado "Pendiente de pago". Luego ve al módulo <strong>Cerrar Comandas</strong> para procesar el cobro.'],
            ],
            'tips' => [
                'El contador de comensales en la parte superior muestra el total de personas en el restaurant en tiempo real.',
                'Puedes cambiar una comanda de mesa usando el botón <strong>Cambiar mesa</strong> sin perder los productos pedidos.',
                'El botón <strong>Actualizar</strong> refresca el estado de todas las mesas. Útil si hay varios meseros trabajando.',
                'Los productos marcados como receta verifican además el stock de los ingredientes antes de agregarlos.',
            ],
        ],
    ];

    $datos = $ayuda[$modulo] ?? null;
@endphp

@if($datos)
{{-- Botón flotante ? --}}
<button type="button"
    id="btnAyudaModulo"
    data-toggle="modal"
    data-target="#modalAyudaModulo"
    title="Ayuda de este módulo"
    style="
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1040;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: {{ $datos['color'] }};
        color: #fff;
        border: none;
        font-size: 20px;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0,0,0,0.25);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: transform 0.15s, box-shadow 0.15s;
    "
    onmouseover="this.style.transform='scale(1.12)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.32)'"
    onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 14px rgba(0,0,0,0.25)'"
>?</button>

{{-- Modal de ayuda --}}
<div class="modal fade" id="modalAyudaModulo" tabindex="-1" role="dialog" aria-labelledby="tituloModalAyuda" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 680px;">
        <div class="modal-content" style="border-radius:12px; overflow:hidden; border:none;">

            {{-- Header --}}
            <div class="modal-header" style="background:{{ $datos['color'] }}; padding:18px 22px; border:none;">
                <h4 class="modal-title" id="tituloModalAyuda" style="color:#fff; font-weight:700; font-size:18px; margin:0; display:flex; align-items:center; gap:10px;">
                    <i class="fa {{ $datos['icono'] }}"></i>
                    Ayuda — {{ $datos['titulo'] }}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                    style="color:#fff; opacity:0.85; font-size:22px; margin:0;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" style="padding:0; background:#f8fafc;">

                {{-- Pasos --}}
                <div style="padding:20px 22px 10px;">
                    <p style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; font-weight:600; margin-bottom:14px;">
                        ¿Cómo funciona?
                    </p>

                    @foreach($datos['pasos'] as $i => $paso)
                    <div style="display:flex; gap:14px; margin-bottom:14px; align-items:flex-start;">
                        <div style="
                            min-width:36px; height:36px;
                            border-radius:50%;
                            background:{{ $datos['color'] }}1a;
                            color:{{ $datos['color'] }};
                            display:flex; align-items:center; justify-content:center;
                            font-size:15px;
                            flex-shrink:0;
                        ">
                            <i class="fa {{ $paso['icono'] }}"></i>
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:13.5px; color:#1e293b; margin-bottom:3px;">
                                {{ $i + 1 }}. {{ $paso['titulo'] }}
                            </div>
                            <div style="font-size:12.5px; color:#475569; line-height:1.6;">
                                {!! $paso['desc'] !!}
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <div style="border-left:2px dashed #e2e8f0; height:8px; margin-left:17px; margin-bottom:14px;"></div>
                    @endif
                    @endforeach
                </div>

                {{-- Tips --}}
                <div style="background:#fff; border-top:1px solid #e2e8f0; padding:16px 22px 20px;">
                    <p style="font-size:12px; text-transform:uppercase; letter-spacing:1px; color:#94a3b8; font-weight:600; margin-bottom:12px;">
                        <i class="fa fa-lightbulb-o" style="color:#f59e0b;"></i> Tips útiles
                    </p>
                    <ul style="margin:0; padding-left:0; list-style:none;">
                        @foreach($datos['tips'] as $tip)
                        <li style="display:flex; gap:8px; font-size:12.5px; color:#475569; margin-bottom:8px; align-items:flex-start; line-height:1.55;">
                            <span style="color:#f59e0b; font-size:11px; margin-top:3px; flex-shrink:0;">●</span>
                            <span>{!! $tip !!}</span>
                        </li>
                        @endforeach
                        @if($modulo === 'config_datos_glob' && strtoupper(trim((string) \App\Models\Globales::where('nom_var','TIPO_NEGOCIO')->value('valor_var'))) === 'RESTAURANT')
                        <li style="display:flex; gap:8px; font-size:12.5px; color:#475569; margin-bottom:8px; align-items:flex-start; line-height:1.55;">
                            <span style="color:#f59e0b; font-size:11px; margin-top:3px; flex-shrink:0;">●</span>
                            <span><strong>PORCENTAJE_PROPINA</strong>: define el % de propina por defecto que se sugiere al cerrar una comanda o venta de restaurant.</span>
                        </li>
                        @endif
                    </ul>
                </div>

            </div>

            <div class="modal-footer" style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:12px 20px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:6px;">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
<script>
(function() {
    var btn   = document.getElementById('btnAyudaModulo');
    var modal = document.getElementById('modalAyudaModulo');
    // Limpiar instancias anteriores que pudieron quedar en body
    document.querySelectorAll('[data-ayuda-flotante]').forEach(function(el) { el.remove(); });
    if (btn)   { btn.setAttribute('data-ayuda-flotante','1');   document.body.appendChild(btn); }
    if (modal) { modal.setAttribute('data-ayuda-flotante','1'); document.body.appendChild(modal); }
})();
</script>
@endif
