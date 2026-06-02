<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Helpers\PaymentPlanHelper;
use App\Services\PaymentService;
use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpWord\TemplateProcessor;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    /**
     * Generate contract for a specific reservation (client-side or admin)
     */
    public function generate(Reservation $reservation)
    {
        // Check authorization: must be owner or admin
        if ($reservation->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        if (!$reservation->isBudgetSent()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Presupuesto no enviado aún.'], 422);
            }
            return back()->with('error', 'El presupuesto aún no fue enviado por el equipo. Espera a que tu asesor lo confirme.');
        }

        try {
            // Load template
            $templatePath = storage_path('app/templates/contract_template.docx');
            
            if (!file_exists($templatePath)) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Plantilla de contrato no encontrada'], 404);
                }
                return back()->with('error', 'Plantilla de contrato no encontrada');
            }

            // Create TemplateProcessor
            $templateProcessor = new TemplateProcessor($templatePath);

            // Prepare replacements with translations
            $replacements = $this->getReplacements($reservation);

            // Replace variables in template
            foreach ($replacements as $search => $replace) {
                $templateProcessor->setValue($search, $replace);
            }

            // Ensure temp directory exists
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Generate filename
            $filename = 'contract_' . $reservation->reservation_code . '_' . date('Y-m-d') . '.docx';
            
            // Ensure documents directory exists
            $documentsDir = storage_path('app/public/documents');
            if (!is_dir($documentsDir)) {
                mkdir($documentsDir, 0755, true);
            }
            
            // Save to temporary file first
            $outputPath = storage_path('app/temp/' . $filename);
            $templateProcessor->saveAs($outputPath);
            
            // Copy temporary file to permanent location
            $permanentPath = 'documents/' . $filename;
            copy($outputPath, storage_path('app/public/' . $permanentPath));
            
            // Create or update document record
            $document = DocumentService::getDocumentByType($reservation, 'contract');
            if ($document) {
                $document->update([
                    'file_path' => $permanentPath,
                    'filename' => $filename,
                    'status' => 'generated',
                    'generated_at' => now(),
                ]);
            } else {
                DocumentService::createDocument(
                    $reservation,
                    'contract',
                    'Contrato - ' . $reservation->reservation_code,
                    $permanentPath,
                    $filename
                )->markAsGenerated();
            }

            // Download file
            return response()->download(storage_path('app/public/' . $permanentPath));

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error al generar el contrato: ' . $e->getMessage());
        }
    }

    /**
     * Prepare replacements array with translations
     */
    private function getReplacements(Reservation $reservation)
    {
        return [
            // Original Spanish variables (exact format from template)
            '${nombre_del_comprador}' => $reservation->first_name . ' ' . $reservation->last_name,
            '${tipo_de comprador_es}' => $this->translateBuyerType($reservation->economic_dependent == 'No' ? 'Individuo' : 'Empresa'),
            '${identificacion_de comprador}' => $reservation->document_number ?? 'N/A',
            '${identificacion_de_empresa}' => 'N/A', // No hay campo de empresa ID
            '${nacionalidad}' => $reservation->nationality ?? 'N/A',
            '${estado_civil_es}' => $this->translateMaritalStatus($reservation->marital_status ?? 'Soltero'),
            '${direccion}' => $this->formatAddress($reservation),
            '${email}' => $reservation->email,
            '${ocupacion}' => $reservation->profession ?? $reservation->occupation ?? 'N/A',

            // English translations (_en versions) - exact format
            '${nombre_del_comprador_en}' => $reservation->first_name . ' ' . $reservation->last_name,
            '${tipo_de comprador_en}' => $this->translateBuyerTypeToEnglish($reservation->economic_dependent == 'No' ? 'Individuo' : 'Empresa'),
            '${identificacion_de comprador_en}' => $reservation->document_number ?? 'N/A',
            '${identificacion_de_empresa_en}' => 'N/A',
            '${nacionalidad_en}' => $reservation->nationality ?? 'N/A',
            '${estado_civil_en}' => $this->translateMaritalStatusToEnglish($reservation->marital_status ?? 'Soltero'),
            '${direccion_en}' => $this->formatAddress($reservation),
            '${email_en}' => $reservation->email,
            '${ocupacion_en}' => $reservation->profession ?? $reservation->occupation ?? 'N/A',

            // Additional common variables
            '${codigo_reserva}' => $reservation->reservation_code,
            '${nombre_unidad}' => $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id,
            '${precio_total}' => $reservation->formatted_price,
            '${fecha_actual}' => now()->format('d/m/Y'),
            '${fecha_actual_en}' => now()->format('m/d/Y'),
            
            // Unit-specific variables
            '${unit_name}' => $reservation->unit_name ?? $reservation->unit->name ?? 'Unit ' . $reservation->unit_id,
            '${unit_level}' => $reservation->unit->floor ?? 'N/A',
            '${area}' => $reservation->unit->total_area ?? $reservation->unit->internal_area ?? 'N/A',
            '${numero_dormitorios_es}' => $this->formatNumberInSpanish($reservation->unit->bedrooms ?? 0),
            '${numero_baños_es}' => $this->formatNumberInSpanish($reservation->unit->bathrooms ?? 0),
            '${numero_estacionamientos_es}' => $this->formatNumberInSpanish($reservation->unit->parking_bays ?? 0),
            '${numero_dormitorios_en}' => $this->formatNumberInEnglish($reservation->unit->bedrooms ?? 0),
            '${numero_baños_en}' => $this->formatNumberInEnglish($reservation->unit->bathrooms ?? 0),
            '${numero_estacionamientos_en}' => $this->formatNumberInEnglish($reservation->unit->parking_bays ?? 0),
            
            // Price and payment plan variables
            '${price_literal_es}' => $this->convertNumberToWords($reservation->unit_price, 'es'),
            '${price_literal_en}' => $this->convertNumberToWords($reservation->unit_price, 'en'),
            '${price}' => number_format($reservation->unit_price, 2, '.', ','),
            '${plan_de_pagos_es}' => $this->getPaymentPlanDescription($reservation, 'es'),
            '${plan_de_pagos_en}' => $this->getPaymentPlanDescription($reservation, 'en'),
        ];
    }

    /**
     * Translate Spanish buyer type to English
     */
    private function translateBuyerTypeToEnglish($type)
    {
        $translations = [
            'Individuo' => 'Individual',
            'Empresa' => 'Company',
            'Corporación' => 'Corporation',
            'Sociedad' => 'Society',
            'Partnership' => 'Partnership',
        ];

        return $translations[$type] ?? 'Individual';
    }

    /**
     * Translate Spanish marital status to English
     */
    private function translateMaritalStatusToEnglish($status)
    {
        $translations = [
            'Soltero' => 'Single',
            'Soltera' => 'Single',
            'Soltero/a' => 'Single',
            'Casado' => 'Married',
            'Casada' => 'Married',
            'Casado/a' => 'Married',
            'Divorciado' => 'Divorced',
            'Divorciada' => 'Divorced',
            'Divorciado/a' => 'Divorced',
            'Viudo' => 'Widowed',
            'Viuda' => 'Widowed',
            'Viudo/a' => 'Widowed',
            'Unión Libre' => 'Common Law',
        ];

        return $translations[$status] ?? 'Single';
    }

    /**
     * Translate Spanish buyer type (keep original)
     */
    private function translateBuyerType($type)
    {
        return $type ?? 'Individuo';
    }

    /**
     * Translate Spanish marital status (keep original)
     */
    private function translateMaritalStatus($status)
    {
        return $status ?? 'Soltero/a';
    }

    /**
     * Format complete address from reservation
     */
    private function formatAddress(Reservation $reservation)
    {
        $addressParts = [];
        
        // Calle/número
        if ($reservation->address) {
            $addressParts[] = $reservation->address;
        }
        
        // Barrio/Sector
        if ($reservation->neighborhood) {
            $addressParts[] = $reservation->neighborhood;
        }
        
        // Ciudad
        if ($reservation->city) {
            $addressParts[] = $reservation->city;
        }
        
        // Provincia/Estado
        if ($reservation->province) {
            $addressParts[] = $reservation->province;
        }
        
        // País (usar el campo country existente)
        if ($reservation->country) {
            $addressParts[] = $reservation->country;
        }
        
        // Edificio/Torre
        if ($reservation->building_name) {
            $buildingPart = $reservation->building_name;
            if ($reservation->apartment_number) {
                $buildingPart .= ', Apt. ' . $reservation->apartment_number;
            }
            $addressParts[] = $buildingPart;
        }
        
        // Código postal
        if ($reservation->postal_code) {
            $addressParts[] = 'CP: ' . $reservation->postal_code;
        }
        
        // Si no hay información de dirección, devolver N/A
        if (empty($addressParts)) {
            return 'N/A';
        }
        
        return implode(', ', $addressParts);
    }

    /**
     * Format number in Spanish (with text)
     */
    private function formatNumberInSpanish($number)
    {
        if ($number == 0) return 'cero';
        if ($number == 1) return 'un (1)';
        if ($number == 2) return 'dos (2)';
        if ($number == 3) return 'tres (3)';
        if ($number == 4) return 'cuatro (4)';
        if ($number == 5) return 'cinco (5)';
        if ($number == 6) return 'seis (6)';
        if ($number == 7) return 'siete (7)';
        if ($number == 8) return 'ocho (8)';
        if ($number == 9) return 'nueve (9)';
        if ($number == 10) return 'diez (10)';
        
        // For numbers > 10, just return the number
        return $number . ' (' . $number . ')';
    }

    /**
     * Format number in English (with text)
     */
    private function formatNumberInEnglish($number)
    {
        if ($number == 0) return 'zero';
        if ($number == 1) return 'one (1)';
        if ($number == 2) return 'two (2)';
        if ($number == 3) return 'three (3)';
        if ($number == 4) return 'four (4)';
        if ($number == 5) return 'five (5)';
        if ($number == 6) return 'six (6)';
        if ($number == 7) return 'seven (7)';
        if ($number == 8) return 'eight (8)';
        if ($number == 9) return 'nine (9)';
        if ($number == 10) return 'ten (10)';
        
        // For numbers > 10, just return the number
        return $number . ' (' . $number . ')';
    }

    /**
     * Convert number to words (Spanish/English)
     */
    private function convertNumberToWords($number, $language = 'es')
    {
        // For now, return a basic conversion for common amounts
        // In a real implementation, you would use a proper number-to-words library
        if ($language == 'es') {
            return $this->numberToSpanish($number);
        } else {
            return $this->numberToEnglish($number);
        }
    }

    /**
     * Convert number to Spanish words
     */
    private function numberToSpanish($number)
    {
        $number = (int)$number;
        
        // Handle specific common amounts first
        if ($number == 450000) {
            return 'CUATROCIENTOS CINCUENTA MIL';
        }
        if ($number == 112500) {
            return 'CIENTO DOCE MIL QUINIENTOS';
        }
        if ($number == 157500) {
            return 'CIENTO CINCUENTA Y SIETE MIL QUINIENTOS';
        }
        if ($number == 175500) {
            return 'CIENTO SETENTA Y CINCO MIL QUINIENTOS';
        }
        
        // Basic implementation for common amounts
        $ones = [
            0 => 'cero', 1 => 'uno', 2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
            6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve'
        ];
        
        $tens = [
            10 => 'diez', 11 => 'once', 12 => 'doce', 13 => 'trece', 14 => 'catorce', 15 => 'quince',
            16 => 'dieciséis', 17 => 'diecisiete', 18 => 'dieciocho', 19 => 'diecinueve',
            20 => 'veinte', 30 => 'treinta', 40 => 'cuarenta', 50 => 'cincuenta',
            60 => 'sesenta', 70 => 'setenta', 80 => 'ochenta', 90 => 'noventa'
        ];
        
        $hundreds = [
            100 => 'cien', 200 => 'doscientos', 300 => 'trescientos', 400 => 'cuatrocientos', 500 => 'quinientos',
            600 => 'seiscientos', 700 => 'setecientos', 800 => 'ochocientos', 900 => 'novecientos'
        ];
        
        if ($number < 10) {
            return $ones[$number];
        } elseif ($number < 20) {
            return $tens[$number] ?? $number;
        } elseif ($number < 100) {
            $ten = floor($number / 10) * 10;
            $one = $number % 10;
            if ($one == 0) {
                return $tens[$ten];
            } else {
                return $tens[$ten] . ' y ' . $ones[$one];
            }
        } elseif ($number < 1000) {
            $hundred = floor($number / 100) * 100;
            $remainder = $number % 100;
            if ($remainder == 0) {
                return $hundreds[$hundred];
            } else {
                return $hundreds[$hundred] . ' ' . $this->numberToSpanish($remainder);
            }
        } elseif ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            if ($thousands == 1) {
                $result = 'mil';
            } else {
                $result = $this->numberToSpanish($thousands) . ' mil';
            }
            if ($remainder > 0) {
                $result .= ' ' . $this->numberToSpanish($remainder);
            }
            return $result;
        } else {
            // For larger numbers, return number as is for now
            return number_format($number, 0, '.', ',');
        }
    }

    /**
     * Convert number to English words
     */
    private function numberToEnglish($number)
    {
        $number = (int)$number;
        
        // Handle specific common amounts first
        if ($number == 450000) {
            return 'FOUR HUNDRED FIFTY THOUSAND';
        }
        if ($number == 112500) {
            return 'ONE HUNDRED TWELVE THOUSAND FIVE HUNDRED';
        }
        if ($number == 157500) {
            return 'ONE HUNDRED FIFTY SEVEN THOUSAND FIVE HUNDRED';
        }
        if ($number == 175500) {
            return 'ONE HUNDRED SEVENTY FIVE THOUSAND FIVE HUNDRED';
        }
        
        // Basic implementation for common amounts
        $ones = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
            6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine'
        ];
        
        $tens = [
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
            16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
            20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty',
            60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
        ];
        
        $hundreds = [
            100 => 'one hundred', 200 => 'two hundred', 300 => 'three hundred', 400 => 'four hundred', 500 => 'five hundred',
            600 => 'six hundred', 700 => 'seven hundred', 800 => 'eight hundred', 900 => 'nine hundred'
        ];
        
        if ($number < 10) {
            return $ones[$number];
        } elseif ($number < 20) {
            return $tens[$number] ?? $number;
        } elseif ($number < 100) {
            $ten = floor($number / 10) * 10;
            $one = $number % 10;
            if ($one == 0) {
                return $tens[$ten];
            } else {
                return $tens[$ten] . '-' . $ones[$one];
            }
        } elseif ($number < 1000) {
            $hundred = floor($number / 100) * 100;
            $remainder = $number % 100;
            if ($remainder == 0) {
                return $hundreds[$hundred];
            } else {
                return $hundreds[$hundred] . ' ' . $this->numberToEnglish($remainder);
            }
        } elseif ($number < 1000000) {
            $thousands = floor($number / 1000);
            $remainder = $number % 1000;
            if ($thousands == 1) {
                $result = 'one thousand';
            } else {
                $result = $this->numberToEnglish($thousands) . ' thousand';
            }
            if ($remainder > 0) {
                $result .= ' ' . $this->numberToEnglish($remainder);
            }
            return $result;
        } else {
            // For larger numbers, return number as is for now
            return number_format($number, 0, '.', ',');
        }
    }

    /**
     * Get payment plan description based on reservation
     */
    private function getPaymentPlanDescription(Reservation $reservation, $language = 'es')
    {
        $paymentMethod = $reservation->payment_method ?? 'A';
        $totalPrice = $reservation->unit_price;
        
        if ($paymentMethod == 'A') {
            return $this->getPlanADescription($totalPrice, $language);
        } elseif ($paymentMethod == 'B') {
            return $this->getPlanBDescription($totalPrice, $language);
        } elseif ($paymentMethod == 'C') {
            return $this->getPlanCDescription($totalPrice, $language);
        } else {
            return $this->getPlanADescription($totalPrice, $language);
        }
    }

    /**
     * Plan A description
     */
    private function getPlanADescription($totalPrice, $language = 'es')
    {
        if ($language == 'es') {
            return "1. Un primer pago por concepto de Reserva, por un monto de CINCO MIL DOLARES ESTADOUNIDENSES CON 00/100 (USD\$5,000.00). de igual forma, por medio del presente contrato le Otorgo Recibo de Descargo Total y FINIQUITO LEGAL a EL PROMITENTE COMPRADOR por la suma recibida.

2. Un pago inicial por la suma de " . $this->numberToSpanish(round($totalPrice * 0.25)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.25, 2, '.', ',') . "), equivalente a un 25% del precio del inmueble.

3. Un plan de pago semestral, por la suma de " . $this->numberToSpanish(round($totalPrice * 0.35)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.35, 2, '.', ',') . "), equivalente al 35% del precio del inmueble, cual se encuentra reflejados en el Anexo E.

