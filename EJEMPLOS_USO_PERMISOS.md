# Ejemplos de Implementación del Sistema de Permisos

## 1. Ejemplo Implementado: Cierres de Caja

### Controlador: VentasController.php

**ANTES:**
```php
public function obtenerCierresDataTable(Request $request)
{
    $user = Auth::user();
    $roleName = $user->role->role_name ?? '';
    
    // Determinar si es admin o superadmin
    $esAdmin = in_array(strtolower($roleName), ['administrador', 'superadministrador', 'admin', 'superadmin']);
    
    $query = Caja::with(['usuario'])
        ->where('estado', 'cerrada')
        ->orderBy('fecha_cierre', 'desc');
    
    // Si no es admin, filtrar solo sus cierres
    if (!$esAdmin) {
        $query->where('user_id', $user->id);
    }
    
    $cierres = $query->get();
    // ...
}
```

**DESPUÉS:**
```php
public function obtenerCierresDataTable(Request $request)
{
    $user = Auth::user();
    
    $query = Caja::with(['usuario'])
        ->where('estado', 'cerrada')
        ->orderBy('fecha_cierre', 'desc');
    
    // Si NO tiene permiso para ver todos los cierres, filtrar solo los suyos
    if (!puedeVerTodosCierres()) {
        $query->where('user_id', $user->id);
    }
    
    $cierres = $query->get();
    // ...
}
```

**VENTAJAS:**
- Código más limpio y legible
- No depende de nombres hardcodeados de roles
- Fácil de gestionar desde base de datos
- Se puede dar permiso a cualquier rol sin modificar código

---

## 2. Ejemplo: Ver Todas las Ventas

### VentasController.php - Método obtenerTickets

```php
public function obtenerTickets(Request $request)
{
    $userId = auth()->id();
    
    $query = Venta::with(['detalles', 'user'])
        ->orderBy('created_at', 'desc');
    
    // Si NO tiene permiso, solo ver sus propias ventas
    if (!puedeVerTodasVentas()) {
        $query->where('user_id', $userId);
    }
    
    $ventas = $query->get();
    
    return response()->json($ventas);
}
```

### Vista Blade

```blade
<div class="card-header">
    <h3>
        @if(puedeVerTodasVentas())
            <i class="fas fa-shopping-cart"></i> Todas las Ventas del Sistema
        @else
            <i class="fas fa-user"></i> Mis Ventas
        @endif
    </h3>
</div>
```

---

## 3. Ejemplo: Anular Tickets

### VentasController.php - Método anularTicket

**ANTES:**
```php
public function anularTicket(Request $request, $ventaId)
{
    $user = auth()->user();
    $venta = Venta::findOrFail($ventaId);
    
    // Verificar permisos
    if ($venta->user_id !== $user->id) {
        $roleName = strtolower($user->role->role_name ?? '');
        if (!in_array($roleName, ['admin', 'administrador'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }
    }
    
    // Lógica de anulación...
}
```

**DESPUÉS:**
```php
public function anularTicket(Request $request, $ventaId)
{
    $user = auth()->user();
    $venta = Venta::findOrFail($ventaId);
    
    // Verificar permisos
    if ($venta->user_id !== $user->id && !puedeAnularTickets()) {
        return response()->json(['error' => 'No tienes permiso para anular tickets de otros usuarios'], 403);
    }
    
    // Lógica de anulación...
}
```

### Vista Blade - Botón de Anulación

```blade
@if($venta->estado !== 'anulada')
    @if($venta->user_id === auth()->id() || puedeAnularTickets())
        <button class="btn btn-danger anular-ticket" data-id="{{ $venta->id }}">
            <i class="fas fa-ban"></i> Anular
        </button>
    @endif
@endif
```

---

## 4. Ejemplo: Gestión de Productos

### ProductosController.php

```php
use App\Models\PermisoRole;

class ProductosController extends Controller
{
    public function eliminar($id)
    {
        // Verificar permiso para eliminar productos
        if (!tienePermiso(PermisoRole::PERMISO_ELIMINAR_PRODUCTOS)) {
            return response()->json([
                'error' => 'No tienes permiso para eliminar productos'
            ], 403);
        }
        
        $producto = Producto::findOrFail($id);
        $producto->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function actualizarPrecio(Request $request, $id)
    {
        // Verificar permiso para modificar precios
        if (!tienePermiso(PermisoRole::PERMISO_MODIFICAR_PRECIOS)) {
            return response()->json([
                'error' => 'No tienes permiso para modificar precios'
            ], 403);
        }
        
        $producto = Producto::findOrFail($id);
        $producto->precio = $request->precio;
        $producto->save();
        
        return response()->json(['success' => true]);
    }
}
```

