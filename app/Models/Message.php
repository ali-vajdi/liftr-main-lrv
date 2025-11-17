<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_type',
        'sender_id',
        'receiver_type',
        'receiver_id',
        'subject',
        'message',
        'is_read',
        'read_at',
        'service_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Sender types
    const SENDER_TYPE_ADMIN = 'admin';
    const SENDER_TYPE_ORGANIZATION = 'organization';

    // Receiver types
    const RECEIVER_TYPE_ORGANIZATION = 'organization';
    const RECEIVER_TYPE_TECHNICIAN = 'technician';

    // Relationships
    public function sender()
    {
        if ($this->sender_type === self::SENDER_TYPE_ADMIN) {
            return $this->belongsTo(Moderator::class, 'sender_id');
        } else {
            return $this->belongsTo(Organization::class, 'sender_id');
        }
    }

    public function receiver()
    {
        if ($this->receiver_type === self::RECEIVER_TYPE_ORGANIZATION) {
            return $this->belongsTo(Organization::class, 'receiver_id');
        } else {
            return $this->belongsTo(Technician::class, 'receiver_id');
        }
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('receiver_type', self::RECEIVER_TYPE_ORGANIZATION)
            ->where(function ($q) use ($organizationId) {
                $q->where('receiver_id', $organizationId)
                  ->orWhereNull('receiver_id'); // All organizations
            });
    }

    public function scopeForTechnician($query, $technicianId, $organizationId = null)
    {
        $query = $query->where('receiver_type', self::RECEIVER_TYPE_TECHNICIAN)
            ->where(function ($q) use ($technicianId) {
                $q->where('receiver_id', $technicianId)
                  ->orWhereNull('receiver_id'); // All technicians
            });
        
        // If organization_id is provided, also filter by sender organization
        if ($organizationId) {
            $query->where(function ($q) use ($organizationId) {
                $q->where(function ($q2) use ($organizationId) {
                    $q2->where('sender_type', self::SENDER_TYPE_ORGANIZATION)
                       ->where('sender_id', $organizationId);
                })->orWhereNull('receiver_id'); // All technicians messages from this organization
            });
        }
        
        return $query;
    }

    // Mark as read
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }
}
