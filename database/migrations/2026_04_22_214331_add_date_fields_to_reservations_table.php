<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->date('expedition_date')->nullable()->after('id_document_path');
            $table->string('expedition_place')->nullable()->after('expedition_date');
            $table->date('birth_date')->nullable()->after('expedition_place');
            $table->integer('age')->nullable()->after('birth_date');
            $table->string('nationality')->nullable()->after('age');
            $table->string('marital_status')->nullable()->after('nationality');
            $table->string('spouse_name')->nullable()->after('marital_status');
            $table->string('spouse_nationality')->nullable()->after('spouse_name');
            $table->string('spouse_document')->nullable()->after('spouse_nationality');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'expedition_date',
                'expedition_place', 
                'birth_date',
                'age',
                'nationality',
                'marital_status',
                'spouse_name',
                'spouse_nationality',
                'spouse_document'
            ]);
        });
    }
};
