<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends BaseController
{
    public function index()
    {
        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")->first();
            if (!$cart)
                return $this->rst(false, 404, "No Cart was found");
            $cartItems = CartItem::where("cart_id", "=", $cart->id)->get();
            // dd($cartItems);
            return $this->rst(false, 200, null, null, ["cart" => $cart, "items" => $cartItems]);
        } catch (JWTException $e) {
            return $this->rst(false, 403, "Failed to fetch cart");
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to fetch cart", [["message" => "database error occurres", "error" => $e]]);
        }
    }

    public function add_item(Request $request)
    {
        $validator = Validator::make($request->only(["product_id", "quantity"]), [
            "product_id" => "required|uuid|exists:products,id",
            "quantity" => "required|integer",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to add item to the cart", [["message" => "error validating data"]]);
        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")
                ->first();
            if (!$cart)
                $cart = Cart::create([
                    "user_id" => $user->id,
                    "total" => 0.0,
                    "status" => "pending",
                ]);
            $product = Product::find($request->product_id);
            if ($product->stock_quantity < $request->quantity)
                return $this->rst(false, 422, "Failed to add item", [["message" => "stock quantity of this product is not enough"]]);
            $ptotal = $product->price * $request->quantity;
            $cartItem = CartItem::create([
                "cart_id" => $cart->id,
                "product_id" => $product->id,
                "price" => $product->price,
                "quantity" => $request->quantity,
                "total" => $ptotal
            ]);
            $cart->calculateTotal();
            return $this->rst(true, 200, "item added");
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to add item", [["message" => "user not find", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to add item", [["message" => "database error occurres", "error" => $e]]);
        }
    }

    public function update_item(string $item_id, Request $request)
    {
        $validator = Validator::make($request->only("quantity"), [
            "quantity" => "required|integer",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to update item", [["message" => "error validating data"]]);
        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")
                ->first();
            if (!$cart)
                return $this->rst(false, 422, "Failed to update item", [["message" => "No cart found"]]);
            $cartItem = CartItem::where("cart_id", "=", $cart->id)
                ->where("id", "=", $item_id)
                ->first();
            if (!$cartItem)
                return $this->rst(false, 404, "Failed to update item", [["message" => "item not found"]]);
            $cartItem->quantity = $request->quantity;
            $cartItem->total = $request->quantity * $cartItem->price;
            $cartItem->save();
            $cart->calculateTotal();
            return $this->rst(true, 200, "item updated");
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to update item", [["message" => "user not find", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to update item", [["message" => "database error occurres", "error" => $e]]);
        }
    }

    public function delete_item(string $item_id)
    {
        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")
                ->first();
            if (!$cart)
                return $this->rst(false, 422, "Failed to delete item", [["message" => "No cart found"]]);
            $cartItem = CartItem::where("cart_id", "=", $cart->id)
                ->where("id", "=", $item_id)
                ->first();
            if (!$cartItem)
                return $this->rst(false, 404, "Failed to delete item", [["message" => "item not found"]]);
            $cartItem->delete();
            $cart->calculateTotal();
            return $this->rst(true, 200, "item deleted");
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to delete item", [["message" => "user not find", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to delete item", [["message" => "database error occurres", "error" => $e]]);
        }
    }

    public function create_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "items.*.product_id" => "required|uuid|exists:products,id",
            "items.*.quantity" => "required|integer",
        ]);
        if ($validator->fails())
            return $this->rst(false, 422, "Failed to update item", [["message" => "error validating data"]]);

        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")
                ->first();
            if (!$cart)
                return $this->rst(false, 422, "Failed to create item", [["message" => "cart already exists"]]);

            $cart = Cart::create([
                "user_id" => $user->id,
                "total" => 0.0,
                "status" => "pending",
            ]);

            $products = Product::orWhere(function (Builder $query) use ($request) {
                foreach ($request->items as $item)
                    $query->orWhere("id", "=", $item["product_id"])->where("stock_quantity", ">", $item["quantity"]);
            })->get();
            if (count($products) == 0)
                return $this->rst(false, 422, "Failed to add items to the new cart", [["message" => "non of the product is found"]]);

            $items = $products->map(function ($p) use ($request, $cart) {
                $cartItem = collect($request->items)->first(function ($item) use ($p) {
                    return $item["product_id"] === $p->id;
                });
                if ($cartItem) {
                    return [
                        "cart_id" => $cart->id,
                        "product_id" => $p->id,
                        "price" => $p->price,
                        "quantity" => $cartItem["quantity"],
                        "total" => $cartItem["quantity"] * $p->price,
                    ];
                }
            });

            CartItem::insert($items->toArray());

            $ctotal = $products->sum("price");
            $cart->total = $ctotal;
            $cart->save();
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to create cart", [["message" => "user not found", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to create cart", [["message" => "database error occurres", "error" => $e]]);
        }
    }

    public function clear_cart()
    {
        try {
            $user = JWTAuth::user();
            $cart = Cart::where("user_id", "=", $user->id)
                ->where("status", "=", "pending")
                ->first();
            if (!$cart)
                return $this->rst(false, 422, "Failed to clear items", [["message" => "No cart found"]]);
            $cartItems = CartItem::where("cart_id", $cart->id)->get();
            if (!$cartItems)
                return $this->rst(false, 422, "Failed to clear items", [["message" => "No items found"]]);
            CartItem::destroy($cartItems);
            $cart->total = 0.0;
            $cart->save();
            return $this->rst(true, 200, "cart cleared");
        } catch (JWTException $e) {
            return $this->rst(false, 401, "Failed to clear cart", [["message" => "user not found", "error" => $e]]);
        } catch (QueryException $e) {
            return $this->rst(false, 500, "Failed to clear cart", [["message" => "database error occurres", "error" => $e]]);
        }
    }
}
