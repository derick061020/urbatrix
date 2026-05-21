<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Reservation;
use App\Helpers\PaymentPlanHelper;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

/**
 * Template-free DOCX builders for the auto-generated documents.
 * Used as a fallback when the .docx template path is missing or the
 * template's placeholders don't match the current schema.
 */
class DocumentBuilder
{
    /**
     * Build a payment plan .docx from current reservation data.
     * Returns the relative path saved under storage/app/public (e.g. "documents/plan_de_pagos_RES-XXX.docx").
     */
    public static function buildPaymentPlan(Reservation $reservation): string
    {
        $breakdown = PaymentPlanHelper::calculatePaymentBreakdown($reservation);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Inter');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection(['marginLeft' => 1200, 'marginRight' => 1200, 'marginTop' => 1200, 'marginBottom' => 1200]);

        $titleStyle = ['size' => 18, 'bold' => true, 'color' => '5C7C68'];
        $section->addText('Plan de Pagos', $titleStyle);
        $section->addText('Reserva: '.($reservation->reservation_code ?? '—'), ['size' => 11, 'color' => '666666']);
        $section->addText('Fecha: '.now()->format('d/m/Y'), ['size' => 11, 'color' => '666666']);
        $section->addTextBreak(1);

        $section->addText('Cliente', ['bold' => true, 'size' => 13]);
        $section->addText(trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: '—');
        if ($reservation->email)   $section->addText('Email: '.$reservation->email);
        if ($reservation->phone)   $section->addText('Teléfono: '.$reservation->phone);
        if ($reservation->document_number) $section->addText('Documento: '.$reservation->document_number);
        $section->addTextBreak(1);

        $section->addText('Unidad', ['bold' => true, 'size' => 13]);
        $section->addText('Identificador: '.($reservation->unit_name ?? $reservation->unit?->name ?? '—'));
        $section->addText('Precio total: $'.number_format((float) $reservation->unit_price, 2, '.', ','));
        $section->addTextBreak(1);

        $section->addText('Estructura del plan', ['bold' => true, 'size' => 13]);
        $tableStyle = ['borderSize' => 6, 'borderColor' => 'D7DBE0', 'cellMargin' => 80];
        $headerStyle = ['bgColor' => 'F2F4F7'];
        $phpWord->addTableStyle('plan', $tableStyle);
        $table = $section->addTable('plan');

        $rows = [
            ['Concepto', 'Porcentaje', 'Monto', 'Detalle'],
            ['Pago inicial',         $reservation->payment_initial_percentage.'%',      '$'.number_format($breakdown['pago_inicial'], 2, '.', ','),
                'Incluye $'.number_format((float) $reservation->legal_costs, 2, '.', ',').' de costos legales'],
            ['Durante construcción', $reservation->payment_construction_percentage.'%', '$'.number_format($breakdown['pago_construccion'], 2, '.', ','),
                ($reservation->payment_installments ?? 0) > 0
                    ? ($reservation->payment_installments.' cuotas de $'.number_format($breakdown['cuota'] ?? 0, 2, '.', ','))
                    : 'Pago único'],
            ['A la entrega',         $reservation->payment_delivery_percentage.'%',     '$'.number_format($breakdown['pago_entrega'], 2, '.', ','), '—'],
        ];

        foreach ($rows as $i => $row) {
            $table->addRow();
            foreach ($row as $cell) {
                $cellStyle = $i === 0 ? $headerStyle : [];
                $table->addCell(3500, $cellStyle)->addText($cell, $i === 0 ? ['bold' => true] : []);
            }
        }

        if (! empty($reservation->budget_notes)) {
            $section->addTextBreak(1);
            $section->addText('Notas', ['bold' => true, 'size' => 13]);
            $section->addText($reservation->budget_notes);
        }

        $section->addTextBreak(2);
        $section->addText('Este plan refleja la configuración acordada al momento de la firma. El cliente y el asesor confirman su conformidad por los medios establecidos.',
            ['italic' => true, 'size' => 10, 'color' => '666666']);

        return self::saveAndRegister($phpWord, $reservation, 'payment_plan',
            'plan_de_pagos_'.$reservation->reservation_code.'.docx',
            'Plan de Pagos - '.$reservation->reservation_code);
    }

