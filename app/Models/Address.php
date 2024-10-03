<?php

namespace App\Models;

class Address extends BaseModel
{
    protected $fillable = ['user_id', 'address_line1', 'address_line2', 'country', 'state', 'city', 'postal_code', 'is_primary'];
}
