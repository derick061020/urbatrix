<?php

/*
|--------------------------------------------------------------------------
| Datos de la empresa / proyecto (SIEBE · Siebe Property Group)
|--------------------------------------------------------------------------
| Datos fijos que alimentan los documentos imprimibles (comprobante de pago,
| hoja de datos para transferencia) y los correos. Editar aquí — o sobreponer
| vía variables de entorno — sin tocar las vistas.
*/

return [

    'brand'        => env('COMPANY_BRAND', 'SIEBE'),
    'project'      => env('COMPANY_PROJECT', 'Siebe Residences'),
    'group'        => env('COMPANY_GROUP', 'Siebe Property Group'),
    'location'     => env('COMPANY_LOCATION', 'Aruba'),

    // Emisor de los comprobantes.
    // PENDIENTE: razón social, RNC/KvK y dirección fiscal reales de Siebe.
    // Se dejan vacíos a propósito: antes estaban los de Makai (IGUANAS LAKE
    // CONDO & RESIDENCE SRL, Rep. Dominicana) y no pueden salir impresos aquí.
    'legal_name'   => env('COMPANY_LEGAL_NAME', 'Siebe Property Group'),
    'rnc'          => env('COMPANY_RNC', ''),
    'address'      => env('COMPANY_ADDRESS', 'Aruba'),

    // Contacto
    'support_email' => env('COMPANY_SUPPORT_EMAIL', 'hello@sieberesidences.com'),
    'phone'         => env('COMPANY_PHONE', ''),
    'website'       => env('COMPANY_WEBSITE', 'sieberesidences.com'),

    // Web pública del proyecto. Ojo, no es la misma que `website`, que es la
    // de la promotora: aquí va el sitio de Siebe Residences. Es a donde se manda a
    // quien decide irse desde el popup de abandono, en vez de devolverlo al
    // historial del navegador, que acababa en la pantalla de registro
    // (Miguel, 11 de octubre).
    'site'          => env('COMPANY_SITE', 'https://sieberesidences.com'),

    // La promesa de compraventa sólo se genera con la plantilla ya adaptada a
    // la sociedad de Siebe (hoy lleva los datos legales de Makai).
    'promise_enabled' => (bool) env('COMPANY_PROMISE_ENABLED', false),

    // WhatsApp de contacto, solo dígitos (formato de wa.me).
    // Vacío a propósito: el que había era el de Duna/Makai y los botones
    // llevaban a su chat. Mientras esté vacío, los botones de WhatsApp
    // salen desactivados en vez de llevar al sitio equivocado.
    'whatsapp'      => env('COMPANY_WHATSAPP', ''),

    // Perfiles sociales del proyecto (URL completa). Vacío = el icono del
    // pie sale apagado y sin enlace, en vez de no hacer nada al pulsarlo,
    // que es lo que pasaba (Kary, 21 oct: «no funcionan»).
    'social' => [
        'facebook'  => env('COMPANY_FACEBOOK', ''),
        'instagram' => env('COMPANY_INSTAGRAM', ''),
    ],

    // Firmante autorizado de los comprobantes
    'signer_name'   => env('COMPANY_SIGNER_NAME', 'Siebe Property Group'),
    'signer_title'  => env('COMPANY_SIGNER_TITLE', 'Departamento de Finanzas'),

    // Datos bancarios para transferencias.
    // PENDIENTE: son los de Siebe, no los de Makai. Vacíos hasta tenerlos: la
    // hoja de transferencia muestra los campos en blanco en vez de una cuenta
    // que no es la del proyecto.
    'bank' => [
        'intermediary_name'    => env('COMPANY_BANK_INT_NAME', ''),
        'intermediary_account' => env('COMPANY_BANK_INT_ACCOUNT', ''),
        'intermediary_address' => env('COMPANY_BANK_INT_ADDRESS', ''),
        'swift'                => env('COMPANY_BANK_SWIFT', ''),
        'aba'                  => env('COMPANY_BANK_ABA', ''),
        'beneficiary_bank'     => env('COMPANY_BANK_BENEF', ''),
        'account_holder'       => env('COMPANY_BANK_HOLDER', ''),
        'account_number'       => env('COMPANY_BANK_ACCOUNT', ''),
    ],
];
