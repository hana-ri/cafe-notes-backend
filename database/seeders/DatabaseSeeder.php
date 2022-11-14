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
            "harga_beli"=> 500,
            "harga_jual"=> 1000,
            "thumbnail" => 'https://res.cloudinary.com/dwhzll0pz/image/upload/v1668380274/product-thumbnails/2022-11-13_225754_bd35fe_1668380274.jpg',
            "stock"=> 1000, 
        ]);

        Product::create([
            "category_id"=> 1,
            "title"=> "Muffin",
            "description"=> "ini adalah deskripsi produk Muffin",
            "harga_beli"=> 1000,
            "harga_jual"=> 1500,
            'thumbnail' => 'https://res.cloudinary.com/dwhzll0pz/image/upload/v1668380377/product-thumbnails/2022-11-13_225937_6b7096_1668380377.jpg',
            "stock"=> 500, 
        ]);

        Product::create([
            "category_id"=> 2,
            "title"=> "Orange Juice",
            "description"=> "ini adalah deskripsi produk Orange Juice",
            "harga_beli"=> 2000,
            "harga_jual"=> 2500,
            'thumbnail' => 'https://res.cloudinary.com/dwhzll0pz/image/upload/v1668380590/product-thumbnails/2022-11-13_230310_f7b52b_1668380590.jpg',
            "stock"=> 1000, 
        ]);

        Product::create([
            "category_id"=> 2,
            "title"=> "Coffe Latte",
            "description"=> "ini adalah deskripsi produk Coffe Latte",
            "harga_beli"=> 3000,
            "harga_jual"=> 3500,
            'thumbnail' => 'https://res.cloudinary.com/dwhzll0pz/image/upload/v1668380530/product-thumbnails/2022-11-13_230210_58b6d4_1668380530.jpg',
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
