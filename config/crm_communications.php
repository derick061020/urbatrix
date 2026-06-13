<?php

/*
|--------------------------------------------------------------------------
| Catálogo maestro de comunicaciones al comprador
|--------------------------------------------------------------------------
| Define las familias, los tipos de comunicación, los canales que cada tipo
| soporta y el estado real de la plantilla (lista / pendiente).
|
| Las familias marcadas como 'locked' son obligatorias por ley/seguridad:
| siempre se envían y no se pueden apagar desde el panel.
|
| channels válidos: email | whatsapp | inapp
*/

return [

    'channels' => ['email', 'whatsapp', 'inapp'],

    'families' => [
        [
            'key'  => 'onboarding',
            'name' => 'Onboarding / cuenta',
            'types' => [
                ['code' => 'bienvenida_kyc', 'name' => 'Bienvenida + solicitud KYC', 'ch' => ['email', 'whatsapp'], 'tpl' => 'pendiente'],
            ],
        ],
        [
            'key'  => 'reserva',
            'name' => 'Reserva y contratos',
            'types' => [
                ['code' => 'reserva_confirmada',  'name' => 'Reserva confirmada',          'ch' => ['email', 'whatsapp', 'inapp'], 'tpl' => 'pendiente'],
                ['code' => 'plan_pago_firma',     'name' => 'Plan de pagos para firma',     'ch' => ['email', 'inapp'],             'tpl' => 'pendiente'],
                ['code' => 'promesa_cv_firma',    'name' => 'Promesa de compraventa',       'ch' => ['email', 'inapp'],             'tpl' => 'pendiente'],
                ['code' => 'contrato_definitivo', 'name' => 'Contrato definitivo emitido',  'ch' => ['email', 'inapp'],             'tpl' => 'pendiente'],
                ['code' => 'copia_notarial',      'name' => 'Copia notarial disponible',    'ch' => ['email', 'inapp'],             'tpl' => 'pendiente'],
            ],
        ],
        [
            'key'  => 'pagos',
            'name' => 'Pagos',
            'types' => [
                ['code' => 'recibo_pago',        'name' => 'Recibo / comprobante de pago', 'ch' => ['email', 'inapp'],             'tpl' => 'lista'],
                ['code' => 'recordatorio_cuota', 'name' => 'Recordatorio de cuota / mora', 'ch' => ['email', 'whatsapp', 'inapp'], 'tpl' => 'pendiente'],
            ],
        ],
        [
            'key'  => 'obra',
            'name' => 'Obra',
            'types' => [
                ['code' => 'avance_obra', 'name' => 'Avance de obra por hito', 'ch' => ['email', 'whatsapp', 'inapp'], 'tpl' => 'pendiente'],
            ],
        ],
        [
            'key'  => 'videollamadas',
            'name' => 'Videollamadas',
            'types' => [
                ['code' => 'vc_confirmacion',        'name' => 'Confirmación de videollamada', 'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_recordatorio_24h',    'name' => 'Recordatorio 24 h antes',      'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_recordatorio_1h',     'name' => 'Recordatorio 1 h antes',       'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_reprogramacion',      'name' => 'Reprogramación de la cita',    'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_cancelacion_cliente', 'name' => 'Cancelación por el cliente',   'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_anulacion_asesor',    'name' => 'Anulación por el asesor',      'ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
                ['code' => 'vc_seguimiento',         'name' => 'Seguimiento post-videollamada','ch' => ['email', 'whatsapp'], 'tpl' => 'lista'],
            ],
        ],
        [
            'key'  => 'soporte',
            'name' => 'Soporte',
            'types' => [
                ['code' => 'ticket_estado', 'name' => 'Cambio de estado de ticket', 'ch' => ['email', 'inapp'], 'tpl' => 'lista'],
            ],
        ],
        [
            'key'    => 'legal',
            'name'   => 'Legal y seguridad',
            'locked' => true,
            'types'  => [
                ['code' => 'alerta_seguridad',   'name' => 'Alerta de seguridad de la cuenta',         'ch' => ['email'], 'tpl' => 'lista'],
                ['code' => 'modificacion_legal', 'name' => 'Cambio de términos / documentos legales',   'ch' => ['email'], 'tpl' => 'lista'],
            ],
        ],
    ],
];
