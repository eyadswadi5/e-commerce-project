<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Product extends BaseModel
{
    protected $fillable = ['name', 'title', 'description', 'price', 'stock_quantity', 'company_id'];

    public function attrs() {
        $productAttrs = DB::table("product_has_attrs")
            ->where("product_id", "=", $this->id)
            ->get(["attr", "desc"]);
        return $productAttrs;
    }

    public function company() {
        $company = Company::find($this->company_id);
        return $company;
    }

}
