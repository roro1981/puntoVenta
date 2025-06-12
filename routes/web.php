<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ComprasController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ConfigurationController;



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
Route::get('/ventas/buscarProducto', [VentasController::class, 'searchProduct']);

//menu reportes
Route::get('/reportes/mov_productos', [ReportesController::class, 'indexMovimientos']);
Route::get('/reportes/trae_movimientos', [ReportesController::class, 'traeMovimientos']);
Route::get('/reportes/exportar-movimientos', [ReportesController::class, 'exportarMovimientos']);

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
Route::get('/usuarios/permisos', [UsersController::class, 'getRolesPermisos']);
Route::post('/permisos/get-menus', [UsersController::class, 'getMenus'])->name('get-menus');
Route::post('/permisos/save', [UsersController::class, 'savePermissions']);

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
Route::post('/logout', [UsersController::class, 'logout'])->name('logout');
