<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Psy\Readline\Userland;

class UsersController extends Controller
{

    public function login(UserRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
           
            Carbon::setLocale('es');
            $date = Carbon::now();
            $fechaEnPalabras = $date->isoFormat('dddd, D MMMM YYYY');

            // Guardar datos en la sesión
            session(['fechaEnPalabras' => $fechaEnPalabras]);
            $horaActual = Carbon::now()->format('H:i');
            

            return response()->json([
                'authenticated' => true,
                'redirectTo' => route('dashboard'),
                'message' => '<strong>Inicio de sesión exitoso!</strong> Bienvenido, ' . $user->name . '.',
            ]);
       
        }

        return response()->json([
            'authenticated' => false,
            'message' => 'Las credenciales no coinciden'
        ], 422);
    }
    
    public function dashboard()
    {
        $fechaEnPalabras = session('fechaEnPalabras', '');
        $horaActual = session('horaActual', '');
     
        return view('menu', compact('fechaEnPalabras', 'horaActual'));
    }
    public function getUserMenus()
    {
        $user = Auth::user();
        $menus = $this->getMenusForUser($user);

        return response()->json($menus);
    }
    private function getMenusForUser($user)
    {
        $menus = [];
        $role = $user->role;

        foreach ($role->submenus as $submenu) {
            $menuId = $submenu->menu->id;

            if (!isset($menus[$menuId])) {
                $menus[$menuId] = [
                    'id' => $submenu->menu->id,
                    'name' => $submenu->menu->menu_name,
                    'route' => $submenu->menu->menu_route,
                    'fa' => $submenu->menu->menu_fa,
                    'submenus' => []
                ];
            }

            $menus[$menuId]['submenus'][] = [
                'id' => $submenu->id,
                'name' => $submenu->submenu_name,
                'route' => $submenu->submenu_route,
            ];
        }

        return array_values($menus);
    }

    public function create(UserRequest $request)
    {
        $validated = $request->validated();
        
        try { 
            $user = User::storeUser($validated);
            
            $response = response()->json([
                'error' => 200,
                'essage' => "Usuario creado correctamente"
            ], 200);    

        } catch (\Exception $e) {
            Log::error("Error al crear usuario ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function update(UserRequest $request, $id)
    {
        $validated = $request->validated();
        try{
            $user = User::findOrFail($id);
            $user->updateUser($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Usuario modificado correctamente ".$user
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al modificar usuario ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'essage' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function index()
    {
        $users = User::select('users.id', 'users.name', 'users.name_complete', 'users.role_id', 'roles.role_name', 'users.created_at', 'users.updated_at')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.estado', 1)
            ->get()
            ->map(function ($user) {
                $user->created_at = date('d/m/Y H:i:s', strtotime($user->created_at));
                $user->updated_at = $user->updated_at ? date('d/m/Y H:i:s', strtotime($user->updated_at)) : 'Aún no tiene modificaciones';
                $user->actions = '<a href="" class="btn btn-sm btn-primary editar" data-rol="'.$user->role_id.'" data-target="#editUserModal" data-user="'.$user->id.'" data-toggle="modal" title="Editar usuario '.$user->name.'"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Eliminar usuario '.$user->name.'"><i class="fa fa-trash"></i></a>';
                                /*'.route('users.edit', $user->id).'
                                '.route('users.destroy', $user->id).'*/
                                return $user;
            });

        $response = [
            'data' => $users,
            'recordsTotal' => $users->count(),
            'recordsFiltered' => $users->count()
        ];

        return response()->json($response);
    }
    public function getRoles()
    {
        $roles = Role::all();
        $user = Auth::user();
        return view('users.principal', compact('roles', 'user'));
    }
    public function getUser($id)
    {
    $user = User::find($id);
    return response()->json($user);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('inicio'));
    }
}    
