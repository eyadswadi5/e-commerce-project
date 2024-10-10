<?php

namespace Database\Seeders;

use App\Models\Category;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();

        foreach(range(1, 30) as $index) {
            Category::create([
                "id" => Str::uuid()->toString(),
                "name" => $faker->word,
                "description" => $faker->sentence,
            ]);
        }
    }
}