### Vista Blade - Botones Condicionales

```blade
<div class="btn-group">
    <button class="btn btn-primary btn-sm editar-producto">
        <i class="fas fa-edit"></i> Editar
    </button>
    
    @if(tienePermiso(\App\Models\PermisoRole::PERMISO_MODIFICAR_PRECIOS))
        <button class="btn btn-warning btn-sm editar-precio">
            <i class="fas fa-dollar-sign"></i> Cambiar Precio
        </button>
    @endif
    
    @if(tienePermiso(\App\Models\PermisoRole::PERMISO_ELIMINAR_PRODUCTOS))
        <button class="btn btn-danger btn-sm eliminar-producto">
            <i class="fas fa-trash"></i> Eliminar
        </button>
    @endif
</div>
```

---

## 5. Ejemplo: Reportes

### ReportesController.php

```php
public function reporteCompleto(Request $request)
{
    // Verificar permiso para ver reportes
    if (!tienePermiso(PermisoRole::PERMISO_VER_REPORTES)) {
        abort(403, 'No tienes acceso a los reportes del sistema');
    }
    
    // Generar reporte completo...
    $datos = [
        'ventas_totales' => Venta::sum('total'),
        'productos_vendidos' => DetalleVenta::sum('cantidad'),
        'usuarios_activos' => User::where('active', true)->count(),
        // etc...
    ];
    
    return view('reportes.completo', $datos);
}

public function exportarExcel(Request $request)
{
    if (!tienePermiso(PermisoRole::PERMISO_VER_REPORTES)) {
        return redirect()->back()->with('error', 'No tienes permiso para exportar reportes');
    }
    
    return Excel::download(new VentasExport, 'ventas.xlsx');
}
```

### Vista del Menú

```blade
@if(tienePermiso(\App\Models\PermisoRole::PERMISO_VER_REPORTES))
    <li class="nav-item">
        <a href="{{ route('reportes.index') }}" class="nav-link">
            <i class="fas fa-chart-bar"></i>
            <p>Reportes</p>
        </a>
    </li>
@endif
```

---

## 6. Ejemplo: Configuración del Sistema

### ConfiguracionController.php

```php
public function index()
{
    // Solo usuarios con permiso pueden acceder
    if (!tienePermiso(PermisoRole::PERMISO_CONFIGURACION_GENERAL)) {
        abort(403, 'No tienes acceso a la configuración del sistema');
    }
    
    $configuracion = Globales::first();
    return view('configuracion.index', compact('configuracion'));
}

public function actualizar(Request $request)
{
    if (!tienePermiso(PermisoRole::PERMISO_CONFIGURACION_GENERAL)) {
        return redirect()->back()->with('error', 'No autorizado');
    }
    
    // Actualizar configuración...
    Globales::updateOrCreate(
        ['id' => 1],
        $request->only(['nombre_empresa', 'rut', 'direccion', 'telefono'])
    );
    
    return redirect()->back()->with('success', 'Configuración actualizada');
}
```

---

## 7. Ejemplo: Gestión de Usuarios

### UsuariosController.php

```php
public function index()
{
    // Verificar permiso para gestionar usuarios
    if (!tienePermiso(PermisoRole::PERMISO_GESTIONAR_USUARIOS)) {
        abort(403, 'No tienes permiso para gestionar usuarios');
    }
    
    $usuarios = User::with('role')->paginate(20);
    return view('usuarios.index', compact('usuarios'));
}

public function crear(Request $request)
{
    if (!tienePermiso(PermisoRole::PERMISO_GESTIONAR_USUARIOS)) {
        return response()->json(['error' => 'No autorizado'], 403);
    }
    
    $usuario = User::create($request->all());
    return response()->json(['success' => true, 'usuario' => $usuario]);
}

public function eliminar($id)
{
    if (!tienePermiso(PermisoRole::PERMISO_GESTIONAR_USUARIOS)) {
        return response()->json(['error' => 'No autorizado'], 403);
    }
    
    $usuario = User::findOrFail($id);
    $usuario->delete();
    
    return response()->json(['success' => true]);
}
```

---

## 8. Ejemplo: Middleware para Rutas Completas

### Crear Middleware

```bash
php artisan make:middleware VerificarPermiso
```

### app/Http/Middleware/VerificarPermiso.php

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarPermiso
{
    public function handle(Request $request, Closure $next, string $permiso)
    {
        if (!tienePermiso($permiso) && !esAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            abort(403, 'No tienes permiso para acceder a esta sección');
        }

        return $next($request);
    }
}
```

### Registrar en app/Http/Kernel.php

```php
protected $middlewareAliases = [
    // ... otros middlewares
    'permiso' => \App\Http\Middleware\VerificarPermiso::class,
];
```

### Usar en routes/web.php

```php
use App\Models\PermisoRole;

