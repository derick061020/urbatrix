<?php

namespace App\Helpers;

use App\Models\Reservation;
use App\Services\DocumentService;
use DateInterval;
use DateTime;
use Illuminate\Contracts\View\View;

/**
 * Builds the view-data arrays for the printable HTML documents
 * (plan de pagos y promesa de compraventa) from a reservation.
 *
 * Keeping all formatting here means the Blade views stay dumb and the
 * ContractController / AdminController generators share one source of truth.
 */
class DocumentDataHelper
{
    private const MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    /**
     * Data for resources/views/documents/plan-de-pagos.blade.php
     */
    public static function paymentPlan(Reservation $reservation): array
    {
        $breakdown = PaymentPlanHelper::calculatePaymentBreakdown($reservation);
        $totalPrice = $breakdown['total_sin_legales'];
        $cantidadCuotas = (int) $breakdown['cantidad_cuotas'];

        // Calendario de cuotas mensuales
        $cuotas = [];
        $inicio = new DateTime();
        for ($i = 1; $i <= $cantidadCuotas; $i++) {
            $fecha = clone $inicio;
            $fecha->add(new DateInterval('P' . $i . 'M'));
            $cuotas[] = [
                'numero' => str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'mes'    => self::MESES[(int) $fecha->format('n')] . ' ' . $fecha->format('Y'),
                'monto'  => number_format($breakdown['cuota'], 2, '.', ','),
            ];
        }

        // Entrega estimada: 24 meses (cláusula del contrato) o más si las cuotas exceden
        $mesesEntrega = max(24, $cantidadCuotas + 1);
        $fechaEntrega = (clone $inicio)->add(new DateInterval('P' . $mesesEntrega . 'M'));
        $entregaTexto = self::MESES[(int) $fechaEntrega->format('n')] . ' ' . $fechaEntrega->format('Y');

        return [
            'referencia'        => $reservation->reservation_code,
            'fecha'             => date('d/m/Y'),
            'comprador_nombre'  => trim($reservation->first_name . ' ' . $reservation->last_name),
            'comprador_email'   => $reservation->email,
            'comprador_id'      => $reservation->document_number ?: 'N/A',

            'unidad'            => self::unidadNombre($reservation),
            'nivel'             => optional($reservation->unit)->floor ?: 'N/A',
            'tipologia'         => self::tipologia($reservation),
            'entrega_estimada'  => $entregaTexto,
            'precio_total'      => number_format($totalPrice, 2, '.', ','),

            'cuotas'            => $cuotas,
            'total_cuotas'      => number_format($breakdown['pago_construccion'], 2, '.', ','),

            'pct_inicial'       => self::pct($breakdown['porcentaje_inicial']),
            'monto_inicial'     => number_format($breakdown['pago_inicial'], 2, '.', ','),

            'pct_construccion'  => self::pct($breakdown['porcentaje_construccion']),
            'monto_construccion' => number_format($breakdown['pago_construccion'], 2, '.', ','),
            'cantidad_cuotas'   => $cantidadCuotas,
            'monto_cuota'       => number_format($breakdown['cuota'], 2, '.', ','),

            'pct_entrega'       => self::pct($breakdown['porcentaje_entrega']),
            'saldo_entrega'     => number_format($breakdown['pago_entrega'], 2, '.', ','),
            'fecha_entrega'     => $entregaTexto,
        ];
    }

    /**
     * Data for resources/views/documents/promesa-compraventa.blade.php
     */
    public static function purchasePromise(Reservation $reservation): array
    {
        $unit = $reservation->unit;
        $price = (float) $reservation->unit_price;

        return [
            'referencia'       => $reservation->reservation_code,
            'fecha'            => date('d/m/Y'),

            // Comprador
            'comprador_nombre'   => trim($reservation->first_name . ' ' . $reservation->last_name),
            'comprador_tipo'     => ($reservation->economic_dependent === 'No' || ! $reservation->economic_dependent) ? 'Individuo' : 'Empresa',
            'comprador_pasaporte' => $reservation->document_number ?: 'N/A',
            'comprador_empresa_id' => 'N/A',
            'comprador_nacionalidad' => $reservation->nationality ?: 'N/A',
            'comprador_estado_civil' => $reservation->marital_status ?: 'N/A',
            'comprador_direccion' => self::formatAddress($reservation),
            'comprador_email'    => $reservation->email,
            'comprador_ocupacion' => $reservation->profession ?: ($reservation->occupation ?: 'N/A'),

            // Unidad
            'unidad'           => self::unidadNombre($reservation),
            'nivel'            => optional($unit)->floor ?: 'N/A',
            'area'             => self::area($unit),
            'dormitorios'      => self::cardinal(optional($unit)->bedrooms ?? 0),
            'banos'            => self::cardinal(optional($unit)->bathrooms ?? 0),
            'estacionamientos' => self::cardinal(optional($unit)->parking_bays ?? 0),

            // Precio
            'precio_usd'       => number_format($price, 2, '.', ','),
            'precio_letras'    => mb_strtoupper(self::numberToWords($price, 'es')),
        ];
    }

