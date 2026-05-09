<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockPredictivoAlert extends Mailable
{
    use Queueable, SerializesModels;

    public array $alertas;

    public string $empresaNombre;

    public string $fechaReporte;

    public int $diasAnalisis;

    public float $umbralDias;

    public function __construct(array $alertas, string $empresaNombre, CarbonInterface $fechaReporte, int $diasAnalisis, float $umbralDias)
    {
        $this->alertas = $alertas;
        $this->empresaNombre = $empresaNombre;
        $this->fechaReporte = $fechaReporte->format('d/m/Y H:i');
        $this->diasAnalisis = $diasAnalisis;
        $this->umbralDias = $umbralDias;
    }

    public function build(): static
    {
        $count = count($this->alertas);
        $subject = 'Reporte diario de stock predictivo - ' . $count . ' producto' . ($count > 1 ? 's' : '') . ' - ' . $this->empresaNombre;

        return $this
            ->subject($subject)
            ->view('emails.stock_predictivo_alert');
    }
}