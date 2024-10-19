<?php

namespace App\Models;

use Illuminate\Database\QueryException;

class Cart extends BaseModel
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'total'
    ];

    protected $casts = [
        'total' => 'decimal:2'
    ];

    public function calculateTotal() {
        try {
            $cartItems = CartItem::where("cart_id", $this->id)->get();
            if (!$cartItems) return false;
            $ctotal = $cartItems->sum("total");
            $this->total = $ctotal;
            $this->save();
        } catch (QueryException $e) {
            return false;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }
}
