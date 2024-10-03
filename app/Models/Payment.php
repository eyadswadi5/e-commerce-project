<?php

namespace App\Models;

class Payment extends BaseModel
{
    protected $fillable = ['order_id', 'user_id', 'payment_method', 'payment_status', 'transaction_id', 'amount'];
}
