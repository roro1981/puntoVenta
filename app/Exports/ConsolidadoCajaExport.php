<?php

namespace App\Exports;

use App\Models\Caja;
use App\Models\Venta;
use App\Models\FormaPagoVenta;
use App\Models\RetiroCaja;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidadoCajaExport implements WithMultipleSheets
{
    protected array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function sheets(): array
    {
        $cajas = Caja::with('usuario')
            ->where('estado', 'cerrada')
            ->whereIn('id', $this->ids)
            ->orderBy('fecha_apertura')
            ->get();

        // Calcular desglose consolidado
        $desgloseVentas = Venta::whereIn('caja_id', $cajas->pluck('id'))
            ->selectRaw('forma_pago, SUM(total) as monto, COUNT(*) as cantidad')
            ->groupBy('forma_pago')
            ->get()
            ->keyBy('forma_pago');

        $cantidadVentas = (int) $desgloseVentas->sum('cantidad');
        $totalVentas    = (float) $desgloseVentas->sum('monto');
        $totalMixto     = (float) ($desgloseVentas->get('MIXTO')?->monto ?? 0);

        $desglose = [
            'efectivo'        => (float) ($desgloseVentas->get('EFECTIVO')?->monto        ?? 0),
            'tarjeta_debito'  => (float) ($desgloseVentas->get('TARJETA_DEBITO')?->monto  ?? 0),
            'tarjeta_credito' => (float) ($desgloseVentas->get('TARJETA_CREDITO')?->monto ?? 0),
            'transferencia'   => (float) ($desgloseVentas->get('TRANSFERENCIA')?->monto   ?? 0),
            'cheque'          => (float) ($desgloseVentas->get('CHEQUE')?->monto          ?? 0),
            'mixto'           => $totalMixto,
        ];

        if ($totalMixto > 0) {
            $mixtoDesglose = FormaPagoVenta::whereIn(
                'venta_id',
                Venta::whereIn('caja_id', $cajas->pluck('id'))->where('forma_pago', 'MIXTO')->select('id')
            )
                ->selectRaw('forma_pago, SUM(monto) as monto')
                ->groupBy('forma_pago')
                ->pluck('monto', 'forma_pago');

            $desglose['efectivo']        += (float) ($mixtoDesglose->get('EFECTIVO')        ?? 0);
            $desglose['tarjeta_debito']  += (float) ($mixtoDesglose->get('TARJETA_DEBITO')  ?? 0);
            $desglose['tarjeta_credito'] += (float) ($mixtoDesglose->get('TARJETA_CREDITO') ?? 0);
            $desglose['transferencia']   += (float) ($mixtoDesglose->get('TRANSFERENCIA')   ?? 0);
            $desglose['cheque']          += (float) ($mixtoDesglose->get('CHEQUE')          ?? 0);
        }

        $montoInicialTotal   = (float) $cajas->sum('monto_inicial');
        $montoDeclaradoTotal = (float) $cajas->sum('monto_final_declarado');
        $diferenciaTotal     = $montoDeclaradoTotal - ($montoInicialTotal + $totalVentas);

        $retiros = RetiroCaja::whereIn('caja_id', $cajas->pluck('id'))
            ->orderBy('created_at')
            ->get(['caja_id', 'monto', 'motivo', 'created_at']);
        $totalRetiros = (float) $retiros->sum('monto');

        $sheets = [
            new ConsolidadoResumenSheet($cajas, $cantidadVentas, $totalVentas, $desglose, $montoInicialTotal, $montoDeclaradoTotal, $diferenciaTotal, $totalRetiros),
            new ConsolidadoDetalleCajasSheet($cajas),
        ];

        if ($retiros->count() > 0) {
            $sheets[] = new ConsolidadoRetirosSheet($retiros);
        }

        return $sheets;
    }
}

// ---------------------------------------------------------------------------
// Hoja 1: Resumen consolidado
// ---------------------------------------------------------------------------

class ConsolidadoResumenSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        protected $cajas,
        protected int $cantidadVentas,
        protected float $totalVentas,
        protected array $desglose,
        protected float $montoInicialTotal,
        protected float $montoDeclaradoTotal,
        protected float $diferenciaTotal,
        protected float $totalRetiros = 0.0,
    ) {}

    public function array(): array
    {
        $rows = [];

        $rows[] = ['CONSOLIDADO DE CAJAS', ''];
        $rows[] = ['Generado:', now()->format('d/m/Y H:i:s')];
        $rows[] = ['Período:', $this->cajas->first()->fecha_apertura->format('d/m/Y H:i') . ' — ' . $this->cajas->last()->fecha_cierre->format('d/m/Y H:i')];
        $rows[] = ['Turnos consolidados:', $this->cajas->count()];
        $rows[] = ['', ''];
        $rows[] = ['--- RESUMEN DE VENTAS ---', ''];
        $rows[] = ['Cantidad de ventas:', $this->cantidadVentas];
        $rows[] = ['Total ventas:', $this->totalVentas];
        $rows[] = ['', ''];
        $rows[] = ['--- DESGLOSE POR FORMA DE PAGO ---', ''];

        $labeles = [
            'efectivo'        => 'Efectivo',
            'tarjeta_debito'  => 'Tarjeta Débito',
            'tarjeta_credito' => 'Tarjeta Crédito',
            'transferencia'   => 'Transferencia',
            'cheque'          => 'Cheque',
            'mixto'           => 'Mixto',
        ];

        foreach ($labeles as $key => $label) {
            if ($this->desglose[$key] > 0) {
                $rows[] = [$label . ':', $this->desglose[$key]];
            }
        }

        $rows[] = ['', ''];
        $rows[] = ['--- TOTALES CONSOLIDADOS ---', ''];
        $rows[] = ['Monto inicial total:', $this->montoInicialTotal];
        $rows[] = ['Total ventas:', $this->totalVentas];
        if ($this->totalRetiros > 0) {
            $rows[] = ['Total retiros:', -$this->totalRetiros];
        }
        $rows[] = ['Monto esperado:', $this->montoInicialTotal + $this->totalVentas - $this->totalRetiros];
        $rows[] = ['Monto declarado total:', $this->montoDeclaradoTotal];

        if ($this->diferenciaTotal > 0) {
            $rows[] = ['Diferencia (sobrante):', $this->diferenciaTotal];
        } elseif ($this->diferenciaTotal < 0) {
            $rows[] = ['Diferencia (faltante):', $this->diferenciaTotal];
        } else {
            $rows[] = ['Diferencia:', 'Cuadre exacto'];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Resumen Consolidado';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}

// ---------------------------------------------------------------------------
// Hoja 2: Detalle por turno
// ---------------------------------------------------------------------------
class ConsolidadoDetalleCajasSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(protected $cajas) {}

    public function array(): array
    {
        $rows = [];
        $rows[] = ['Nº Cierre', 'Usuario', 'Apertura', 'Cierre', 'Monto Inicial', 'Total Ventas', 'Monto Esperado', 'Monto Declarado', 'Diferencia'];

        foreach ($this->cajas as $caja) {
            $esperado   = (float) $caja->monto_inicial + (float) $caja->monto_ventas;
            $rows[] = [
                str_pad($caja->id, 4, '0', STR_PAD_LEFT),
                $caja->usuario->name ?? 'N/A',
                $caja->fecha_apertura->format('d/m/Y H:i'),
                $caja->fecha_cierre->format('d/m/Y H:i'),
                (float) $caja->monto_inicial,
                (float) $caja->monto_ventas,
                $esperado,
                (float) $caja->monto_final_declarado,
                (float) $caja->diferencia,
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Detalle por Turno';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

// ---------------------------------------------------------------------------
// Hoja 3: Retiros de caja
// ---------------------------------------------------------------------------
class ConsolidadoRetirosSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(protected $retiros) {}

    public function array(): array
    {
        $rows = [];
        $rows[] = ['Nº Caja', 'Fecha y Hora', 'Motivo', 'Monto'];

        foreach ($this->retiros as $retiro) {
            $rows[] = [
                str_pad($retiro->caja_id, 4, '0', STR_PAD_LEFT),
                \Carbon\Carbon::parse($retiro->created_at)->format('d/m/Y H:i'),
                $retiro->motivo,
                -(float) $retiro->monto,
            ];
        }

        $rows[] = ['', '', 'TOTAL RETIROS:', -(float) $this->retiros->sum('monto')];

        return $rows;
    }

    public function title(): string
    {
        return 'Retiros de Caja';
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->retiros) + 2;
        return [
            1         => ['font' => ['bold' => true]],
            $lastRow  => ['font' => ['bold' => true]],
        ];
    }
}
