<?php

namespace Database\Seeders;

use App\Models\Company;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create();
        foreach (range(1, 20) as $index) {
            Company::create([
                "id" => Str::uuid()->toString(),
                "name" => $faker->word,
                "description" => $faker->sentence,
            ]);
        }

    }
}
