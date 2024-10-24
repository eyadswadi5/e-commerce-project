<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();
        $faker = Factory::create();
        $reviews = array();
        
        foreach ($products as $p) {
            foreach(range(1, rand(3,20)) as $index) {
                $user = $users->random();
                $review = [
                    "id" => Str::uuid()->toString(),
                    "user_id" => $user->id,
                    "product_id" => $p->id,
                    "rating" => $faker->numberBetween(1, 5),
                    "comment" => $faker->sentence,
                    "approved" => $faker->numberBetween(0,1),
                ];
                $reviews[] = $review;
            }

        }
        Review::insert($reviews);
    }
}
