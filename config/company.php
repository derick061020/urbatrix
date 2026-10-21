<?php

/*
|--------------------------------------------------------------------------
| Datos de la empresa / proyecto (MAKAI · Duna Development Group)
|--------------------------------------------------------------------------
| Datos fijos que alimentan los documentos imprimibles (comprobante de pago,
| hoja de datos para transferencia) y los correos. Editar aquí — o sobreponer
| vía variables de entorno — sin tocar las vistas.
*/

return [

    'brand'        => env('COMPANY_BRAND', 'MAKAI'),
    'project'      => env('COMPANY_PROJECT', 'Makai Residences'),
    'group'        => env('COMPANY_GROUP', 'Duna Development Group'),
    'location'     => env('COMPANY_LOCATION', 'Cap Cana, República Dominicana'),

    // Emisor de los comprobantes
    'legal_name'   => env('COMPANY_LEGAL_NAME', 'IGUANAS LAKE CONDO & RESIDENCE SRL'),
    'rnc'          => env('COMPANY_RNC', ''),
    'address'      => env('COMPANY_ADDRESS', 'Cap Cana, Punta Cana, República Dominicana'),

    // Contacto
    'support_email' => env('COMPANY_SUPPORT_EMAIL', 'hello@makairesidences.com'),
    'phone'         => env('COMPANY_PHONE', '+1 849 499 2578'),
    'website'       => env('COMPANY_WEBSITE', 'makairesidences.com'),

    // Web pública del proyecto. Ojo, no es la misma que `website`, que es la
    // de la promotora: aquí va el sitio de Makai. Es a donde se manda a
    // quien decide irse desde el popup de abandono, en vez de devolverlo al
    // historial del navegador, que acababa en la pantalla de registro
    // (Miguel, 11 de octubre).
    'site'          => env('COMPANY_SITE', 'https://makairesidences.com'),

    // WhatsApp de contacto, solo dígitos (formato de wa.me).
    // El de Duna, el que ya venía escrito a mano en las vistas.
    'whatsapp'      => env('COMPANY_WHATSAPP', '18097109044'),

    // Perfiles sociales del proyecto (URL completa). Vacío = el icono del
    // pie sale apagado y sin enlace, en vez de no hacer nada al pulsarlo,
    // que es lo que pasaba (Kary, 21 oct: «no funcionan»).
    'social' => [
        'facebook'  => env('COMPANY_FACEBOOK', 'https://www.facebook.com/profile.php?id=61574783614005'),
        'instagram' => env('COMPANY_INSTAGRAM', 'https://www.instagram.com/makairesidences/'),
        'x'         => env('COMPANY_X', ''),
    ],

    // Firmante autorizado de los comprobantes
    'signer_name'   => env('COMPANY_SIGNER_NAME', 'Duna Development Group'),
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
