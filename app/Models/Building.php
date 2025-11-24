<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'organization_user_id',
        'name',
        'manager_name',
        'manager_phone',
        'building_type',
        'province_id',
        'city_id',
        'address',
        'selected_latitude',
        'selected_longitude',
        'service_start_date',
        'service_end_date',
        'status',
        'elevators_count',
        'monthly_amount',
        'slug',
    ];

    protected $casts = [
        'selected_latitude' => 'decimal:8',
        'selected_longitude' => 'decimal:8',
        'service_start_date' => 'timestamp',
        'service_end_date' => 'timestamp',
        'status' => 'boolean',
        'monthly_amount' => 'decimal:2',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function organizationUser()
    {
        return $this->belongsTo(OrganizationUser::class);
    }

    public function elevators()
    {
        return $this->hasMany(Elevator::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Accessors
    public function getBuildingTypeTextAttribute()
    {
        $types = [
            'residential' => 'مسکونی',
            'office' => 'اداری',
            'commercial' => 'تجاری'
        ];
        
        return $types[$this->building_type] ?? $this->building_type;
    }

    public function getStatusTextAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Generate a unique 9-character slug
     */
    protected static function generateUniqueSlug(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $maxAttempts = 100;
        $attempt = 0;

        do {
            $slug = '';
            for ($i = 0; $i < 9; $i++) {
                $slug .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $attempt++;
        } while (static::where('slug', $slug)->exists() && $attempt < $maxAttempts);

        if ($attempt >= $maxAttempts) {
            throw new \Exception('Unable to generate unique slug after ' . $maxAttempts . ' attempts');
        }

        return $slug;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($building) {
            if (empty($building->slug)) {
                $building->slug = static::generateUniqueSlug();
            }
        });
    }
}
