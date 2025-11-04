<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DescriptionChecklist extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'order',
        'moderator_id',
    ];

    // Relationship with moderator
    public function moderator()
    {
        return $this->belongsTo(Moderator::class);
    }
}
