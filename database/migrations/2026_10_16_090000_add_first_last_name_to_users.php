<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nombre y apellido del usuario, por separado.
 *
 * La ficha del cliente y el formulario de perfil los vienen escribiendo desde
 * hace tiempo, pero no había ninguna migración que los creara: en Makai las
 * columnas existen porque se añadieron a mano en algún momento, y en Bahía Mar
 * y Siebe no existían. Resultado: guardar el perfil reventaba con «Unknown
 * column 'first_name'» en esos dos (lo encontró Miguel el 16 de octubre).
 *
 * No se rellenan partiendo de `name`. El controlador compone `name` a partir
 * de estas dos cuando tienen valor, así que un reparto mal hecho —los apellidos
 * compuestos no se parten por el primer espacio— acabaría reescribiendo el
 * nombre que hoy está bien. Quedan nulas y se llenan cuando alguien edite su
 * ficha, que es cuando hay un humano decidiendo dónde va cada parte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['first_name', 'last_name'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
