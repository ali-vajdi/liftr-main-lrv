<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Elevator extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'name',
        'stops_count',
        'capacity',
        'status',
        'description',
    ];

    protected $casts = [
        'stops_count' => 'integer',
        'capacity' => 'integer',
        'status' => 'boolean',
    ];

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        return $this->status ? 'فعال' : 'غیرفعال';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status ? 'badge-success' : 'badge-danger';
    }
}
