<?php

namespace App\Models;

class PaymentMethod extends BaseModel
{
    protected $fillable = ['user_id', 'type', 'details', 'is_primary'];
}
