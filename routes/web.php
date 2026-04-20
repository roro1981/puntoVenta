<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\PermisosController;
use App\Http\Controllers\MesasController;
use App\Http\Controllers\ComandasController;
use App\Http\Controllers\GarzonesController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('inicio');

//menu almacen
Route::get('/almacen/productos', [ProductosController::class, 'index']);
Route::get('/almacen/productosCarga', [ProductosController::class, 'listProducts']);
Route::get('/almacen/productos/plantilla-xlsx', [ProductosController::class, 'downloadProductsXlsxTemplate'])->name('productos.plantilla.xlsx');
Route::post('/almacen/productos/importar-xlsx', [ProductosController::class, 'importProductsXlsx'])->name('productos.importar.xlsx');
Route::get('/almacen/productos/{uuid}/editar', [ProductosController::class, 'showProduct'])->name('productos.editar');
Route::put('/almacen/productos/{uuid}/actualizar', [ProductosController::class, 'updateProduct'])->name('productos.actualizar');
Route::delete('/almacen/productos/{prod}/delete', [ProductosController::class, 'deleteProd']);
Route::get('/almacen/categorias', [ProductosController::class, 'indexCat']);
Route::get('/almacen/traeCategorias', [ProductosController::class, 'showCategories']);
Route::post('/almacen/createCat', [ProductosController::class, 'CreateCategory']);
Route::get('/almacen/{cat}/show', [ProductosController::class, 'getCategory']);
Route::put('/almacen/{cat}/edit', [ProductosController::class, 'updateCategory']);
Route::delete('/almacen/{cat}/delete', [ProductosController::class, 'deleteCat']);
Route::post('/almacen/upload-foto',  [ProductosController::class, 'uploadPhotoProduct']);
Route::post('/almacen/productos/create', [ProductosController::class, 'storeProduct']);
Route::get('/almacen/recetas_crear', [ProductosController::class, 'indexReceipesCreate']);
Route::get('/almacen/recetas', [ProductosController::class, 'indexReceipes']);
Route::get('/almacen/recetasCarga', [ProductosController::class, 'listReceipes']);
Route::post('/almacen/upload-foto-receta',  [ProductosController::class, 'uploadPhotoReceipe']);
Route::get('/almacen/searchInsumos', [ProductosController::class, 'searchInsumos']);
Route::get('/almacen/findInsumo', [ProductosController::class, 'findInsumo']);
Route::post('/almacen/crearReceta', [ProductosController::class, 'storeReceipe']);
Route::get('/almacen/recetas/{uuid}/edit', [ProductosController::class, 'editReceipe']);
Route::put('/almacen/recetas/{uuid}/update', [ProductosController::class, 'updateReceipe']);
Route::put('/almacen/recetas/{uuid}/delete', [ProductosController::class, 'deleteReceipe']);
Route::get('/almacen/promociones_crear', [ProductosController::class, 'indexPromoCreate']);
Route::get('/almacen/searchProductos', [ProductosController::class, 'searchProductos']);
Route::get('/almacen/findProducto', [ProductosController::class, 'findProducto']);
Route::post('/almacen/crearPromocion', [ProductosController::class, 'storePromo']);
Route::get('/almacen/promocionesCarga', [ProductosController::class, 'listPromos']);
Route::get('/almacen/promociones', [ProductosController::class, 'indexPromos']);
Route::get('/almacen/promociones/{uuid}/edit', [ProductosController::class, 'editPromos']);
Route::put('/almacen/promociones/{uuid}/update', [ProductosController::class, 'updatePromo']);
Route::put('/almacen/promociones/{uuid}/delete', [ProductosController::class, 'deletePromo']);
Route::get('/almacen/precio_segun_cant', [ProductosController::class, 'indexRange']);
Route::get('/almacen/productosRangoCarga', [ProductosController::class, 'listProductsRange']);
Route::post('/almacen/precio_segun_cant/create', [ProductosController::class, 'storeRange']);
Route::get('/almacen/precio_segun_cant/{uuid}/editar', [ProductosController::class, 'showProductRange']);
Route::put('/almacen/precio_segun_cant/{uuid}/actualizar', [ProductosController::class, 'updateRange']);
Route::delete('/almacen/precio_segun_cant/{uuid}/delete', [ProductosController::class, 'deleteRange']);

