<?php

namespace App\Models;

class CartItem extends BaseModel
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'price',
        'quantity',
        'total'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
