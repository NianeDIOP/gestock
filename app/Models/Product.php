<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'description',
        'price',
        'quantity',
        'category_id',
        'low_stock_alert'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Générer automatiquement une référence unique
    public static function generateReference()
    {
        $lastProduct = self::latest()->first();
        $year = date('Y');
        
        if (!$lastProduct) {
            return 'PROD-' . $year . '-0001';
        }

        $lastNumber = intval(substr($lastProduct->reference, -4));
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return 'PROD-' . $year . '-' . $nextNumber;
    }

    // app/Models/Product.php
    public function updateStockAlert() {
    $this->low_stock_alert = $this->quantity <= $this->stock_threshold;
    $this->save();
}
public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}