<?php

use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;


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
Route::get('/almacen/productos/{id}/editar', [ProductosController::class, 'showProduct'])->name('productos.editar');
Route::put('/almacen/productos/{producto}/actualizar', [ProductosController::class, 'updateProduct'])->name('productos.actualizar');
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

//menu usuarios
Route::get('/usuarios/usuarios', [UsersController::class, 'getRoles'])->name('users.getRoles');
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::post('/users/create', [UsersController::class, 'create']);
Route::get('/users/{user}/show', [UsersController::class, 'getUser']);
Route::put('/users/{user}/edit', [UsersController::class, 'update']);
Route::delete('/users/{user}/delete', [UsersController::class, 'delete']);
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