//menu re-activaciones
Route::get('/reactivaciones/cats_elim', [ProductosController::class, 'indexDeletedCategories']);
Route::get('/reactivaciones/cat_elim', [ProductosController::class, 'indexDeletedCategories']);
Route::get('/reactivaciones/traeCategoriasEliminadas', [ProductosController::class, 'listDeletedCategories']);
Route::put('/reactivaciones/{cat}/reactivar', [ProductosController::class, 'reactivateCategory']);
Route::get('/reactivaciones/prods_elim', [ProductosController::class, 'indexDeletedProducts']);
Route::get('/reactivaciones/traeProductosEliminados', [ProductosController::class, 'listDeletedProducts']);
Route::put('/reactivaciones/prod/{uuid}/reactivar', [ProductosController::class, 'reactivateProduct']);
Route::get('/reactivaciones/recetas_elim', [ProductosController::class, 'indexDeletedReceipes']);
Route::get('/reactivaciones/traeRecetasEliminadas', [ProductosController::class, 'listDeletedReceipes']);
Route::put('/reactivaciones/receta/{uuid}/reactivar', [ProductosController::class, 'reactivateReceipe']);
Route::get('/reactivaciones/promos_elim', [ProductosController::class, 'indexDeletedPromos']);
Route::get('/reactivaciones/traePromosEliminadas', [ProductosController::class, 'listDeletedPromos']);
Route::put('/reactivaciones/promo/{uuid}/reactivar', [ProductosController::class, 'reactivatePromo']);
Route::get('/reactivaciones/provs_elim', [ComprasController::class, 'indexDeletedProveedores']);
Route::get('/reactivaciones/traeProveedoresEliminados', [ComprasController::class, 'listDeletedProveedores']);
Route::put('/reactivaciones/prov/{uuid}/reactivar', [ComprasController::class, 'reactivateProveedor']);

//menu compras
Route::get('/compras/proveedores', [ComprasController::class, 'indexProveedores']);
Route::get('/compras/proveedores_list', [ComprasController::class, 'listProveedores']);
Route::get('/compras/facturas/detalle-doc', [ComprasController::class, 'traeDetalleDoc']);
Route::get('/compras/{region}/comunas', [ComprasController::class, 'getComunas']);
Route::post('/compras/createProveedor', [ComprasController::class, 'createProveedor']);
Route::get('/compras/{proveedor}/edit', [ComprasController::class, 'editProveedor']);
Route::put('/compras/{proveedor}/update', [ComprasController::class, 'updateProveedor']);
Route::delete('/compras/{uuid}/delete', [ComprasController::class, 'deleteProveedor']);
Route::get('/compras/ingresos', [ComprasController::class, 'indexIngresos']);
Route::get('/compras/pago-proveedor/{uuid}', [ComprasController::class, 'pagoProv']);
Route::get('/compras/productos-compra', [ComprasController::class, 'productosCompra']);
Route::get('/compras/productos-dashboard', [ComprasController::class, 'productosDesdeDashboard']);
Route::get('/compras/trae-docs', [ComprasController::class, 'traeDocs']);
Route::get('/compras/facturas-calendario', [ComprasController::class, 'facturasCalendario']);
Route::get('/compras/facturas/{estado}', [ComprasController::class, 'traeDocsPorEstado']);
Route::post('/compras/facturas/grabaFactura', [ComprasController::class, 'grabaCompra']);
Route::post('/compras/subir-foto-doc', [ComprasController::class, 'subirFotoDoc']);
Route::post('/compras/registrar-pago', [ComprasController::class, 'grabaPago']);
Route::get('/compras/detalle-pagos', [ComprasController::class, 'detallePagos']);
Route::post('/compras/boleta/grabar', [ComprasController::class, 'grabarBoleta']);
Route::get('/compras/ent_sal', [ComprasController::class, 'indexMovs']);
Route::get('/compras/searchProductosAll', [ComprasController::class, 'searchProductosAll']);
Route::get('/compras/cargaProdMov', [ComprasController::class, 'cargarMovimiento']);
Route::post('/compras/movimientos/grabar', [ComprasController::class, 'registrarMovimientos']);

