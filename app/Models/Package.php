<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'duration_days',
        'duration_label',
        'price',
        'is_public',
        'use_periods',
        'period_days',
        'moderator_id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'use_periods' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Get status text
    public function getStatusTextAttribute()
    {
        return $this->is_public ? 'عمومی' : 'خصوصی';
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return $this->is_public ? 'badge-success' : 'badge-warning';
    }

    // Relationship with moderator
    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    // Relationship with organization packages
    public function organizationPackages()
    {
        return $this->hasMany(OrganizationPackage::class);
    }

    // Get formatted price
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 0, '.', ',') . ' تومان';
    }
}
