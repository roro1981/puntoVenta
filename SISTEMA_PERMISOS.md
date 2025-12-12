# Sistema de Permisos por Roles

## Descripción

Sistema de permisos flexible que permite asignar capacidades específicas a roles más allá de los menús. Permite controlar acciones específicas dentro del sistema como ver todos los cierres de caja, anular tickets, etc.

## Estructura de Base de Datos

### Tabla: `permisos_roles`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID autoincremental |
| role_id | bigint | FK a tabla roles |
| codigo_permiso | varchar(100) | Código único del permiso |
| descripcion | varchar(255) | Descripción del permiso |
| activo | boolean | Estado del permiso |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Fecha de actualización |

**Índices:**
- `unique_role_permiso`: índice único en (role_id, codigo_permiso)
- `codigo_permiso`: índice para búsquedas rápidas

## Permisos Disponibles

```php
// Definidos como constantes en App\Models\PermisoRole

PERMISO_CIERRES_CAJA          // Ver todos los cierres de caja
PERMISO_VER_TODAS_VENTAS      // Ver todas las ventas del sistema
PERMISO_ANULAR_TICKETS        // Anular tickets de cualquier usuario
PERMISO_ELIMINAR_PRODUCTOS    // Eliminar productos del sistema
PERMISO_MODIFICAR_PRECIOS     // Modificar precios de productos
PERMISO_GESTIONAR_USUARIOS    // Crear, editar y eliminar usuarios
PERMISO_VER_REPORTES          // Ver reportes completos del sistema
PERMISO_CONFIGURACION_GENERAL // Acceder a configuración general
```

## Uso en el Código

### 1. Verificar permiso en Controladores

```php
use App\Models\PermisoRole;

// Método 1: Usando el helper
public function verTodosCierres()
{
    if (!puedeVerTodosCierres()) {
        return response()->json(['error' => 'No tienes permiso'], 403);
    }
    
    // Código para ver todos los cierres...
}

// Método 2: Usando el modelo User
public function anularTicket()
{
    if (!auth()->user()->tienePermiso(PermisoRole::PERMISO_ANULAR_TICKETS)) {
        abort(403, 'No tienes permiso para anular tickets');
    }
    
    // Código para anular ticket...
}

// Método 3: Verificación directa
public function index()
{
    if (tienePermiso(PermisoRole::PERMISO_CIERRES_CAJA) || esAdmin()) {
        // Ver todos los cierres
        $cierres = Caja::all();
    } else {
        // Ver solo mis cierres
        $cierres = Caja::where('user_id', auth()->id())->get();
    }
    
    return view('cierres.index', compact('cierres'));
}
```

### 2. Verificar permiso en Vistas Blade

```php
@if(tienePermiso(\App\Models\PermisoRole::PERMISO_CIERRES_CAJA) || esAdmin())
    <button class="btn btn-primary">Ver Todos los Cierres</button>
@else
    <button class="btn btn-secondary">Ver Mis Cierres</button>
@endif

@if(puedeAnularTickets())
    <button class="btn btn-danger" onclick="anularTicket()">Anular Ticket</button>
@endif
```

### 3. Ejemplo Completo: Cierres de Caja

**Controlador: `app/Http/Controllers/CajaController.php`**

```php
public function obtenerCierres(Request $request)
{
    $userId = auth()->id();
    $query = Caja::with('user');
    
    // Si NO tiene permiso para ver todos, filtrar por usuario
    if (!puedeVerTodosCierres()) {
        $query->where('user_id', $userId);
    }
    
    $cierres = $query->orderBy('fecha_apertura', 'desc')->get();
    
    return response()->json($cierres);
}
```

**Vista: `resources/views/caja/index.blade.php`**

```php
<div class="card">
    <div class="card-header">
        <h3>
            @if(puedeVerTodosCierres())
                Todos los Cierres de Caja
            @else
                Mis Cierres de Caja
            @endif
        </h3>
    </div>
    <div class="card-body">
        <!-- DataTable aquí -->
    </div>
</div>
```

## Funciones Helper Disponibles

```php
// Verificar permiso específico
tienePermiso('PERMISO_CIERRES_CAJA')

// Verificar si es admin
esAdmin()

// Helpers específicos
puedeVerTodosCierres()
puedeVerTodasVentas()
puedeAnularTickets()
```

