<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    public function index()
    {
        //temporarly displaying products in this way.
        try {
            $products = Product::all();
            $products = $products->map(function ($p) {
                return [
                    "id" => $p->id,
                    "name" => $p->name,
                    "title" => $p->title,
                    "description" => $p->description,
                    "price" => $p->price,
                    "quantity" => $p->stock_quantity,
                    "company" => $p->company()->name,
                    "attrs" => $p->attrs(),
                ];
            });
            return $this->rst(true, 200, null, null, ["products" => $products]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch products", [["message" => "database error occurres"]]);
        }
    }

    public function find(string $product_id)
    {
        try {
            $product = Product::findOrFail($product_id);
            $product = [
                "id" => $product->id,
                "name" => $product->name,
                "title" => $product->title,
                "description" => $product->description,
                "price" => $product->price,
                "quantity" => $product->stock_quantity,
                "company" => $product->company()->name,
                "attrs" => $product->attrs(),
            ];
            return $this->rst(true, 200, null, null, ["product" => $product]);
        } catch (QueryException $e) {
            return $this->rst(false, 422, "Failed to fetch product details", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "Failed to fetch product details", [["message" => "product not found"]]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->only(["name", "title", "description", "price", "stock_quantity", "company_id", "categories", "attrs"]), [
            "name" => "required|string|max:50",
            "title" => "required|string",
            "description" => "string|nullable",
            "price" => "required|numeric",
            "stock_quantity" => "required|integer",
            "company_id" => "required|uuid|exists:companies,id",
            "categories.*" => "required|uuid|exists:categories,id",
            "attrs" => "nullable|array|min:1",
            "attrs.*.attr" => "required|string",
            "attrs.*.desc" => "required|string",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "error validating data", [["message" => $validator->errors()]]);

        try {
            $product = Product::create($validator->getData());

            $categories = collect($validator->getData()["categories"])
                ->map(function ($c) use ($product) {
                    return [
                        "id" => Str::uuid()->toString(),
                        "product_id" => $product->id,
                        "category_id" => $c,
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                })->toArray();

            $attrs = collect($validator->getData()["attrs"])
                ->map(function ($attr) use ($product) {
                    return [
                        "id" => Str::uuid()->toString(),
                        "product_id" => $product->id,
                        "attr" => $attr["attr"],
                        "desc" => $attr["desc"],
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                })->toArray();

            DB::table("product_has_categories")->insert($categories);
            DB::table("product_has_attrs")->insert($attrs);

            return $this->rst(true, 201, "product created");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to store product", [["message" => "database error occurres"]]);
        }
    }

    public function update(string $product_id, Request $request) {
        $validator = Validator::make($request->only(["name", "title", "description", "price", "stock_quantity", "company_id", "remove_categories", "remove_attrs", "attrs"]), [
            "name" => "required|string|max:50",
            "title" => "required|string",
            "description" => "string|nullable",
            "price" => "required|numeric",
            "stock_quantity" => "required|integer",
            "company_id" => "required|uuid|exists:companies,id",
            "remove_categories.*" => "nullable|uuid|exists:categories,id",
            "remove_attrs.*" => "nullable|uuid|exists:product_has_attrs,id",
            "attrs" => "nullable|array|min:1",
            "attrs.*.attr" => "required|string",
            "attrs.*.desc" => "required|string",
        ]);
        if ($validator->fails())
        return $this->rst(false, 422, "error validating data", [["message" => $validator->errors()]]);

        try {
            $product = Product::findOrFail($product_id);
            $product->update($validator->getData());

            if ($request->has("remove_categories")) {
                DB::table("product_has_categories")
                    ->where("product_id", "=", $product->id)
                    ->whereIn("category_id", $validator->getData()["remove_categories"])
                    ->delete();
            }
            if ($request->has("remove_attrs")) {
                DB::table("product_has_attrs")
                    ->whereIn("id", $validator->getData()["remove_attrs"])
                    ->delete();
            }

            $attrs = collect($validator->getData()["attrs"])
            ->map(function ($attr) use ($product) {
                return [
                    "id" => Str::uuid()->toString(),
                    "product_id" => $product->id,
                    "attr" => $attr["attr"],
                    "desc" => $attr["desc"],
                ];
            })->toArray();
            DB::table("product_has_attrs")
                ->upsert($attrs, ["product_id", "attr"], ["desc"]);

            return $this->rst(true, 200, "product updated");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to update product", [["message" => "database error occurres", "errorMessage" => $e]]);
        }
    }

    public function delete(string $product_id) {
        try {
            // Product::destroy($product_id);
            $product = Product::findOrFail($product_id);
            $product->delete();
            return $this->rst(true, 200, "product deleted");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "failed deleting product", [["message" => "database error occurres"]]);
        } catch (ModelNotFoundException $e) {
            return $this->rst(false, 422, "failed deleting product", [["message" => "product not found"]]);
        }
    }
}
