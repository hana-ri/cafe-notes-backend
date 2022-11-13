<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormat;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\CloudinaryStorage;

class ProductController extends Controller
{

    // Fungsi dari JWT
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Mencari seluruh produk
            $products = Product::all();
            
            // Jika produk belum ada maka akan mengembalikan response dalam if
            if ($products->count() == 0) {
                return  ResponseFormat::createResponse(404, 'No products yet.');
            }
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on index product', $e->getMessage());    
        }

        return ResponseFormat::createResponse(200, 'OK', $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi role
        if (!$this->validateRole()) {
            return ResponseFormat::createResponse(401, 'You don not have permission');
        }

        try {
            // Validasi data baru
            $validator = $this->validateProduct();
            
            // Jika ada data yang tidak valid maka akan mengembalikan response error
            if ($validator->fails()) {
                return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->errors());
            }

            // Menampung data yang sudah divalidasi
            $validatedData = $validator->validated();

            // Menyimpan gambar/thumbnail produk jika ada
            if(isset($validatedData['thumbnail'])) {
                // Memberikan nama unik untung menghidari tertimpanya image/thumbnail yang sudah ada
                $newFileName = substr(md5($request->file('thumbnail')), 6, 6).'_'.time();
                $fileExtension = $request->file('thumbnail')->extension();
                // Menyimpan data pada server
                // $validatedData['thumbnail'] = $request->file('thumbnail')->storeAs('product-thumbnails', "$newFileName.$fileExtension");
                $validatedData['thumbnail'] = CloudinaryStorage::upload($request->file('thumbnail')->getRealPath(), "$newFileName.$fileExtension");
            }

            // Menyimpan data ke database
            Product::create($validatedData);
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on store product', $e->getMessage());    
        }
        return ResponseFormat::createResponse(201, 'Created', $validatedData);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $product = Product::findOrfail($id);
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Product not found', $e->getMessage());
        } 
        return ResponseFormat::createResponse(200, 'OK', $product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validasi role
        if (!$this->validateRole()) {
            return ResponseFormat::createResponse(401, 'You don not have permission');
        }

        try {
            // Mendapatkan data lama
            $oldProduct = Product::findOrfail($id);
            // Memvalidasi data baru
            $validator = $this->validateProduct();
            
            // Jika ada data yang salah maka akan mengembalikan response error
            if ($validator->fails()) {
                return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->errors());
            }

            $validatedData = $validator->validated();

            // Menyimpan gambar/thumbnail produk jika ada
            if(isset($validatedData['thumbnail'])) {
                // Jika ada gambar/thumbnail lama maka akan dihapus
                if($oldProduct->thumbnail) {
                    Storage::delete($oldProduct->thumbnail);
                }
                // Memberikan nama unik untung menghidari tertimpanya image/thumbnail yang sudah ada
                $newFileName = substr(md5($request->file('thumbnail')), 6, 6).'_'.time();
                $fileExtension = $request->file('thumbnail')->extension();
                // Menyimpan gambar pada server
                // $validatedData['thumbnail'] = $request->file('thumbnail')->storeAs('product-thumbnails', "$newFileName.$fileExtension");
                $validatedData['thumbnail'] = CloudinaryStorage::replace($oldProduct->thumbnail, $request->file('thumbnail')->getRealPath(), "$newFileName.$fileExtension");
            }
    
            $product = [
                'old_product' => $oldProduct,
                'update_product' => $validatedData
            ];

            Product::where('id', $id)->update($validatedData);
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'id product not found on update product', $e->getMessage());    
        }
        return ResponseFormat::createResponse(200, 'OK', $product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Validasi role
        if (!$this->validateRole()) {
            return ResponseFormat::createResponse(401, 'You don not have permission');
        }

        try {
            // Cari id produk jika gagal lempar pada exception
            $product = Product::findOrfail($id);
            CloudinaryStorage::delete($product->thumbnail);
            $product->delete();
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Product id not found', $e->getMessage());    
        }
        return ResponseFormat::createResponse(201, 'OK');
    }

    // Validasi data
    public function validateProduct(){
        return Validator::make(request()->all(), [
            'title' => 'required|max:190', // Tidak boleh kosong dan panjang maksimal 190 karakter
            'thumbnail' => 'nullable|image|max:1024', // optional jika diisi wajib berupa data gambar dan masimal 1mb
            'description' => 'nullable', // optional
            'description' => 'nullable', // optional
            'harga_beli' => 'required|integer', // tidak boleh kosong dan harus integer
            'harga_jual' => 'required|integer', // tidak boleh kosong dan harus integer
            'stock' => 'required|integer', // tidak boleh kosong dan harus integer
            'category_id' => 'required', // tidak boleh kosong
        ]);
    }

    public function validateRole()
    {
        if (auth()->user()->role == "Admin") {
            return true;
        }
        return false;    
    }

}