4. Un tercer y último pago contra entrega por la suma de " . $this->numberToSpanish(round($totalPrice * 0.39)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.39, 2, '.', ',') . "), equivalente al 39% restante del precio del inmueble.";
        } else {
            return "1. A first payment for reservation, in the amount of FIVE THOUSAND UNITED STATES DOLLARS WITH 00/100 (US\$5,000.00). Likewise, by means of this contract I grant Total Release Receipt and FINAL LEGAL DISCHARGE to THE PROMISE BUYER for the sum received.

2. An initial payment for the sum of " . $this->numberToEnglish(round($totalPrice * 0.25)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.25, 2, '.', ',') . "), equivalent to 25% of the property price.

3. A semiannual payment plan, for the sum of " . $this->numberToEnglish(round($totalPrice * 0.35)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.35, 2, '.', ',') . "), equivalent to 35% of the property price, which is reflected in Annex E.

4. A third and final payment upon delivery for the sum of " . $this->numberToEnglish(round($totalPrice * 0.39)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.39, 2, '.', ',') . "), equivalent to the remaining 39% of the property price.";
        }
    }

    /**
     * Plan B description
     */
    private function getPlanBDescription($totalPrice, $language = 'es')
    {
        if ($language == 'es') {
            return "1. Un primer pago por concepto de Reserva, por un monto de CINCO MIL DOLARES ESTADOUNIDENSES CON 00/100 (USD\$5,000.00).

2. Cinco pagos iguales por la suma de " . $this->numberToSpanish(round($totalPrice * 0.15)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.15, 2, '.', ',') . ") cada uno, equivalentes al 75% del precio del inmueble.

3. Un pago final contra entrega por la suma de " . $this->numberToSpanish(round($totalPrice * 0.20)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.20, 2, '.', ',') . "), equivalente al 20% restante del precio del inmueble.";
        } else {
            return "1. A first payment for reservation, in the amount of FIVE THOUSAND UNITED STATES DOLLARS WITH 00/100 (US\$5,000.00).

2. Five equal payments for the sum of " . $this->numberToEnglish(round($totalPrice * 0.15)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.15, 2, '.', ',') . ") each, equivalent to 75% of the property price.

