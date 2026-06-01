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
 * Email "avance de obra · reporte mensual" (E-04 del briefing).
 */
class ConstructionProgressMail extends Mailable
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
            subject: 'Novedades de obra · ' . ($this->report->project->name ?? config('company.project')),
        );
    }

    public function content(): Content
    {
        $r = $this->reservation;

        return new Content(
            view: 'emails.construction-progress',
            with: [
                'report'        => $this->report,
                'proyecto'      => $this->report->project->name ?? config('company.project'),
                'nombreCliente' => $r ? (trim(($r->first_name ?? '') . ' ' . ($r->last_name ?? '')) ?: optional($r->user)->name) : 'Estimado cliente',
                'unidad'        => $r ? ($r->unit_name ?? optional($r->unit)->custom_id ?? optional($r->unit)->name) : null,
                'nombreAsesor'  => $r->budget_configured_by ?? null,
                'linkPortal'    => route('dashboard.progress'),
            ],
        );
    }
}