//menu ventas
Route::get('/ventas/generar_ventas', [VentasController::class, 'indexVentas']);
Route::get('/ventas/cerrar_comandas', [ComandasController::class, 'indexCerrarComandas']);
Route::get('/ventas/cerrar_comandas/pendientes', [ComandasController::class, 'obtenerComandasPendientesPago']);
Route::get('/ventas/cerrar_comandas/info-caja', [ComandasController::class, 'obtenerInfoCajaCerrarComandas']);
Route::post('/ventas/cerrar_comandas/cerrar-caja', [ComandasController::class, 'cerrarCajaCerrarComandas']);
Route::post('/ventas/cerrar_comandas/cerrar/{comandaId}', [ComandasController::class, 'cerrarComanda']);
Route::post('/ventas/abrir-caja', [VentasController::class, 'abrirCaja']);
Route::post('/ventas/verificar-password', [VentasController::class, 'verificarPassword']);
Route::get('/ventas/info-caja', [VentasController::class, 'obtenerInfoCaja']);
Route::post('/ventas/cerrar-caja', [VentasController::class, 'cerrarCaja']);
Route::get('/ventas/cierres_caja', [VentasController::class, 'historialCierres']);
Route::get('/ventas/obtener-cierres', [VentasController::class, 'obtenerCierresDataTable']);
Route::get('/ventas/detalle-cierre/{id}', [VentasController::class, 'obtenerDetalleCierre']);
Route::get('/ventas/tickets_emitidos', [VentasController::class, 'historialTickets']);
Route::get('/ventas/obtener-tickets', [VentasController::class, 'obtenerTickets']);
Route::get('/ventas/detalle-ticket/{id}', [VentasController::class, 'obtenerDetalleTicket']);
Route::post('/ventas/anular-ticket/{id}', [VentasController::class, 'anularTicket']);
Route::get('/ventas/buscarProducto', [VentasController::class, 'searchProduct']);
Route::post('/ventas/verificar-stock', [VentasController::class, 'verificarStock']);
Route::post('/ventas/precio-por-cantidad', [VentasController::class, 'obtenerPrecioPorCantidad']);
Route::post('/ventas/guardar-borrador', [VentasController::class, 'guardarBorrador']);
Route::delete('/ventas/eliminar-borrador/{uuid_borrador}', [VentasController::class, 'eliminarBorrador']);
Route::get('/ventas/traer-borradores', [VentasController::class, 'traer_borradores']);
Route::get('/ventas/borrador/{uuid}/productos', [VentasController::class, 'productosPorUuid']);
Route::post('/ventas/procesar-venta', [VentasController::class, 'procesarVenta']);
Route::get('/ventas/ticket-pdf/{id}', [VentasController::class, 'generarTicketPDF']);
Route::get('/ventas/cierre-caja-pdf/{id}', [VentasController::class, 'generarTicketCierrePDF']);

// Preventa (ALMACEN_PREVENTA)
Route::get('/ventas/generar_preventa', [VentasController::class, 'indexPreventa']);
Route::post('/ventas/procesar-preventa', [VentasController::class, 'procesarPreventa']);
Route::get('/ventas/ticket-preventa-pdf/{id}', [VentasController::class, 'generarTicketPreventaPDF']);
Route::get('/ventas/preventas-pendientes', [VentasController::class, 'listarPreventasPendientes']);
Route::get('/ventas/cierre_preventa', [VentasController::class, 'indexCierrePreventa']);
Route::post('/ventas/preventa/buscar', [VentasController::class, 'buscarPreventaPorCodigo']);
Route::post('/ventas/preventa/cerrar', [VentasController::class, 'cerrarPreventa']);

