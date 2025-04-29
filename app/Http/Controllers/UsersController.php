<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Menu;
use App\Models\Role;
use App\Models\Submenu;
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
            User::storeUser($validated);

            $response = response()->json([
                'error' => 200,
                'message' => "Usuario creado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al crear usuario " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function update(UserRequest $request, $uuid)
    {
        $request->validated();
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();
            $user->updateUser($request);

            $response = response()->json([
                'error' => 200,
                'message' => "Usuario modificado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al modificar usuario " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function delete($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->firstOrFail();
            $superAdminRoleId = Role::where('role_name', 'SuperAdministrador')->first()->id;

            if ($user->role_id == $superAdminRoleId) {
                $superAdminCount = User::where('role_id', $superAdminRoleId)
                    ->where('name_complete', '<>', 'Rodrigo Panes')
                    ->where('estado', '=', 1)
                    ->count();

                if ($superAdminCount <= 1) {
                    return response()->json([
                        'error' => 403,
                        'message' => "No se puede eliminar el último superadministrador, debe existir al menos 1"
                    ], 403);
                }
            }

            $user->deleteUser();

            $response = response()->json([
                'error' => 200,
                'message' => "Usuario eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar usuario " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function index()
    {
        $users = User::select('users.uuid', 'users.id', 'users.name', 'users.name_complete', 'users.role_id', 'roles.role_name', 'users.created_at', 'users.updated_at')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.estado', 1)
            ->where('users.name_complete', '<>', 'Rodrigo Panes')
            ->get()
            ->map(function ($user) {
                $user->created_at = date('d/m/Y H:i:s', strtotime($user->created_at));
                $user->updated_at = $user->updated_at ? date('d/m/Y H:i:s', strtotime($user->updated_at)) : 'Aún no tiene modificaciones';
                $user->actions = '<a href="" class="btn btn-sm btn-primary editar" data-rol="' . $user->role_id . '" data-target="#editUserModal" data-uuid="' . $user->uuid . '" data-toggle="modal" title="Editar usuario ' . $user->name . '"><i class="fa fa-edit"></i></a>
                                <a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-uuid="' . $user->uuid . '" data-nameuser="' . $user->name . '" title="Eliminar usuario ' . $user->name . '"><i class="fa fa-trash"></i></a>';
                return $user;
            });

        $response = [
            'data' => $users,
            'recordsTotal' => $users->count(),
            'recordsFiltered' => $users->count()
        ];

        return response()->json($response);
    }
    public function createRole(Request $request)
    {
        try {
            $validated = $request->validate([
                'role_name' => 'required|string|max:50'
            ]);
            $roleName = ucfirst(strtolower($validated['role_name']));
            $role = Role::create([
                'role_name' => $roleName,
                'created_at' => now()
            ]);

            $role->save();
            $response = response()->json([
                'error' => 200,
                'message' => "Rol creado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al crear rol " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function rolesTable()
    {
        $roles = Role::select('roles.id', 'roles.role_name', 'roles.created_at', 'roles.updated_at')
            ->where('roles.role_name', '<>', 'SuperAdministrador')
            ->get()
            ->map(function ($roles) {
                $roles->asociados = '<button type="button" data-id="' . $roles->id . '" class="btn btn-primary ver-btn">
                                            <i class="fa fa-eye"></i> Ver
                                        </button>';
                $roles->usuarios = '<button type="button" data-rol="' . $roles->role_name . '" data-id="' . $roles->id . '" class="btn btn-success ver-btn_users">
                                            <i class="fa fa-eye"></i> Ver
                                        </button>';
                $roles->created_at = date('d/m/Y H:i:s', strtotime($roles->created_at));
                $roles->updated_at = $roles->updated_at ? date('d/m/Y H:i:s', strtotime($roles->updated_at)) : 'Aún no tiene modificaciones';
                $roles->actions = '<a href="" class="btn btn-sm btn-danger eliminar" data-toggle="tooltip" data-rolid="' . $roles->id . '" data-namerol="' . $roles->role_name . '" title="Eliminar rol ' . $roles->role_name . '"><i class="fa fa-trash"></i></a>';
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

    public function ver_users($id)
    {
        $usuarios = User::where('role_id', $id)
            ->with('role')
            ->get();

        $usersList = $usuarios->map(function ($user) {
            return [
                'user_name' => $user->name,
                'user_name_complete' => $user->name_complete,
            ];
        });

        return response()->json([
            'usuarios' => $usersList
        ]);
    }

    public function getUser($uuid)
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        return response()->json($user);
    }
    public function getRolesPermisos()
    {
        $roles = Role::all();
        return view('users.permisos', compact('roles'));
    }
    public function getMenus(Request $request)
    {
        $roleId = $request->role_id;
        $submenus = Submenu::with('menu')
            ->get()
            ->groupBy('menu_id');

        $selectedSubmenus = Role::find($roleId)->submenus->pluck('id')->toArray();

        $submenusFormatted = $submenus->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'submenu_name' => $item->submenu_name,
                    'menu_name' => $item->menu->menu_name, // Agregar el nombre del menú
                ];
            });
        });

        return response()->json([
            'submenus' => $submenusFormatted,
            'selectedSubmenus' => $selectedSubmenus
        ]);
    }

    public function savePermissions(Request $request)
    {
        $roleId = $request->input('role_id');
        $selectedSubmenus = $request->input('selected_submenus');

        $role = Role::find($roleId);

        if ($role) {

            $role->submenus()->detach();

            $role->submenus()->attach($selectedSubmenus);

            return response()->json(['success' => true, 'message' => 'Permisos guardados con éxito']);
        } else {
            return response()->json(['success' => false, 'message' => 'Rol no encontrado'], 404);
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('inicio'));
    }
}
