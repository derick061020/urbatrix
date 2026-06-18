<?php

namespace App\Support\Imports;

use App\Models\Unit;

/**
 * Campos de la tabla `units` que el admin puede importar desde un CSV.
 * Subconjunto representativo del $fillable de {@see Unit}; el único campo
 * obligatorio es `name`.
 */
class UnitImportResource extends ImportResource
{
    public function key(): string
    {
        return 'units';
    }

    public function label(): string
    {
        return 'Unidades';
    }

    public function model(): string
    {
        return Unit::class;
    }

    public function fields(): array
    {
        return [
            'name'          => ['label' => 'Nombre',           'type' => 'string',  'required' => true, 'sample' => 'Casa 12 - Roble'],
            'custom_id'     => ['label' => 'ID / Código',      'type' => 'string',  'sample' => 'UN-012'],
            'type'          => ['label' => 'Tipo',             'type' => 'string',  'sample' => 'Casa'],
            'status'        => ['label' => 'Estado',           'type' => 'enum',    'enum' => ['AVAILABLE', 'PENDING', 'RESERVED', 'HELD', 'SOLD'], 'sample' => 'AVAILABLE'],
            'price'         => ['label' => 'Precio',           'type' => 'number',  'sample' => '185000'],
            'description'   => ['label' => 'Descripción',      'type' => 'string',  'sample' => 'Casa de 3 dormitorios con jardín'],
            'address'       => ['label' => 'Dirección',        'type' => 'string',  'sample' => 'Av. Principal 123'],
            'plot'          => ['label' => 'Es lote/terreno',  'type' => 'boolean', 'sample' => '0'],
            'bedrooms'      => ['label' => 'Dormitorios',      'type' => 'integer', 'sample' => '3'],
            'bathrooms'     => ['label' => 'Baños',            'type' => 'number',  'sample' => '2'],
            'parking_bays'  => ['label' => 'Cocheras',         'type' => 'integer', 'sample' => '1'],
            'floor'         => ['label' => 'Piso',             'type' => 'string',  'sample' => 'PB'],
            'layout'        => ['label' => 'Distribución',     'type' => 'string',  'sample' => '3 amb'],
            'internal_area' => ['label' => 'Sup. interna (m²)', 'type' => 'number', 'sample' => '90'],
            'external_area' => ['label' => 'Sup. externa (m²)', 'type' => 'number', 'sample' => '40'],
            'total_area'    => ['label' => 'Sup. total (m²)',  'type' => 'number',  'sample' => '130'],
            'public'        => ['label' => 'Visible al público', 'type' => 'boolean', 'sample' => '1'],
        ];
    }

    public function matchKeys(): array
    {
        return ['custom_id', 'name'];
    }

    public function creationDefaults(): array
    {
        // `type` y `price` son NOT NULL en la tabla `units`; damos un fallback
        // por si el CSV no mapea esas columnas al crear.
        return [
            'type'   => 'N/A',
            'price'  => 0,
            'status' => 'AVAILABLE',
        ];
    }
}
