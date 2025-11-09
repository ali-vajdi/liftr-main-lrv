<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceElevatorChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_checklist_id',
        'elevator_id',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    // Relationships
    public function serviceChecklist()
    {
        return $this->belongsTo(ServiceChecklist::class);
    }

    public function elevator()
    {
        return $this->belongsTo(Elevator::class);
    }

    public function descriptions()
    {
        return $this->hasMany(ServiceChecklistDescription::class);
    }
}
