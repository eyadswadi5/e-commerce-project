<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where(function (Builder $query) {
            $role = Role::where("role", "=", "user")->first();
            $query->where("role_id", "=", $role->id);
        })->get();
        $products = Product::all();
        $faker = Factory::create();
        $status = ["complete", "checked out"];
        foreach(range(1, 100) as $index) {
            $user = $users->random();
            $items = $products->random(rand(1, 10));
            $cart = Cart::create([
                "user_id" => $user->id,
                "total" => 0.0,
                "status" => $status[rand(0,1)],
            ]);
            $total = 0;
            $cartItems = array();
            foreach($items as $item) {
                $quantity = $faker->numberBetween(1, 3);
                $totalPrice = $quantity * $item->price;
                $cartItem = [
                    "id" => Str::uuid()->toString(),
                    "cart_id" => $cart->id,
                    "product_id" => $item->id,
                    "price" => $item->price,
                    "quantity" => $quantity,
                    "total" => $totalPrice,
                ];
                $cartItems += $cartItem;
                $total += $totalPrice;
            }
            CartItem::create($cartItems);
            $cart->total = $total;
            $cart->save();
        }
    }
}
