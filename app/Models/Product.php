<?php

namespace App\Models;

class Product extends BaseModel
{
    protected $fillable = ['name', 'description', 'price', 'stock_quantity', 'company_id'];
}
