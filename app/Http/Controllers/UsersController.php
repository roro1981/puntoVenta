<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\Models\Caja;
use App\Models\Comanda;
use App\Models\CorporateData;
use App\Models\DetalleComanda;
use App\Models\DetalleVenta;
use App\Models\Menu;
use App\Models\MenuRole;
use App\Models\Producto;
use App\Models\Role;
use App\Models\Submenu;
use App\Models\User;
use App\Models\Globales;
use App\Models\Venta;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\DashboardGerencialPdf;
use function PHPUnit\Framework\throwException;

class UsersController extends Controller
{

    public function requestPasswordRecovery(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $user = User::where('name', $validated['name'])
            ->where('estado', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe un usuario activo con ese nombre.'
            ], 422);
        }

        if (empty($user->email)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tu usuario no tiene correo configurado. Contacta al administrador para habilitar la recuperación.'
            ], 422);
        }

        DB::table('password_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $codigo = (string) random_int(100000, 999999);

        DB::table('password_recovery_codes')->insert([
            'user_id' => $user->id,
            'code_hash' => Hash::make($codigo),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
            'created_at' => now(),
        ]);

        $empresaNombre = CorporateData::where('item', 'NOMBRE_FANTASIA')->value('description_item')
            ?: CorporateData::where('item', 'NOMBRE_EMPRESA')->value('description_item')
            ?: 'PVenta';

        Mail::send('emails.password_recovery_code', [
            'usuario' => $user->name_complete ?: $user->name,
            'codigo' => $codigo,
            'expiraMinutos' => 10,
            'empresaNombre' => $empresaNombre,
            'fechaEnvio' => Carbon::now()->format('d/m/Y H:i'),
        ], function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Código de recuperación de contraseña');
        });

        return response()->json([
            'ok' => true,
            'message' => 'Te enviamos un código de recuperación a tu correo registrado.'
        ]);
    }

    public function resetPasswordRecovery(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|digits:6',
            'password' => 'required|string|min:6|max:128|confirmed',
        ]);

        $user = User::where('name', $validated['name'])
            ->where('estado', 1)
            ->first();

        if (!$user) {
            return response()->json([
                'ok' => false,
                'message' => 'No existe un usuario activo con ese nombre.'
            ], 422);
        }

        $recovery = DB::table('password_recovery_codes')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$recovery) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay una solicitud activa. Primero solicita un código de recuperación.'
            ], 422);
        }

        if ((int) $recovery->attempts >= 5) {
            return response()->json([
                'ok' => false,
                'message' => 'Superaste el número de intentos permitidos. Solicita un nuevo código.'
            ], 422);
        }

        if (Carbon::parse($recovery->expires_at)->isPast()) {
            DB::table('password_recovery_codes')
                ->where('id', $recovery->id)
                ->update(['used_at' => now()]);

            return response()->json([
                'ok' => false,
                'message' => 'El código expiró. Solicita uno nuevo.'
            ], 422);
        }

        if (!Hash::check($validated['code'], $recovery->code_hash)) {
            DB::table('password_recovery_codes')
                ->where('id', $recovery->id)
                ->update(['attempts' => DB::raw('attempts + 1')]);

            return response()->json([
                'ok' => false,
                'message' => 'El código ingresado no es válido.'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'updated_at' => now(),
        ]);

        DB::table('password_recovery_codes')
            ->where('id', $recovery->id)
            ->update(['used_at' => now()]);

        return response()->json([
            'ok' => true,
            'message' => 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.'
        ]);
    }

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
                'message' => 'Inicio de sesión exitoso.',
                'userName' => $user->name_complete,
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

        if (puedeVerDashboardGerencial()) {
            $tipoDashboard = 'gerencial';
            $dashboardData = $this->buildDashboardData();
        } elseif (puedeVerDashboardAdministrador()) {
            $tipoDashboard = 'administrador';
            $dashboardData = $this->buildDashboardData();
        } else {
            $tipoDashboard = 'usuario';
            $dashboardData = [];
        }

        return view('menu', compact('fechaEnPalabras', 'horaActual', 'dashboardData', 'tipoDashboard'));
    }

    private function buildDashboardData(): array
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        $hace6Dias = Carbon::today()->subDays(6);
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        
        // Asignar tipo de caja específico según el tipo de negocio
        $tipoCajaDashboard = match($tipoNegocio) {
            'RESTAURANT' => 'RESTAURANT',
            'ALMACEN' => 'ALMACEN', 
            'ALMACEN_PREVENTA' => 'ALMACEN_PREVENTA',
            default => 'ALMACEN'
        };
        
        $corporateData = CorporateData::pluck('description_item', 'item')->toArray();

        $ventasMesPagos = Venta::query()
            ->with('formasPago')
            ->whereHas('caja', function ($query) use ($tipoCajaDashboard) {
                $query->where('tipo_caja', $tipoCajaDashboard);
            })
            ->whereBetween('fecha_venta', [$inicioMes, Carbon::now()])
            ->where('estado', '!=', 'anulada')
            ->get();

        if ($tipoNegocio === 'RESTAURANT') {
            $operacionesHoy = Comanda::query()
                ->where('estado', 'CERRADA')
                ->whereDate('fecha_cierre', $hoy);

            $operacionesMes = Comanda::query()
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$inicioMes, Carbon::now()])
                ->get();

            $operacionesPeriodo7Dias = Comanda::query()
                ->selectRaw('DATE(fecha_cierre) as fecha, SUM(total) as total')
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$hace6Dias->copy()->startOfDay(), Carbon::now()->endOfDay()])
                ->groupBy(DB::raw('DATE(fecha_cierre)'))
                ->orderBy('fecha')
                ->get()
                ->keyBy('fecha');

            $ventasHoyTotal = (float) $operacionesHoy->sum('total');
            $ticketsHoy = (int) $operacionesHoy->count();
            $ventasMesTotal = (float) $operacionesMes->sum('total');
            $topProducts = DetalleComanda::query()
                ->with(['producto:id,descripcion', 'receta:id,nombre'])
                ->select(
                    'detalle_comandas.producto_id',
                    'detalle_comandas.receta_id',
                    'detalle_comandas.tipo_item',
                    DB::raw('SUM(detalle_comandas.cantidad) as cantidad_total'),
                    DB::raw('SUM(detalle_comandas.subtotal) as monto_total')
                )
                ->join('comandas', 'comandas.id', '=', 'detalle_comandas.comanda_id')
                ->where('comandas.estado', 'CERRADA')
                ->whereBetween('comandas.fecha_cierre', [$inicioMes, Carbon::now()])
                ->groupBy('detalle_comandas.producto_id', 'detalle_comandas.receta_id', 'detalle_comandas.tipo_item')
                ->orderByDesc(DB::raw('SUM(detalle_comandas.cantidad)'))
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    $nombre = $item->tipo_item === 'RECETA'
                        ? (optional($item->receta)->nombre ?? 'Receta')
                        : (optional($item->producto)->descripcion ?? 'Producto');

                    return [
                        'nombre' => $nombre,
                        'cantidad' => (float) $item->cantidad_total,
                        'monto' => (float) $item->monto_total,
                    ];
                })
                ->all();
        } else {
            $operacionesHoy = Venta::query()
                ->whereHas('caja', function ($query) use ($tipoCajaDashboard) {
                    $query->where('tipo_caja', $tipoCajaDashboard);
                })
                ->whereDate('fecha_venta', $hoy)
                ->where('estado', '!=', 'anulada');

            $operacionesMes = Venta::query()
                ->whereHas('caja', function ($query) use ($tipoCajaDashboard) {
                    $query->where('tipo_caja', $tipoCajaDashboard);
                })
                ->whereBetween('fecha_venta', [$inicioMes, Carbon::now()])
                ->where('estado', '!=', 'anulada')
                ->get();

            $operacionesPeriodo7Dias = Venta::query()
                ->selectRaw('DATE(fecha_venta) as fecha, SUM(total) as total')
                ->whereHas('caja', function ($query) use ($tipoCajaDashboard) {
                    $query->where('tipo_caja', $tipoCajaDashboard);
                })
                ->whereBetween('fecha_venta', [$hace6Dias->copy()->startOfDay(), Carbon::now()->endOfDay()])
                ->where('estado', '!=', 'anulada')
                ->groupBy(DB::raw('DATE(fecha_venta)'))
                ->orderBy('fecha')
                ->get()
                ->keyBy('fecha');

            $ventasHoyTotal = (float) $operacionesHoy->sum('total');
            $ticketsHoy = (int) $operacionesHoy->count();
            $ventasMesTotal = (float) $operacionesMes->sum('total');
            $topProducts = DetalleVenta::query()
                ->selectRaw('descripcion_producto, SUM(cantidad) as cantidad_total, SUM(subtotal_linea) as monto_total')
                ->join('ventas', 'ventas.id', '=', 'detalles_ventas.venta_id')
                ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
                ->where('cajas.tipo_caja', $tipoCajaDashboard)
                ->whereBetween('ventas.fecha_venta', [$inicioMes, Carbon::now()])
                ->where('ventas.estado', '!=', 'anulada')
                ->where(function ($query) {
                    $query->whereNull('detalles_ventas.anulado')
                        ->orWhere('detalles_ventas.anulado', false);
                })
                ->groupBy('descripcion_producto')
                ->orderByDesc(DB::raw('SUM(cantidad)'))
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'nombre' => $item->descripcion_producto,
                        'cantidad' => (float) $item->cantidad_total,
                        'monto' => (float) $item->monto_total,
                    ];
                })
                ->all();
        }

        $ticketPromedioHoy = $ticketsHoy > 0 ? $ventasHoyTotal / $ticketsHoy : 0;
        $cajasAbiertas = (int) Caja::where('estado', 'abierta')
            ->where('tipo_caja', $tipoCajaDashboard)
            ->count();
        $alertasStock = (int) Producto::query()
            ->where('estado', 'Activo')
            ->where('tipo', '<>', 'S')
            ->whereNotNull('stock_minimo')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->count();
        $sinStockBaseQuery = Producto::query()
            ->where('estado', 'Activo')
            ->where('tipo', '<>', 'S')
            ->where('stock', '<=', 0);

        if ($tipoNegocio === 'RESTAURANT') {
            $sinStockBaseQuery->whereHas('categoria', function ($query) {
                $query->whereRaw("LOWER(TRIM(descripcion_categoria)) = ?", ['insumos']);
            });
        } else {
            $sinStockBaseQuery->where(function ($query) {
                $query->whereNull('categoria_id')
                    ->orWhereDoesntHave('categoria', function ($categoriaQuery) {
                        $categoriaQuery->whereRaw("LOWER(TRIM(descripcion_categoria)) = ?", ['insumos']);
                    });
            });
        }

        $stockCritico = (int) (clone $sinStockBaseQuery)->count();
        $alertaStockProductos = Producto::query()
            ->with('categoria:id,descripcion_categoria')
            ->where('estado', 'Activo')
            ->where('tipo', '<>', 'S')
            ->whereNotNull('stock_minimo')
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderByDesc('stock')
            ->orderBy('descripcion')
            ->get()
            ->map(function ($producto) {
                return [
                    'codigo'      => $producto->codigo,
                    'descripcion' => $producto->descripcion,
                    'categoria'   => optional($producto->categoria)->descripcion_categoria ?? 'Sin categoria',
                    'stock'       => (float) $producto->stock,
                    'stock_minimo' => (float) $producto->stock_minimo,
                    'precio_venta' => (float) $producto->precio_venta,
                ];
            })
            ->all();
        $sinStockPorCategoria = (clone $sinStockBaseQuery)
            ->with('categoria:id,descripcion_categoria')
            ->orderBy('categoria_id')
            ->orderBy('descripcion')
            ->get()
            ->groupBy(function ($producto) {
                return optional($producto->categoria)->descripcion_categoria ?? 'Sin categoria';
            })
            ->map(function ($productos, $categoria) {
                return [
                    'categoria' => $categoria,
                    'items' => $productos->map(function ($producto) {
                        return [
                            'codigo'       => $producto->codigo,
                            'descripcion'  => $producto->descripcion,
                            'stock'        => (float) $producto->stock,
                            'stock_minimo' => (float) $producto->stock_minimo,
                            'precio_venta' => (float) $producto->precio_venta,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
        $detalleCajasAbiertas = Caja::query()
            ->with('usuario:id,name,name_complete')
            ->where('estado', 'abierta')
            ->where('tipo_caja', $tipoCajaDashboard)
            ->orderBy('fecha_apertura')
            ->get()
            ->map(function ($caja) {
                $ventasCaja = Venta::query()
                    ->where('caja_id', $caja->id)
                    ->where('estado', '!=', 'anulada');

                $montoVendido = (float) $ventasCaja->sum('total');
                $cantidadVentasCaja = (int) $ventasCaja->count();
                $montoEsperado = (float) $caja->monto_inicial + $montoVendido;

                return [
                    'id' => $caja->id,
                    'tipo_caja' => $caja->tipo_caja,
                    'cajero' => $caja->usuario->name_complete ?? $caja->usuario->name ?? 'N/A',
                    'apertura' => optional($caja->fecha_apertura)->format('d/m/Y H:i'),
                    'tiempo_abierta' => $caja->fecha_apertura ? $this->formatElapsedTime($caja->fecha_apertura) : 'N/A',
                    'monto_inicial' => (float) $caja->monto_inicial,
                    'monto_vendido' => $montoVendido,
                    'monto_esperado' => $montoEsperado,
                    'cantidad_ventas' => $cantidadVentasCaja,
                    'observaciones' => $caja->observaciones,
                ];
            })
            ->all();
        $comandasPendientes = $tipoNegocio === 'RESTAURANT'
            ? (int) Comanda::whereIn('estado', ['EN CONSUMO', 'PENDIENTE DE PAGO'])->count()
            : 0;

        // Preventas pendientes para tipo ALMACEN_PREVENTA (solo para gerencial/administrador)
        $preventasPendientes = 0;
        if ($tipoNegocio === 'ALMACEN_PREVENTA') {
            $preventasPendientes = (int) Venta::where('estado', 'PREVENTA')->count();
        }

        $labels7Dias = [];
        $data7Dias = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = $hace6Dias->copy()->addDays($i);
            $clave = $fecha->format('Y-m-d');
            $labels7Dias[] = $fecha->format('d/m');
            $data7Dias[] = isset($operacionesPeriodo7Dias[$clave]) ? (float) $operacionesPeriodo7Dias[$clave]->total : 0;
        }

        $promedio7Dias = count($data7Dias) > 0 ? array_sum($data7Dias) / count($data7Dias) : 0;

        $paymentTotals = [
            'EFECTIVO' => 0,
            'TARJETA_DEBITO' => 0,
            'TARJETA_CREDITO' => 0,
            'TRANSFERENCIA' => 0,
            'CHEQUE' => 0,
        ];

        foreach ($ventasMesPagos as $venta) {
            if ($venta->forma_pago === 'MIXTO') {
                foreach ($venta->formasPago as $formaPago) {
                    if (array_key_exists($formaPago->forma_pago, $paymentTotals)) {
                        $paymentTotals[$formaPago->forma_pago] += (float) $formaPago->monto;
                    }
                }

                continue;
            }

            if (array_key_exists($venta->forma_pago, $paymentTotals)) {
                $paymentTotals[$venta->forma_pago] += (float) $venta->total;
            }
        }

        $totalPagos = array_sum($paymentTotals);
        $paymentBreakdown = collect($paymentTotals)
            ->filter(fn ($amount) => $amount > 0)
            ->map(function ($amount, $label) use ($totalPagos) {
                return [
                    'label' => str_replace('_', ' ', $label),
                    'amount' => (float) $amount,
                    'percentage' => $totalPagos > 0 ? round(($amount / $totalPagos) * 100, 1) : 0,
                ];
            })
            ->values()
            ->all();

        // ============================================================
        // NUEVOS KPIs GERENCIALES
        // ============================================================

        // 1. Ventas por hora del día de hoy
        if ($tipoNegocio === 'RESTAURANT') {
            $vphrRaw = DB::table('comandas')
                ->selectRaw('HOUR(fecha_cierre) as hora, SUM(total) as total')
                ->whereDate('fecha_cierre', $hoy)
                ->where('estado', 'CERRADA')
                ->groupBy(DB::raw('HOUR(fecha_cierre)'))
                ->orderBy('hora')
                ->get()->keyBy('hora');
        } else {
            $vphrRaw = DB::table('ventas')
                ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
                ->selectRaw('HOUR(ventas.fecha_venta) as hora, SUM(ventas.total) as total')
                ->whereDate('ventas.fecha_venta', $hoy)
                ->where('ventas.estado', '!=', 'anulada')
                ->where('cajas.tipo_caja', $tipoCajaDashboard)
                ->groupBy(DB::raw('HOUR(ventas.fecha_venta)'))
                ->orderBy('hora')
                ->get()->keyBy('hora');
        }
        $ventasPorHora = [];
        for ($h = 8; $h <= 22; $h++) {
            $ventasPorHora[] = [
                'hora'  => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
                'total' => isset($vphrRaw[$h]) ? (float) $vphrRaw[$h]->total : 0,
            ];
        }

        // 2. Ventas mes anterior y delta %
        $inicioMesAnterior     = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnterior        = Carbon::now()->subMonth()->endOfMonth();
        if ($tipoNegocio === 'RESTAURANT') {
            $ventasMesAnteriorTotal = (float) DB::table('comandas')
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$inicioMesAnterior, $finMesAnterior])
                ->sum('total');
        } else {
            $ventasMesAnteriorTotal = (float) DB::table('ventas')
                ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
                ->where('cajas.tipo_caja', $tipoCajaDashboard)
                ->whereBetween('ventas.fecha_venta', [$inicioMesAnterior, $finMesAnterior])
                ->where('ventas.estado', '!=', 'anulada')
                ->sum('ventas.total');
        }
        $deltaMes = $ventasMesAnteriorTotal > 0
            ? round((($ventasMesTotal - $ventasMesAnteriorTotal) / $ventasMesAnteriorTotal) * 100, 1)
            : null;

        // 3. Ventas por categoría del mes
        if ($tipoNegocio === 'RESTAURANT') {
            $vpcRaw = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$inicioMes, Carbon::now()])
                ->selectRaw('COALESCE(c.descripcion_categoria, "Sin categoria") as categoria, SUM(dc.subtotal) as total')
                ->groupBy('c.descripcion_categoria')
                ->orderByDesc(DB::raw('SUM(dc.subtotal)'))
                ->limit(8)->get();
        } else {
            $vpcRaw = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', $tipoCajaDashboard)
                ->whereBetween('v.fecha_venta', [$inicioMes, Carbon::now()])
                ->where('v.estado', '!=', 'anulada')
                ->where(function ($q) { $q->whereNull('dv.anulado')->orWhere('dv.anulado', false); })
                ->selectRaw('COALESCE(c.descripcion_categoria, "Sin categoria") as categoria, SUM(dv.subtotal_linea) as total')
                ->groupBy('c.descripcion_categoria')
                ->orderByDesc(DB::raw('SUM(dv.subtotal_linea)'))
                ->limit(8)->get();
        }
        $ventasPorCategoria = $vpcRaw->map(fn ($r) => [
            'categoria' => $r->categoria ?? 'Sin categoria',
            'total'     => (float) $r->total,
        ])->all();

        // 4. Ventas por día de semana (promedio últimas 4 semanas)
        $hace4Semanas = Carbon::today()->subDays(27)->startOfDay();
        if ($tipoNegocio === 'RESTAURANT') {
            $dsdRaw = DB::table('comandas')
                ->selectRaw('DAYOFWEEK(fecha_cierre) as dow, SUM(total) as total, COUNT(DISTINCT DATE(fecha_cierre)) as dias')
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$hace4Semanas, Carbon::now()->endOfDay()])
                ->groupBy(DB::raw('DAYOFWEEK(fecha_cierre)'))
                ->get()->keyBy('dow');
        } else {
            $dsdRaw = DB::table('ventas')
                ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
                ->selectRaw('DAYOFWEEK(ventas.fecha_venta) as dow, SUM(ventas.total) as total, COUNT(DISTINCT DATE(ventas.fecha_venta)) as dias')
                ->where('cajas.tipo_caja', $tipoCajaDashboard)
                ->whereBetween('ventas.fecha_venta', [$hace4Semanas, Carbon::now()->endOfDay()])
                ->where('ventas.estado', '!=', 'anulada')
                ->groupBy(DB::raw('DAYOFWEEK(ventas.fecha_venta)'))
                ->get()->keyBy('dow');
        }
        $diasNombres = [1 => 'Dom', 2 => 'Lun', 3 => 'Mar', 4 => 'Mie', 5 => 'Jue', 6 => 'Vie', 7 => 'Sab'];
        $ventasPorDiaSemana = [];
        foreach ($diasNombres as $dow => $nombre) {
            $e = $dsdRaw[$dow] ?? null;
            $ventasPorDiaSemana[] = [
                'dia'   => $nombre,
                'total' => $e ? (int) round((float) $e->total / max(1, (int) $e->dias)) : 0,
            ];
        }

        // 5. Evolución últimos 6 meses (ventas + compras estimadas)
        $labels6Meses      = [];
        $data6MesesVentas  = [];
        $data6MesesCompras = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes  = Carbon::now()->subMonths($i);
            $iniM = $mes->copy()->startOfMonth();
            $finM = $mes->copy()->endOfMonth();
            $labels6Meses[] = $mes->isoFormat('MMM YY');
            if ($tipoNegocio === 'RESTAURANT') {
                $vtaM = (float) DB::table('comandas')
                    ->where('estado', 'CERRADA')
                    ->whereBetween('fecha_cierre', [$iniM, $finM])
                    ->sum('total');
            } else {
                $vtaM = (float) DB::table('ventas')
                    ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
                    ->where('cajas.tipo_caja', $tipoCajaDashboard)
                    ->whereBetween('ventas.fecha_venta', [$iniM, $finM])
                    ->where('ventas.estado', '!=', 'anulada')
                    ->sum('ventas.total');
            }
            $data6MesesVentas[] = $vtaM;
            $compraM = (float) DB::table('historial_movimientos as hm')
                ->join('productos as p', 'p.id', '=', 'hm.producto_id')
                ->whereRaw('UPPER(hm.tipo_mov) LIKE "%COMPRA%"')
                ->whereBetween('hm.fecha', [$iniM, $finM])
                ->selectRaw('SUM(ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0)) as total')
                ->value('total') ?? 0;
            $data6MesesCompras[] = $compraM;
        }

        // 6. Margen bruto estimado del mes
        if ($tipoNegocio === 'RESTAURANT') {
            $margenRow = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->join('productos as p', 'p.id', '=', 'dc.producto_id')
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$inicioMes, Carbon::now()])
                ->where('dc.tipo_item', 'PRODUCTO')
                ->where('p.precio_compra_neto', '>', 0)
                ->selectRaw('SUM(dc.subtotal) as vta, SUM(dc.cantidad * p.precio_compra_neto) as costo')
                ->first();
        } else {
            $margenRow = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->where('ca.tipo_caja', $tipoCajaDashboard)
                ->whereBetween('v.fecha_venta', [$inicioMes, Carbon::now()])
                ->where('v.estado', '!=', 'anulada')
                ->where('p.precio_compra_neto', '>', 0)
                ->selectRaw('SUM(dv.subtotal_linea) as vta, SUM(dv.cantidad * p.precio_compra_neto) as costo')
                ->first();
        }
        $margenBruto = ($margenRow && $margenRow->vta > 0)
            ? round((($margenRow->vta - $margenRow->costo) / $margenRow->vta) * 100, 1)
            : null;

        // 7. Rotación de inventario (top productos más vendidos últimos 30 días)
        $hace30Dias  = Carbon::today()->subDays(29)->startOfDay();
        $hace60Dias  = Carbon::today()->subDays(60)->startOfDay();
        $hace90Dias  = Carbon::today()->subDays(90)->startOfDay();
        $rotacionRaw = DB::table('historial_movimientos as hm')
            ->join('productos as p', 'p.id', '=', 'hm.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->whereRaw('UPPER(hm.tipo_mov) LIKE "%VENTA%"')
            ->where('p.estado', 'Activo')
            ->where('p.tipo', '<>', 'S')
            ->where('p.stock', '>', 0)
            ->whereBetween('hm.fecha', [$hace30Dias, Carbon::now()->endOfDay()])
            ->selectRaw('hm.producto_id, p.descripcion, COALESCE(c.descripcion_categoria, "Sin categoria") as categoria, p.stock, SUM(ABS(hm.cantidad)) as vendido30')
            ->groupBy('hm.producto_id', 'p.descripcion', 'p.stock', 'c.descripcion_categoria')
            ->orderByDesc(DB::raw('SUM(ABS(hm.cantidad))'))
            ->limit(15)
            ->get();
        $rotacionInventario = $rotacionRaw->map(function ($r) {
            $diario    = $r->vendido30 / 30;
            $diasStock = $diario > 0 ? (int) min(999, round($r->stock / $diario)) : 999;
            return [
                'nombre'    => $r->descripcion,
                'categoria' => $r->categoria,
                'stock'     => (float) $r->stock,
                'vendido30' => (float) $r->vendido30,
                'diasStock' => $diasStock,
            ];
        })->sortBy('diasStock')->values()->all();

        // 8. Sobrestock → días de inventario > 60
        //    Días de inventario = stock / (unidades vendidas últimos 30 días / 30)
        //    0–30 días: saludable | 30–60 días: alto | +60 días: sobrestock
        $sobrestockRaw = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin(DB::raw('(
                SELECT producto_id, SUM(ABS(cantidad)) as vendido30
                FROM historial_movimientos
                WHERE UPPER(tipo_mov) LIKE "%VENTA%"
                  AND fecha BETWEEN "' . $hace30Dias->toDateTimeString() . '"
                                AND "' . Carbon::now()->endOfDay()->toDateTimeString() . '"
                GROUP BY producto_id
            ) as vm'), 'vm.producto_id', '=', 'p.id')
            ->where('p.estado', 'Activo')
            ->where('p.tipo', '<>', 'S')
            ->where('p.stock', '>', 0)
            ->where('p.fec_creacion', '<', $hace60Dias)  // excluir productos nuevos
            ->whereNotNull('p.stock_minimo')
            ->where('p.stock_minimo', '>', 0)
            ->selectRaw('
                p.descripcion,
                COALESCE(c.descripcion_categoria, "Sin categoria") as categoria,
                p.stock,
                p.stock_minimo,
                COALESCE(vm.vendido30, 0) as vendido30,
                CASE WHEN COALESCE(vm.vendido30, 0) > 0
                     THEN ROUND(p.stock / (COALESCE(vm.vendido30, 0) / 30), 1)
                     ELSE 999
                END as dias_inventario
            ')
            ->havingRaw('dias_inventario > 60')
            ->orderByDesc('dias_inventario')
            ->limit(15)
            ->get();

        $sobrestock = $sobrestockRaw->map(function ($r) {
            return [
                'nombre'      => $r->descripcion,
                'categoria'   => $r->categoria,
                'stock'       => (float) $r->stock,
                'stockMinimo' => (float) $r->stock_minimo,
                'exceso'      => (float) ($r->stock - $r->stock_minimo),
            ];
        })->all();

        // 9. Productos nuevos (creados hace ≤60 días: aún no han tenido tiempo de rotar, no son problema)
        $productosNuevos = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->where('p.estado', 'Activo')
            ->where('p.tipo', '<>', 'S')
            ->where('p.stock', '>', 0)
            ->where('p.fec_creacion', '>=', $hace60Dias)
            ->selectRaw('
                p.descripcion,
                COALESCE(c.descripcion_categoria, "Sin categoria") as categoria,
                p.stock,
                DATEDIFF(NOW(), p.fec_creacion) as dias_desde_creacion
            ')
            ->orderBy('p.fec_creacion', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'nombre'            => $r->descripcion,
                    'categoria'         => $r->categoria,
                    'stock'             => (float) $r->stock,
                    'diasDesdeCreacion' => (int) $r->dias_desde_creacion,
                ];
            })->all();

        // 10. Productos estancados (sin ventas hace 30+ días, no son nuevos — estos son los peligrosos)
        //     30–60 días sin venta: advertencia ⚠️ | 60–90 días: alto 🔴 | +90 días: crítico 🚨
        $estancadosRaw = DB::table('productos as p')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->leftJoin(DB::raw('(
                SELECT producto_id, MAX(fecha) as ult_venta
                FROM historial_movimientos
                WHERE UPPER(tipo_mov) LIKE "%VENTA%"
                GROUP BY producto_id
            ) as uv'), 'uv.producto_id', '=', 'p.id')
            ->where('p.estado', 'Activo')
            ->where('p.tipo', '<>', 'S')
            ->where('p.stock', '>', 0)
            ->where('p.fec_creacion', '<', $hace60Dias)  // excluir productos nuevos
            ->where(function ($q) use ($hace30Dias) {
                $q->whereNull('uv.ult_venta')
                  ->orWhere('uv.ult_venta', '<', $hace30Dias);
            })
            ->selectRaw('
                p.descripcion,
                COALESCE(c.descripcion_categoria, "Sin categoria") as categoria,
                p.stock,
                uv.ult_venta,
                CASE
                    WHEN uv.ult_venta IS NULL THEN 999
                    ELSE DATEDIFF(NOW(), uv.ult_venta)
                END as dias_sin_venta
            ')
            ->orderByDesc(DB::raw('dias_sin_venta'))
            ->limit(15)
            ->get()
            ->map(function ($r) {
                $dias = (int) $r->dias_sin_venta;
                return [
                    'nombre'       => $r->descripcion,
                    'categoria'    => $r->categoria,
                    'stock'        => (float) $r->stock,
                    'ultVenta'     => $r->ult_venta,
                    'diasSinVenta' => $dias,
                    'nivel'        => $dias >= 90 ? 'critico' : ($dias >= 60 ? 'alto' : 'advertencia'),
                ];
            })->all();

        // ── Anulaciones del mes por usuario ────────────────────────────────────
        $anulacionesMes = DB::table('detalles_ventas as dv')
            ->join('users as u', 'u.id', '=', 'dv.user_anulacion_id')
            ->whereNotNull('dv.user_anulacion_id')
            ->whereNotNull('dv.fecha_anulacion')
            ->whereBetween('dv.fecha_anulacion', [$inicioMes, Carbon::now()])
            ->selectRaw('
                u.name as usuario,
                COUNT(*) as cantidad,
                SUM(dv.subtotal_linea) as monto_total
            ')
            ->groupBy('u.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get()
            ->map(fn ($r) => [
                'usuario'    => $r->usuario,
                'cantidad'   => (int) $r->cantidad,
                'montoTotal' => (float) $r->monto_total,
            ])->all();

        $totalAnulacionesMes = array_sum(array_column($anulacionesMes, 'cantidad'));
        $montoAnulacionesMes = array_sum(array_column($anulacionesMes, 'montoTotal'));

        // Anulaciones de hoy (para alerta rápida)
        $anulacionesHoy = (int) DB::table('detalles_ventas')
            ->whereNotNull('user_anulacion_id')
            ->whereDate('fecha_anulacion', $hoy)
            ->count();

        // ── Mermas del mes (historial_movimientos tipo MERMA) ──────────────────
        $mermasMes = DB::table('historial_movimientos as hm')
            ->join('productos as p', 'p.id', '=', 'hm.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->whereRaw("UPPER(hm.tipo_mov) LIKE '%MERMA%'")
            ->whereBetween('hm.fecha', [$inicioMes, Carbon::now()])
            ->selectRaw('
                COALESCE(c.descripcion_categoria, "Sin categoria") as categoria,
                p.descripcion as producto,
                SUM(ABS(hm.cantidad)) as cantidad_total,
                SUM(ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0)) as costo_total
            ')
            ->groupBy('p.id', 'p.descripcion', 'c.descripcion_categoria')
            ->orderByDesc(DB::raw('SUM(ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0))'))
            ->limit(15)
            ->get()
            ->map(fn ($r) => [
                'producto'      => $r->producto,
                'categoria'     => $r->categoria,
                'cantidadTotal' => (float) $r->cantidad_total,
                'costoTotal'    => (float) $r->costo_total,
            ])->all();

        $costoMermasMes    = array_sum(array_column($mermasMes, 'costoTotal'));
        $cantidadMermasMes = (int) DB::table('historial_movimientos')
            ->whereRaw("UPPER(tipo_mov) LIKE '%MERMA%'")
            ->whereBetween('fecha', [$inicioMes, Carbon::now()])
            ->count();

        // ============================================================
        // PANEL RENTABILIDAD EN TIEMPO REAL (Fases 1, 2 y 3)
        // ============================================================
        $ahora = Carbon::now();
        $rentabilidadHoy = $this->calcularRentabilidadSnapshot($tipoNegocio, $tipoCajaDashboard, $hoy, $ahora);

        $rentabilidadUltimos7Dias = [];
        for ($d = 1; $d <= 7; $d++) {
            $diaHistorico = Carbon::today()->subDays($d);
            $horaHistorica = $diaHistorico->copy()->setTime($ahora->hour, $ahora->minute, $ahora->second);
            $snapshotHistorico = $this->calcularRentabilidadSnapshot($tipoNegocio, $tipoCajaDashboard, $diaHistorico, $horaHistorica);
            $rentabilidadUltimos7Dias[] = $snapshotHistorico['utilidad_neta'];
        }

        $promedioRentabilidad7DiasHora = count($rentabilidadUltimos7Dias) > 0
            ? (array_sum($rentabilidadUltimos7Dias) / count($rentabilidadUltimos7Dias))
            : 0.0;

        $variacionRentabilidadPct = $promedioRentabilidad7DiasHora > 0
            ? round((($rentabilidadHoy['utilidad_neta'] - $promedioRentabilidad7DiasHora) / $promedioRentabilidad7DiasHora) * 100, 1)
            : null;

        $umbralVerde = (float) (Globales::where('nom_var', 'UMBRAL_RENTAB_VERDE')->value('valor_var') ?? 5);
        $umbralAmarillo = (float) (Globales::where('nom_var', 'UMBRAL_RENTAB_AMARILLO')->value('valor_var') ?? -5);

        if (!is_null($variacionRentabilidadPct)) {
            if ($variacionRentabilidadPct >= $umbralVerde) {
                $nivelRentabilidad = 'green';
                $estadoRentabilidad = 'Por encima del promedio';
            } elseif ($variacionRentabilidadPct >= $umbralAmarillo) {
                $nivelRentabilidad = 'yellow';
                $estadoRentabilidad = 'En rango';
            } else {
                $nivelRentabilidad = 'red';
                $estadoRentabilidad = 'Por debajo del promedio';
            }
        } else {
            $nivelRentabilidad = $rentabilidadHoy['utilidad_neta'] > 0 ? 'green' : 'yellow';
            $estadoRentabilidad = 'Sin base de comparación';
        }

        $driversRentabilidad = $this->obtenerDriversRentabilidadHoy($tipoNegocio, $tipoCajaDashboard, $hoy, $ahora);

        $insights = [];
        $insights[] = $ticketsHoy > 0
            ? ($tipoNegocio === 'RESTAURANT'
                ? 'Hoy van ' . $ticketsHoy . ' comandas cerradas con un promedio de $' . number_format($ticketPromedioHoy, 0, ',', '.') . '.'
                : 'Hoy van ' . $ticketsHoy . ' tickets emitidos con un ticket promedio de $' . number_format($ticketPromedioHoy, 0, ',', '.') . '.')
            : ($tipoNegocio === 'RESTAURANT' ? 'Hoy aun no se registran comandas cerradas.' : 'Hoy aun no se registran ventas.');
        $insights[] = $ventasHoyTotal >= $promedio7Dias
            ? 'El ritmo de ventas del dia va sobre el promedio de los ultimos 7 dias.'
            : 'El ritmo de ventas del dia va bajo el promedio de los ultimos 7 dias.';
        $insights[] = $alertasStock > 0
            ? 'Hay ' . $alertasStock . ' productos en alerta de stock y ' . $stockCritico . ' sin stock.'
            : 'No hay alertas de stock por debajo del minimo configurado.';

        if ($tipoNegocio === 'RESTAURANT') {
            $insights[] = $comandasPendientes > 0
                ? 'Existen ' . $comandasPendientes . ' comandas activas o pendientes de pago.'
                : 'No hay comandas activas pendientes de gestionar.';
        }

        $status = $this->buildDashboardStatus(
            $ventasHoyTotal,
            $promedio7Dias,
            $cajasAbiertas,
            $stockCritico,
            $comandasPendientes,
            $tipoNegocio
        );

        return [
            'tipoNegocio' => $tipoNegocio,
            'empresa' => [
                'nombre' => $corporateData['name_enterprise'] ?? 'Mi negocio',
                'fantasia' => $corporateData['fantasy_name_enterprise'] ?? null,
                'logo' => $corporateData['logo_enterprise'] ?? null,
            ],
            'status' => $status,
            'summary' => [
                'ventasHoy' => $ventasHoyTotal,
                'ventasMes' => $ventasMesTotal,
                'ticketsHoy' => $ticketsHoy,
                'ticketPromedioHoy' => $ticketPromedioHoy,
                'cajasAbiertas' => $cajasAbiertas,
                'alertasStock' => $alertasStock,
                'stockCritico' => $stockCritico,
                'comandasPendientes' => $comandasPendientes,
                'preventasPendientes' => $preventasPendientes,
                'promedio7Dias' => $promedio7Dias,
            ],
            'trend' => [
                'labels' => $labels7Dias,
                'data' => $data7Dias,
            ],
            'paymentBreakdown' => $paymentBreakdown,
            'topProducts' => $topProducts,
            'insights' => $insights,
            'rentabilidad' => [
                'utilidadNetaHoy' => $rentabilidadHoy['utilidad_neta'],
                'margenNetoHoy' => $rentabilidadHoy['margen_neto'],
                'promedio7DiasMismaHora' => $promedioRentabilidad7DiasHora,
                'variacionPct' => $variacionRentabilidadPct,
                'nivel' => $nivelRentabilidad,
                'estado' => $estadoRentabilidad,
                'umbrales' => [
                    'verde' => $umbralVerde,
                    'amarillo' => $umbralAmarillo,
                ],
                'componentes' => [
                    'ingresos' => $rentabilidadHoy['ingresos'],
                    'costo' => $rentabilidadHoy['costo'],
                    'gastosOperativos' => $rentabilidadHoy['gastos_operativos'],
                ],
                'drivers' => [
                    'top' => $driversRentabilidad['top'],
                    'worst' => $driversRentabilidad['worst'],
                ],
                'referenciaHora' => $ahora->format('H:i:s'),
            ],
            'ventasPorHora' => $ventasPorHora,
            'ventasMesAnterior' => $ventasMesAnteriorTotal,
            'deltaMes' => $deltaMes,
            'ventasPorCategoria' => $ventasPorCategoria,
            'ventasPorDiaSemana' => $ventasPorDiaSemana,
            'evolucion6Meses' => [
                'labels'  => $labels6Meses,
                'ventas'  => $data6MesesVentas,
                'compras' => $data6MesesCompras,
            ],
            'margenBruto' => $margenBruto,
            'rotacionInventario' => $rotacionInventario,
            'sobrestock' => $sobrestock,
            'productosNuevos' => $productosNuevos,
            'productosEstancados' => $estancadosRaw,
            'anulaciones' => [
                'porUsuario'    => $anulacionesMes,
                'totalMes'      => $totalAnulacionesMes,
                'montoMes'      => $montoAnulacionesMes,
                'cantidadHoy'   => $anulacionesHoy,
            ],
            'mermas' => [
                'porProducto'   => $mermasMes,
                'costoMes'      => $costoMermasMes,
                'cantidadMes'   => $cantidadMermasMes,
            ],
            'details' => [
                'stockAlerts' => $alertaStockProductos,
                'outOfStockByCategory' => $sinStockPorCategoria,
                'openCashboxes' => $detalleCajasAbiertas,
            ],
        ];
    }

    private function formatElapsedTime(Carbon $fechaApertura): string
    {
        $minutosTotales = $fechaApertura->diffInMinutes(now());
        $horas = intdiv($minutosTotales, 60);
        $minutos = $minutosTotales % 60;

        return $horas . 'h ' . $minutos . 'm';
    }

    private function calcularRentabilidadSnapshot(string $tipoNegocio, string $tipoCajaDashboard, Carbon $fecha, Carbon $horaCorte): array
    {
        $fechaStr = $fecha->toDateString();
        $horaStr = $horaCorte->format('H:i:s');

        if ($tipoNegocio === 'RESTAURANT') {
            $ingresos = (float) DB::table('comandas')
                ->where('estado', 'CERRADA')
                ->whereDate('fecha_cierre', $fechaStr)
                ->whereTime('fecha_cierre', '<=', $horaStr)
                ->sum('total');

            $costo = (float) DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->join('productos as p', 'p.id', '=', 'dc.producto_id')
                ->where('com.estado', 'CERRADA')
                ->whereDate('com.fecha_cierre', $fechaStr)
                ->whereTime('com.fecha_cierre', '<=', $horaStr)
                ->where('dc.tipo_item', 'PRODUCTO')
                ->sum(DB::raw('dc.cantidad * COALESCE(p.precio_compra_neto, 0)'));
        } else {
            $ingresos = (float) DB::table('ventas as v')
                ->join('cajas as c', 'c.id', '=', 'v.caja_id')
                ->where('c.tipo_caja', $tipoCajaDashboard)
                ->whereDate('v.fecha_venta', $fechaStr)
                ->whereTime('v.fecha_venta', '<=', $horaStr)
                ->where('v.estado', '!=', 'anulada')
                ->sum('v.total');

            $costo = (float) DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as c', 'c.id', '=', 'v.caja_id')
                ->join('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->where('c.tipo_caja', $tipoCajaDashboard)
                ->whereDate('v.fecha_venta', $fechaStr)
                ->whereTime('v.fecha_venta', '<=', $horaStr)
                ->where('v.estado', '!=', 'anulada')
                ->where(function ($q) {
                    $q->whereNull('dv.anulado')->orWhere('dv.anulado', false);
                })
                ->sum(DB::raw('dv.cantidad * COALESCE(p.precio_compra_neto, 0)'));
        }

        $retiros = (float) DB::table('retiros_caja as rc')
            ->join('cajas as c', 'c.id', '=', 'rc.caja_id')
            ->where('c.tipo_caja', $tipoCajaDashboard)
            ->whereDate('rc.created_at', $fechaStr)
            ->whereTime('rc.created_at', '<=', $horaStr)
            ->sum('rc.monto');

        $mermasQuery = DB::table('historial_movimientos as hm')
            ->join('productos as p', 'p.id', '=', 'hm.producto_id')
            ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
            ->whereRaw("UPPER(hm.tipo_mov) LIKE '%MERMA%'")
            ->whereDate('hm.fecha', $fechaStr)
            ->whereTime('hm.fecha', '<=', $horaStr);

        if ($tipoNegocio === 'RESTAURANT') {
            $mermasQuery->whereRaw("LOWER(TRIM(COALESCE(cat.descripcion_categoria, ''))) = ?", ['insumos']);
        } else {
            $mermasQuery->where(function ($q) {
                $q->whereNull('cat.descripcion_categoria')
                    ->orWhereRaw("LOWER(TRIM(cat.descripcion_categoria)) <> ?", ['insumos']);
            });
        }

        $mermas = (float) $mermasQuery->sum(DB::raw('ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0)'));
        $gastosOperativos = $retiros + $mermas;

        $utilidadNeta = $ingresos - $costo - $gastosOperativos;
        $margenNeto = $ingresos > 0 ? round(($utilidadNeta / $ingresos) * 100, 1) : null;

        return [
            'ingresos' => round($ingresos, 2),
            'costo' => round($costo, 2),
            'gastos_operativos' => round($gastosOperativos, 2),
            'utilidad_neta' => round($utilidadNeta, 2),
            'margen_neto' => $margenNeto,
        ];
    }

    private function obtenerDriversRentabilidadHoy(string $tipoNegocio, string $tipoCajaDashboard, Carbon $fecha, Carbon $horaCorte): array
    {
        $fechaStr = $fecha->toDateString();
        $horaStr = $horaCorte->format('H:i:s');

        if ($tipoNegocio === 'RESTAURANT') {
            $rows = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->join('productos as p', 'p.id', '=', 'dc.producto_id')
                ->where('com.estado', 'CERRADA')
                ->whereDate('com.fecha_cierre', $fechaStr)
                ->whereTime('com.fecha_cierre', '<=', $horaStr)
                ->where('dc.tipo_item', 'PRODUCTO')
                ->selectRaw('p.descripcion as producto, SUM(dc.subtotal) as ingresos, SUM(dc.cantidad * COALESCE(p.precio_compra_neto, 0)) as costo')
                ->groupBy('p.id', 'p.descripcion')
                ->get();
        } else {
            $rows = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as c', 'c.id', '=', 'v.caja_id')
                ->join('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->where('c.tipo_caja', $tipoCajaDashboard)
                ->whereDate('v.fecha_venta', $fechaStr)
                ->whereTime('v.fecha_venta', '<=', $horaStr)
                ->where('v.estado', '!=', 'anulada')
                ->where(function ($q) {
                    $q->whereNull('dv.anulado')->orWhere('dv.anulado', false);
                })
                ->selectRaw('COALESCE(dv.descripcion_producto, p.descripcion) as producto, SUM(dv.subtotal_linea) as ingresos, SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0)) as costo')
                ->groupBy('dv.producto_uuid', 'dv.descripcion_producto', 'p.descripcion')
                ->get();
        }

        $drivers = collect($rows)->map(function ($row) {
            $ingresos = (float) $row->ingresos;
            $costo = (float) $row->costo;
            $utilidad = $ingresos - $costo;

            return [
                'producto' => (string) $row->producto,
                'ingresos' => round($ingresos, 2),
                'costo' => round($costo, 2),
                'utilidad' => round($utilidad, 2),
                'margen' => $ingresos > 0 ? round(($utilidad / $ingresos) * 100, 1) : null,
            ];
        });

        return [
            'top' => $drivers->sortByDesc('utilidad')->take(5)->values()->all(),
            'worst' => $drivers->sortBy('utilidad')->take(5)->values()->all(),
        ];
    }

    private function buildDashboardStatus(
        float $ventasHoy,
        float $promedio7Dias,
        int $cajasAbiertas,
        int $stockCritico,
        int $comandasPendientes,
        string $tipoNegocio
    ): array {
        $score = 100;
        $mensajes = [];

        if ($cajasAbiertas === 0) {
            $score -= 20;
            $mensajes[] = 'No hay cajas abiertas en este momento.';
        } else {
            $mensajes[] = 'Hay ' . $cajasAbiertas . ' caja(s) abierta(s).';
        }

        if ($stockCritico > 0) {
            $score -= min(35, $stockCritico * 5);
            $mensajes[] = 'Existen productos sin stock que requieren accion inmediata.';
        }

        if ($promedio7Dias > 0 && $ventasHoy < ($promedio7Dias * 0.8)) {
            $score -= 20;
            $mensajes[] = 'Las ventas del dia van por debajo del promedio semanal.';
        } elseif ($promedio7Dias > 0) {
            $mensajes[] = 'Las ventas del dia mantienen un ritmo saludable.';
        }

        if ($tipoNegocio === 'RESTAURANT' && $comandasPendientes >= 8) {
            $score -= 15;
            $mensajes[] = 'Hay muchas comandas activas o pendientes de pago.';
        }

        if ($score >= 80) {
            return [
                'level' => 'bien',
                'title' => 'Tu negocio se ve estable hoy',
                'message' => implode(' ', $mensajes),
                'score' => $score,
            ];
        }

        if ($score >= 55) {
            return [
                'level' => 'atencion',
                'title' => 'Tu negocio necesita atencion en algunos puntos',
                'message' => implode(' ', $mensajes),
                'score' => $score,
            ];
        }

        return [
            'level' => 'critico',
            'title' => 'Tu negocio muestra senales que conviene revisar hoy',
            'message' => implode(' ', $mensajes),
            'score' => $score,
        ];
    }

    /**
     * Devuelve anulaciones y mermas filtradas por rango de fechas.
     * GET /dashboard/control-interno?desde=YYYY-MM-DD&hasta=YYYY-MM-DD
     */
    public function controlInternoData(Request $request)
    {
        $desde = $request->query('desde')
            ? Carbon::parse($request->query('desde'))->startOfDay()
            : Carbon::now()->startOfWeek();
        $hasta = $request->query('hasta')
            ? Carbon::parse($request->query('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        // Anulaciones por usuario en el rango
        $anulacionesPorUsuario = DB::table('detalles_ventas as dv')
            ->join('users as u', 'u.id', '=', 'dv.user_anulacion_id')
            ->whereNotNull('dv.user_anulacion_id')
            ->whereNotNull('dv.fecha_anulacion')
            ->whereBetween('dv.fecha_anulacion', [$desde, $hasta])
            ->selectRaw('u.name as usuario, COUNT(*) as cantidad, SUM(dv.subtotal_linea) as monto_total')
            ->groupBy('u.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->get()
            ->map(fn ($r) => [
                'usuario'    => $r->usuario,
                'cantidad'   => (int) $r->cantidad,
                'montoTotal' => (float) $r->monto_total,
            ])->all();

        $totalAnulaciones = array_sum(array_column($anulacionesPorUsuario, 'cantidad'));
        $montoAnulaciones = array_sum(array_column($anulacionesPorUsuario, 'montoTotal'));
        $anulacionesHoy   = (int) DB::table('detalles_ventas')
            ->whereNotNull('user_anulacion_id')
            ->whereDate('fecha_anulacion', Carbon::today())
            ->count();

        // Mermas en el rango
        $mermasPorProducto = DB::table('historial_movimientos as hm')
            ->join('productos as p', 'p.id', '=', 'hm.producto_id')
            ->leftJoin('categorias as c', 'c.id', '=', 'p.categoria_id')
            ->whereRaw("UPPER(hm.tipo_mov) LIKE '%MERMA%'")
            ->whereBetween('hm.fecha', [$desde, $hasta])
            ->selectRaw('
                COALESCE(c.descripcion_categoria, "Sin categoria") as categoria,
                p.descripcion as producto,
                SUM(ABS(hm.cantidad)) as cantidad_total,
                SUM(ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0)) as costo_total
            ')
            ->groupBy('p.id', 'p.descripcion', 'c.descripcion_categoria')
            ->orderByDesc(DB::raw('SUM(ABS(hm.cantidad) * COALESCE(p.precio_compra_neto, 0))'))
            ->limit(15)
            ->get()
            ->map(fn ($r) => [
                'producto'      => $r->producto,
                'categoria'     => $r->categoria,
                'cantidadTotal' => (float) $r->cantidad_total,
                'costoTotal'    => (float) $r->costo_total,
            ])->all();

        $costoMermas    = array_sum(array_column($mermasPorProducto, 'costoTotal'));
        $cantidadMermas = (int) DB::table('historial_movimientos')
            ->whereRaw("UPPER(tipo_mov) LIKE '%MERMA%'")
            ->whereBetween('fecha', [$desde, $hasta])
            ->count();

        return response()->json([
            'anulaciones' => [
                'porUsuario'  => $anulacionesPorUsuario,
                'totalMes'    => $totalAnulaciones,
                'montoMes'    => $montoAnulaciones,
                'cantidadHoy' => $anulacionesHoy,
            ],
            'mermas' => [
                'porProducto' => $mermasPorProducto,
                'costoMes'    => $costoMermas,
                'cantidadMes' => $cantidadMermas,
            ],
            'retiros' => $this->buildRetirosData($desde, $hasta),
            'cierres_alerta' => $this->buildCierresAlertaData($desde, $hasta),
        ]);
    }

    /** Retiros de caja en el rango, agrupados por cajero */
    private function buildRetirosData($desde, $hasta): array
    {
        $retiros = DB::table('retiros_caja as rc')
            ->join('cajas as c', 'c.id', '=', 'rc.caja_id')
            ->join('users as u', 'u.id', '=', 'rc.creado_por')
            ->whereBetween('rc.created_at', [$desde, $hasta])
            ->selectRaw('u.name as usuario, COUNT(*) as cantidad, SUM(rc.monto) as monto_total')
            ->groupBy('u.name')
            ->orderByDesc(DB::raw('SUM(rc.monto)'))
            ->get()
            ->map(fn ($r) => [
                'usuario'    => $r->usuario,
                'cantidad'   => (int) $r->cantidad,
                'montoTotal' => (float) $r->monto_total,
            ])->all();

        return [
            'porUsuario'   => $retiros,
            'montoTotal'   => array_sum(array_column($retiros, 'montoTotal')),
            'cantidadTotal'=> array_sum(array_column($retiros, 'cantidad')),
        ];
    }

    /** Cierres de caja en el rango con diferencia superior a $5.000 */
    private function buildCierresAlertaData($desde, $hasta): array
    {
        $umbral = 5000;
        $cierres = DB::table('cajas as cj')
            ->join('users as u', 'u.id', '=', 'cj.user_id')
            ->where('cj.estado', 'cerrada')
            ->whereBetween('cj.fecha_cierre', [$desde, $hasta])
            ->whereRaw('ABS(COALESCE(cj.diferencia, 0)) >= ?', [$umbral])
            ->selectRaw('
                u.name as cajero,
                cj.monto_inicial + cj.monto_ventas as monto_esperado,
                cj.monto_final_declarado as monto_declarado,
                cj.diferencia as diferencia,
                DATE_FORMAT(cj.fecha_cierre, "%d/%m/%Y %H:%i") as fecha_cierre
            ')
            ->orderByDesc(DB::raw('ABS(COALESCE(cj.diferencia, 0))'))
            ->limit(20)
            ->get()
            ->map(fn ($r) => [
                'cajero'          => $r->cajero,
                'montoEsperado'   => (float) $r->monto_esperado,
                'montoDeclarado'  => (float) $r->monto_declarado,
                'diferencia'      => (float) $r->diferencia,
                'fechaCierre'     => $r->fecha_cierre,
            ])->all();

        return [
            'cierres'     => $cierres,
            'totalAlertas'=> count($cierres),
            'umbral'      => $umbral,
        ];
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
        $roleName = mb_strtolower(trim((string) ($role->role_name ?? '')));
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        foreach ($role->submenus as $submenu) {
            if (strtolower(trim((string) $submenu->submenu_route)) === '/restaurant/config-garzones') {
                continue;
            }

            if ($this->debeOcultarSubmenuPorTipoNegocio($tipoNegocio, $submenu->submenu_route)) {
                continue;
            }

            if (strtolower(trim((string) $submenu->submenu_route)) === '/control_propinas' && !in_array($roleName, ['garzon', 'garzón'], true)) {
                continue;
            }

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

        ksort($menus);
        return array_values($menus);
    }

    private function debeOcultarSubmenuPorTipoNegocio(string $tipoNegocio, ?string $submenuRoute): bool
    {
        $ruta = strtolower(trim((string) $submenuRoute));

        if ($tipoNegocio === 'RESTAURANT') {
            // En restaurant: ocultar generar_ventas y todo lo de promociones
            return in_array($ruta, [
                '/generar_ventas',
                '/generar_preventa',
                '/cierre_preventa',
                '/vtas_vendedor',
                '/promociones_crear',
                '/promociones',
                '/promos_elim',
            ], true);
        }

        if ($tipoNegocio === 'ALMACEN') {
            // En almacen: ocultar cosas de restaurant, recetas y preventa
            return in_array($ruta, [
                '/generar_comandas',
                '/cerrar_comandas',
                '/control_propinas',
                '/restaurant/config-mesas',
                '/restaurant/config-menu-qr',
                '/vtas_garzon',
                '/vtas_mesa',
                '/recetas_crear',
                '/recetas',
                '/recetas_elim',
                '/generar_preventa',
                '/cierre_preventa',
            ], true);
        }

        if ($tipoNegocio === 'ALMACEN_PREVENTA') {
            // En almacen preventa: ocultar generar_ventas normal y todo lo de restaurant/recetas
            return in_array($ruta, [
                '/generar_ventas',
                '/generar_comandas',
                '/cerrar_comandas',
                '/control_propinas',
                '/restaurant/config-mesas',
                '/restaurant/config-menu-qr',
                '/vtas_garzon',
                '/vtas_mesa',
                '/recetas_crear',
                '/recetas',
                '/recetas_elim',
            ], true);
        }

        return false;
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
        $query = User::select('users.uuid', 'users.id', 'users.name', 'users.name_complete', 'users.role_id', 'roles.role_name', 'users.created_at', 'users.updated_at')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.estado', 1)
            ->where('users.name_complete', '<>', 'Rodrigo Panes');
        
        // Si el usuario autenticado NO es SuperAdministrador, excluir SuperAdministradores del listado
        $userRole = auth()->user()->role->role_name;
        if ($userRole !== 'SuperAdministrador') {
            $query->where('roles.role_name', '!=', 'SuperAdministrador');
        }
        
        $users = $query->get()
            ->map(function ($user) {
                $user->created_at = date('d/m/Y H:i:s', strtotime($user->created_at));
                $user->updated_at = $user->updated_at ? date('d/m/Y H:i:s', strtotime($user->updated_at)) : 'Aún no tiene modificaciones';
                $user->actions = '<a href="" class="btn btn-sm btn-primary editar_usu" data-rol="' . $user->role_id . '" data-target="#editUserModal" data-uuid="' . $user->uuid . '" data-toggle="modal" title="Editar usuario ' . $user->name . '"><i class="fa fa-edit"></i></a>
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
    public function deleteRole($id)
    {
        try {
            $role = Role::findOrFail($id);

            if (Role::esRolSistemaPorDefecto($role->role_name)) {
                return response()->json([
                    'error' => 403,
                    'message' => "No se puede eliminar el rol {$role->role_name} porque es un rol base del sistema."
                ], 403);
            }

            $userCount = User::where('role_id', $id)->count();
            if ($userCount > 0) {
                return response()->json([
                    'error' => 403,
                    'message' => "No se puede eliminar el rol porque hay usuarios asociados a él."
                ], 403);
            }

            // Eliminar primero las relaciones en menu_roles
            MenuRole::where('role_id', $id)->delete();

            // Ahora eliminar el rol
            $role->delete();

            $response = response()->json([
                'error' => 200,
                'message' => "Rol eliminado correctamente"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error al eliminar rol " . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => $e->getMessage()
            ], 500);
        }

        return $response;
    }
    public function rolesTable()
    {
        $tipoNegocio = $this->obtenerTipoNegocioActual();

        $roles = $this->obtenerRolesVisiblesPorTipoNegocio($tipoNegocio)
            ->map(function ($roles) {
                $roles->asociados = '<button type="button" data-id="' . $roles->id . '" class="btn btn-primary ver-btn">
                                            <i class="fa fa-eye"></i> Ver
                                        </button>';
                $roles->usuarios = '<button type="button" data-rol="' . $roles->role_name . '" data-id="' . $roles->id . '" class="btn btn-success ver-btn_users">
                                            <i class="fa fa-eye"></i> Ver
                                        </button>';
                $roles->created_at = date('d/m/Y H:i:s', strtotime($roles->created_at));
                $roles->updated_at = $roles->updated_at ? date('d/m/Y H:i:s', strtotime($roles->updated_at)) : 'Aún no tiene modificaciones';

                if (Role::esRolSistemaPorDefecto($roles->role_name)) {
                    $roles->actions = '<span class="label label-default" title="Rol base protegido">Protegido</span>';
                } else {
                    $roles->actions = '<a href="" class="btn btn-sm btn-danger eliminar-rol" data-toggle="tooltip" data-rolid="' . $roles->id . '" data-namerol="' . $roles->role_name . '" title="Eliminar rol ' . $roles->role_name . '"><i class="fa fa-trash"></i></a>';
                }

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
        $roles = $this->obtenerRolesVisiblesPorTipoNegocio($this->obtenerTipoNegocioActual());
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
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        $menus = Menu::with(['submenus' => function ($query) use ($id) {
            $query->whereHas('menuRoles', function ($query) use ($id) {
                $query->where('role_id', $id);
            });
        }])
            ->whereHas('submenus.menuRoles', function ($query) use ($id) {
                $query->where('role_id', $id);
            })
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($menu) use ($tipoNegocio) {
                $submenusFiltrados = $menu->submenus
                    ->filter(function ($submenu) use ($tipoNegocio) {
                        return !$this->debeOcultarSubmenuPorTipoNegocio($tipoNegocio, $submenu->submenu_route);
                    })
                    ->values();

                $menu->setRelation('submenus', $submenusFiltrados);
                return $menu;
            })
            ->filter(function ($menu) {
                return $menu->submenus->isNotEmpty();
            })
            ->values();

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
        $roles = $this->obtenerRolesVisiblesPorTipoNegocio($this->obtenerTipoNegocioActual());
        return view('users.permisos', compact('roles'));
    }

    private function obtenerTipoNegocioActual(): string
    {
        return strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
    }

    private function obtenerRolesVisiblesPorTipoNegocio(string $tipoNegocio)
    {
        return Role::all()
            ->filter(function ($role) use ($tipoNegocio) {
                return Role::esRolVisiblePorTipoNegocio($role->role_name, $tipoNegocio);
            })
            ->values();
    }

    public function getMenus(Request $request)
    {
        $roleId = $request->role_id;
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        $submenus = Submenu::with('menu')
            ->get()
            ->filter(function ($submenu) use ($tipoNegocio) {
                return !$this->debeOcultarSubmenuPorTipoNegocio($tipoNegocio, $submenu->submenu_route);
            })
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

    /**
     * Obtiene el detalle de todas las preventas pendientes para el dashboard gerencial/administrador.
     * Solo para tipo de negocio ALMACEN_PREVENTA.
     */
    public function preventasPendientesDashboard()
    {
        // Verificar que el usuario tenga permisos para ver el dashboard gerencial/administrador
        if (!puedeVerDashboardGerencial() && !puedeVerDashboardAdministrador()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para acceder a esta información'
            ], 403);
        }

        // Verificar que el tipo de negocio sea ALMACEN_PREVENTA
        $tipoNegocio = strtoupper(trim((string) \App\Models\Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        if ($tipoNegocio !== 'ALMACEN_PREVENTA') {
            return response()->json([
                'success' => false,
                'message' => 'Esta funcionalidad solo está disponible para negocios de tipo almacén con preventas'
            ], 400);
        }

        $preventas = \App\Models\Venta::query()
            ->with(['usuario:id,name,name_complete', 'detalles:venta_id,producto_uuid,cantidad,precio_unitario,subtotal_linea,descripcion_producto'])
            ->where('estado', 'PREVENTA')
            ->orderByDesc('id')
            ->get(['id', 'total', 'fecha_venta', 'user_id']);

        $data = $preventas->map(function ($venta) {
            $totalItems = $venta->detalles->sum('cantidad');
            
            // Priorizar name_complete, luego name, como fallback usar "Usuario ID"
            $vendedorNombre = 'N/A';
            if ($venta->usuario) {
                $vendedorNombre = $venta->usuario->name_complete ?? $venta->usuario->name ?? "Usuario #{$venta->user_id}";
                
                // Para almacén, usar término "vendedor" en lugar de nombres similares a garzones
                if (empty($venta->usuario->name_complete) && !empty($venta->usuario->name)) {
                    // Si solo tiene 'name', asumimos que es un usuario de sistema
                    $vendedorNombre = "Vendedor: " . $venta->usuario->name;
                }
            } else {
                $vendedorNombre = "Usuario #{$venta->user_id}";
            }

            return [
                'venta_id' => $venta->id,
                'numero_preventa' => str_pad((string) $venta->id, 6, '0', STR_PAD_LEFT),
                'total' => (int) $venta->total,
                'fecha_preventa' => optional($venta->fecha_venta)->format('d/m/Y H:i:s'),
                'vendedor' => $vendedorNombre,
                'total_items' => (int) $totalItems,
                'productos' => $venta->detalles->map(function ($detalle) {
                    return [
                        'producto' => $detalle->descripcion_producto,
                        'cantidad' => (float) $detalle->cantidad,
                        'precio_unitario' => (float) $detalle->precio_unitario,
                        'subtotal' => (float) $detalle->subtotal_linea,
                    ];
                })->toArray()
            ];
        })->values();

        return response()->json([
            'success' => true,
            'preventas' => $data,
            'total_preventas' => $preventas->count(),
            'monto_total' => $preventas->sum('total')
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('inicio'));
    }

    public function enviarPdfGerencial(Request $request)
    {
        $request->validate([
            'email'       => ['required', 'email:rfc,dns'],
            'pdf_base64'  => ['required', 'string'],
        ]);

        $email      = $request->input('email');
        $pdfBase64  = $request->input('pdf_base64');

        // Verificar tamaño máximo ~22 MB de datos reales en base64
        if (strlen($pdfBase64) > 30_000_000) {
            return response()->json(['success' => false, 'message' => 'El PDF generado supera el tamaño permitido (máx. 22 MB).'], 422);
        }

        $empresa  = CorporateData::first();
        $nombre   = $empresa ? ($empresa->fantasia ?: $empresa->nombre ?? 'Empresa') : 'Empresa';
        $filename = 'dashboard-gerencial-' . now()->format('Y-m-d') . '.pdf';

        try {
            Mail::to($email)->send(new DashboardGerencialPdf($nombre, $pdfBase64, $filename));
            return response()->json(['success' => true, 'message' => 'PDF enviado correctamente a ' . $email]);
        } catch (\Exception $e) {
            Log::error('[DashboardPDF] Error al enviar correo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'No se pudo enviar el correo. Verifica la configuración de correo del sistema.'], 500);
        }
    }
}
