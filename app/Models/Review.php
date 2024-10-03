<?php

namespace App\Models;

class Review extends BaseModel
{
    protected $fillable = ['user_id', 'product_id', 'rating', 'comment', 'approved'];
}
