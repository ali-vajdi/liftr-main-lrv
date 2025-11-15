<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'phone_number',
        'message',
        'cost',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    // Relationship with organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Check if SMS is for system (no organization)
    public function getIsSystemSmsAttribute()
    {
        return is_null($this->organization_id);
    }

    // Get status text
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'در انتظار';
            case self::STATUS_SENT:
                return 'ارسال شده';
            case self::STATUS_FAILED:
                return 'ناموفق';
            default:
                return 'نامشخص';
        }
    }

    // Get status badge class
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'badge-warning';
            case self::STATUS_SENT:
                return 'badge-success';
            case self::STATUS_FAILED:
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }
}
