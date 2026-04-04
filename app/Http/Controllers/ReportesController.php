<?php

namespace App\Http\Controllers;

use App\Models\Producto;
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
        $productos = Producto::where('estado', 'Activo')->where('tipo', '<>', 'S')->get();
        return view('reportes.movimientos_productos', compact("productos"));
    }

    public function traeMovimientos(Request $request, ReportesService $service)
    {
        $html = $service->traeMovimientos(
            $request->idp,
            $request->tipo_mov,
            $request->fec_desde,
            $request->fec_hasta
        );

        return response($html);
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
}
