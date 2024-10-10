<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use Faker\Factory;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $categories = Category::all();

        if ($companies->isEmpty() || $categories->isEmpty()) {
            $this->command->info("No companies or categories found. Please seed those first.");
            return;
        }

        $faker = Factory::create();

        foreach (range(1, 80) as $index) {

            $company = $companies->random();
            $product = Product::create([
                "id" => Str::uuid()->toString(),
                "name" => $faker->word,
                "title" => $faker->sentence,
                "description" => $faker->paragraph,
                "price" => $faker->randomFloat(2, 10, 240),
                "stock_quantity" => $faker->numberBetween(1, 100),
                "company_id" => $company->id,
            ]);

            $productCategories = $categories->random(rand(1, 3));
            foreach($productCategories as $c) {
                DB::table("product_has_categories")
                    ->insert([
                        "id" => Str::uuid()->toString(),
                        "product_id" => $product->id,
                        "category_id" => $c->id,
                    ]);
            }

            // foreach(range(1, rand(4, 8)) as $attrIndex) {
                DB::table("product_has_attrs")
                    ->insert([
                        "id" => Str::uuid()->toString(),
                        "product_id" => $product->id,
                        "attr" => $faker->word,
                        "desc" => $faker->sentence,
                    ]);
            // }
        }

    }
}
