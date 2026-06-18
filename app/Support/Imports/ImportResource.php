<?php

namespace App\Support\Imports;

/**
 * Define un "recurso importable": qué modelo Eloquent recibe los datos,
 * qué campos se pueden mapear desde un CSV, cómo se castean/validan y por
 * qué campos se detectan duplicados.
 *
 * Para sumar una entidad nueva (Usuarios, Agentes, etc.) basta crear una
 * subclase y registrarla en {@see ImportResourceRegistry}. El controlador y
 * las vistas no necesitan cambios.
 */
abstract class ImportResource
{
    /** Clave única usada en las rutas (ej. "units"). */
    abstract public function key(): string;

    /** Etiqueta visible para el admin (ej. "Unidades"). */
    abstract public function label(): string;

    /** Clase del modelo Eloquent destino. */
    abstract public function model(): string;

    /**
     * Campos importables. Estructura:
     *  'campo' => [
     *      'label'    => 'Nombre visible',
     *      'type'     => 'string|number|integer|boolean|enum',
     *      'enum'     => ['A','B'],        // sólo si type=enum
     *      'required' => true,             // opcional
     *      'sample'   => 'valor demo',     // para el CSV de ejemplo
     *  ]
     *
     * @return array<string, array<string, mixed>>
     */
    abstract public function fields(): array;

    /**
     * Campos candidatos para detectar duplicados al actualizar/upsert.
     *
     * @return array<int, string>
     */
    abstract public function matchKeys(): array;
}