    /**
     * Build a purchase-promise .docx from current reservation data.
     */
    public static function buildPurchasePromise(Reservation $reservation): string
    {
        $breakdown = PaymentPlanHelper::calculatePaymentBreakdown($reservation);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Inter');
        $phpWord->setDefaultFontSize(11);
        $section = $phpWord->addSection(['marginLeft' => 1200, 'marginRight' => 1200, 'marginTop' => 1200, 'marginBottom' => 1200]);

        $section->addText('Promesa de Compraventa', ['size' => 18, 'bold' => true, 'color' => '5C7C68']);
        $section->addText('Reserva: '.($reservation->reservation_code ?? '—'), ['size' => 11, 'color' => '666666']);
        $section->addText('Fecha: '.now()->format('d/m/Y'), ['size' => 11, 'color' => '666666']);
        $section->addTextBreak(1);

        $section->addText('Comprador', ['bold' => true, 'size' => 13]);
        $section->addText('Nombre: '.trim(($reservation->first_name ?? '').' '.($reservation->last_name ?? '')) ?: '—');
        $section->addText('Documento: '.($reservation->document_number ?? '—'));
        $section->addText('Nacionalidad: '.($reservation->nationality ?? '—'));
        $section->addText('Estado civil: '.($reservation->marital_status ?? '—'));
        $section->addText('Email: '.($reservation->email ?? '—'));
        $section->addText('Teléfono: '.($reservation->phone ?? '—'));
        $section->addText('Dirección: '.self::formatAddress($reservation));
        $section->addTextBreak(1);

        $section->addText('Objeto', ['bold' => true, 'size' => 13]);
        $section->addText('El vendedor promete vender al comprador, y este promete adquirir, la unidad descrita a continuación, en las condiciones establecidas en este documento.');
        $section->addTextBreak(1);

        $section->addText('Unidad', ['bold' => true, 'size' => 13]);
        $section->addText('Identificador: '.($reservation->unit_name ?? $reservation->unit?->name ?? '—'));
        $section->addText('Proyecto: Makai Residences');
        if ($reservation->unit) {
            $u = $reservation->unit;
            if ($u->floor)         $section->addText('Planta: '.$u->floor);
            if ($u->bedrooms)      $section->addText('Habitaciones: '.$u->bedrooms);
            if ($u->bathrooms)     $section->addText('Baños: '.$u->bathrooms);
            if ($u->parking_bays)  $section->addText('Parqueos: '.$u->parking_bays);
            if ($u->internal_area) $section->addText('Área interior: '.$u->internal_area.' m²');
            if ($u->external_area) $section->addText('Área exterior: '.$u->external_area.' m²');
        }
        $section->addText('Precio total: $'.number_format((float) $reservation->unit_price, 2, '.', ','));
        $section->addTextBreak(1);

        $section->addText('Plan de pagos acordado', ['bold' => true, 'size' => 13]);
        $section->addText('• Inicial ('.$reservation->payment_initial_percentage.'%): $'
            .number_format($breakdown['pago_inicial'], 2, '.', ',')
            .' — incluye $'.number_format((float) $reservation->legal_costs, 2, '.', ',').' legales');
        $section->addText('• Construcción ('.$reservation->payment_construction_percentage.'%): $'
            .number_format($breakdown['pago_construccion'], 2, '.', ',')
            .(($reservation->payment_installments ?? 0) > 0
                ? ' — '.$reservation->payment_installments.' cuotas de $'.number_format($breakdown['cuota'] ?? 0, 2, '.', ',')
                : ' — pago único'));
        $section->addText('• Entrega ('.$reservation->payment_delivery_percentage.'%): $'
            .number_format($breakdown['pago_entrega'], 2, '.', ','));
        $section->addTextBreak(1);

        $section->addText('Condiciones generales', ['bold' => true, 'size' => 13]);
        $section->addText('1. Esta promesa de compraventa rige hasta la firma del contrato definitivo.');
        $section->addText('2. El comprador se compromete a cumplir el plan de pagos en los plazos acordados.');
        $section->addText('3. Cualquier modificación deberá constar por escrito y ser aceptada por ambas partes.');
        $section->addText('4. La firma electrónica del presente documento por parte del comprador y del vendedor tendrá plena validez jurídica.');
        $section->addTextBreak(2);

        $section->addText('___________________________                ___________________________', ['size' => 10]);
        $section->addText('Comprador                                          Vendedor', ['size' => 10, 'color' => '666666']);

        return self::saveAndRegister($phpWord, $reservation, 'purchase_promise',
            'promesa_compraventa_'.$reservation->reservation_code.'.docx',
            'Promesa de Compraventa - '.$reservation->reservation_code);
    }

    private static function saveAndRegister(PhpWord $phpWord, Reservation $reservation, string $type, string $fileName, string $title): string
    {
        $documentsDir = storage_path('app/public/documents');
        if (! is_dir($documentsDir)) mkdir($documentsDir, 0755, true);

        $relPath = 'documents/'.$fileName;
        $absPath = storage_path('app/public/'.$relPath);
        IOFactory::createWriter($phpWord, 'Word2007')->save($absPath);

        $existing = $reservation->documents()->ofType($type)->first();
        if ($existing) {
            $existing->update([
                'file_path'   => $relPath,
                'filename'    => $fileName,
                'status'      => $existing->status === 'pending' ? 'generated' : $existing->status,
                'generated_at'=> now(),
            ]);
        } else {
            \App\Services\DocumentService::createDocument(
                $reservation, $type, $title, $relPath, $fileName
            )->markAsGenerated();
        }

        return $relPath;
    }

    private static function formatAddress(Reservation $r): string
    {
        $parts = array_filter([$r->address, $r->neighborhood, $r->city, $r->province, $r->country]);
        return $parts ? implode(', ', $parts) : '—';
    }
}
