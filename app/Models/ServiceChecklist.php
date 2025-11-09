<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'technician_id',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    public function elevatorChecklists()
    {
        return $this->hasMany(ServiceElevatorChecklist::class);
    }

    public function signatures()
    {
        return $this->hasMany(ServiceSignature::class);
    }

    public function history()
    {
        return $this->hasMany(ServiceChecklistHistory::class);
    }

    public function managerSignature()
    {
        return $this->hasOne(ServiceSignature::class)->where('type', 'manager');
    }

    public function technicianSignature()
    {
        return $this->hasOne(ServiceSignature::class)->where('type', 'technician');
    }
}
