<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TurnoCierreResumenMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): static
    {
        $empresa = $this->data['empresa'] ?? 'Empresa';
        $cajero = $this->data['cajero_nombre'] ?? 'Cajero';
        $cajaId = $this->data['caja']->id ?? 'N/A';

        $subject = 'Cierre de caja - ' . $cajero . ' (Caja #' . $cajaId . ') - ' . $empresa;

        return $this
            ->subject($subject)
            ->view('emails.turno_cierre_resumen');
    }
}