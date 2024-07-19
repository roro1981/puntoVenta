<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;

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

Route::get('/usuarios/usuarios', [UsersController::class, 'getRoles'])->name('users.getRoles');
Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::post('/users/create', [UsersController::class, 'create']);
Route::get('/users/{user}/show', [UsersController::class, 'getUser']);
Route::put('/users/{user}/edit', [UsersController::class, 'update']);
Route::delete('/users/{user}/delete', [UsersController::class, 'delete']);
Route::get('/users/menus', [UsersController::class, 'getUserMenus'])->middleware('auth')->name('user.menus');

Route::post('/login', [UsersController::class, 'login'])->name('login');
Route::get('/dashboard', [UsersController::class, 'dashboard'])->middleware('auth')->name('dashboard');
Route::post('/logout', [UsersController::class, 'logout'])->name('logout');


