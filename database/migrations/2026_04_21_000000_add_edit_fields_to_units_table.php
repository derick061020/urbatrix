<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            // Reservation Details
            $table->decimal('discount', 12, 2)->default(0)->after('description');
            $table->integer('additional_parking')->default(0)->after('discount');
            $table->decimal('price_adjustment', 12, 2)->default(0)->after('additional_parking');
            $table->decimal('purchase_price', 12, 2)->nullable()->after('price_adjustment');

            // Reservation Customer
            $table->string('first_name')->nullable()->after('purchase_price');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('contact_number')->nullable()->after('last_name');
            $table->string('email')->nullable()->after('contact_number');

            // Agent relationship
            $table->foreignId('agent_id')->nullable()->after('email')->constrained('agents')->nullOnDelete();

            // Unit General
            $table->boolean('plot')->default(false)->after('agent_id');
            $table->string('address')->nullable()->after('plot');
            $table->string('custom_id')->nullable()->after('address');
            $table->string('price_wording')->nullable()->after('custom_id');
            $table->decimal('levies', 12, 2)->default(0)->after('price_wording');
            $table->decimal('rates', 12, 2)->default(0)->after('levies');
            $table->decimal('est_rental', 12, 2)->default(0)->after('rates');
            $table->boolean('guaranteed_rental')->default(false)->after('est_rental');
            $table->boolean('override_action')->default(false)->after('guaranteed_rental');

            // Unit Specifications
            $table->string('floor')->nullable()->after('override_action');
            $table->string('layout')->nullable()->after('floor');
            $table->integer('bedrooms')->default(0)->after('layout');
            $table->decimal('bathrooms', 4, 1)->default(0)->after('bedrooms');
            $table->integer('parking_bays')->default(0)->after('bathrooms');
            $table->integer('pools')->default(0)->after('parking_bays');
            $table->string('direction')->nullable()->after('pools');
            $table->string('outlook')->nullable()->after('direction');
            $table->boolean('aircon')->default(false)->after('outlook');

            // Unit Monthly Expenses
            $table->decimal('expense_1', 12, 2)->default(0)->after('aircon');
            $table->decimal('expense_2', 12, 2)->default(0)->after('expense_1');
            $table->decimal('expense_3', 12, 2)->default(0)->after('expense_2');

            // Unit Custom Information
            $table->string('custom_1')->nullable()->after('expense_3');
            $table->string('custom_2')->nullable()->after('custom_1');
            $table->string('custom_3')->nullable()->after('custom_2');

            // Unit Dimensions
            $table->decimal('internal_area', 10, 2)->default(0)->after('custom_3');
            $table->decimal('external_area', 10, 2)->default(0)->after('internal_area');
            $table->decimal('total_area', 10, 2)->default(0)->after('external_area');

            // Unit Settings
            $table->boolean('bypass_launch_date')->default(false)->after('total_area');
            $table->boolean('display_on_home_page')->default(true)->after('bypass_launch_date');
            $table->boolean('show_enquire_button')->default(false)->after('display_on_home_page');
            $table->boolean('set_discount_globally')->default(false)->after('show_enquire_button');
            $table->boolean('hide_original_price')->default(false)->after('set_discount_globally');
            $table->boolean('show_price_alternative')->default(false)->after('hide_original_price');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn([
                'discount', 'additional_parking', 'price_adjustment', 'purchase_price',
                'first_name', 'last_name', 'contact_number', 'email',
                'agent_id',
                'plot', 'address', 'custom_id', 'price_wording', 'levies', 'rates',
                'est_rental', 'guaranteed_rental', 'override_action',
                'floor', 'layout', 'bedrooms', 'bathrooms', 'parking_bays', 'pools',
                'direction', 'outlook', 'aircon',
                'expense_1', 'expense_2', 'expense_3',
                'custom_1', 'custom_2', 'custom_3',
                'internal_area', 'external_area', 'total_area',
                'bypass_launch_date', 'display_on_home_page', 'show_enquire_button',
                'set_discount_globally', 'hide_original_price', 'show_price_alternative',
            ]);
        });
    }
};
