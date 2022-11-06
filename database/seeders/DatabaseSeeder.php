<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        User::create([
            "name"=> "Akun Admin",
            "email"=> "admin123@gmail.com",
            'role'=> 'Admin',
            'password' => '$2y$10$bGMm2SITpLzLwHvEjMS0YuFz6/2qhNZ2IdfyovBaHwl0qVIZEmvQq',
        ]);

        Product::create([
            "category_id"=> 1,
            "title"=> "Strawberry Parfait",
            "description"=> "ini adalah deskripsi produk Strawberry Parfait",
            "harga_beli"=> 1000,
            "harga_jual"=> 2000,
            "stock"=> 1000, 
        ]);

        Product::create([
            "category_id"=> 1,
            "title"=> "Muffin",
            "description"=> "ini adalah deskripsi produk Muffin",
            "harga_beli"=> 500,
            "harga_jual"=> 1000,
            "stock"=> 500, 
        ]);

        Product::create([
            "category_id"=> 2,
            "title"=> "Orange Juice",
            "description"=> "ini adalah deskripsi produk Orange Juice",
            "harga_beli"=> 2000,
            "harga_jual"=> 2500,
            "stock"=> 1000, 
        ]);

        Product::create([
            "category_id"=> 2,
            "title"=> "Coffe Latte",
            "description"=> "ini adalah deskripsi produk Coffe Latte",
            "harga_beli"=> 600,
            "harga_jual"=> 1200,
            "stock"=> 1000, 
        ]);

        Category::create([
            'name' => 'Makanan'
        ]);

        Category::create([
            'name' => 'Minuman'
        ]);
    }
}
