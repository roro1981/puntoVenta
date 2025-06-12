<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ReporteExport;
use App\Services\ReportesService;
use App\Exports\MovimientosExport;
use Maatwebsite\Excel\Facades\Excel;

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

        $desdeFormat = \Carbon\Carbon::parse($desde)->format('d-m-Y');
        $hastaFormat = \Carbon\Carbon::parse($hasta)->format('d-m-Y');

        $productoNombreSanitizado = Str::slug($productoNombre, '_');

        $fileName = "movimientos_{$productoNombreSanitizado}_{$desdeFormat}_al_{$hastaFormat}.xlsx";

        return Excel::download(new MovimientosExport($tipo, $uuid, $desde, $hasta), $fileName);
    }
}
