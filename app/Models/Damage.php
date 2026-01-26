<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class Damage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'building_id',
        'technician_id',
        'report_date',
        'visit_date',
        'description',
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'visit_date' => 'datetime',
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function technician()
    {
        return $this->belongsTo(Technician::class);
    }

    // Accessors for Jalali dates
    public function getReportDateJalaliAttribute()
    {
        return $this->report_date ? Jalalian::forge($this->report_date)->format('Y/m/d H:i') : null;
    }

    public function getVisitDateJalaliAttribute()
    {
        return $this->visit_date ? Jalalian::forge($this->visit_date)->format('Y/m/d H:i') : null;
    }
}
