<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DashboardGerencialPdf extends Mailable
{
    use Queueable, SerializesModels;

    public string $empresaNombre;
    public string $pdfBase64;
    public string $filename;

    public function __construct(string $empresaNombre, string $pdfBase64, string $filename)
    {
        $this->empresaNombre = $empresaNombre;
        $this->pdfBase64     = $pdfBase64;
        $this->filename      = $filename;
    }

    public function build(): static
    {
        $pdfContent = base64_decode($this->pdfBase64);

        return $this
            ->subject('Dashboard Gerencial — ' . $this->empresaNombre)
            ->view('emails.dashboard_gerencial_pdf')
            ->attachData($pdfContent, $this->filename, [
                'mime' => 'application/pdf',
            ]);
    }
}
