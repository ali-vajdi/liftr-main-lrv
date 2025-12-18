<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceView extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_id',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
