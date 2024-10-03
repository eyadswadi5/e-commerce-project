<?php

namespace App\Models;


class Order extends BaseModel
{
    protected $fillable = ['user_id', 'total_amount', 'status', 'shipping_address_id'];

}
