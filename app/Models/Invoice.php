<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'building_id',
        'invoice_number',
        'subtotal',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
        'invoice_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'datetime',
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

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('order');
    }

}
