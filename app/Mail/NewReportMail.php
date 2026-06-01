<?php

namespace App\Mail;

use App\Models\ConstructionReport;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email "nuevo reporte de avance subido" (E-12 del briefing).
 */
class NewReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ConstructionReport $report,
        public ?Reservation $reservation = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo reporte de avance · ' . $this->report->period,
        );
    }

    public function content(): Content
    {
        $r = $this->reservation;

        return new Content(
            view: 'emails.new-report',
            with: [
                'report'        => $this->report,
                'proyecto'      => $this->report->project->name ?? config('company.project'),
                'nombreCliente' => $r ? (trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: optional($r->user)->name) : 'Estimado cliente',
                'unidad'        => $r ? ($r->unit_name ?? optional($r->unit)->custom_id ?? optional($r->unit)->name ?? '—') : '—',
                'nombreAsesor'  => $r->budget_configured_by ?? null,
                'numFotos'      => count($this->report->photos ?? []),
                'hitosActualizados' => collect($this->report->phases ?? [])->where('status', 'active')->count(),
                'linkPortal'    => route('dashboard.progress'),
            ],
        );
    }
}
