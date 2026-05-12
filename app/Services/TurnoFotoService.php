<?php

namespace App\Services;

use App\Mail\TurnoCierreResumenMail;
use App\Models\Caja;
use App\Models\Comanda;
use App\Models\CorporateData;
use App\Models\RetiroCaja;
use App\Models\DetalleVenta;
use App\Models\FormaPagoVenta;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TurnoFotoService
{
    public function enviarResumenCorreo(Caja $caja, string $moduloOrigen = 'ALMACEN'): array
    {
        $caja->refresh();
        $caja->loadMissing('usuario');

        $fechaApertura = Carbon::parse($caja->fecha_apertura);
        $fechaCierre = Carbon::parse($caja->fecha_cierre ?? now());
        $esRestaurant = strtoupper((string) $caja->tipo_caja) === 'RESTAURANT';

        $mailDestino = CorporateData::where('item', 'mail_enterprise')->value('description_item');
        if (empty($mailDestino)) {
            return [
                'enviado' => false,
                'destino' => null,
                'error' => 'No hay correo corporativo configurado (mail_enterprise).',
            ];
        }

        $ventasIds = Venta::where('caja_id', $caja->id)
            ->where('estado', 'completada')
            ->pluck('id');

        $totalVentasBruto = (float) Venta::whereIn('id', $ventasIds)->sum('total');
        $totalVentasReal = (float) DetalleVenta::whereIn('venta_id', $ventasIds)->sum('subtotal_linea');
        $totalPropinasTurno = max(0.0, $totalVentasBruto - $totalVentasReal);
        $retirosTurno = $this->retirosCajaTurno($caja->id);
        $totalCajaTurnoBruto = (float) $caja->monto_inicial + $totalVentasReal + $totalPropinasTurno;
        $totalCajaTurno = $totalCajaTurnoBruto - (float) ($retirosTurno['total'] ?? 0);
        $cantidadVentas = (int) Venta::whereIn('id', $ventasIds)->count();

        $desgloseFormas = $this->obtenerDesgloseRealFormasPago($ventasIds);
        $formaDominante = $this->formaDominante($desgloseFormas);
        $productoEstrella = $this->productoEstrella($ventasIds);

        $totalAyerMismoTipo = $this->totalAyerPorTipoCaja((string) $caja->tipo_caja, $fechaCierre);
        $variacionVsAyer = $totalAyerMismoTipo > 0
            ? (($totalVentasReal - $totalAyerMismoTipo) / $totalAyerMismoTipo) * 100
            : null;

        $corporateData = CorporateData::pluck('description_item', 'item')->toArray();
        $empresa = $corporateData['fantasy_name_enterprise']
            ?? $corporateData['name_enterprise']
            ?? 'Mi Empresa';

        $cajeroNombre = (string) (optional($caja->usuario)->name_complete
            ?? optional($caja->usuario)->name
            ?? 'Cajero');

        $duracionTurno = $fechaApertura->diff($fechaCierre);
        $duracionFormato = $duracionTurno->h . 'h ' . $duracionTurno->i . 'm ' . $duracionTurno->s . 's';

        $data = [
            'empresa' => $empresa,
            'caja' => $caja,
            'cajero_nombre' => $cajeroNombre,
            'modulo_origen' => strtoupper(trim($moduloOrigen)),
            'fecha_apertura' => $fechaApertura,
            'fecha_cierre' => $fechaCierre,
            'duracion' => $duracionFormato,
            'observaciones' => trim((string) ($caja->observaciones ?? '')),
            'total_ventas' => $totalVentasReal,
            'total_ventas_bruto' => $totalVentasBruto,
            'total_propinas_turno' => $totalPropinasTurno,
            'monto_caja_inicial' => (float) $caja->monto_inicial,
            'total_caja_turno_bruto' => $totalCajaTurnoBruto,
            'total_caja_turno' => $totalCajaTurno,
            'cantidad_ventas' => $cantidadVentas,
            'forma_dominante' => $formaDominante,
            'producto_estrella' => $productoEstrella,
            'total_ayer' => $totalAyerMismoTipo,
            'variacion_vs_ayer' => $variacionVsAyer,
            'desglose_formas' => $desgloseFormas,
            'diferencia' => (float) $caja->diferencia,
            'productos_resumen' => $this->productosVendidosResumen($ventasIds),
            'es_restaurant' => $esRestaurant,
            'retiros' => $retirosTurno,
        ];

        if ($esRestaurant) {
            $data['restaurant'] = $this->resumenRestaurantTurno($fechaApertura, $fechaCierre, $totalVentasReal);
        }

        try {
            Mail::to($mailDestino)->send(new TurnoCierreResumenMail($data));

            return [
                'enviado' => true,
                'destino' => $mailDestino,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('[TurnoCierre] Error enviando resumen por correo: ' . $e->getMessage(), [
                'caja_id' => $caja->id,
                'modulo' => $moduloOrigen,
                'destino' => $mailDestino,
            ]);

            return [
                'enviado' => false,
                'destino' => $mailDestino,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function obtenerDesgloseRealFormasPago($ventasIds): array
    {
        if ($ventasIds->isEmpty()) {
            return [
                'EFECTIVO' => 0.0,
                'TARJETA_DEBITO' => 0.0,
                'TARJETA_CREDITO' => 0.0,
                'TRANSFERENCIA' => 0.0,
                'CHEQUE' => 0.0,
            ];
        }

        $directo = Venta::whereIn('id', $ventasIds)
            ->where('forma_pago', '!=', 'MIXTO')
            ->selectRaw('forma_pago, SUM(total) as total')
            ->groupBy('forma_pago')
            ->pluck('total', 'forma_pago');

        $ventasMixtasIds = Venta::whereIn('id', $ventasIds)
            ->where('forma_pago', 'MIXTO')
            ->pluck('id');

        $mixto = $ventasMixtasIds->isEmpty()
            ? collect()
            : FormaPagoVenta::whereIn('venta_id', $ventasMixtasIds)
                ->selectRaw('forma_pago, SUM(monto) as total')
                ->groupBy('forma_pago')
                ->pluck('total', 'forma_pago');

        $formas = ['EFECTIVO', 'TARJETA_DEBITO', 'TARJETA_CREDITO', 'TRANSFERENCIA', 'CHEQUE'];
        $desglose = [];
        foreach ($formas as $forma) {
            $desglose[$forma] = (float) ($directo[$forma] ?? 0) + (float) ($mixto[$forma] ?? 0);
        }

        return $desglose;
    }

    private function formaDominante(array $desglose): array
    {
        if (empty($desglose)) {
            return ['forma' => 'N/A', 'monto' => 0.0];
        }

        arsort($desglose);
        $forma = (string) array_key_first($desglose);
        $monto = (float) ($desglose[$forma] ?? 0);

        return [
            'forma' => str_replace('_', ' ', $forma),
            'monto' => $monto,
        ];
    }

    private function productoEstrella($ventasIds): array
    {
        if ($ventasIds->isEmpty()) {
            return ['nombre' => 'Sin ventas', 'cantidad' => 0.0];
        }

        $top = DetalleVenta::whereIn('venta_id', $ventasIds)
            ->select('descripcion_producto', DB::raw('SUM(cantidad) as total_cantidad'))
            ->groupBy('descripcion_producto')
            ->orderByDesc('total_cantidad')
            ->first();

        if (!$top) {
            return ['nombre' => 'Sin ventas', 'cantidad' => 0.0];
        }

        return [
            'nombre' => (string) $top->descripcion_producto,
            'cantidad' => (float) $top->total_cantidad,
        ];
    }

    private function totalAyerPorTipoCaja(string $tipoCaja, Carbon $fechaCierre): float
    {
        $ayer = $fechaCierre->copy()->subDay()->toDateString();

        return (float) Venta::query()
            ->join('detalles_ventas', 'detalles_ventas.venta_id', '=', 'ventas.id')
            ->join('cajas', 'cajas.id', '=', 'ventas.caja_id')
            ->where('ventas.estado', 'completada')
            ->where('cajas.tipo_caja', strtoupper(trim($tipoCaja)))
            ->whereDate('ventas.fecha_venta', $ayer)
            ->sum('detalles_ventas.subtotal_linea');
    }

    private function productosVendidosResumen($ventasIds, int $limit = 30): array
    {
        if ($ventasIds->isEmpty()) {
            return [];
        }

        return DetalleVenta::whereIn('venta_id', $ventasIds)
            ->select(
                'descripcion_producto',
                DB::raw('SUM(cantidad) as total_cantidad'),
                DB::raw('SUM(subtotal_linea) as total_monto')
            )
            ->groupBy('descripcion_producto')
            ->orderByDesc('total_cantidad')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'producto' => (string) $row->descripcion_producto,
                'cantidad' => (float) $row->total_cantidad,
                'monto' => (float) $row->total_monto,
            ])
            ->values()
            ->all();
    }

    private function retirosCajaTurno(int $cajaId): array
    {
        $retiros = RetiroCaja::where('caja_id', $cajaId)
            ->orderBy('id')
            ->get(['monto', 'motivo', 'creado_por']);

        $total = (float) $retiros->sum('monto');
        $detalle = $retiros->map(fn($r) => [
            'monto'  => (float) $r->monto,
            'motivo' => (string) ($r->motivo ?? 'Sin motivo'),
        ])->values()->all();

        return [
            'total'   => $total,
            'cantidad' => $retiros->count(),
            'detalle' => $detalle,
        ];
    }

    private function resumenRestaurantTurno(Carbon $fechaApertura, Carbon $fechaCierre, float $totalVentas): array
    {
        $base = Comanda::query()
            ->whereNotNull('fecha_cierre')
            ->whereBetween('fecha_cierre', [$fechaApertura, $fechaCierre])
            ->where('estado', 'CERRADA');

        $comensalesAtendidos = (int) (clone $base)->sum('comensales');
        $mesasAtendidas = (int) (clone $base)->whereNotNull('mesa_id')->distinct('mesa_id')->count('mesa_id');
        $comandasCerradas = (int) (clone $base)->count();
        $totalPropinas = (float) (clone $base)->sum('propina');

        $mesaMasOcupadaRaw = (clone $base)
            ->join('mesas', 'mesas.id', '=', 'comandas.mesa_id')
            ->selectRaw('comandas.mesa_id, mesas.nombre as mesa_nombre, SUM(comandas.comensales) as total_comensales, COUNT(comandas.id) as total_comandas')
            ->groupBy('comandas.mesa_id', 'mesas.nombre')
            ->orderByDesc('total_comensales')
            ->orderByDesc('total_comandas')
            ->first();

        $mesaMasOcupada = $mesaMasOcupadaRaw
            ? [
                'mesa_id' => (int) $mesaMasOcupadaRaw->mesa_id,
                'mesa' => (string) $mesaMasOcupadaRaw->mesa_nombre,
                'comensales' => (int) $mesaMasOcupadaRaw->total_comensales,
                'comandas' => (int) $mesaMasOcupadaRaw->total_comandas,
            ]
            : null;

        $propinasPorGarzon = Comanda::query()
            ->whereNotNull('comandas.fecha_cierre')
            ->whereBetween('comandas.fecha_cierre', [$fechaApertura, $fechaCierre])
            ->where('comandas.estado', 'CERRADA')
            ->leftJoin('users', 'users.id', '=', 'comandas.garzon_id')
            ->selectRaw("comandas.garzon_id, COALESCE(NULLIF(TRIM(COALESCE(users.name_complete, users.name)), ''), 'Sin garzon') as garzon, SUM(comandas.propina) as propina_total, SUM(comandas.comensales) as comensales, COUNT(comandas.id) as comandas")
            ->groupBy('comandas.garzon_id', 'users.name_complete', 'users.name')
            ->orderByDesc('propina_total')
            ->get()
            ->map(fn($row) => [
                'garzon_id' => $row->garzon_id ? (int) $row->garzon_id : null,
                'garzon' => (string) $row->garzon,
                'propina_total' => (float) $row->propina_total,
                'comensales' => (int) $row->comensales,
                'comandas' => (int) $row->comandas,
            ])
            ->values()
            ->all();

        $ticketPromedioComensal = $comensalesAtendidos > 0 ? ($totalVentas / $comensalesAtendidos) : 0.0;

        return [
            'comensales_atendidos' => $comensalesAtendidos,
            'mesas_atendidas' => $mesasAtendidas,
            'comandas_cerradas' => $comandasCerradas,
            'mesa_mas_ocupada' => $mesaMasOcupada,
            'total_propinas' => $totalPropinas,
            'propinas_por_garzon' => $propinasPorGarzon,
            'ticket_promedio_comensal' => $ticketPromedioComensal,
        ];
    }
}