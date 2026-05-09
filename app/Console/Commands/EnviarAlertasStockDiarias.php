<?php

namespace App\Console\Commands;

use App\Services\StockAlertService;
use Illuminate\Console\Command;

class EnviarAlertasStockDiarias extends Command
{
    protected $signature = 'stock:alertas-diarias {--dias=14} {--umbral=3}';

    protected $description = 'Envia el reporte diario consolidado de stock minimo y el reporte diario de stock predictivo';

    public function handle(StockAlertService $stockAlertService): int
    {
        $resultado = $stockAlertService->enviarReportesDiarios(
            (int) $this->option('dias'),
            (float) $this->option('umbral')
        );

        $this->info('Reporte stock minimo enviados: ' . $resultado['stock_minimo']);
        $this->info('Reporte stock predictivo enviados: ' . $resultado['stock_predictivo']);

        return self::SUCCESS;
    }
}