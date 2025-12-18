<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceChecklistDescription extends Model
{
    use HasFactory, SoftDeletes;

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
