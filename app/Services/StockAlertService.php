<?php

namespace App\Services;

use App\Mail\StockMinimoAlert;
use App\Mail\StockPredictivoAlert;
use App\Models\CorporateData;
use App\Models\HistorialMovimientos;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StockAlertService
{
    public function enviarReportesDiarios(int $diasAnalisis = 14, float $umbralDias = 3): array
    {
        return [
            'stock_minimo' => $this->enviarReporteStockMinimoDiario(),
            'stock_predictivo' => $this->enviarReporteStockPredictivoDiario($diasAnalisis, $umbralDias),
        ];
    }

    public function enviarReporteStockMinimoDiario(): int
    {
        $mailDestino = $this->obtenerMailDestino();
        if (!$mailDestino) {
            return 0;
        }

        $empresaNombre = $this->obtenerEmpresaNombre();
        $fechaReporte = now();

        $productos = Producto::where('stock_minimo', '>', 0)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->with('categoria:id,descripcion_categoria')
            ->orderBy('stock', 'asc')
            ->get(['id', 'descripcion', 'codigo', 'stock', 'stock_minimo', 'categoria_id', 'unidad_medida']);

        if ($productos->isEmpty()) {
            return 0;
        }

        $alertas = $productos->map(fn(Producto $producto) => $this->mapearProductoStockMinimo($producto))
            ->values()
            ->all();

        try {
            Mail::to($mailDestino)->send(new StockMinimoAlert($alertas, $empresaNombre, $fechaReporte));
            return count($alertas);
        } catch (\Exception $e) {
            Log::error('[StockAlert] Error al enviar reporte diario de stock mínimo: ' . $e->getMessage(), [
                'destino' => $mailDestino,
                'fecha_reporte' => $fechaReporte->toDateTimeString(),
            ]);
            return 0;
        }
    }

    public function enviarReporteStockPredictivoDiario(int $diasAnalisis = 14, float $umbralDias = 3): int
    {
        $mailDestino = $this->obtenerMailDestino();
        if (!$mailDestino) {
            return 0;
        }

        $empresaNombre = $this->obtenerEmpresaNombre();
        $fechaReporte = now();
        $fechaDesde = Carbon::now()->subDays($diasAnalisis);

        $consumos = HistorialMovimientos::query()
            ->select('producto_id', DB::raw('SUM(cantidad) as total_consumido'))
            ->whereIn('tipo_mov', ['VENTA', 'SALIDA', 'MERMA'])
            ->where('fecha', '>=', $fechaDesde)
            ->groupBy('producto_id')
            ->havingRaw('SUM(cantidad) > 0')
            ->pluck('total_consumido', 'producto_id');

        if ($consumos->isEmpty()) {
            return 0;
        }

        $productos = Producto::whereIn('id', $consumos->keys()->all())
            ->with('categoria:id,descripcion_categoria')
            ->get(['id', 'descripcion', 'codigo', 'stock', 'stock_minimo', 'categoria_id', 'unidad_medida'])
            ->keyBy('id');

        $alertas = [];
        foreach ($consumos as $productoId => $totalConsumido) {
            $producto = $productos->get($productoId);

            if (!$producto) {
                continue;
            }

            $stockActual = (float) $producto->stock;
            $stockMinimo = max((float) $producto->stock_minimo, 0);

            if ($stockActual <= $stockMinimo) {
                continue;
            }

            $promedioDiario = (float) $totalConsumido / max($diasAnalisis, 1);
            if ($promedioDiario <= 0) {
                continue;
            }

            $diasRestantes = ($stockActual - $stockMinimo) / $promedioDiario;
            if ($diasRestantes > $umbralDias) {
                continue;
            }

            $alertas[] = [
                'producto' => $producto->descripcion ?? ('Producto #' . $producto->id),
                'codigo' => $producto->codigo,
                'categoria' => optional($producto->categoria)->descripcion_categoria,
                'stock_actual' => $stockActual,
                'stock_minimo' => $stockMinimo,
                'unidad' => $producto->unidad_medida,
                'promedio_diario' => round($promedioDiario, 2),
                'dias_restantes' => round($diasRestantes, 1),
                'periodo_analisis' => $diasAnalisis,
            ];
        }

        if (empty($alertas)) {
            return 0;
        }

        usort($alertas, fn(array $a, array $b) => $a['dias_restantes'] <=> $b['dias_restantes']);

        try {
            Mail::to($mailDestino)->send(new StockPredictivoAlert($alertas, $empresaNombre, $fechaReporte, $diasAnalisis, $umbralDias));
            return count($alertas);
        } catch (\Exception $e) {
            Log::error('[StockAlert] Error al enviar reporte diario de stock predictivo: ' . $e->getMessage(), [
                'destino' => $mailDestino,
                'fecha_reporte' => $fechaReporte->toDateTimeString(),
                'dias_analisis' => $diasAnalisis,
                'umbral_dias' => $umbralDias,
            ]);
            return 0;
        }
    }

    private function obtenerMailDestino(): ?string
    {
        return CorporateData::where('item', 'mail_enterprise')->value('description_item');
    }

    private function obtenerEmpresaNombre(): string
    {
        return CorporateData::whereIn('item', ['fantasy_name_enterprise', 'name_enterprise'])
            ->orderByRaw("FIELD(item,'fantasy_name_enterprise','name_enterprise')")
            ->value('description_item') ?? 'Empresa';
    }

    private function mapearProductoStockMinimo(Producto $producto): array
    {
        return [
            'producto' => $producto->descripcion ?? ('Producto #' . $producto->id),
            'codigo' => $producto->codigo,
            'stock_actual' => (float) $producto->stock,
            'stock_minimo' => (float) $producto->stock_minimo,
            'categoria' => optional($producto->categoria)->descripcion_categoria,
            'unidad' => $producto->unidad_medida,
        ];
    }
}
