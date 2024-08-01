<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Psy\Readline\Userland;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\throwException;

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
                'message' => "Usuario creado correctamente"
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
                'message' => "Usuario modificado correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al modificar usuario ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function delete($id)
    {
        try{
            $user = User::findOrFail($id);
            $user->deleteUser();

            $response = response()->json([
                'error' => 200,
                'message' => "Usuario eliminado correctamente"
            ], 200); 
        }catch (\Exception $e){
            Log::error("Error al eliminar usuario ". $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function index()
    {
        $users = User::select('users.id', 'users.name', 'users.name_complete', 'users.role_id', 'roles.role_name', 'users.created_at', 'users.updated_at')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.estado', 1)
            ->where('users.name_complete', '<>', 'Rodrigo Panes')
            ->get()
            ->map(function ($user) {
                $user->created_at = date('d/m/Y H:i:s', strtotime($user->created_at));
                $user->updated_at = $user->updated_at ? date('d/m/Y H:i:s', strtotime($user->updated_at)) : 'Aún no tiene modificaciones';
                $user->actions = '<a href="" class="btn btn-sm btn-primary editar" data-rol="'.$user->role_id.'" data-target="#editUserModal" data-user="'.$user->id.'" data-toggle="modal" title="Editar usuario '.$user->name.'"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-user="'.$user->id.'" data-nameuser="'.$user->name.'" title="Eliminar usuario '.$user->name.'"><i class="fa fa-trash"></i></a>';
                return $user;
            });

        $response = [
            'data' => $users,
            'recordsTotal' => $users->count(),
            'recordsFiltered' => $users->count()
        ];

        return response()->json($response);
    }
    public function rolesTable()
    {
        $roles = Role::select('roles.id', 'roles.role_name', 'roles.created_at', 'roles.updated_at')
            ->get()
            ->map(function ($roles) {
                $roles->asociados = '<button type="button" data-id="'.$roles->id.'" class="btn btn-primary ver-btn">
                                            <i class="fa fa-eye"></i> Ver
                                        </button>';
                $roles->created_at = date('d/m/Y H:i:s', strtotime($roles->created_at));
                $roles->updated_at = $roles->updated_at ? date('d/m/Y H:i:s', strtotime($roles->updated_at)) : 'Aún no tiene modificaciones';
                $roles->actions = '<a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-rolid="'.$roles->id.'" data-namerol="'.$roles->role_name.'" title="Eliminar rol '.$roles->role_name.'"><i class="fa fa-trash"></i></a>';
                return $roles;
            });

        $response = [
            'data' => $roles,
            'recordsTotal' => $roles->count(),
            'recordsFiltered' => $roles->count()
        ];
        return response()->json($response);
    }
    public function getRoles()
    {
        $roles = Role::all();
        $user = Auth::user();
        return view('users.principal', compact('roles', 'user'));
    }
    public function indexRoles()
    {
        return view('users.roles');
    }

    public function ver($id)
    {
        $rol = Role::findOrFail($id);
    
        $menus = Menu::with(['submenus' => function ($query) use ($id) {
            $query->whereHas('menuRoles', function ($query) use ($id) {
                $query->where('role_id', $id);
            });
        }])
        ->whereHas('submenus.menuRoles', function ($query) use ($id) {
            $query->where('role_id', $id);
        })
        ->orderBy('id', 'asc')
        ->get();

        return response()->json([
            'role_name' => $rol->role_name, 
            'menus' => $menus
        ]);
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
