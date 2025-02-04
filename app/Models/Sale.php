<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'sale_date',
        'client_name',
        'client_phone',
        'payment_method',
        'notes',
        'payment_status',
        'subtotal',
        'tax',
        'total',
        'products',
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'products' => 'array', // Cast JSON en tableau PHP
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
