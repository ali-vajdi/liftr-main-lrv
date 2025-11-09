<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceChecklistHistory extends Model
{
    use HasFactory;

    protected $table = 'service_checklist_history';

    public $timestamps = false;

    protected $fillable = [
        'service_checklist_id',
        'technician_id',
        'action',
        'changes',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function serviceChecklist()
    {
        return $this->belongsTo(ServiceChecklist::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }
}
