<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model {
    protected $fillable = [
        'quotation_number',
        'date',
        'client_name', 
        'client_phone',
        'client_email',
        'notes',
        'subtotal',
        'tax',
        'total',
        'status'
    ];
 
    protected $casts = [
        'date' => 'datetime'
    ];
 
    public function items() {
        return $this->hasMany(QuotationItem::class);
    }
 }