<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
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


