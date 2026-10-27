<?php

/*
|--------------------------------------------------------------------------
| Datos de la empresa / proyecto (BAHÍA MAR)
|--------------------------------------------------------------------------
| Datos fijos que alimentan los documentos imprimibles (comprobante de pago,
| hoja de datos para transferencia) y los correos. Editar aquí — o sobreponer
| vía variables de entorno — sin tocar las vistas.
*/

return [

    // Color de marca para piezas que se inyectan fuera de los layouts (salvavidas).
    'accent'       => env('COMPANY_ACCENT', '#074540'),
    'salvavidas'   => (bool) env('COMPANY_SALVAVIDAS', true),
    'brand'        => env('COMPANY_BRAND', 'BAHÍA MAR'),
    'project'      => env('COMPANY_PROJECT', 'Bahía Mar'),
    'group'        => env('COMPANY_GROUP', 'BAHÍA MAR'),
    'location'     => env('COMPANY_LOCATION', 'República Dominicana'),

    // Emisor de los comprobantes
    // Landmass Capital, con los datos que pasó Miguel el 22 oct (grupo «Izan
    // Chatbot Landmass»). Antes ponía la sociedad de Makai (IGUANAS LAKE).
    'legal_name'   => env('COMPANY_LEGAL_NAME', 'Landmass Capital'),
    'rnc'          => env('COMPANY_RNC', '133-04860-9'),
    'address'      => env('COMPANY_ADDRESS', 'Edificio Cedro, Punta Cana Village · Blvd. 1ro. de Noviembre, Oficinas 2008/2009 · Punta Cana 23000'),

    // Contacto
    'support_email' => env('COMPANY_SUPPORT_EMAIL', 'hello@landmasscapital.com'),
    // Único número de contacto de Bahía Mar, también el del WhatsApp (Miguel,
    // 26 oct: «poner ese número en todas las secciones … como único número»).
    'phone'         => env('COMPANY_PHONE', '+1 829 599 7785'),
    'website'       => env('COMPANY_WEBSITE', 'landmasscapital.com'),

    // Web pública del proyecto. Ojo, no es la misma que `website`, que es la
    // de la promotora: aquí va el sitio de Bahía Mar. Es a donde se manda a
    // quien decide irse desde el popup de abandono, en vez de devolverlo al
    // historial del navegador, que acababa en la pantalla de registro
    // (Miguel, 11 de octubre).
    'site'          => env('COMPANY_SITE', 'https://bahiamarterrenas.com'),

    // WhatsApp de contacto, solo dígitos (formato de wa.me). El de Bahía Mar
    // que dio Miguel el 26 oct; antes estaba vacío porque el heredado era el
    // de Duna/Makai. Vacío = los botones de WhatsApp salen desactivados.
    'whatsapp'      => env('COMPANY_WHATSAPP', '18295997785'),

    // Perfiles sociales del proyecto (URL completa). Vacío = el icono del
    // pie sale apagado y sin enlace, en vez de no hacer nada al pulsarlo,
    // que es lo que pasaba (Kary, 21 oct: «no funcionan»).
    'social' => [
        'facebook'  => env('COMPANY_FACEBOOK', ''),
        'instagram' => env('COMPANY_INSTAGRAM', ''),
    ],

    // Ficha de alta del broker (/broker/alta), de la que pasó Miguel el 26 oct
    // (Popup_Alta_Broker.html). Comisión null = no se muestra.
    'broker_alta' => [
        'tax_id_label' => 'RNC / Cédula',
        'phone_ph'     => '+1 (809) 000-0000',
        'currency_ph'  => 'USD / DOP / EUR…',
        'law_note'     => 'Cumplimiento de la Ley 155-17 (prevención de lavado de activos). Acredita tu identidad profesional antes de operar.',
        'kinds' => [
            'broker' => ['commission' => 4, 'tax_note' => 'Retenciones de ley (18 % ITBIS + 10 % ISR).'],
            'agency' => ['commission' => 6, 'tax_note' => 'Retención del 30 % del ITBIS.'],
        ],
        'docs' => [
            'broker' => [
                'id_doc'   => 'Documento de identidad (pasaporte o cédula)',
                'bank_ref' => 'Carta de referencia bancaria',
                'pro_ref'  => 'Carta de referencia profesional',
            ],
            'agency' => [
                'registro'    => 'Certificado de Registro Mercantil vigente',
                'rnc_cert'    => 'Certificación de inscripción en el RNC',
                'articles'    => 'Estatutos sociales vigentes',
                'designacion' => 'Documento de designación del representante',
                'rep_id'      => 'Identidad del representante',
                'acciones'    => 'Lista de suscripción y pago de acciones',
                'bank_ref'    => 'Carta de referencia bancaria',
                'pro_ref'     => 'Carta de referencia profesional',
            ],
        ],
    ],

    // Firmante autorizado de los comprobantes
    'signer_name'   => env('COMPANY_SIGNER_NAME', 'Landmass'),
    'signer_title'  => env('COMPANY_SIGNER_TITLE', 'Departamento de Finanzas'),

    // Datos bancarios para transferencias en USD
    'bank' => [
        'intermediary_name'    => env('COMPANY_BANK_INT_NAME', 'Citibank N.A, New York Branch'),
        'intermediary_account' => env('COMPANY_BANK_INT_ACCOUNT', '36265334'),
        'intermediary_address' => env('COMPANY_BANK_INT_ADDRESS', '111 Wall Street, New York, USA 10043'),
        'swift'                => env('COMPANY_BANK_SWIFT', 'CITIUS33XXX'),
        'aba'                  => env('COMPANY_BANK_ABA', '021000089'),
        'beneficiary_bank'     => env('COMPANY_BANK_BENEF', 'Banco Múltiple López de Haro, S.A. · Ave. Sarasota No. 20, Santo Domingo, Rep. Dom. 10109'),
        'account_holder'       => env('COMPANY_BANK_HOLDER', 'IGUANAS LAKE CONDO & RESIDENCE SRL'),
        'account_number'       => env('COMPANY_BANK_ACCOUNT', '4010388162'),
    ],
];
