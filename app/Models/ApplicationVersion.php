<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'version',
        'force_update',
        'description',
        'moderator_id',
    ];

    protected $casts = [
        'force_update' => 'boolean',
    ];

    // Relationship with moderator
    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }

    // Get platform text
    public function getPlatformTextAttribute()
    {
        return $this->platform === 'web' ? 'وب' : 'اندروید';
    }

    // Get force update text
    public function getForceUpdateTextAttribute()
    {
        return $this->force_update ? 'بله' : 'خیر';
    }
}
