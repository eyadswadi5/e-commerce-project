<?php

namespace App\Models;

class OrderItem extends BaseModel
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'unit_price'];
}
