<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Str;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $categories = ['Electronics', 'Home Appliances', 'Fashion', 'Gardening', 'Food', 'Books', 'Health and Beauty', 'Sports and Outdoors', 'Toys', 'Crafts and Hobbies', 'Other'];

        foreach ($categories as $cat) {
            Category::firstOrCreate([
                'name' => $cat,
                'slug' => Str::slug($cat)
            ]);
        }
    }
}
