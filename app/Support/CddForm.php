<?php

namespace App\Support;

/**
 * Campos de los dos formularios KYC de Siebe Group («KYC form Natural Persons»
 * y «KYC form Legal Entities», Miguel, 26 oct), en el mismo orden que el papel.
 * Lo usan el portal del cliente (para pintarlo) y el expediente del admin
 * (para leerlo), así que un campo nuevo se añade sólo aquí.
 *
 * Cada campo: [clave, etiqueta, tipo, obligatorio, opciones]. Tipos: text,
 * date, email, tel, textarea, select, radio, checks, money, percent.
 */
class CddForm
{
    public const MARITAL = [
        'never_married' => 'Soltero/a (nunca casado/a)',
        'divorced'      => 'Divorciado/a',
        'married'       => 'Casado/a',
        'cohabitation'  => 'Contrato de convivencia',
    ];

    public const INCOME = [
        'employed'      => 'Por cuenta ajena',
        'self_employed' => 'Por cuenta propia',
        'retired'       => 'Jubilado/a',
        'other'         => 'Otro',
    ];

    public const PAYMENT = [
        'bank_financing' => 'Financiación bancaria',
        'personal_funds' => 'Fondos propios',
        'third_party'    => 'Préstamo de un tercero',
        'other'          => 'Otro',
    ];

    /** Datos de cada comprador (persona física). */
    public static function buyerFields(): array
    {
        return [
            ['full_name',         'Nombre completo',                    'text',   true],
            ['former_names',      'Nombres anteriores',                 'text',   false],
            ['country_residency', 'País de residencia',                 'text',   true],
            ['dob',               'Fecha de nacimiento',                'date',   true],
            ['nationality',       'Nacionalidad',                       'text',   true],
            ['tax_id',            'Nº de identificación fiscal / censo','text',   true],
            ['address',           'Domicilio',                          'text',   true],
            ['phone',             'Teléfono',                           'tel',    true],
            ['email',             'Email',                              'email',  true],
            ['marital_status',    'Estado civil',                       'select', true, self::MARITAL],
            ['spouse_name',       'Nombre del cónyuge o pareja',        'text',   false],
            ['spouse_dob',        'Fecha de nacimiento del cónyuge',    'date',   false],
        ];
    }

    /** Detalle de la fuente de ingresos según el tipo elegido. */
    public static function incomeFields(): array
    {
        return [
            'employed'      => [['occupation', 'Ocupación'], ['employer', 'Empresa y lugar de trabajo']],
            'self_employed' => [['company', 'Nombre de la empresa'], ['activities', 'Actividad principal']],
            'retired'       => [['pension', 'Pensión'], ['former_employer', 'Antigua empresa']],
            'other'         => [['other', 'Descripción']],
        ];
    }

    public static function paymentFields(): array
    {
        return [
            'bank_financing' => [['bank', 'Banco'], ['amount', 'Importe financiado']],
            'personal_funds' => [['bank', 'Banco'], ['account_type', 'Tipo de cuenta'], ['account_number', 'Número de cuenta'], ['amount', 'Importe con fondos propios']],
            'third_party'    => [['name', 'Nombre del tercero'], ['relationship', 'Relación con el tercero'], ['amount', 'Importe financiado por el tercero']],
            'other'          => [['type', 'Tipo de operación'], ['source', 'Origen de los fondos'], ['amount', 'Importe']],
        ];
    }

    /** Persona jurídica: identificación de la sociedad. */
    public static function companyFields(): array
    {
        return [
            ['legal_form',     'Forma jurídica',                 'text',     true],
            ['legal_name',     'Razón social',                   'text',     true],
            ['trade_name',     'Nombre comercial',               'text',     false],
            ['country_inc',    'País de constitución',           'text',     true],
            ['date_inc',       'Fecha de constitución',          'date',     true],
            ['reg_nr',         'Nº de registro mercantil',       'text',     true],
            ['tax_id',         'Nº de identificación fiscal',    'text',     true],
            ['address',        'Domicilio social',               'text',     true],
            ['share_type',     'Tipo de acciones',               'radio',    true, ['regular' => 'Nominativas', 'bearer' => 'Al portador', 'nominee' => 'Fiduciarias (nominee)']],
            ['representative', 'Nombre del representante',       'text',     true],
            ['income',         'Principal fuente de ingresos de la sociedad', 'textarea', true],
        ];
    }

    public static function uboFields(): array
    {
        return [
            ['name',    'Nombre completo',      'text',    true],
            ['dob',     'Fecha de nacimiento',  'date',    true],
            ['sex',     'Sexo',                 'select',  false, ['m' => 'Hombre', 'f' => 'Mujer']],
            ['address', 'Domicilio',            'text',    true],
            ['econ',    'Propiedad económica',  'percent', true],
            ['control', 'Control',              'percent', true],
        ];
    }

    public static function entityFields(): array
    {
        return [
            ['name',    'Razón social',          'text',    true],
            ['type',    'Tipo de entidad',       'text',    true],
            ['country', 'País de constitución',  'text',    true],
            ['econ',    'Propiedad económica',   'percent', true],
            ['control', 'Control',               'percent', true],
        ];
    }

    public const REASONS = [
        'liability'   => 'Protección patrimonial y gestión del riesgo',
        'tax'         => 'Optimización y eficiencia fiscal',
        'estate'      => 'Planificación sucesoria más sencilla',
        'flexibility' => 'Flexibilidad estructural y operativa',
        'other'       => 'Otro',
    ];

    public const PURPOSE = [
        'main_residence' => 'Será la residencia principal',
        'vacation'       => 'Casa de vacaciones, sin alquilar cuando no se use',
        'vacation_rent'  => 'Casa de vacaciones, alquilada cuando no se use',
        'investment'     => 'Inversión, sin uso personal de los propietarios',
        'other'          => 'Otro',
    ];

    /** Documentos que se adjuntan, según el tipo de comprador. [clave => [etiqueta, obligatorio]] */
    public static function docs(string $type): array
    {
        return $type === 'legal' ? [
            'coc'          => ['Extracto de la Cámara de Comercio (de menos de 3 meses)', true],
            'articles'     => ['Escritura de constitución / estatutos (sociedades extranjeras: copia certificada)', true],
            'ubo_ids'      => ['Copia certificada del pasaporte, permiso de conducir o cédula de cada beneficiario final', true],
            'ubo_address'  => ['Prueba de domicilio de cada beneficiario final (extracto bancario, censo o factura)', true],
            'shareholders' => ['Registro de accionistas de cada entidad de la estructura', true],
            'org_chart'    => ['Organigrama certificado con la declaración de un asesor independiente (si la estructura es compleja)', false],
        ] : [
            'ids'            => ['Dos documentos de identidad oficiales y vigentes de cada comprador', true],
            'address'        => ['Prueba de domicilio de cada comprador', true],
            'income_backup'  => ['Justificantes de la fuente de ingresos (nóminas, carta de la empresa, estados financieros o pensión)', true],
            'payment_backup' => ['Justificantes del método de pago (aprobación bancaria u otros)', true],
        ];
    }
}
