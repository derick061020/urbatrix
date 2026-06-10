<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos del CRM (export Bitrix) en los usuarios/clientes. Columnas reales para
 * los datos con valor + `crm_raw` (JSON) con la fila ORIGINAL completa, para no
 * perder ninguna columna del CSV.
 *
 * Nota: NO se toca la columna `country` existente (código de país de verificación,
 * string(10)); las ubicaciones del CSV van en `country_residence`/`country_address`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('crm_id', 64)->nullable()->index()->after('id');

            // Contacto / comercial
            $table->string('position')->nullable();          // Cargo
            $table->string('company')->nullable();           // Compañía
            $table->string('contact_type')->nullable();      // Tipo de Contacto
            $table->string('responsible')->nullable();       // Responsable
            $table->string('broker')->nullable();            // Broker
            $table->string('agency')->nullable();            // Agencia
            $table->string('project')->nullable();           // Proyecto

            // Identidad / KYC
            $table->string('birthdate', 60)->nullable();     // Cumpleaños / Fecha de nacimiento
            $table->string('document_type')->nullable();     // Tipo de identificación
            $table->string('document_number')->nullable();   // Número de documento
            $table->string('document_issue_date', 60)->nullable();  // Fecha de expedición
            $table->string('document_issue_place')->nullable();     // Lugar de expedición
            $table->string('nationality')->nullable();
            $table->string('age', 20)->nullable();
            $table->string('marital_status')->nullable();    // Estado civil
            $table->string('gender', 40)->nullable();        // Género
            $table->string('birth_place', 500)->nullable();  // Lugar de nacimiento
            $table->string('profession')->nullable();
            $table->string('occupation')->nullable();
            $table->string('depends_on_third', 10)->nullable(); // ¿Depende económicamente de un tercero?

            // Dirección
            $table->string('address', 500)->nullable();      // Calle / número
            $table->string('city')->nullable();
            $table->string('province')->nullable();          // Provincia / Estado
            $table->string('sector')->nullable();            // Barrio / Sector
            $table->string('country_residence', 120)->nullable(); // País de residencia
            $table->string('country_address', 120)->nullable();   // País (dirección)
            $table->string('building')->nullable();          // Edificio / Torre
            $table->string('apartment', 120)->nullable();    // Nro. Apartamento / Residencia
            $table->string('postal_code', 40)->nullable();   // Código postal

            // Cónyuge / dependiente
            $table->string('spouse_name')->nullable();
            $table->string('spouse_nationality')->nullable();
            $table->string('spouse_document')->nullable();

            // Fila original completa (todas las columnas del CSV, sin pérdida).
            $table->json('crm_raw')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'crm_id', 'position', 'company', 'contact_type', 'responsible', 'broker', 'agency', 'project',
                'birthdate', 'document_type', 'document_number', 'document_issue_date', 'document_issue_place',
                'nationality', 'age', 'marital_status', 'gender', 'birth_place', 'profession', 'occupation',
                'depends_on_third', 'address', 'city', 'province', 'sector', 'country_residence', 'country_address',
                'building', 'apartment', 'postal_code', 'spouse_name', 'spouse_nationality', 'spouse_document',
                'crm_raw',
            ]);
        });
    }
};
