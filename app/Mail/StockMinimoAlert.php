<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockMinimoAlert extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array */
    public array $alertas;

    /** @var string */
    public string $empresaNombre;

    /** @var string */
    public string $fechaReporte;

    /**
     * @param array  $alertas       Lista de ['producto', 'codigo', 'stock_actual', 'stock_minimo', 'categoria', 'unidad']
     * @param string $empresaNombre Nombre de la empresa
     * @param CarbonInterface $fechaReporte Fecha del reporte diario
     */
    public function __construct(array $alertas, string $empresaNombre, CarbonInterface $fechaReporte)
    {
        $this->alertas       = $alertas;
        $this->empresaNombre = $empresaNombre;
        $this->fechaReporte  = $fechaReporte->format('d/m/Y H:i');
    }

    public function build(): static
    {
        $count   = count($this->alertas);
        $subject = 'Reporte diario de stock mínimo - ' . $count . ' producto' . ($count > 1 ? 's' : '') . ' - ' . $this->empresaNombre;

        return $this
            ->subject($subject)
            ->view('emails.stock_minimo_alert');
    }
}