    /**
     * Data for resources/views/documents/kyc.blade.php
     *
     * El KYC es el formulario que el cliente completó en /form, no el documento
     * de identidad. Acá lo poblamos con lo que el cliente rellenó en la reserva.
     */
    public static function kyc(Reservation $reservation): array
    {
        $kycDoc = $reservation->relationLoaded('documents')
            ? $reservation->documents->firstWhere('document_type', 'kyc')
            : $reservation->documents()->where('document_type', 'kyc')->first();
        $status = $kycDoc->status ?? 'pending';

        $estados = ['pending' => 'En revisión', 'approved' => 'Aprobado', 'rejected' => 'Rechazado'];
        $clases  = ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected'];

        $advisor = \App\Models\Agent::where('active', true)->orderBy('id')->first();

        // Imagen del documento de identidad, si el cliente la adjuntó.
        $idPath = $reservation->id_document_path ?: ($kycDoc->file_path ?? null);
        $idImagenUrl = null;
        if ($idPath && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $idPath)) {
            $idImagenUrl = \Illuminate\Support\Facades\Storage::disk('public')->exists($idPath)
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($idPath)
                : asset(ltrim($idPath, '/'));
        }

        return [
            'referencia'       => $reservation->reservation_code,
            'proyecto'         => 'Makai Residences',
            'unidad'           => self::unidadNombre($reservation),
            'comprador_nombre' => trim($reservation->first_name . ' ' . $reservation->last_name) ?: 'N/A',
            'nombres'          => $reservation->first_name ?: 'N/A',
            'apellidos'        => $reservation->last_name ?: 'N/A',
            'fecha_llenado'    => optional($reservation->updated_at)->format('d / m / Y') ?: date('d / m / Y'),
            'estado'           => $estados[$status] ?? 'En revisión',
            'estado_clase'     => $clases[$status] ?? 'pending',
            'asesor'           => $advisor->name ?? 'Duna Development Group',

            'fecha_nacimiento' => $reservation->birth_date ? $reservation->birth_date->format('d / m / Y') : 'N/A',
            'nacionalidad'     => $reservation->nationality ?: 'N/A',
            'pais_residencia'  => $reservation->country ?: 'N/A',

            'id_tipo'          => $reservation->id_type ?: 'N/A',
            'id_numero'        => $reservation->document_number ?: 'N/A',
            'id_expedicion'    => $reservation->expedition_date
                ? $reservation->expedition_date->format('m / Y')
                : ($reservation->expedition_place ?: 'N/A'),
            'id_imagen_url'    => $idImagenUrl,

            'telefono'         => $reservation->phone ?: 'N/A',
            'email'            => $reservation->email ?: 'N/A',
            'direccion'        => self::formatAddress($reservation),
        ];
    }

    /**
     * Renderiza el formulario KYC a HTML y lo persiste en storage, devolviendo
     * la ruta relativa (para guardarla en Document->file_path). No toca el
     * registro Document — el estado de revisión lo maneja el flujo de KYC.
     */
    public static function renderKycHtml(Reservation $reservation): string
    {
        $html = view('documents.kyc', self::kyc($reservation))->render();

        $documentsDir = storage_path('app/public/documents');
        if (! is_dir($documentsDir)) {
            mkdir($documentsDir, 0755, true);
        }

        $fileName = 'kyc_' . $reservation->reservation_code . '.html';
        $filePath = 'documents/' . $fileName;
        file_put_contents(storage_path('app/public/' . $filePath), $html);

        return $filePath;
    }

    /**
     * Renderiza la vista imprimible, guarda el HTML en storage para que
     * preview/descarga sigan funcionando, registra el Document y devuelve
     * la vista lista para mostrarse (e imprimirse a PDF) en el navegador.
     *
     * @param  string  $type  'payment_plan' | 'purchase_promise'
     */
    public static function renderAndStore(Reservation $reservation, string $type): View
    {
        $config = [
            'payment_plan' => [
                'view'   => 'documents.plan-de-pagos',
                'data'   => fn () => self::paymentPlan($reservation),
                'prefix' => 'plan_de_pagos',
                'title'  => 'Plan de Pagos',
            ],
            'purchase_promise' => [
                'view'   => 'documents.promesa-compraventa',
                'data'   => fn () => self::purchasePromise($reservation),
                'prefix' => 'promesa_compraventa',
                'title'  => 'Promesa de Compraventa',
            ],
        ][$type];

        $data = ($config['data'])();
        $view = view($config['view'], $data);

        // Persistir HTML para preview inline / descarga desde la pestaña Documentos
        $documentsDir = storage_path('app/public/documents');
        if (! is_dir($documentsDir)) {
            mkdir($documentsDir, 0755, true);
        }

        $fileName = $config['prefix'] . '_' . $reservation->reservation_code . '.html';
        $filePath = 'documents/' . $fileName;
        file_put_contents(storage_path('app/public/' . $filePath), $view->render());

        $document = DocumentService::getDocumentByType($reservation, $type);
        if ($document) {
            $document->update([
                'file_path'    => $filePath,
                'filename'     => $fileName,
                'status'       => 'generated',
                'generated_at' => now(),
            ]);
        } else {
            DocumentService::createDocument(
                $reservation,
                $type,
                $config['title'] . ' - ' . $reservation->reservation_code,
                $filePath,
                $fileName
            )->markAsGenerated();
        }

        return $view;
    }

    /* ───────────────── helpers ───────────────── */

    private static function unidadNombre(Reservation $reservation): string
    {
        return $reservation->unit_name
            ?: (optional($reservation->unit)->name ?: 'Unidad ' . $reservation->unit_id);
    }

    private static function tipologia(Reservation $reservation): string
    {
        $unit = $reservation->unit;
        if ($unit && $unit->layout) {
            return $unit->layout;
        }
        $bedrooms = (int) (optional($unit)->bedrooms ?? 0);
        return $bedrooms > 0 ? $bedrooms . ' dormitorio' . ($bedrooms > 1 ? 's' : '') : 'N/A';
    }

    private static function area($unit): string
    {
        $area = $unit ? ($unit->total_area ?: $unit->internal_area) : null;
        return $area ? rtrim(rtrim(number_format((float) $area, 2, '.', ','), '0'), '.') : 'N/A';
    }

    private static function pct($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    /** Cardinal con cifra entre paréntesis: 2 → "dos (2)" */
    private static function cardinal($number): string
    {
        $number = (int) $number;
        $palabras = [
            0 => 'cero', 1 => 'un', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve', 10 => 'diez',
        ];
        return isset($palabras[$number])
            ? $palabras[$number] . ' (' . $number . ')'
            : $number . ' (' . $number . ')';
    }

    public static function formatAddress(Reservation $reservation): string
    {
        $parts = array_filter([
            $reservation->address,
            $reservation->neighborhood,
            $reservation->city,
            $reservation->province,
            $reservation->country,
            $reservation->postal_code ? 'CP: ' . $reservation->postal_code : null,
        ]);

        return $parts ? implode(', ', $parts) : 'N/A';
    }

    /**
     * Conversión de número a letras (es). Suficiente para montos en USD.
     */
    public static function numberToWords($number, string $language = 'es'): string
    {
        $number = (int) $number;

        $ones = [
            0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve',
        ];
        $tens = [
            10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta',
            60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa',
        ];
        $hundreds = [
            100 => 'cien', 200 => 'doscientos', 300 => 'trescientos', 400 => 'cuatrocientos',
            500 => 'quinientos', 600 => 'seiscientos', 700 => 'setecientos', 800 => 'ochocientos',
            900 => 'novecientos',
        ];

        if ($number < 10) {
            return $ones[$number];
        }
        if ($number < 20) {
            return $tens[$number];
        }
        if ($number < 100) {
            $ten = (int) (floor($number / 10) * 10);
            $one = $number % 10;
            if ($one === 0) {
                return $tens[$ten];
            }
            // 21-29 usan "veintiuno..." pero mantenemos forma simple "veinte y uno"
            return $tens[$ten] . ' y ' . $ones[$one];
        }
        if ($number < 1000) {
            $hundred = (int) (floor($number / 100) * 100);
            $remainder = $number % 100;
            $prefix = ($number > 100 && $hundred === 100) ? 'ciento' : $hundreds[$hundred];
            return $remainder === 0 ? $prefix : $prefix . ' ' . self::numberToWords($remainder);
        }
        if ($number < 1000000) {
            $thousands = (int) floor($number / 1000);
            $remainder = $number % 1000;
            $result = $thousands === 1 ? 'mil' : self::numberToWords($thousands) . ' mil';
            return $remainder > 0 ? $result . ' ' . self::numberToWords($remainder) : $result;
        }
        if ($number < 1000000000) {
            $millions = (int) floor($number / 1000000);
            $remainder = $number % 1000000;
            $result = $millions === 1 ? 'un millón' : self::numberToWords($millions) . ' millones';
            return $remainder > 0 ? $result . ' ' . self::numberToWords($remainder) : $result;
        }

        return number_format($number, 0, '.', ',');
    }
}