## Gestión de Permisos

### Asignar permiso a un rol (programáticamente)

```php
use App\Models\PermisoRole;
use App\Models\Role;

$role = Role::find(1);

PermisoRole::create([
    'role_id' => $role->id,
    'codigo_permiso' => PermisoRole::PERMISO_CIERRES_CAJA,
    'descripcion' => 'Ver todos los cierres de caja',
    'activo' => true
]);
```

### Verificar permisos de un rol

```php
$role = Role::find(1);

if ($role->tienePermiso(PermisoRole::PERMISO_CIERRES_CAJA)) {
    echo "El rol tiene el permiso";
}

// Obtener todos los permisos del rol
$permisos = $role->permisos()->where('activo', true)->get();
```

### Activar/Desactivar permiso

```php
$permiso = PermisoRole::where('role_id', 1)
    ->where('codigo_permiso', PermisoRole::PERMISO_CIERRES_CAJA)
    ->first();

$permiso->activo = false;
$permiso->save();
```

## Crear Nuevos Permisos

### 1. Agregar constante en el modelo

```php
// app/Models/PermisoRole.php

const PERMISO_MI_NUEVO_PERMISO = 'PERMISO_MI_NUEVO_PERMISO';
```

### 2. Agregar en el seeder (opcional)

```php
// database/seeders/PermisosRolesSeeder.php

[
    'codigo' => PermisoRole::PERMISO_MI_NUEVO_PERMISO,
    'descripcion' => 'Descripción del nuevo permiso'
]
```

### 3. Crear helper específico (opcional)

```php
// app/Helpers/PermisosHelper.php

if (!function_exists('puedoHacerAlgo')) {
    function puedoHacerAlgo()
    {
        return tienePermiso(\App\Models\PermisoRole::PERMISO_MI_NUEVO_PERMISO) || esAdmin();
    }
}
```

## Middleware (Opcional)

Puedes crear un middleware para proteger rutas completas:

```php
// app/Http/Middleware/VerificarPermiso.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarPermiso
{
    public function handle(Request $request, Closure $next, string $permiso)
    {
        if (!tienePermiso($permiso) && !esAdmin()) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }

        return $next($request);
    }
}

// Registrar en app/Http/Kernel.php
protected $middlewareAliases = [
    // ... otros middlewares
    'permiso' => \App\Http\Middleware\VerificarPermiso::class,
];

// Usar en rutas
Route::get('/cierres/todos', [CajaController::class, 'verTodos'])
    ->middleware(['auth', 'permiso:PERMISO_CIERRES_CAJA']);
```

## Ejecutar el Sistema

```bash
# 1. Ejecutar migración
php artisan migrate

# 2. Ejecutar seeder (asigna todos los permisos al rol Admin)
php artisan db:seed --class=PermisosRolesSeeder

# 3. Regenerar autoload para cargar los helpers
composer dump-autoload
```

## Ejemplo Práctico: Sistema de Cierres

**Antes (sin permisos):**
```php
// Todos los usuarios solo ven sus propios cierres
$cierres = Caja::where('user_id', auth()->id())->get();
```

**Después (con permisos):**
```php
// Los usuarios con permiso ven todos los cierres
if (puedeVerTodosCierres()) {
    $cierres = Caja::all(); // Todos
} else {
    $cierres = Caja::where('user_id', auth()->id())->get(); // Solo propios
}
```

## Notas Importantes

1. **Admin siempre tiene todos los permisos**: Los helpers verifican `esAdmin()` automáticamente
2. **Permisos se verifican por código**: No por ID, lo que facilita la portabilidad
3. **Índice único**: Previene duplicados de role_id + codigo_permiso
4. **Estado activo**: Permite desactivar permisos sin eliminarlos
5. **Extensible**: Fácil agregar nuevos permisos sin modificar la estructura

## Troubleshooting

**Error: "Class 'App\Models\PermisoRole' not found"**
```bash
composer dump-autoload
```

**Los helpers no funcionan**
```bash
# Verificar que composer.json tiene:
"files": [
    "app/Helpers/PermisosHelper.php"
]

# Luego:
composer dump-autoload
```

**El seeder no encuentra el rol Admin**
- Verificar el nombre exacto del rol en la tabla `roles`
- Ajustar el seeder con el nombre correcto