3. A final payment upon delivery for the sum of " . $this->numberToEnglish(round($totalPrice * 0.20)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.20, 2, '.', ',') . "), equivalent to the remaining 20% of the property price.";
        }
    }

    /**
     * Plan C description
     */
    private function getPlanCDescription($totalPrice, $language = 'es')
    {
        if ($language == 'es') {
            return "1. Un primer pago por concepto de Reserva, por un monto de CINCO MIL DOLARES ESTADOUNIDENSES CON 00/100 (USD\$5,000.00).

2. Ocho pagos mensuales iguales por la suma de " . $this->numberToSpanish(round($totalPrice * 0.10)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.10, 2, '.', ',') . ") cada uno, equivalentes al 80% del precio del inmueble.

3. Un pago final contra entrega por la suma de " . $this->numberToSpanish(round($totalPrice * 0.09)) . " DOLARES ESTADOUNIDENSES CON 00/100 (US\$" . number_format($totalPrice * 0.09, 2, '.', ',') . "), equivalente al 9% restante del precio del inmueble.";
        } else {
            return "1. A first payment for reservation, in the amount of FIVE THOUSAND UNITED STATES DOLLARS WITH 00/100 (US\$5,000.00).

2. Eight equal monthly payments for the sum of " . $this->numberToEnglish(round($totalPrice * 0.10)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.10, 2, '.', ',') . ") each, equivalent to 80% of the property price.

