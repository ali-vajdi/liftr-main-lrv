<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChecklistDescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_elevator_checklist_id',
        'checklist_id',
        'title',
        'description',
    ];

    // Relationships
    public function serviceElevatorChecklist()
    {
        return $this->belongsTo(ServiceElevatorChecklist::class);
    }

    public function checklist()
    {
        return $this->belongsTo(DescriptionChecklist::class, 'checklist_id');
    }
}
