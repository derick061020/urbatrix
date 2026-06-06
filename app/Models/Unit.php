<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing
        'name',
        'status',
        'type',
        'price',
        'public',
        'pre_arranged',
        'shortlisted_count',
        'images_count',
        'description',

        // Reservation Details
        'discount',
        'additional_parking',
        'price_adjustment',
        'purchase_price',

        // Reservation Customer
        'first_name',
        'last_name',
        'contact_number',
        'email',

        // Agent
        'agent_id',

        // Unit General
        'plot',
        'address',
        'custom_id',
        'price_wording',
        'levies',
        'rates',
        'est_rental',
        'guaranteed_rental',
        'override_action',

        // Unit Specifications
        'floor',
        'layout',
        'bedrooms',
        'bathrooms',
        'parking_bays',
        'pools',
        'direction',
        'outlook',
        'aircon',

        // Unit Monthly Expenses
        'expense_1',
        'expense_2',
        'expense_3',

        // Unit Custom Information
        'custom_1',
        'custom_2',
        'custom_3',

        // Unit Dimensions
        'internal_area',
        'external_area',
        'total_area',

        // Unit Settings
        'bypass_launch_date',
        'display_on_home_page',
        'show_enquire_button',
        'set_discount_globally',
        'hide_original_price',
        'show_price_alternative',

        'project_id',
        'reserved_until',
        'reserved_by_reservation_id',
        'is_high_demand',
        'is_second_chance',
        'released_at',
        'views_today',
        'views_total',
        'for_investment_text',
        'for_living_text',
        'projected_value',
        'projected_value_year',
        'roi_percent',
        'comparison_text',
        'amenities',
        'amenities_text',
        'walk_score',
        'school_proximity',
    ];

    /**
     * Cuando se elimina una unidad (desde el admin o donde sea), se borran
     * también las entradas de wishlist de los usuarios que la tenían guardada.
     * La migración ya define cascadeOnDelete, pero lo hacemos explícito acá para
     * garantizarlo aunque el motor no fuerce las claves foráneas.
     */
    protected static function booted(): void
    {
        static::deleting(function (Unit $unit) {
            \App\Models\Wishlist::where('unit_id', $unit->id)->delete();
        });
    }

    public function wishlists()
    {
        return $this->hasMany(\App\Models\Wishlist::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    public function views()
    {
        return $this->hasMany(\App\Models\UnitView::class)->orderByDesc('viewed_at');
    }

    public function reservedByReservation()
    {
        return $this->belongsTo(\App\Models\Reservation::class, 'reserved_by_reservation_id');
    }

    /**
     * Auto-release units whose 48h hold has expired and aren't tied to a
     * confirmed/signed reservation. Returns the count released.
     */
    public static function releaseExpiredHolds(): int
    {
        // Only auto-release holds created by the reservation system (those carry a
        // reserved_by_reservation_id). Units a human marked RESERVED/SOLD from the admin
        // keep their status — otherwise they silently flip back to AVAILABLE on home load.
        $expired = static::whereNotNull('reserved_until')
            ->whereNotNull('reserved_by_reservation_id')
            ->where('reserved_until', '<', now())
            ->whereIn('status', ['RESERVED', 'reserved'])
            ->get();

        $released = 0;
        foreach ($expired as $unit) {
            $stillBooked = \App\Models\Reservation::where('unit_id', $unit->id)
                ->whereIn('status', ['confirmed', 'contract_signed', 'signed'])
                ->exists();
            if ($stillBooked) {
                $unit->update(['reserved_until' => null]); // keep RESERVED, just drop the timer
                continue;
            }
            $unit->update([
                'status' => 'AVAILABLE',
                'reserved_until' => null,
                'reserved_by_reservation_id' => null,
            ]);
            $released++;
        }
        return $released;
    }

    public function isOnHold(): bool
    {
        return $this->reserved_until && $this->reserved_until->isFuture();
    }

    protected $casts = [
        'price' => 'decimal:2',
        'public' => 'boolean',
        'pre_arranged' => 'boolean',
        'reserved_until' => 'datetime',
        'released_at' => 'datetime',
        'is_high_demand'   => 'boolean',
        'is_second_chance' => 'boolean',
        'projected_value'  => 'decimal:2',
        'roi_percent'      => 'decimal:2',
        'discount' => 'decimal:2',
        'price_adjustment' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'plot' => 'boolean',
        'levies' => 'decimal:2',
        'rates' => 'decimal:2',
        'est_rental' => 'decimal:2',
        'guaranteed_rental' => 'boolean',
        'override_action' => 'boolean',
        'bathrooms' => 'decimal:1',
        'aircon' => 'boolean',
        'expense_1' => 'decimal:2',
        'expense_2' => 'decimal:2',
        'expense_3' => 'decimal:2',
        'internal_area' => 'decimal:2',
        'external_area' => 'decimal:2',
        'total_area' => 'decimal:2',
        'bypass_launch_date' => 'boolean',
        'display_on_home_page' => 'boolean',
        'show_enquire_button' => 'boolean',
        'set_discount_globally' => 'boolean',
        'hide_original_price' => 'boolean',
        'show_price_alternative' => 'boolean',
        'amenities' => 'array',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function brokers()
    {
        return $this->belongsToMany(User::class, 'broker_unit')->withTimestamps();
    }

    public function images()
    {
        return $this->hasMany(UnitImage::class)->orderBy('sort_order');
    }

    public function histories()
    {
        return $this->hasMany(UnitHistory::class)->orderByDesc('datetime');
    }

    public function dealHistories()
    {
        return $this->hasMany(DealHistory::class)->orderByDesc('datetime');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class)->orderByDesc('created_at_event');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function activeReservation()
    {
        return $this->hasOne(Reservation::class)
            ->whereIn('status', ['confirmed', 'pending'])
            ->latest();
    }
}