// Ruta protegida por permiso
Route::get('/cierres/todos', [VentasController::class, 'obtenerCierresDataTable'])
    ->middleware(['auth', 'permiso:' . PermisoRole::PERMISO_CIERRES_CAJA]);

// Grupo de rutas protegidas
Route::middleware(['auth', 'permiso:' . PermisoRole::PERMISO_VER_REPORTES])
    ->prefix('reportes')
    ->group(function () {
        Route::get('/', [ReportesController::class, 'index']);
        Route::get('/ventas', [ReportesController::class, 'ventas']);
        Route::get('/productos', [ReportesController::class, 'productos']);
    });

// Gestión de usuarios
Route::middleware(['auth', 'permiso:' . PermisoRole::PERMISO_GESTIONAR_USUARIOS])
    ->prefix('usuarios')
    ->group(function () {
        Route::get('/', [UsuariosController::class, 'index']);
        Route::post('/', [UsuariosController::class, 'crear']);
        Route::put('/{id}', [UsuariosController::class, 'actualizar']);
        Route::delete('/{id}', [UsuariosController::class, 'eliminar']);
    });
```

---

## 9. Ejemplo: JavaScript - Mostrar/Ocultar Elementos

### En Blade

```blade
@push('scripts')
<script>
    // Pasar permisos a JavaScript
    window.permisos = {
        puedeEliminar: {{ tienePermiso(\App\Models\PermisoRole::PERMISO_ELIMINAR_PRODUCTOS) ? 'true' : 'false' }},
        puedeModificarPrecios: {{ tienePermiso(\App\Models\PermisoRole::PERMISO_MODIFICAR_PRECIOS) ? 'true' : 'false' }},
        esAdmin: {{ esAdmin() ? 'true' : 'false' }}
    };
</script>
@endpush
```

### En archivo JS

```javascript
// Mostrar botones según permisos
function renderizarBotones(producto) {
    let botones = `
        <button class="btn btn-sm btn-primary" onclick="editar(${producto.id})">
            <i class="fas fa-edit"></i>
        </button>
    `;
    
    if (window.permisos.puedeModificarPrecios) {
        botones += `
            <button class="btn btn-sm btn-warning" onclick="cambiarPrecio(${producto.id})">
                <i class="fas fa-dollar-sign"></i>
            </button>
        `;
    }
    
    if (window.permisos.puedeEliminar) {
        botones += `
            <button class="btn btn-sm btn-danger" onclick="eliminar(${producto.id})">
                <i class="fas fa-trash"></i>
            </button>
        `;
    }
    
    return botones;
}
```

---

## 10. Asignar Permisos Manualmente

### Desde Tinker

```php
php artisan tinker

use App\Models\Role;
use App\Models\PermisoRole;

// Obtener el rol
$role = Role::where('role_name', 'Cajero')->first();

// Asignar permiso de cierres de caja
PermisoRole::create([
    'role_id' => $role->id,
    'codigo_permiso' => PermisoRole::PERMISO_CIERRES_CAJA,
    'descripcion' => 'Ver todos los cierres de caja',
    'activo' => true
]);

// Verificar
$role->tienePermiso(PermisoRole::PERMISO_CIERRES_CAJA); // true
```

### Desde Controlador

```php
// En PermisosController o cualquier controlador de configuración
public function asignarPermisoCajero()
{
    $cajeroRole = Role::where('role_name', 'Cajero')->first();
    
    PermisoRole::updateOrCreate(
        [
            'role_id' => $cajeroRole->id,
            'codigo_permiso' => PermisoRole::PERMISO_CIERRES_CAJA
        ],
        [
            'descripcion' => 'Ver todos los cierres de caja',
            'activo' => true
        ]
    );
    
    return response()->json(['success' => true]);
}
```

---

## Resumen de Cambios Necesarios

Para implementar completamente el sistema de permisos:

1. ✅ **Base de datos** - Migración ejecutada
2. ✅ **Modelos** - PermisoRole, Role, User actualizados
3. ✅ **Helpers** - Funciones globales creadas
4. ✅ **Seeder** - Permisos iniciales
5. ✅ **Ejemplo implementado** - Cierres de caja
6. ⏳ **Pendiente** - Aplicar en otros módulos según necesidad

### Próximos pasos recomendados:

1. Asignar permisos a roles existentes usando el seeder o manualmente
2. Reemplazar verificaciones hardcodeadas de roles por helpers
3. Agregar botones condicionales en vistas
4. Crear middleware si se necesita proteger rutas completas
5. Documentar qué permisos tiene cada rol