//menu reportes
Route::get('/reportes/mov_productos', [ReportesController::class, 'indexMovimientos']);
Route::get('/reportes/trae_movimientos', [ReportesController::class, 'traeMovimientos']);
Route::get('/reportes/mov_productos/search', [ReportesController::class, 'searchProductosMovimientos']);
Route::get('/reportes/exportar-movimientos', [ReportesController::class, 'exportarMovimientos']);
Route::get('/reportes/vtas_fecha', [ReportesController::class, 'indexVentasFecha']);
Route::get('/reportes/vtas_fecha/data', [ReportesController::class, 'dataVentasFecha']);
Route::get('/reportes/vtas_fecha/exportar', [ReportesController::class, 'exportarVentasFecha']);
Route::get('/reportes/vtas_forma_pago', [ReportesController::class, 'indexFormasPago']);
Route::get('/reportes/vtas_forma_pago/data', [ReportesController::class, 'dataFormasPago']);
Route::get('/reportes/vtas_forma_pago/exportar', [ReportesController::class, 'exportarFormasPago']);
Route::get('/reportes/vtas_vendedor', [ReportesController::class, 'indexVendedor']);
Route::get('/reportes/vtas_vendedor/data', [ReportesController::class, 'dataVendedor']);
Route::get('/reportes/vtas_vendedor/exportar', [ReportesController::class, 'exportarVendedor']);
Route::get('/reportes/vtas_garzon', [ReportesController::class, 'indexGarzon']);
Route::get('/reportes/vtas_garzon/data', [ReportesController::class, 'dataGarzon']);
Route::get('/reportes/vtas_garzon/exportar_ventas', [ReportesController::class, 'exportarVentasGarzon']);
Route::get('/reportes/vtas_garzon/exportar_propinas', [ReportesController::class, 'exportarPropinasGarzon']);
Route::get('/reportes/vtas_mesa', [ReportesController::class, 'indexMesa']);
Route::get('/reportes/vtas_mesa/data', [ReportesController::class, 'dataMesa']);
Route::get('/reportes/vtas_mesa/exportar', [ReportesController::class, 'exportarMesa']);
Route::get('/reportes/prods_mas_vendidos', [ReportesController::class, 'indexProductosTop']);
Route::get('/reportes/prods_mas_vendidos/data', [ReportesController::class, 'dataProductosTop']);
Route::get('/reportes/prods_mas_vendidos/exportar', [ReportesController::class, 'exportarProductosTop']);
Route::get('/reportes/prods_rentables', [ReportesController::class, 'indexProductosRentables']);
Route::get('/reportes/prods_rentables/data', [ReportesController::class, 'dataProductosRentables']);
Route::get('/reportes/prods_rentables/exportar', [ReportesController::class, 'exportarProductosRentables']);
Route::get('/reportes/cat_mas_vendidas', [ReportesController::class, 'indexCategoriasVendidas']);
Route::get('/reportes/cat_mas_vendidas/data', [ReportesController::class, 'dataCategoriasVendidas']);
Route::get('/reportes/cat_mas_vendidas/exportar', [ReportesController::class, 'exportarCategoriasVendidas']);
Route::get('/reportes/inventario', [ReportesController::class, 'indexInventario']);
Route::get('/reportes/inventario/data', [ReportesController::class, 'dataInventario']);
Route::get('/reportes/inventario/exportar', [ReportesController::class, 'exportarInventario']);
Route::get('/reportes/hist_precio_prod', [ReportesController::class, 'indexHistorialPrecio']);
Route::get('/reportes/hist_precio_prod/data', [ReportesController::class, 'dataHistorialPrecio']);
Route::get('/reportes/hist_precio_prod/compras', [ReportesController::class, 'dataHistorialCompras']);
Route::get('/reportes/hist_precio_prod/exportar', [ReportesController::class, 'exportarHistorialPrecio']);
Route::get('/reportes/hist_precio_prod/search', [ReportesController::class, 'searchEntidadPrecio']);

//menu usuarios
Route::get('/usuarios/usuarios', [UsersController::class, 'getRoles'])->name('users.getRoles');
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::post('/users/create', [UsersController::class, 'create']);
Route::get('/users/{uuid}/show', [UsersController::class, 'getUser']);
Route::put('/users/{uuid}/edit', [UsersController::class, 'update']);
Route::delete('/users/{uuid}/delete', [UsersController::class, 'delete']);
Route::get('/users/menus', [UsersController::class, 'getUserMenus'])->middleware('auth')->name('user.menus');
Route::get('/usuarios/roles', [UsersController::class, 'indexRoles']);
Route::get('/roles', [UsersController::class, 'rolesTable']);
Route::get('roles/{id}/ver', [UsersController::class, 'ver'])->name('roles.ver');
Route::get('roles/users-associated/{id}/ver', [UsersController::class, 'ver_users'])->name('roles.ver_users');
Route::post('/roles/create', [UsersController::class, 'createRole']);
Route::delete('/roles/{id}/delete', [UsersController::class, 'deleteRole']);
Route::get('/usuarios/permisos', [UsersController::class, 'getRolesPermisos']);
Route::post('/permisos/get-menus', [UsersController::class, 'getMenus'])->name('get-menus');
Route::post('/permisos/save', [UsersController::class, 'savePermissions']);

Route::get('/usuarios/permisos-roles', [PermisosController::class, 'index']);
Route::get('/permisos/disponibles', [PermisosController::class, 'permisosDisponibles']);
Route::post('/permisos/asignar-multiples', [PermisosController::class, 'asignarMultiples']);
Route::get('/permisos/role/{roleId}', [PermisosController::class, 'permisosPorRole']);

