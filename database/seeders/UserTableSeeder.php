<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Carbon\Factory;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::all();
        $role = Role::where("role", "=", "user")->first();

        $faker = FakerFactory::create();

        foreach(range(1, 50) as $index) {
            $user = User::create([
                "name" => $faker->word,
                "email" => $faker->email,
                "phone_number" => $faker->phoneNumber,
                "password" => Hash::make("eyadswadi"),
                "role_id" => $role->id,
                "email_verified_at" => now(),
            ]);

            PermissionService::setUserPermissions($user);

            foreach (range(1, rand(1,2)) as $ad) {
                DB::table("addresses")
                    ->insert([
                        "id" => Str::uuid()->toString(),
                        "user_id" => $user->id,
                        "address_line1" => $faker->address,
                        "country" => $faker->country,
                        "state" => $faker->word,
                        "city" => $faker->city,
                        "postal_code" => $faker->postcode,
                    ]);
            }
        }
    }
}
