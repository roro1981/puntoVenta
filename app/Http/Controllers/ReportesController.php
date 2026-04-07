<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Receta;
use App\Models\Promocion;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ReporteExport;
use App\Services\ReportesService;
use App\Exports\MovimientosExport;
use App\Exports\VentasFechaExport;
use App\Exports\FormasPagoExport;
use App\Exports\VendedorExport;
use App\Exports\GarzonVentasExport;
use App\Exports\GarzonPropinasExport;
use App\Exports\MesaExport;
use App\Exports\ProductosMasVendidosExport;
use App\Exports\ProductosRentablesExport;
use App\Exports\CategoriasVendidasExport;
use App\Exports\InventarioExport;
use App\Exports\HistorialPrecioExport;
use App\Models\Globales;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReportesController extends Controller
{

    protected $reportesService;

    public function __construct(ReportesService $reportesService)
    {
        $this->reportesService = $reportesService;
    }

    public function indexMovimientos()
    {
        return view('reportes.movimientos_productos');
    }

    public function traeMovimientos(Request $request, ReportesService $service)
    {
        $request->validate([
            'idp'       => 'required|string',
            'tipo_mov'  => 'required|integer|min:1|max:8',
            'fec_desde' => 'required|string',
            'fec_hasta' => 'required|string',
        ]);

        $data = $service->dataMovimientosJson(
            $request->input('idp'),
            (int) $request->input('tipo_mov'),
            $request->input('fec_desde'),
            $request->input('fec_hasta')
        );

        return response()->json($data);
    }

    public function searchProductosMovimientos(Request $request, ReportesService $service)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        return response()->json($service->buscarProductos($q));
    }

    public function exportarMovimientos(Request $request)
    {
        $tipo = $request->query('tipo_mov');
        $uuid = $request->query('idprod');
        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $productoNombre = Producto::where('uuid', $uuid)->value('descripcion') ?? 'producto';

        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');

        $productoNombreSanitizado = Str::slug($productoNombre, '_');

        $fileName = "movimientos_{$productoNombreSanitizado}_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new MovimientosExport($tipo, $uuid, $desde, $hasta), $fileName);
    }

    // ---------------------------------------------------------------
    // VENTAS POR FECHA
    // ---------------------------------------------------------------

    public function indexVentasFecha()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.vtas_fecha', compact('tipoNegocio'));
    }

    public function dataVentasFecha(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = Carbon::parse($request->desde)->startOfDay();
        $hasta = Carbon::parse($request->hasta)->endOfDay();

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $tipoCaja    = $tipoNegocio === 'RESTAURANT' ? 'RESTAURANT' : 'ALMACEN';

        if ($tipoNegocio === 'RESTAURANT') {
            // --- totales generales ---
            $totalesRow = DB::table('comandas')
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$desde, $hasta])
                ->selectRaw('COUNT(*) as tickets, SUM(total) as total, AVG(total) as promedio')
                ->first();

            $totalVentas   = (float) ($totalesRow->total ?? 0);
            $totalTickets  = (int)   ($totalesRow->tickets ?? 0);
            $ticketPromedio = (float) ($totalesRow->promedio ?? 0);

            // --- tendencia por día ---
            $tendenciaRaw = DB::table('comandas')
                ->where('estado', 'CERRADA')
                ->whereBetween('fecha_cierre', [$desde, $hasta])
                ->selectRaw('DATE(fecha_cierre) as fecha, COUNT(*) as tickets, SUM(total) as total')
                ->groupBy(DB::raw('DATE(fecha_cierre)'))
                ->orderBy('fecha')
                ->get();

        } else {
            // --- totales generales ---
            $totalesRow = DB::table('ventas as v')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->where('ca.tipo_caja', $tipoCaja)
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->selectRaw('COUNT(*) as tickets, SUM(v.total) as total, AVG(v.total) as promedio')
                ->first();

            $totalVentas    = (float) ($totalesRow->total ?? 0);
            $totalTickets   = (int)   ($totalesRow->tickets ?? 0);
            $ticketPromedio = (float) ($totalesRow->promedio ?? 0);

            // --- tendencia por día ---
            $tendenciaRaw = DB::table('ventas as v')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->where('ca.tipo_caja', $tipoCaja)
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->selectRaw('DATE(v.fecha_venta) as fecha, COUNT(*) as tickets, SUM(v.total) as total')
                ->groupBy(DB::raw('DATE(v.fecha_venta)'))
                ->orderBy('fecha')
                ->get();
        }

        $tendencia = $tendenciaRaw->map(fn ($r) => [
            'fecha'   => Carbon::parse($r->fecha)->format('d/m'),
            'tickets' => (int) $r->tickets,
            'total'   => (float) $r->total,
        ])->all();

        $diasPeriodo = max(1, $desde->diffInDays($hasta) + 1);
        $promedioDiario = $diasPeriodo > 0 ? round($totalVentas / $diasPeriodo, 0) : 0;

        return response()->json([
            'tipoNegocio'    => $tipoNegocio,
            'totalVentas'    => $totalVentas,
            'totalTickets'   => $totalTickets,
            'ticketPromedio' => $ticketPromedio,
            'promedioDiario' => $promedioDiario,
            'diasPeriodo'    => $diasPeriodo,
            'tendencia'      => $tendencia,
        ]);
    }

    public function exportarVentasFecha(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName = "ventas_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new VentasFechaExport($desde, $hasta), $fileName);
    }

    public function exportarFormasPago(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = $request->query('desde');
        $hasta = $request->query('hasta');

        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName = "formas_pago_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new FormasPagoExport($desde, $hasta), $fileName);
    }

    public function indexFormasPago()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.vtas_forma_pago', compact('tipoNegocio'));
    }

    public function dataFormasPago(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = Carbon::parse($request->desde)->startOfDay();
        $hasta = Carbon::parse($request->hasta)->endOfDay();

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $tipoCaja    = $tipoNegocio === 'RESTAURANT' ? 'RESTAURANT' : 'ALMACEN';

        // --- totales generales (1 fila por venta, sin doble conteo MIXTO) ---
        $totalesRow = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw('COUNT(*) as tickets, SUM(v.total) as total')
            ->first();

        $totalVentas  = (float) ($totalesRow->total   ?? 0);
        $totalTickets = (int)   ($totalesRow->tickets  ?? 0);

        // --- formas de pago desglosadas (MIXTO se expande por fpv) ---
        $pagosRaw = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->leftJoin('formas_pago_venta as fpv', 'fpv.venta_id', '=', 'v.id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("
                CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END as forma,
                COUNT(*) as transacciones,
                SUM(CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.monto ELSE v.total END) as monto
            ")
            ->groupBy(DB::raw("CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END"))
            ->havingRaw("forma IS NOT NULL AND forma != ''")
            ->orderByDesc('monto')
            ->get();

        $totalPagos     = $pagosRaw->sum('monto');
        $formaDominante = optional($pagosRaw->first())->forma ?? '—';

        $formasPago = $pagosRaw->map(fn ($r) => [
            'label'         => str_replace('_', ' ', $r->forma),
            'transacciones' => (int)   $r->transacciones,
            'monto'         => (float) $r->monto,
            'porcentaje'    => $totalPagos > 0 ? round(($r->monto / $totalPagos) * 100, 1) : 0,
            'promedio'      => $r->transacciones > 0 ? round($r->monto / $r->transacciones, 0) : 0,
        ])->values()->all();

        // --- tendencia diaria por forma de pago ---
        $tendenciaRaw = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->leftJoin('formas_pago_venta as fpv', 'fpv.venta_id', '=', 'v.id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("
                DATE(v.fecha_venta) as fecha,
                CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END as forma,
                SUM(CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.monto ELSE v.total END) as monto
            ")
            ->groupBy(DB::raw("DATE(v.fecha_venta), CASE WHEN v.forma_pago = 'MIXTO' THEN fpv.forma_pago ELSE v.forma_pago END"))
            ->havingRaw("forma IS NOT NULL AND forma != ''")
            ->orderBy('fecha')
            ->get();

        $formasKeys = $tendenciaRaw->pluck('forma')->unique()->filter()->values();
        $fechas     = $tendenciaRaw->pluck('fecha')->unique()->sort()->values();

        $tendencia = $fechas->map(function ($f) use ($tendenciaRaw, $formasKeys) {
            $row = ['fecha' => Carbon::parse($f)->format('d/m')];
            foreach ($formasKeys as $forma) {
                $val       = $tendenciaRaw->where('fecha', $f)->where('forma', $forma)->first();
                $row[$forma] = $val ? (float) $val->monto : 0;
            }
            return $row;
        })->values()->all();

        return response()->json([
            'tipoNegocio'    => $tipoNegocio,
            'totalVentas'    => $totalVentas,
            'totalTickets'   => $totalTickets,
            'ticketPromedio' => $totalTickets > 0 ? round($totalVentas / $totalTickets, 0) : 0,
            'formaDominante' => str_replace('_', ' ', $formaDominante),
            'formasPago'     => $formasPago,
            'tendencia'      => $tendencia,
            'formasKeys'     => $formasKeys->values()->all(),
            'formasLabels'   => $formasKeys->map(fn ($f) => str_replace('_', ' ', $f))->values()->all(),
        ]);
    }

    public function indexVendedor()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.vtas_vendedor', compact('tipoNegocio'));
    }

    public function dataVendedor(Request $request)
    {
        $request->validate([
            'desde'       => 'required|date',
            'hasta'       => 'required|date|after_or_equal:desde',
            'vendedor_id' => 'nullable|integer',
        ]);

        $desde      = Carbon::parse($request->desde)->startOfDay();
        $hasta      = Carbon::parse($request->hasta)->endOfDay();
        $vendedorId = $request->input('vendedor_id') ?: null;

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $tipoCaja    = $tipoNegocio === 'RESTAURANT' ? 'RESTAURANT' : 'ALMACEN';

        // --- lista de vendedores con ventas en el periodo (para el select) ---
        $vendedores = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->selectRaw("u.id, COALESCE(u.name_complete, u.name) as nombre")
            ->groupBy('u.id', 'u.name', 'u.name_complete')
            ->orderBy('nombre')
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre])
            ->all();

        // --- totales generales ---
        $totalesRow = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->when($vendedorId, fn ($q) => $q->where('v.user_id', $vendedorId))
            ->selectRaw('COUNT(*) as tickets, SUM(v.total) as total')
            ->first();

        $totalVentas  = (float) ($totalesRow->total   ?? 0);
        $totalTickets = (int)   ($totalesRow->tickets  ?? 0);

        // --- ranking de vendedores ---
        $rankingRaw = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->when($vendedorId, fn ($q) => $q->where('v.user_id', $vendedorId))
            ->selectRaw("u.id, COALESCE(u.name_complete, u.name) as nombre, COUNT(*) as transacciones, SUM(v.total) as total")
            ->groupBy('u.id', 'u.name', 'u.name_complete')
            ->orderByDesc('total')
            ->get();

        $vendedorDestacado = optional($rankingRaw->first())->nombre ?? '—';
        $ranTotal          = $rankingRaw->sum('total') ?: 1;

        $ranking = $rankingRaw->map(fn ($r) => [
            'nombre'        => $r->nombre,
            'transacciones' => (int)   $r->transacciones,
            'total'         => (float) $r->total,
            'porcentaje'    => round(($r->total / $ranTotal) * 100, 1),
            'promedio'      => $r->transacciones > 0 ? round($r->total / $r->transacciones, 0) : 0,
        ])->values()->all();

        // --- tendencia diaria ---
        $tendenciaRaw = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->when($vendedorId, fn ($q) => $q->where('v.user_id', $vendedorId))
            ->selectRaw('DATE(v.fecha_venta) as fecha, COUNT(*) as tickets, SUM(v.total) as total')
            ->groupBy(DB::raw('DATE(v.fecha_venta)'))
            ->orderBy('fecha')
            ->get();

        $tendencia = $tendenciaRaw->map(fn ($r) => [
            'fecha'   => Carbon::parse($r->fecha)->format('d/m'),
            'tickets' => (int)   $r->tickets,
            'total'   => (float) $r->total,
        ])->all();

        // --- detalle de ventas (para DataTable) ---
        $detalle = DB::table('ventas as v')
            ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->where('ca.tipo_caja', $tipoCaja)
            ->where('v.estado', '!=', 'anulada')
            ->whereBetween('v.fecha_venta', [$desde, $hasta])
            ->when($vendedorId, fn ($q) => $q->where('v.user_id', $vendedorId))
            ->selectRaw("
                v.id as folio,
                DATE_FORMAT(v.fecha_venta, '%d-%m-%Y %H:%i') as fecha_venta,
                COALESCE(u.name_complete, u.name) as vendedor,
                v.forma_pago,
                v.total,
                v.estado
            ")
            ->orderBy('u.name')
            ->orderBy('v.fecha_venta')
            ->get()
            ->map(fn ($r) => [
                'folio'      => $r->folio,
                'fecha'      => $r->fecha_venta,
                'vendedor'   => $r->vendedor,
                'forma_pago' => str_replace('_', ' ', $r->forma_pago ?? ''),
                'total'      => (float) $r->total,
                'total_fmt'  => '$' . number_format($r->total, 0, ',', '.'),
                'estado'     => $r->estado,
            ])->all();

        return response()->json([
            'tipoNegocio'       => $tipoNegocio,
            'totalVentas'       => $totalVentas,
            'totalTickets'      => $totalTickets,
            'ticketPromedio'    => $totalTickets > 0 ? round($totalVentas / $totalTickets, 0) : 0,
            'vendedorDestacado' => $vendedorDestacado,
            'vendedores'        => $vendedores,
            'ranking'           => $ranking,
            'tendencia'         => $tendencia,
            'detalle'           => $detalle,
        ]);
    }

    public function exportarVendedor(Request $request)
    {
        $request->validate([
            'desde'       => 'required|date',
            'hasta'       => 'required|date|after_or_equal:desde',
            'vendedor_id' => 'nullable|integer',
        ]);

        $desde      = $request->query('desde');
        $hasta      = $request->query('hasta');
        $vendedorId = $request->query('vendedor_id') ? (int) $request->query('vendedor_id') : null;

        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "ventas_vendedor_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new VendedorExport($desde, $hasta, $vendedorId), $fileName);
    }

    // ---------------------------------------------------------------
    // VENTAS POR GARZÓN
    // ---------------------------------------------------------------

    public function indexGarzon()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.vtas_garzon', compact('tipoNegocio'));
    }

    public function dataGarzon(Request $request)
    {
        $request->validate([
            'desde'     => 'required|date',
            'hasta'     => 'required|date|after_or_equal:desde',
            'garzon_id' => 'nullable|integer',
        ]);

        $desde    = Carbon::parse($request->desde)->startOfDay();
        $hasta    = Carbon::parse($request->hasta)->endOfDay();
        $garzonId = $request->input('garzon_id') ?: null;

        // --- lista de garzones con comandas en el periodo (para el select) ---
        $garzones = DB::table('comandas as com')
            ->join('garzones as g', 'g.id', '=', 'com.garzon_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw("g.id, CONCAT(g.nombre, ' ', g.apellido) as nombre")
            ->groupBy('g.id', 'g.nombre', 'g.apellido')
            ->orderBy('g.nombre')
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre])
            ->all();

        // --- totales generales ---
        $totalesRow = DB::table('comandas as com')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->when($garzonId, fn ($q) => $q->where('com.garzon_id', $garzonId))
            ->selectRaw('COUNT(*) as comandas, SUM(com.total) as total, SUM(CASE WHEN com.incluye_propina = 1 THEN com.propina ELSE 0 END) as propinas')
            ->first();

        $totalVentas   = (float) ($totalesRow->total    ?? 0);
        $totalComandas = (int)   ($totalesRow->comandas  ?? 0);
        $totalPropinas = (float) ($totalesRow->propinas  ?? 0);

        // --- ranking por garzón ---
        $rankingRaw = DB::table('comandas as com')
            ->join('garzones as g', 'g.id', '=', 'com.garzon_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->when($garzonId, fn ($q) => $q->where('com.garzon_id', $garzonId))
            ->selectRaw("
                g.id,
                CONCAT(g.nombre, ' ', g.apellido) as nombre,
                COUNT(*) as comandas,
                COUNT(DISTINCT com.mesa_id) as mesas,
                SUM(com.comensales) as comensales,
                SUM(com.total) as total,
                SUM(CASE WHEN com.incluye_propina = 1 THEN com.propina ELSE 0 END) as propina
            ")
            ->groupBy('g.id', 'g.nombre', 'g.apellido')
            ->orderByDesc('total')
            ->get();

        $garzonDestacado = optional($rankingRaw->first())->nombre ?? '—';
        $ranTotal        = $rankingRaw->sum('total') ?: 1;

        $ranking = $rankingRaw->map(fn ($r) => [
            'nombre'     => $r->nombre,
            'comandas'   => (int)   $r->comandas,
            'mesas'      => (int)   $r->mesas,
            'comensales' => (int)   $r->comensales,
            'total'      => (float) $r->total,
            'porcentaje' => round(($r->total / $ranTotal) * 100, 1),
            'propina'    => (float) $r->propina,
        ])->values()->all();

        // --- tendencia diaria ---
        $tendenciaRaw = DB::table('comandas as com')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->when($garzonId, fn ($q) => $q->where('com.garzon_id', $garzonId))
            ->selectRaw('DATE(com.fecha_cierre) as fecha, COUNT(*) as comandas, SUM(com.total) as total')
            ->groupBy(DB::raw('DATE(com.fecha_cierre)'))
            ->orderBy('fecha')
            ->get();

        $tendencia = $tendenciaRaw->map(fn ($r) => [
            'fecha'    => Carbon::parse($r->fecha)->format('d/m'),
            'comandas' => (int)   $r->comandas,
            'total'    => (float) $r->total,
        ])->all();

        // --- propinas por garzón ---
        $propinasRaw = DB::table('comandas as com')
            ->join('garzones as g', 'g.id', '=', 'com.garzon_id')
            ->where('com.estado', 'CERRADA')
            ->where('com.incluye_propina', 1)
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->when($garzonId, fn ($q) => $q->where('com.garzon_id', $garzonId))
            ->selectRaw("
                CONCAT(g.nombre, ' ', g.apellido) as nombre,
                COUNT(*) as comandas,
                SUM(com.propina) as propina,
                AVG(com.propina) as promedio
            ")
            ->groupBy('g.id', 'g.nombre', 'g.apellido')
            ->orderByDesc('propina')
            ->get();

        $propinas = $propinasRaw->map(fn ($r) => [
            'nombre'   => $r->nombre,
            'comandas' => (int)   $r->comandas,
            'propina'  => (float) $r->propina,
            'promedio' => (float) $r->promedio,
        ])->values()->all();

        // --- detalle de comandas ---
        $detalle = DB::table('comandas as com')
            ->leftJoin('garzones as g', 'g.id', '=', 'com.garzon_id')
            ->leftJoin('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->when($garzonId, fn ($q) => $q->where('com.garzon_id', $garzonId))
            ->selectRaw("
                com.numero_comanda as folio,
                DATE_FORMAT(com.fecha_cierre, '%d-%m-%Y %H:%i') as fecha_cierre,
                COALESCE(CONCAT(g.nombre, ' ', g.apellido), 'Sin garzón') as garzon,
                COALESCE(m.nombre, 'Sin mesa') as mesa,
                com.comensales,
                com.subtotal,
                CASE WHEN com.incluye_propina = 1 THEN com.propina ELSE 0 END as propina,
                com.total
            ")
            ->orderBy('com.fecha_cierre', 'desc')
            ->get()
            ->map(fn ($r) => [
                'folio'      => $r->folio,
                'fecha'      => $r->fecha_cierre,
                'garzon'     => $r->garzon,
                'mesa'       => $r->mesa,
                'comensales' => (int)   $r->comensales,
                'subtotal'   => (float) $r->subtotal,
                'propina'    => (float) $r->propina,
                'total'      => (float) $r->total,
            ])->all();

        return response()->json([
            'totalVentas'     => $totalVentas,
            'totalComandas'   => $totalComandas,
            'totalPropinas'   => $totalPropinas,
            'garzonDestacado' => $garzonDestacado,
            'garzones'        => $garzones,
            'ranking'         => $ranking,
            'tendencia'       => $tendencia,
            'propinas'        => $propinas,
            'detalle'         => $detalle,
        ]);
    }

    public function exportarVentasGarzon(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "ventas_garzon_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new GarzonVentasExport($desde, $hasta), $fileName);
    }

    public function exportarPropinasGarzon(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "propinas_garzon_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new GarzonPropinasExport($desde, $hasta), $fileName);
    }

    // ---------------------------------------------------------------
    // VENTAS POR MESA
    // ---------------------------------------------------------------

    public function indexMesa()
    {
        return view('reportes.vtas_mesa');
    }

    public function dataMesa(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde = Carbon::parse($request->desde)->startOfDay();
        $hasta = Carbon::parse($request->hasta)->endOfDay();

        // --- totales generales ---
        $totalesRow = DB::table('comandas as com')
            ->join('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw('COUNT(*) as comandas, SUM(com.total) as total, SUM(com.comensales) as comensales, AVG(com.total) as promedio')
            ->first();

        $totalVentas    = (float) ($totalesRow->total      ?? 0);
        $totalComandas  = (int)   ($totalesRow->comandas   ?? 0);
        $totalComensales = (int)  ($totalesRow->comensales ?? 0);
        $ticketPromedio = (float) ($totalesRow->promedio   ?? 0);

        // --- ranking por mesa ---
        $rankingRaw = DB::table('comandas as com')
            ->join('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw("
                m.id,
                m.nombre,
                m.capacidad,
                COUNT(*) as comandas,
                SUM(com.comensales) as comensales,
                SUM(com.total) as total,
                AVG(com.total) as promedio
            ")
            ->groupBy('m.id', 'm.nombre', 'm.capacidad')
            ->orderByDesc('total')
            ->get();

        $mesaDestacada = optional($rankingRaw->first())->nombre ?? '—';
        $ranTotal      = $rankingRaw->sum('total') ?: 1;

        $ranking = $rankingRaw->map(fn ($r) => [
            'nombre'     => $r->nombre,
            'capacidad'  => (int)   $r->capacidad,
            'comandas'   => (int)   $r->comandas,
            'comensales' => (int)   $r->comensales,
            'total'      => (float) $r->total,
            'porcentaje' => round(($r->total / $ranTotal) * 100, 1),
            'promedio'   => (float) $r->promedio,
        ])->values()->all();

        // --- distribución: donut por mesa (top 8 + resto) ---
        $donut = collect($ranking)->take(8)->map(fn ($r) => [
            'label' => $r['nombre'],
            'valor' => $r['total'],
        ])->values()->all();

        if (count($ranking) > 8) {
            $resto = collect($ranking)->slice(8)->sum('total');
            $donut[] = ['label' => 'Otras', 'valor' => (float) $resto];
        }

        // --- tendencia diaria ---
        $tendenciaRaw = DB::table('comandas as com')
            ->join('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw('DATE(com.fecha_cierre) as fecha, COUNT(*) as comandas, SUM(com.total) as total, SUM(com.comensales) as comensales')
            ->groupBy(DB::raw('DATE(com.fecha_cierre)'))
            ->orderBy('fecha')
            ->get();

        $tendencia = $tendenciaRaw->map(fn ($r) => [
            'fecha'      => Carbon::parse($r->fecha)->format('d/m'),
            'comandas'   => (int)   $r->comandas,
            'total'      => (float) $r->total,
            'comensales' => (int)   $r->comensales,
        ])->all();

        // --- detalle de comandas ---
        $detalle = DB::table('comandas as com')
            ->leftJoin('mesas as m', 'm.id', '=', 'com.mesa_id')
            ->where('com.estado', 'CERRADA')
            ->whereBetween('com.fecha_cierre', [$desde, $hasta])
            ->selectRaw("
                com.numero_comanda as folio,
                DATE_FORMAT(com.fecha_cierre, '%d-%m-%Y %H:%i') as fecha_cierre,
                COALESCE(m.nombre, 'Sin mesa') as mesa,
                com.comensales,
                com.subtotal,
                CASE WHEN com.incluye_propina = 1 THEN com.propina ELSE 0 END as propina,
                com.total
            ")
            ->orderBy('m.nombre')
            ->orderBy('com.fecha_cierre', 'desc')
            ->get()
            ->map(fn ($r) => [
                'folio'      => $r->folio,
                'fecha'      => $r->fecha_cierre,
                'mesa'       => $r->mesa,
                'comensales' => (int)   $r->comensales,
                'subtotal'   => (float) $r->subtotal,
                'propina'    => (float) $r->propina,
                'total'      => (float) $r->total,
            ])->all();

        return response()->json([
            'totalVentas'     => $totalVentas,
            'totalComandas'   => $totalComandas,
            'totalComensales' => $totalComensales,
            'ticketPromedio'  => $ticketPromedio,
            'mesaDestacada'   => $mesaDestacada,
            'ranking'         => $ranking,
            'donut'           => $donut,
            'tendencia'       => $tendencia,
            'detalle'         => $detalle,
        ]);
    }

    public function exportarMesa(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "ventas_mesa_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new MesaExport($desde, $hasta), $fileName);
    }

    // ---------------------------------------------------------------
    // PRODUCTOS MÁS VENDIDOS
    // ---------------------------------------------------------------

    public function indexProductosTop()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.prods_mas_vendidos', compact('tipoNegocio'));
    }

    public function dataProductosTop(Request $request)
    {
        $request->validate([
            'desde'        => 'required|date',
            'hasta'        => 'required|date|after_or_equal:desde',
            'categoria_id' => 'nullable|integer',
        ]);

        $desde       = Carbon::parse($request->desde)->startOfDay();
        $hasta       = Carbon::parse($request->hasta)->endOfDay();
        $categoriaId = $request->input('categoria_id') ?: null;

        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $diasPeriodo = max(1, (int) $desde->diffInDays($hasta) + 1);

        // Período anterior (misma duración, justo antes)
        $prevHasta = (clone $desde)->subDay()->endOfDay();
        $prevDesde = (clone $prevHasta)->subDays($diasPeriodo - 1)->startOfDay();

        $rankingActual   = $this->getRankingProds($desde, $hasta, $tipoNegocio, $categoriaId);
        $rankingAnterior = $this->getRankingProds($prevDesde, $prevHasta, $tipoNegocio, $categoriaId);

        $totalUnidades     = (float) $rankingActual->sum('unidades');
        $totalUnidadesPrev = (float) $rankingAnterior->sum('unidades');
        $variacionTotal    = $totalUnidadesPrev > 0
            ? round(($totalUnidades - $totalUnidadesPrev) / $totalUnidadesPrev * 100, 1)
            : null;

        // Mapa del período anterior: nombre → [rank, unidades]
        $mapaPrev = [];
        foreach ($rankingAnterior as $idx => $r) {
            $mapaPrev[$r->nombre] = ['rank' => $idx + 1, 'unidades' => (float) $r->unidades];
        }

        // Construir ranking final con todos los KPIs
        $ranking = [];
        foreach ($rankingActual as $idx => $r) {
            $rankAct    = $idx + 1;
            $prev       = $mapaPrev[$r->nombre] ?? null;
            $variacion  = $prev ? ($prev['unidades'] > 0
                ? round(((float)$r->unidades - $prev['unidades']) / $prev['unidades'] * 100, 1)
                : null)
                : null;
            $rankPrev   = $prev ? $prev['rank'] : null;
            $cambio     = $rankPrev !== null ? $rankPrev - $rankAct : null;
            $tieneStock = (bool) ($r->tiene_stock ?? true);
            $stock      = $tieneStock ? (float) ($r->stock ?? 0) : null;

            if (!$tieneStock || $stock === null) {
                $diasCob = null;
                $estado  = 'no_aplica';
            } else {
                $unidadesF = (float) $r->unidades;
                $diasCob   = $unidadesF > 0
                    ? round(($stock * $diasPeriodo) / $unidadesF, 1)
                    : null;
                if ($stock <= 0) {
                    $estado = 'critico';
                } elseif ($diasCob === null) {
                    $estado = 'no_aplica';
                } elseif ($diasCob < 3) {
                    $estado = 'critico';
                } elseif ($diasCob <= 7) {
                    $estado = 'riesgo';
                } else {
                    $estado = 'ok';
                }
            }

            $ranking[] = [
                'rankingActual'   => $rankAct,
                'nombre'          => $r->nombre,
                'codigo'          => $r->codigo ?? '',
                'categoria'       => $r->categoria ?? 'Sin categoría',
                'unidades'        => (float) $r->unidades,
                'participacion'   => $totalUnidades > 0 ? round((float)$r->unidades / $totalUnidades * 100, 1) : 0,
                'variacion'       => $variacion,
                'esNuevo'         => $rankPrev === null,
                'rankingAnterior' => $rankPrev,
                'cambioRanking'   => $cambio,
                'stock'           => $stock,
                'tieneStock'      => $tieneStock,
                'diasCobertura'   => $diasCob,
                'estado'          => $estado,
            ];
        }

        // KPIs globales
        $top10Unidades     = collect($ranking)->take(10)->sum('unidades');
        $participacionTop10 = $totalUnidades > 0 ? round($top10Unidades / $totalUnidades * 100, 1) : 0;
        $lider             = $ranking[0] ?? null;

        // Stock en riesgo (Top 20)
        $stockCritico = collect($ranking)
            ->filter(fn ($r) => in_array($r['estado'], ['critico', 'riesgo']) && $r['rankingActual'] <= 20)
            ->values()->all();

        // Nuevos y salidos del Top 10
        $top10Actual   = collect($ranking)->take(10)->pluck('nombre')->all();
        $top10Anterior = collect($rankingAnterior)->take(10)->map(fn ($r) => $r->nombre)->values()->all();
        $nuevosEnTop   = array_values(array_diff($top10Actual, $top10Anterior));
        $salieronDeTop = array_values(array_diff($top10Anterior, $top10Actual));

        // Tendencia Top 5 por día
        $top5Nombres = collect($ranking)->take(5)->pluck('nombre')->all();
        $tendenciaRaw = $this->getTendenciaTop5($desde, $hasta, $tipoNegocio, $top5Nombres);

        $fechasTend = $tendenciaRaw->pluck('fecha')->unique()->sort()->values();
        $tendencia  = [
            'fechas' => $fechasTend->map(fn ($f) => Carbon::parse($f)->format('d/m'))->values()->all(),
            'series' => array_map(function ($nombre) use ($tendenciaRaw, $fechasTend) {
                return [
                    'nombre' => $nombre,
                    'data'   => $fechasTend->map(function ($f) use ($tendenciaRaw, $nombre) {
                        $row = $tendenciaRaw->first(fn ($r) => $r->fecha === $f && $r->nombre === $nombre);
                        return $row ? (float) $row->unidades : 0;
                    })->values()->all(),
                ];
            }, $top5Nombres),
        ];

        // Categorías disponibles para el filtro
        $categorias = $this->getCategoriasConVentas($desde, $hasta, $tipoNegocio);

        // Hallazgos gerenciales
        $hallazgos = $this->generarHallazgosProductos(
            $ranking, $lider, $participacionTop10, $variacionTotal,
            count($stockCritico), count($nuevosEnTop)
        );

        return response()->json([
            'totalUnidades'       => $totalUnidades,
            'totalProductos'      => count($ranking),
            'liderNombre'         => $lider['nombre']        ?? '—',
            'liderUnidades'       => $lider['unidades']      ?? 0,
            'liderParticipacion'  => $lider['participacion'] ?? 0,
            'participacionTop10'  => $participacionTop10,
            'variacionTotal'      => $variacionTotal,
            'ranking'             => $ranking,
            'tendencia'           => $tendencia,
            'stockCritico'        => $stockCritico,
            'nuevosEnTop'         => $nuevosEnTop,
            'salieronDeTop'       => $salieronDeTop,
            'categorias'          => $categorias,
            'hallazgos'           => $hallazgos,
            'diasPeriodo'         => $diasPeriodo,
        ]);
    }

    public function exportarProductosTop(Request $request)
    {
        $request->validate([
            'desde'        => 'required|date',
            'hasta'        => 'required|date|after_or_equal:desde',
            'categoria_id' => 'nullable|integer',
        ]);

        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $categoriaId = $request->query('categoria_id') ?: null;
        $desdeFormat = Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "productos_mas_vendidos_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new ProductosMasVendidosExport($desde, $hasta, $categoriaId), $fileName);
    }

    // ---------------------------------------------------------------
    // HELPERS PRIVADOS — Productos más vendidos
    // ---------------------------------------------------------------

    private function getRankingProds(Carbon $desde, Carbon $hasta, string $tipoNegocio, ?int $categoriaId = null)
    {
        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->when($categoriaId, fn ($q) => $q->whereRaw('COALESCE(p.categoria_id, r.categoria_id) = ?', [$categoriaId]))
                ->selectRaw("
                    dc.producto_id,
                    dc.receta_id,
                    MAX(COALESCE(p.descripcion, r.nombre, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, r.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    MAX(CASE WHEN dc.producto_id IS NOT NULL THEN p.stock ELSE NULL END) as stock,
                    MAX(CASE WHEN dc.producto_id IS NOT NULL THEN 1 ELSE 0 END) as tiene_stock,
                    SUM(dc.cantidad) as unidades
                ")
                ->groupBy('dc.producto_id', 'dc.receta_id')
                ->orderByDesc(DB::raw('SUM(dc.cantidad)'))
                ->get();
        } else {
            // Líneas de productos (sin promo)
            $rowsProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->when($categoriaId, fn ($q) => $q->where('p.categoria_id', $categoriaId))
                ->selectRaw("
                    dv.producto_uuid as prod_key,
                    MAX(COALESCE(p.descripcion, dv.descripcion_producto, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    MAX(p.stock) as stock,
                    MAX(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) as tiene_stock,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy('dv.producto_uuid')->get();

            // Líneas de promociones
            $rowsPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->when($categoriaId, fn ($q) => $q->where('promo.categoria_id', $categoriaId))
                ->selectRaw("
                    CONCAT('promo_', promo.id) as prod_key,
                    MAX(promo.nombre) as nombre,
                    MAX(promo.codigo) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    NULL as stock,
                    0 as tiene_stock,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy('dv.promo_id', 'promo.id')
                ->get();

            return $rowsProductos->concat($rowsPromos)
                ->sortByDesc(fn ($r) => (float) $r->unidades)
                ->values();
        }
    }

    private function getTendenciaTop5(Carbon $desde, Carbon $hasta, string $tipoNegocio, array $top5)
    {
        if (empty($top5)) return collect();
        $placeholders = implode(',', array_fill(0, count($top5), '?'));

        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->whereRaw("COALESCE(p.descripcion, r.nombre, 'Sin nombre') IN ({$placeholders})", $top5)
                ->selectRaw("
                    DATE(com.fecha_cierre) as fecha,
                    dc.producto_id,
                    dc.receta_id,
                    MAX(COALESCE(p.descripcion, r.nombre, 'Sin nombre')) as nombre,
                    SUM(dc.cantidad) as unidades
                ")
                ->groupBy(DB::raw('DATE(com.fecha_cierre)'), 'dc.producto_id', 'dc.receta_id')
                ->orderBy('fecha')
                ->get();
        } else {
            return DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereRaw("COALESCE(promo.nombre, p.descripcion, dv.descripcion_producto, 'Sin nombre') IN ({$placeholders})", $top5)
                ->selectRaw("
                    DATE(v.fecha_venta) as fecha,
                    COALESCE(CONCAT('promo_', promo.id), dv.producto_uuid, dv.descripcion_producto) as prod_key,
                    MAX(COALESCE(promo.nombre, p.descripcion, dv.descripcion_producto, 'Sin nombre')) as nombre,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy(
                    DB::raw('DATE(v.fecha_venta)'),
                    DB::raw("COALESCE(CONCAT('promo_', promo.id), dv.producto_uuid, dv.descripcion_producto)")
                )
                ->orderBy('fecha')
                ->get();
        }
    }

    private function getCategoriasConVentas(Carbon $desde, Carbon $hasta, string $tipoNegocio): array
    {
        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->whereNotNull(DB::raw('COALESCE(p.categoria_id, r.categoria_id)'))
                ->selectRaw('COALESCE(p.categoria_id, r.categoria_id) as id, MAX(cat.descripcion_categoria) as nombre')
                ->groupBy(DB::raw('COALESCE(p.categoria_id, r.categoria_id)'))
                ->orderBy('nombre')
                ->get()
                ->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre])
                ->all();
        } else {
            $fromProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->whereNotNull('p.categoria_id')
                ->selectRaw('p.categoria_id as id, MAX(cat.descripcion_categoria) as nombre')
                ->groupBy('p.categoria_id')
                ->get();

            $fromPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->whereNotNull('promo.categoria_id')
                ->selectRaw('promo.categoria_id as id, MAX(cat.descripcion_categoria) as nombre')
                ->groupBy('promo.categoria_id')
                ->get();

            return $fromProductos->concat($fromPromos)
                ->unique('id')
                ->sortBy('nombre')
                ->map(fn ($r) => ['id' => $r->id, 'nombre' => $r->nombre])
                ->values()
                ->all();
        }
    }

    private function generarHallazgosProductos(
        array $ranking, ?array $lider, float $participacionTop10,
        ?float $variacionTotal, int $nRiesgoStock, int $nNuevos
    ): array {
        $hallazgos = [];

        if ($lider) {
            $hallazgos[] = [
                'tipo'  => 'info',
                'texto' => "\"{$lider['nombre']}\" lidera con " .
                    number_format($lider['unidades'], 0, ',', '.') .
                    " unidades y el {$lider['participacion']}% de participación total.",
            ];
        }

        if ($participacionTop10 > 0) {
            $hallazgos[] = [
                'tipo'  => 'info',
                'texto' => "El Top 10 concentra el {$participacionTop10}% de todas las unidades vendidas.",
            ];
        }

        if ($variacionTotal !== null) {
            $dir  = $variacionTotal >= 0 ? 'Crecimiento' : 'Caída';
            $abs  = abs($variacionTotal);
            $tipo = $variacionTotal >= 0 ? 'ok' : 'warning';
            $hallazgos[] = ['tipo' => $tipo, 'texto' => "{$dir} total del {$abs}% en unidades vs el período anterior."];
        }

        if ($nRiesgoStock > 0) {
            $s = $nRiesgoStock === 1 ? 'producto' : 'productos';
            $hallazgos[] = [
                'tipo'  => 'critico',
                'texto' => "{$nRiesgoStock} {$s} del Top 20 presentan riesgo de quiebre de stock.",
            ];
        }

        $masSubio = collect($ranking)->filter(fn ($r) => ($r['cambioRanking'] ?? 0) >= 2)
            ->sortByDesc('cambioRanking')->first();
        if ($masSubio) {
            $hallazgos[] = [
                'tipo'  => 'ok',
                'texto' => "\"{$masSubio['nombre']}\" sube {$masSubio['cambioRanking']} posiciones respecto al período anterior.",
            ];
        }

        $masCayo = collect($ranking)->filter(fn ($r) => ($r['cambioRanking'] ?? 0) <= -2)
            ->sortBy('cambioRanking')->first();
        if ($masCayo) {
            $caida  = abs($masCayo['cambioRanking']);
            $sufijo = $masCayo['variacion'] !== null ? ' (' . abs($masCayo['variacion']) . '% menos unidades)' : '';
            $hallazgos[] = [
                'tipo'  => 'warning',
                'texto' => "\"{$masCayo['nombre']}\" cae {$caida} posiciones{$sufijo}.",
            ];
        }

        if ($nNuevos > 0) {
            $s = $nNuevos === 1 ? 'producto nuevo entró' : 'productos nuevos entraron';
            $hallazgos[] = ['tipo' => 'info', 'texto' => "{$nNuevos} {$s} al Top 10 vs el período anterior."];
        }

        return $hallazgos;
    }

    // ---------------------------------------------------------------
    // PRODUCTOS MÁS RENTABLES
    // ---------------------------------------------------------------

    public function indexProductosRentables()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.prods_rentables', compact('tipoNegocio'));
    }

    public function dataProductosRentables(Request $request)
    {
        $request->validate([
            'desde'        => 'required|date',
            'hasta'        => 'required|date|after_or_equal:desde',
            'categoria_id' => 'nullable|integer',
        ]);

        $desde       = Carbon::parse($request->desde)->startOfDay();
        $hasta       = Carbon::parse($request->hasta)->endOfDay();
        $categoriaId = $request->input('categoria_id') ?: null;
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        $diasPeriodo = max(1, (int) $desde->diffInDays($hasta) + 1);
        $prevHasta   = (clone $desde)->subDay()->endOfDay();
        $prevDesde   = (clone $prevHasta)->subDays($diasPeriodo - 1)->startOfDay();

        $rankingActual   = $this->getRankingRentables($desde, $hasta, $tipoNegocio, $categoriaId);
        $rankingAnterior = $this->getRankingRentables($prevDesde, $prevHasta, $tipoNegocio, $categoriaId);

        // Mapas del período anterior
        $mapaPrev = [];
        foreach ($rankingAnterior as $idx => $r) {
            $ingPrev  = (float) $r->ingresos;
            $costPrev = (float) $r->costo_total;
            $utilPrev = $ingPrev - $costPrev;
            $mapaPrev[$r->nombre] = [
                'rank'     => $idx + 1,
                'utilidad' => $utilPrev,
                'margen'   => $ingPrev > 0 ? round($utilPrev / $ingPrev * 100, 1) : 0,
            ];
        }

        $totalIngresos = 0.0;
        $totalCosto    = 0.0;
        $ranking       = [];

        foreach ($rankingActual as $idx => $r) {
            $ingresos = (float) $r->ingresos;
            $costo    = (float) $r->costo_total;
            $utilidad = $ingresos - $costo;
            $margen   = $ingresos > 0 ? round($utilidad / $ingresos * 100, 1) : 0;
            $totalIngresos += $ingresos;
            $totalCosto    += $costo;

            $prev       = $mapaPrev[$r->nombre] ?? null;
            $varUtil    = $prev ? ($prev['utilidad'] != 0
                ? round(($utilidad - $prev['utilidad']) / abs($prev['utilidad']) * 100, 1)
                : null)
                : null;
            $varMargen  = $prev ? round($margen - $prev['margen'], 1) : null;
            $rankPrev   = $prev ? $prev['rank'] : null;
            $cambioRank = $rankPrev !== null ? $rankPrev - ($idx + 1) : null;

            if ($margen >= 40) {
                $semaforo = 'excelente';
            } elseif ($margen >= 20) {
                $semaforo = 'bueno';
            } elseif ($margen >= 5) {
                $semaforo = 'bajo';
            } else {
                $semaforo = 'critico';
            }

            $ranking[] = [
                'rankingActual'   => $idx + 1,
                'nombre'          => $r->nombre,
                'codigo'          => $r->codigo ?? '',
                'categoria'       => $r->categoria ?? 'Sin categoría',
                'unidades'        => (float) $r->unidades,
                'ingresos'        => $ingresos,
                'costo'           => $costo,
                'utilidad'        => $utilidad,
                'margen'          => $margen,
                'semaforo'        => $semaforo,
                'varUtilidad'     => $varUtil,
                'varMargen'       => $varMargen,
                'esNuevo'         => $rankPrev === null,
                'rankingAnterior' => $rankPrev,
                'cambioRanking'   => $cambioRank,
            ];
        }

        $totalUtilidad = $totalIngresos - $totalCosto;
        $margenGlobal  = $totalIngresos > 0 ? round($totalUtilidad / $totalIngresos * 100, 1) : 0;
        $lider         = $ranking[0] ?? null;

        // Top 3 por margen % (mínimo 5 unidades)
        $estrellas = collect($ranking)
            ->filter(fn ($r) => $r['unidades'] >= 5)
            ->sortByDesc('margen')
            ->take(5)
            ->values()->all();

        // Alertas: margen crítico
        $alertasCritico = collect($ranking)
            ->filter(fn ($r) => $r['semaforo'] === 'critico')
            ->values()->all();

        // Categorías disponibles
        $categorias = $this->getCategoriasConVentas($desde, $hasta, $tipoNegocio);

        // Distribución utilidad por categoría (donut)
        $distCategorias = $this->getDistribucionCategorias($desde, $hasta, $tipoNegocio, $categoriaId);

        // Hallazgos
        $hallazgos = $this->generarHallazgosRentables(
            $ranking, $lider, $margenGlobal, count($alertasCritico)
        );

        return response()->json([
            'totalIngresos'    => $totalIngresos,
            'totalCosto'       => $totalCosto,
            'totalUtilidad'    => $totalUtilidad,
            'margenGlobal'     => $margenGlobal,
            'liderNombre'      => $lider['nombre']   ?? '—',
            'liderUtilidad'    => $lider['utilidad']  ?? 0,
            'liderMargen'      => $lider['margen']    ?? 0,
            'totalProductos'   => count($ranking),
            'ranking'          => $ranking,
            'estrellas'        => $estrellas,
            'alertasCritico'   => $alertasCritico,
            'distCategorias'   => $distCategorias,
            'categorias'       => $categorias,
            'hallazgos'        => $hallazgos,
            'diasPeriodo'      => $diasPeriodo,
        ]);
    }

    public function exportarProductosRentables(Request $request)
    {
        $request->validate([
            'desde'        => 'required|date',
            'hasta'        => 'required|date|after_or_equal:desde',
            'categoria_id' => 'nullable|integer',
        ]);

        $desde       = $request->query('desde');
        $hasta       = $request->query('hasta');
        $categoriaId = $request->query('categoria_id') ?: null;
        $desdeF      = Carbon::parse($desde)->format('d-m-Y');
        $hastaF      = Carbon::parse($hasta)->format('d-m-Y');
        $fileName    = "productos_rentables_{$desdeF}_al_{$hastaF}.xlsx";

        return Excel::download(new ProductosRentablesExport($desde, $hasta, $categoriaId), $fileName);
    }

    // ── Helpers privados — Rentables ──

    private function getRankingRentables(Carbon $desde, Carbon $hasta, string $tipoNegocio, ?int $categoriaId = null)
    {
        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->when($categoriaId, fn ($q) => $q->whereRaw('COALESCE(p.categoria_id, r.categoria_id) = ?', [$categoriaId]))
                ->selectRaw("
                    dc.producto_id,
                    dc.receta_id,
                    MAX(COALESCE(p.descripcion, r.nombre, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, r.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dc.cantidad) as unidades,
                    SUM(dc.subtotal) as ingresos,
                    SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0)) as costo_total
                ")
                ->groupBy('dc.producto_id', 'dc.receta_id')
                ->orderByDesc(DB::raw('SUM(dc.subtotal) - SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0))'))
                ->get();
        } else {
            // Productos individuales
            $rowsProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->when($categoriaId, fn ($q) => $q->where('p.categoria_id', $categoriaId))
                ->selectRaw("
                    dv.producto_uuid as prod_key,
                    MAX(COALESCE(p.descripcion, dv.descripcion_producto, 'Sin nombre')) as nombre,
                    MAX(COALESCE(p.codigo, '')) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dv.cantidad) as unidades,
                    SUM(dv.subtotal_linea) as ingresos,
                    SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0)) as costo_total
                ")
                ->groupBy('dv.producto_uuid')
                ->get();

            // Promociones
            $rowsPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->when($categoriaId, fn ($q) => $q->where('promo.categoria_id', $categoriaId))
                ->selectRaw("
                    CONCAT('promo_', promo.id) as prod_key,
                    MAX(promo.nombre) as nombre,
                    MAX(promo.codigo) as codigo,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dv.cantidad) as unidades,
                    SUM(dv.subtotal_linea) as ingresos,
                    SUM(dv.cantidad * promo.precio_costo) as costo_total
                ")
                ->groupBy('dv.promo_id', 'promo.id')
                ->get();

            return $rowsProductos->concat($rowsPromos)
                ->sortByDesc(fn ($r) => (float) $r->ingresos - (float) $r->costo_total)
                ->values();
        }
    }

    private function getDistribucionCategorias(Carbon $desde, Carbon $hasta, string $tipoNegocio, ?int $categoriaId = null): array
    {
        if ($tipoNegocio === 'RESTAURANT') {
            $rows = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->when($categoriaId, fn ($q) => $q->whereRaw('COALESCE(p.categoria_id, r.categoria_id) = ?', [$categoriaId]))
                ->selectRaw("
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dc.subtotal) - SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0)) as utilidad
                ")
                ->groupBy(DB::raw('COALESCE(p.categoria_id, r.categoria_id)'))
                ->having(DB::raw('SUM(dc.subtotal) - SUM(dc.cantidad * COALESCE(p.precio_compra_neto, r.precio_costo, 0))'), '>', 0)
                ->orderByDesc('utilidad')
                ->get();
        } else {
            $fromProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->whereNotNull('p.id')
                ->when($categoriaId, fn ($q) => $q->where('p.categoria_id', $categoriaId))
                ->selectRaw("
                    p.categoria_id as cat_id,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dv.subtotal_linea) - SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0)) as utilidad
                ")
                ->groupBy('p.categoria_id')
                ->having(DB::raw('SUM(dv.subtotal_linea) - SUM(dv.cantidad * COALESCE(p.precio_compra_neto, 0))'), '>', 0)
                ->get();

            $fromPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->when($categoriaId, fn ($q) => $q->where('promo.categoria_id', $categoriaId))
                ->selectRaw("
                    promo.categoria_id as cat_id,
                    MAX(COALESCE(cat.descripcion_categoria, 'Sin categoría')) as categoria,
                    SUM(dv.subtotal_linea) - SUM(dv.cantidad * promo.precio_costo) as utilidad
                ")
                ->groupBy('promo.categoria_id')
                ->having(DB::raw('SUM(dv.subtotal_linea) - SUM(dv.cantidad * promo.precio_costo)'), '>', 0)
                ->get();

            return $fromProductos->concat($fromPromos)
                ->groupBy('cat_id')
                ->map(fn ($grp) => [
                    'categoria' => $grp->first()->categoria,
                    'utilidad'  => round((float) $grp->sum('utilidad'), 0),
                ])
                ->filter(fn ($r) => $r['utilidad'] > 0)
                ->sortByDesc('utilidad')
                ->values()
                ->all();
        }

        return $rows->map(fn ($r) => [
            'categoria' => $r->categoria,
            'utilidad'  => round((float) $r->utilidad, 0),
        ])->all();
    }

    private function generarHallazgosRentables(
        array $ranking, ?array $lider, float $margenGlobal, int $nCriticos
    ): array {
        $hallazgos = [];

        // Margen global
        if ($margenGlobal >= 30) {
            $hallazgos[] = ['tipo' => 'ok', 'texto' => "Margen global del negocio: {$margenGlobal}%. Rentabilidad saludable."];
        } elseif ($margenGlobal >= 10) {
            $hallazgos[] = ['tipo' => 'warning', 'texto' => "Margen global del negocio: {$margenGlobal}%. Hay oportunidad de mejora en costos o precios."];
        } else {
            $hallazgos[] = ['tipo' => 'critico', 'texto' => "Margen global del negocio: {$margenGlobal}%. Nivel crítico — se recomienda revisión urgente de costos y precios."];
        }

        // Líder en utilidad
        if ($lider) {
            $hallazgos[] = [
                'tipo'  => 'info',
                'texto' => "\"{$lider['nombre']}\" genera la mayor utilidad bruta (\$" .
                    number_format($lider['utilidad'], 0, ',', '.') . ") con un margen del {$lider['margen']}%.",
            ];
        }

        // Producto con mejor margen
        $mejorMargen = collect($ranking)->filter(fn ($r) => $r['unidades'] >= 5)->sortByDesc('margen')->first();
        if ($mejorMargen && $mejorMargen['nombre'] !== ($lider['nombre'] ?? '')) {
            $hallazgos[] = [
                'tipo'  => 'ok',
                'texto' => "\"{$mejorMargen['nombre']}\" tiene el mejor margen del período ({$mejorMargen['margen']}%). Considere potenciar su venta.",
            ];
        }

        // Alertas críticos
        if ($nCriticos > 0) {
            $s = $nCriticos === 1 ? 'producto tiene' : 'productos tienen';
            $hallazgos[] = [
                'tipo'  => 'critico',
                'texto' => "{$nCriticos} {$s} margen por debajo del 5%. Revise sus costos de compra o ajuste el precio de venta.",
            ];
        }

        // Oportunidad: producto con alto volumen pero bajo margen
        $oportunidad = collect($ranking)
            ->filter(fn ($r) => in_array($r['semaforo'], ['bajo', 'critico']) && $r['rankingActual'] <= 5)
            ->first();
        if ($oportunidad) {
            $hallazgos[] = [
                'tipo'  => 'warning',
                'texto' => "\"{$oportunidad['nombre']}\" está entre los 5 más rentables en volumen pero con margen {$oportunidad['semaforo']} ({$oportunidad['margen']}%). Optimizar su precio o costo tendría alto impacto.",
            ];
        }

        return $hallazgos;
    }

    // ---------------------------------------------------------------
    // CATEGORÍAS MÁS VENDIDAS
    // ---------------------------------------------------------------

    public function exportarCategoriasVendidas(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde    = $request->query('desde');
        $hasta    = $request->query('hasta');
        $desdeF   = Carbon::parse($desde)->format('d-m-Y');
        $hastaF   = Carbon::parse($hasta)->format('d-m-Y');
        $fileName = "categorias_vendidas_{$desdeF}_al_{$hastaF}.xlsx";

        return Excel::download(new CategoriasVendidasExport($desde, $hasta), $fileName);
    }

    public function indexCategoriasVendidas()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.vtas_categoria', compact('tipoNegocio'));
    }

    public function dataCategoriasVendidas(Request $request)
    {
        $request->validate([
            'desde' => 'required|date',
            'hasta' => 'required|date|after_or_equal:desde',
        ]);

        $desde       = Carbon::parse($request->desde)->startOfDay();
        $hasta       = Carbon::parse($request->hasta)->endOfDay();
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        $diasPeriodo = max(1, (int) $desde->diffInDays($hasta) + 1);

        // Período anterior (igual duración)
        $prevHasta = (clone $desde)->subDay()->endOfDay();
        $prevDesde = (clone $prevHasta)->subDays($diasPeriodo - 1)->startOfDay();

        $actual   = $this->getRankingCategorias($desde, $hasta, $tipoNegocio);
        $anterior = $this->getRankingCategorias($prevDesde, $prevHasta, $tipoNegocio);

        $totalActual   = $actual->sum(fn ($r) => (float) $r->monto);
        $totalAnterior = $anterior->sum(fn ($r) => (float) $r->monto);

        // Mapa período anterior
        $mapaPrev = [];
        foreach ($anterior as $idx => $r) {
            $mapaPrev[$r->categoria] = ['rank' => $idx + 1, 'monto' => (float) $r->monto, 'unidades' => (float) $r->unidades];
        }

        // Construir ranking final
        $ranking = [];
        foreach ($actual as $idx => $r) {
            $rankAct  = $idx + 1;
            $monto    = (float) $r->monto;
            $unidades = (float) $r->unidades;
            $prev     = $mapaPrev[$r->categoria] ?? null;

            $variacionMonto = $prev && $prev['monto'] > 0
                ? round(($monto - $prev['monto']) / $prev['monto'] * 100, 1)
                : null;

            $cambioRank = $prev ? ($prev['rank'] - $rankAct) : null;

            $ranking[] = [
                'rank'           => $rankAct,
                'categoria'      => $r->categoria,
                'monto'          => $monto,
                'unidades'       => $unidades,
                'participacion'  => $totalActual > 0 ? round($monto / $totalActual * 100, 1) : 0,
                'variacion'      => $variacionMonto,
                'cambioRank'     => $cambioRank,
                'rankAnterior'   => $prev ? $prev['rank'] : null,
                'esNueva'        => $prev === null,
            ];
        }

        // Variación total ingresos
        $variacionTotal = $totalAnterior > 0
            ? round(($totalActual - $totalAnterior) / $totalAnterior * 100, 1)
            : null;

        // Tendencia top 5 por día
        $top5 = collect($ranking)->take(5)->pluck('categoria')->all();
        $tendencia = $this->getTendenciaCategorias($desde, $hasta, $tipoNegocio, $top5);

        // Hallazgos gerenciales
        $lider = $ranking[0] ?? null;
        $hallazgos = [];

        if ($lider) {
            $hallazgos[] = [
                'tipo'  => 'info',
                'texto' => "\"{$lider['categoria']}\" es la categoría líder con $" .
                    number_format($lider['monto'], 0, ',', '.') .
                    " en ventas ({$lider['participacion']}% del total).",
            ];
        }

        if ($variacionTotal !== null) {
            $dir  = $variacionTotal >= 0 ? 'Crecimiento' : 'Caída';
            $tipo = $variacionTotal >= 0 ? 'ok' : 'warning';
            $hallazgos[] = ['tipo' => $tipo, 'texto' => "{$dir} del " . abs($variacionTotal) . "% en ingresos totales vs el período anterior."];
        }

        $masSubio = collect($ranking)->filter(fn ($r) => ($r['cambioRank'] ?? 0) >= 2)->sortByDesc('cambioRank')->first();
        if ($masSubio) {
            $hallazgos[] = ['tipo' => 'ok', 'texto' => "\"{$masSubio['categoria']}\" sube {$masSubio['cambioRank']} posiciones respecto al período anterior."];
        }

        $mayor_caida = collect($ranking)->filter(fn ($r) => isset($r['variacion']) && $r['variacion'] < -10)->sortBy('variacion')->first();
        if ($mayor_caida) {
            $caida = abs($mayor_caida['variacion']);
            $hallazgos[] = ['tipo' => 'warning', 'texto' => "\"{$mayor_caida['categoria']}\" cayó un {$caida}% en ingresos vs el período anterior."];
        }

        $concentracion = collect($ranking)->take(3)->sum('participacion');
        if ($concentracion > 70) {
            $hallazgos[] = ['tipo' => 'info', 'texto' => "Las 3 primeras categorías concentran el {$concentracion}% de los ingresos."];
        }

        return response()->json([
            'totalMonto'     => $totalActual,
            'totalCategorias'=> count($ranking),
            'variacionTotal' => $variacionTotal,
            'lider'          => $lider,
            'ranking'        => $ranking,
            'tendencia'      => $tendencia,
            'hallazgos'      => $hallazgos,
            'diasPeriodo'    => $diasPeriodo,
        ]);
    }

    private function getRankingCategorias(Carbon $desde, Carbon $hasta, string $tipoNegocio)
    {
        if ($tipoNegocio === 'RESTAURANT') {
            return DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dc.subtotal) as monto,
                    SUM(dc.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->orderByDesc(DB::raw('SUM(dc.subtotal)'))
                ->get();
        } else {
            $fromProductos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->where(fn ($q) => $q->whereNull('dv.anulado')->orWhere('dv.anulado', false))
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dv.subtotal_linea) as monto,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            $fromPromos = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->where(fn ($q) => $q->whereNull('dv.anulado')->orWhere('dv.anulado', false))
                ->selectRaw("
                    COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
                    SUM(dv.subtotal_linea) as monto,
                    SUM(dv.cantidad) as unidades
                ")
                ->groupBy(DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            return $fromProductos->concat($fromPromos)
                ->groupBy('categoria')
                ->map(function ($rows, $cat) {
                    return (object) [
                        'categoria' => $cat,
                        'monto'     => $rows->sum(fn ($r) => (float) $r->monto),
                        'unidades'  => $rows->sum(fn ($r) => (float) $r->unidades),
                    ];
                })
                ->sortByDesc(fn ($r) => $r->monto)
                ->values();
        }
    }

    private function getTendenciaCategorias(Carbon $desde, Carbon $hasta, string $tipoNegocio, array $categorias)
    {
        if (empty($categorias)) return ['fechas' => [], 'series' => []];
        $placeholders = implode(',', array_fill(0, count($categorias), '?'));

        if ($tipoNegocio === 'RESTAURANT') {
            $raw = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->leftJoin('productos as p', 'p.id', '=', 'dc.producto_id')
                ->leftJoin('recetas as r', 'r.id', '=', 'dc.receta_id')
                ->leftJoin('categorias as cat', function ($join) {
                    $join->on('cat.id', '=', DB::raw('COALESCE(p.categoria_id, r.categoria_id)'));
                })
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$desde, $hasta])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->whereRaw("COALESCE(cat.descripcion_categoria, 'Sin categoría') IN ({$placeholders})", $categorias)
                ->selectRaw("DATE(com.fecha_cierre) as fecha, COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria, SUM(dc.subtotal) as monto")
                ->groupBy(DB::raw('DATE(com.fecha_cierre)'), DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->orderBy('fecha')->get();
        } else {
            $prodRaw = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->leftJoin('productos as p', 'p.uuid', '=', 'dv.producto_uuid')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNull('dv.promo_id')->whereNotNull('dv.producto_uuid')
                ->whereRaw("COALESCE(cat.descripcion_categoria, 'Sin categoría') IN ({$placeholders})", $categorias)
                ->selectRaw("DATE(v.fecha_venta) as fecha, COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria, SUM(dv.subtotal_linea) as monto")
                ->groupBy(DB::raw('DATE(v.fecha_venta)'), DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            $promoRaw = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->join('promociones as promo', 'promo.id', '=', 'dv.promo_id')
                ->leftJoin('categorias as cat', 'cat.id', '=', 'promo.categoria_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$desde, $hasta])
                ->whereNotNull('dv.promo_id')
                ->whereRaw("COALESCE(cat.descripcion_categoria, 'Sin categoría') IN ({$placeholders})", $categorias)
                ->selectRaw("DATE(v.fecha_venta) as fecha, COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria, SUM(dv.subtotal_linea) as monto")
                ->groupBy(DB::raw('DATE(v.fecha_venta)'), DB::raw("COALESCE(cat.descripcion_categoria, 'Sin categoría')"))
                ->get();

            $raw = $prodRaw->concat($promoRaw)
                ->groupBy(fn ($r) => $r->fecha . '|' . $r->categoria)
                ->map(function ($rows) {
                    $first = $rows->first();
                    return (object) ['fecha' => $first->fecha, 'categoria' => $first->categoria, 'monto' => $rows->sum(fn ($r) => (float) $r->monto)];
                })->values();
        }

        $fechas = $raw->pluck('fecha')->unique()->sort()->values();

        return [
            'fechas' => $fechas->map(fn ($f) => Carbon::parse($f)->format('d/m'))->values()->all(),
            'series' => array_map(function ($cat) use ($raw, $fechas) {
                return [
                    'nombre' => $cat,
                    'data'   => $fechas->map(function ($f) use ($raw, $cat) {
                        $row = $raw->first(fn ($r) => $r->fecha === $f && $r->categoria === $cat);
                        return $row ? (float) $row->monto : 0;
                    })->values()->all(),
                ];
            }, $categorias),
        ];
    }

    // ---------------------------------------------------------------
    // INVENTARIO
    // ---------------------------------------------------------------

    public function indexInventario()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.inventario', compact('tipoNegocio'));
    }

    public function exportarInventario()
    {
        $fecha    = Carbon::now()->format('d-m-Y');
        $fileName = "inventario_{$fecha}.xlsx";
        return Excel::download(new InventarioExport(), $fileName);
    }

    public function dataInventario()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));

        // Productos activos, no eliminados
        $query = DB::table('productos as p')
            ->leftJoin('categorias as cat', 'cat.id', '=', 'p.categoria_id')
            ->where('p.estado', 'Activo')
            ->whereNull('p.fec_eliminacion');

        // Insumos (tipo I) solo en RESTAURANT
        if ($tipoNegocio !== 'RESTAURANT') {
            $query->where('p.tipo', '<>', 'I');
        }

        $productos = $query->selectRaw("
            p.id, p.uuid, p.codigo, p.descripcion, p.tipo, p.unidad_medida,
            p.stock, p.stock_minimo, p.precio_compra_neto, p.precio_venta,
            cat.id as categoria_id,
            COALESCE(cat.descripcion_categoria, 'Sin categoría') as categoria,
            (p.stock * p.precio_compra_neto) as valor_inventario
        ")->get();

        // Ventas últimos 30 días por producto
        $hace30 = Carbon::now()->subDays(30)->startOfDay();
        $ahora  = Carbon::now()->endOfDay();

        if ($tipoNegocio === 'RESTAURANT') {
            $ventas30 = DB::table('detalle_comandas as dc')
                ->join('comandas as com', 'com.id', '=', 'dc.comanda_id')
                ->where('com.estado', 'CERRADA')
                ->whereBetween('com.fecha_cierre', [$hace30, $ahora])
                ->where('dc.estado', '!=', 'CANCELADO')
                ->whereNotNull('dc.producto_id')
                ->selectRaw('dc.producto_id, SUM(dc.cantidad) as vendido')
                ->groupBy('dc.producto_id')
                ->get()
                ->keyBy('producto_id');
        } else {
            $ventas30 = DB::table('detalles_ventas as dv')
                ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
                ->join('cajas as ca', 'ca.id', '=', 'v.caja_id')
                ->where('ca.tipo_caja', 'ALMACEN')
                ->where('v.estado', '!=', 'anulada')
                ->whereBetween('v.fecha_venta', [$hace30, $ahora])
                ->whereNull('dv.promo_id')
                ->whereNotNull('dv.producto_uuid')
                ->where(fn ($q) => $q->whereNull('dv.anulado')->orWhere('dv.anulado', false))
                ->selectRaw('dv.producto_uuid, SUM(dv.cantidad) as vendido')
                ->groupBy('dv.producto_uuid')
                ->get()
                ->keyBy('producto_uuid');
        }

        $resultado  = [];
        $agotados   = 0;
        $criticos   = 0;
        $totalValor = 0;

        foreach ($productos as $p) {
            $stock    = (float) $p->stock;
            $stockMin = (float) $p->stock_minimo;
            $valor    = (float) $p->valor_inventario;
            $totalValor += $valor;

            $v30Row    = $tipoNegocio === 'RESTAURANT'
                ? ($ventas30[$p->id]   ?? null)
                : ($ventas30[$p->uuid] ?? null);
            $vendido30 = $v30Row ? (float) $v30Row->vendido : 0;

            // Días cobertura
            $diasCobertura = null;
            if ($vendido30 > 0 && $stock > 0) {
                $diasCobertura = (int) round($stock / ($vendido30 / 30));
            }

            // Estado
            if ($stock <= 0) {
                $estado = 'Agotado';
                $agotados++;
            } elseif ($stock <= $stockMin) {
                $estado = 'Crítico';
                $criticos++;
            } elseif ($stockMin > 0 && $stock > $stockMin * 5 && $vendido30 == 0) {
                $estado = 'Sobrestock';
            } else {
                $estado = 'Normal';
            }

            $resultado[] = [
                'id'               => $p->id,
                'codigo'           => $p->codigo,
                'nombre'           => $p->descripcion,
                'categoria'        => $p->categoria,
                'categoria_id'     => $p->categoria_id,
                'tipo'             => $p->tipo,
                'unidad'           => $p->unidad_medida,
                'stock'            => $stock,
                'stock_minimo'     => $stockMin,
                'precio_costo'     => (float) $p->precio_compra_neto,
                'precio_venta'     => (float) $p->precio_venta,
                'valor_inventario' => $valor,
                'ventas_30d'       => $vendido30,
                'dias_cobertura'   => $diasCobertura,
                'estado'           => $estado,
            ];
        }

        // Distribución por categoría (capital)
        $distCategorias = collect($resultado)
            ->groupBy('categoria')
            ->map(fn ($rows, $cat) => [
                'categoria' => $cat,
                'valor'     => round($rows->sum(fn ($r) => $r['valor_inventario'])),
                'cantidad'  => $rows->count(),
            ])
            ->sortByDesc('valor')
            ->values()
            ->all();

        // Lista única de categorías para filtro
        $categorias = collect($resultado)->pluck('categoria')->unique()->sort()->values()->all();

        // Hallazgos
        $totalProductos = count($resultado);
        $hallazgos = [];

        $hallazgos[] = ['tipo' => 'info',
            'texto' => "El inventario tiene {$totalProductos} productos activos con un valor total de $" .
                number_format($totalValor, 0, ',', '.') . '.'];

        if ($agotados > 0) {
            $hallazgos[] = ['tipo' => 'bad',
                'texto' => "{$agotados} producto(s) agotados sin stock disponible."];
        }

        if ($criticos > 0) {
            $hallazgos[] = ['tipo' => 'warning',
                'texto' => "{$criticos} producto(s) con stock bajo el mínimo configurado."];
        }

        $sobrestock = collect($resultado)->filter(fn ($r) => $r['estado'] === 'Sobrestock')->count();
        if ($sobrestock > 0) {
            $hallazgos[] = ['tipo' => 'info',
                'texto' => "{$sobrestock} producto(s) con posible sobrestock (más de 5× el mínimo sin ventas en 30 días)."];
        }

        $sinRotacion = collect($resultado)->filter(fn ($r) => $r['ventas_30d'] == 0 && $r['stock'] > 0)->count();
        if ($sinRotacion > 0) {
            $hallazgos[] = ['tipo' => 'warning',
                'texto' => "{$sinRotacion} producto(s) con stock disponible pero sin ventas en los últimos 30 días."];
        }

        if (!empty($distCategorias) && $totalValor > 0) {
            $top = $distCategorias[0];
            $pct = round($top['valor'] / $totalValor * 100, 1);
            $hallazgos[] = ['tipo' => 'info',
                'texto' => "La categoría \"{$top['categoria']}\" concentra el {$pct}% del valor del inventario ($" .
                    number_format($top['valor'], 0, ',', '.') . ').'];
        }

        return response()->json([
            'valorTotal'      => round($totalValor),
            'totalProductos'  => $totalProductos,
            'agotados'        => $agotados,
            'criticos'        => $criticos,
            'productos'       => $resultado,
            'distCategorias'  => $distCategorias,
            'categorias'      => $categorias,
            'hallazgos'       => $hallazgos,
        ]);
    }

    // =========================================================
    // HISTORIAL DE PRECIO DE PRODUCTO
    // =========================================================

    public function indexHistorialPrecio()
    {
        $tipoNegocio = strtoupper(trim((string) Globales::where('nom_var', 'TIPO_NEGOCIO')->value('valor_var')));
        return view('reportes.historial_precio', compact('tipoNegocio'));
    }

    /**
     * Historial de cambios de precio (tabla historial_precios).
     * GET /reportes/historial_precio/data
     * Params: tipo (PRODUCTO|RECETA|PROMOCION), entidad_id (int)
     */
    public function dataHistorialPrecio(Request $request)
    {
        $tipo = strtoupper($request->input('tipo', 'PRODUCTO'));
        $entidadId = (int) $request->input('entidad_id', 0);

        if (!in_array($tipo, ['PRODUCTO', 'RECETA', 'PROMOCION']) || $entidadId <= 0) {
            return response()->json(['historial' => [], 'nombre' => '']);
        }

        $historial = DB::table('historial_precios')
            ->where('entidad_tipo', $tipo)
            ->where('entidad_id', $entidadId)
            ->orderBy('fecha_cambio')
            ->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'campo'           => $r->campo,
                'precio_anterior' => $r->precio_anterior,
                'precio_nuevo'    => (float) $r->precio_nuevo,
                'variacion'       => $r->precio_anterior !== null
                    ? round((((float)$r->precio_nuevo - (float)$r->precio_anterior) / max((float)$r->precio_anterior, 1)) * 100, 1)
                    : null,
                'usuario'         => $r->usuario,
                'fecha_cambio'    => $r->fecha_cambio,
            ]);

        // Nombre de la entidad
        $nombre = match ($tipo) {
            'PRODUCTO'  => DB::table('productos')->where('id', $entidadId)->value('descripcion') ?? '',
            'RECETA'    => DB::table('recetas')->where('id', $entidadId)->value('nombre') ?? '',
            'PROMOCION' => DB::table('promociones')->where('id', $entidadId)->value('nombre') ?? '',
            default     => '',
        };

        return response()->json([
            'historial' => $historial,
            'nombre'    => $nombre,
        ]);
    }

    /**
     * Historial de precio de compra: facturas + boletas.
     * GET /reportes/historial_precio/compras
     * Params: producto_id (int)
     */
    public function dataHistorialCompras(Request $request)
    {
        $productoId = (int) $request->input('producto_id', 0);

        if ($productoId <= 0) {
            return response()->json(['compras' => [], 'nombre' => '']);
        }

        // Facturas
        $facturas = DB::table('detalle_factura as df')
            ->join('facturas as f', 'f.num_factura', '=', 'df.num_factura')
            ->join('proveedores as pr', 'pr.id', '=', 'f.prov_id')
            ->where('df.id_prod', $productoId)
            ->selectRaw("
                'Factura' as tipo_doc,
                f.num_factura as num_doc,
                f.fecha_doc as fecha,
                pr.razon_social as proveedor,
                df.cantidad,
                df.precio as precio_unitario,
                df.descuento
            ")
            ->orderBy('f.fecha_doc');

        // Boletas
        $boletas = DB::table('detalle_boleta as db')
            ->join('boletas as b', 'b.num_boleta', '=', 'db.num_boleta')
            ->join('proveedores as pr', 'pr.id', '=', 'b.prov_id')
            ->where('db.id_prod', $productoId)
            ->selectRaw("
                'Boleta' as tipo_doc,
                b.num_boleta as num_doc,
                b.fecha_boleta as fecha,
                pr.razon_social as proveedor,
                db.cantidad,
                db.precio as precio_unitario,
                db.descu as descuento
            ")
            ->orderBy('b.fecha_boleta');

        $compras = $facturas->unionAll($boletas)
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => [
                'tipo_doc'       => $r->tipo_doc,
                'num_doc'        => $r->num_doc,
                'fecha'          => $r->fecha,
                'proveedor'      => $r->proveedor,
                'cantidad'       => (float) $r->cantidad,
                'precio_unitario' => (float) $r->precio_unitario,
                'descuento'      => (float) $r->descuento,
            ]);

        $nombre = DB::table('productos')->where('id', $productoId)->value('descripcion') ?? '';

        return response()->json([
            'compras' => $compras,
            'nombre'  => $nombre,
        ]);
    }

    public function exportarHistorialPrecio(Request $request)
    {
        $tipo      = strtoupper($request->input('tipo', 'PRODUCTO'));
        $entidadId = (int) $request->input('entidad_id', 0);
        $fecha     = Carbon::now()->format('d-m-Y');
        $fileName  = "historial_precio_{$fecha}.xlsx";
        return Excel::download(new HistorialPrecioExport($tipo, $entidadId), $fileName);
    }

    public function searchEntidadPrecio(Request $request)
    {
        $tipo = strtoupper($request->input('tipo', 'PRODUCTO'));
        $term = $request->input('q', '');

        if ($tipo === 'RECETA') {
            $data = Receta::where('estado', 'Activo')
                ->where(function ($q) use ($term) {
                    $q->where('codigo', 'like', "%{$term}%")
                      ->orWhere('nombre', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get(['id', 'codigo', 'nombre']);
            return response()->json($data->map(fn($r) => [
                'id'          => $r->id,
                'codigo'      => $r->codigo,
                'descripcion' => $r->nombre,
            ]));
        }

        if ($tipo === 'PROMOCION') {
            $data = Promocion::where('estado', 'Activo')
                ->where(function ($q) use ($term) {
                    $q->where('codigo', 'like', "%{$term}%")
                      ->orWhere('nombre', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get(['id', 'codigo', 'nombre']);
            return response()->json($data->map(fn($r) => [
                'id'          => $r->id,
                'codigo'      => $r->codigo,
                'descripcion' => $r->nombre,
            ]));
        }

        // PRODUCTO (default)
        $data = Producto::where('tipo', '<>', 'I')
            ->where('estado', 'Activo')
            ->where(function ($q) use ($term) {
                $q->where('codigo', 'like', "%{$term}%")
                  ->orWhere('descripcion', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'codigo', 'descripcion']);
        return response()->json($data);
    }
}