//menu restaurant
Route::get('/configuracion/restaurant/config-mesas', [MesasController::class, 'index']);
Route::get('/configuracion/restaurant/config-garzones', [GarzonesController::class, 'index']);
Route::get('/restaurant/garzones/obtener', [GarzonesController::class, 'obtener']);
Route::post('/restaurant/garzones/crear', [GarzonesController::class, 'crear']);
Route::put('/restaurant/garzones/actualizar/{id}', [GarzonesController::class, 'actualizar']);
Route::delete('/restaurant/garzones/eliminar/{id}', [GarzonesController::class, 'eliminar']);
Route::get('/restaurant/mesas/obtener', [MesasController::class, 'obtener']);
Route::post('/restaurant/mesas/crear', [MesasController::class, 'crear']);
Route::put('/restaurant/mesas/actualizar/{id}', [MesasController::class, 'actualizar']);
Route::delete('/restaurant/mesas/eliminar/{id}', [MesasController::class, 'eliminar']);

// Comandas - Atención de mesas
Route::get('/ventas/generar_comandas', [ComandasController::class, 'index']);
Route::get('/restaurant/comandas/obtener-mesas', [ComandasController::class, 'obtenerMesas']);
Route::get('/restaurant/comandas/ver/{mesaId}', [ComandasController::class, 'verComanda']);
Route::put('/restaurant/comandas/actualizar-comensales/{comandaId}', [ComandasController::class, 'actualizarComensales']);
Route::get('/restaurant/comandas/obtener-productos', [ComandasController::class, 'obtenerProductos']);
Route::post('/restaurant/comandas/verificar-stock-receta', [ComandasController::class, 'verificarStockReceta']);
Route::get('/restaurant/comandas/obtener-garzones', [ComandasController::class, 'obtenerGarzones']);
Route::post('/restaurant/comandas/crear', [ComandasController::class, 'crearComanda']);
Route::put('/restaurant/comandas/actualizar/{comandaId}', [ComandasController::class, 'actualizarComanda']);
Route::put('/restaurant/comandas/cambiar-mesa/{comandaId}', [ComandasController::class, 'cambiarMesaComanda']);
Route::put('/restaurant/comandas/solicitar-cuenta/{comandaId}', [ComandasController::class, 'solicitarCuenta']);
Route::post('/restaurant/comandas/sincronizar-productos/{comandaId}', [ComandasController::class, 'sincronizarProductos']);
Route::post('/restaurant/comandas/agregar-producto', [ComandasController::class, 'agregarProducto']);
Route::put('/restaurant/comandas/actualizar-producto/{detalleId}', [ComandasController::class, 'actualizarProducto']);
Route::delete('/restaurant/comandas/eliminar-producto/{detalleId}', [ComandasController::class, 'eliminarProducto']);
Route::get('/restaurant/comandas/imprimir/{comandaId}', [ComandasController::class, 'imprimirComanda']);
Route::get('/restaurant/comandas/ticket-pago/{comandaId}/{ventaId}', [ComandasController::class, 'imprimirTicketPagoComanda']);
Route::get('/restaurant/comandas/layout-json', [ComandasController::class, 'obtenerLayoutMesas']);
Route::post('/restaurant/comandas/layout-json', [ComandasController::class, 'guardarLayoutMesas']);

//menu configuracion
Route::get('/configuracion/datos_corp', [ConfigurationController::class, 'index']);
Route::post('/configuracion/upload-logo',  [ConfigurationController::class, 'uploadLogo']);
Route::post('/configuracion/update-corporate-data',  [ConfigurationController::class, 'updateCorporateData']);
Route::get('/configuracion/datos_glob', [ConfigurationController::class, 'indexGlobales']);
Route::get('/configuracion/datos_globales', [ConfigurationController::class, 'globalesTable']);
Route::put('/configuracion/update-global/{id}', [ConfigurationController::class, 'updateGlobal']);
Route::get('/configuracion/impuestos', [ConfigurationController::class, 'indexImpuestos']);
Route::get('/configuracion/impuestos-table', [ConfigurationController::class, 'impuestosTable']);
Route::put('/configuracion/update-impuesto/{id}', [ConfigurationController::class, 'updateImpuesto']);

Route::post('/login', [UsersController::class, 'login'])->name('login');
Route::get('/dashboard', [UsersController::class, 'dashboard'])->middleware('auth')->name('dashboard');
Route::get('/dashboard/preventas-pendientes', [UsersController::class, 'preventasPendientesDashboard'])->middleware('auth')->name('dashboard.preventas-pendientes');
Route::post('/logout', [UsersController::class, 'logout'])->name('logout');