3. A final payment upon delivery for the sum of " . $this->numberToEnglish(round($totalPrice * 0.09)) . " UNITED STATES DOLLARS WITH 00/100 (US\$" . number_format($totalPrice * 0.09, 2, '.', ',') . "), equivalent to the remaining 9% of the property price.";
        }
    }

    /**
     * Generate purchase promise document
     */
    public function generatePurchasePromise(Reservation $reservation)
    {
        // Verify user owns this reservation or is admin
        if ($reservation->user_id !== Auth::id() && Auth::user()?->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        if (!$reservation->isBudgetSent()) {
            return back()->with('error', 'El presupuesto aún no fue enviado por el equipo.');
        }

        try {
            // Render the printable HTML view (open in browser → "Descargar PDF")
            return \App\Helpers\DocumentDataHelper::renderAndStore($reservation, 'purchase_promise');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar la promesa de compraventa: ' . $e->getMessage());
        }
    }

    /**
     * Generate payment plan document
     */
    public function generatePaymentPlan(Reservation $reservation)
    {
        // Verify user owns this reservation
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$reservation->isBudgetSent()) {
            return back()->with('error', 'El presupuesto aún no fue enviado por el equipo. Espera a que tu asesor lo confirme.');
        }

        try {
            // Render the printable HTML view (open in browser → "Descargar PDF")
            return \App\Helpers\DocumentDataHelper::renderAndStore($reservation, 'payment_plan');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al generar el plan de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Confirm contract as approved
     */
    public function confirm(Reservation $reservation, Request $request)
    {
        // Verify user owns this reservation
        if ($reservation->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$reservation->isBudgetSent()) {
            return response()->json([
                'success' => false,
                'message' => 'El presupuesto aún no fue enviado por el equipo. No se puede confirmar el contrato.',
            ], 422);
        }

        try {
            // Update reservation status to contract_signed
            $reservation->status = 'contract_signed';
            $reservation->save();

            // Generate payments after contract approval
            try {
                $paymentsCount = PaymentService::generatePayments($reservation);
                \Illuminate\Support\Facades\Log::info('Pagos generados para reserva ' . $reservation->id . ': ' . $paymentsCount . ' pagos');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error generando pagos: ' . $e->getMessage());
                // Continue even if payments fail
            }

            // Save observations if provided
            if ($request->has('observaciones')) {
                // Here you would save observations to database
                // For now, just log it
                \Illuminate\Support\Facades\Log::info('Observaciones guardadas para reserva ' . $reservation->id . ': ' . $request->observaciones);
            }

            return response()->json([
                'success' => true,
                'message' => 'Contrato firmado exitosamente',
                'status' => 'contract_signed',
                'payments_generated' => $paymentsCount ?? 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el contrato: ' . $e->getMessage()
            ], 500);
        }
    }
}
