<?php

namespace App\Support\Imports;

/**
 * Registro de recursos importables disponibles en el panel admin.
 * Para habilitar una entidad nueva, agregá su clase aquí.
 */
class ImportResourceRegistry
{
    /** @var array<string, class-string<ImportResource>> */
    protected static array $resources = [
        'units' => UnitImportResource::class,
        // 'users' => UserImportResource::class,  // futuro: importar usuarios (solo datos)
    ];

    /**
     * Todos los recursos instanciados, indexados por key.
     *
     * @return array<string, ImportResource>
     */
    public static function all(): array
    {
        return collect(static::$resources)
            ->map(fn (string $class) => new $class())
            ->all();
    }

    /** Resuelve un recurso por su key, o null si no existe. */
    public static function find(string $key): ?ImportResource
    {
        $class = static::$resources[$key] ?? null;

        return $class ? new $class() : null;
    }

    /** Igual que find() pero aborta con 404 si no existe. */
    public static function findOrFail(string $key): ImportResource
    {
        return static::find($key) ?? abort(404, "Recurso de importación «{$key}» no encontrado.");
    }
}
