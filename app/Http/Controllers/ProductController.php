<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormat;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products = Product::all();
 
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
        try {
            // Validasi data baru
            $validator = $this->validateProduct();
            
            // Jika ada data yang tidak valid maka akan mengembalikan response error
            if ($validator->fails()) {
                return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->messages());
            }

            // Menampung data yang sudah divalidasi
            $validatedData = $validator->validated();
            $validatedData['user_id'] = auth()->user()->id;

            // Menyimpan gambar/thumbnail produk jika ada
            if(isset($validatedData['thumbnail'])) {
                // Memberikan nama unik untung menghidari tertimpanya image/thumbnail yang sudah ada
                $newFileName = substr(md5($request->file('thumbnail')), 6, 6).'_'.time();
                $fileExtension = $request->file('thumbnail')->extension();
                // Menyimpan data pada server
                $validatedData['thumbnail'] = $request->file('thumbnail')->storeAs('product-thumbnails', "$newFileName.$fileExtension");
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
            return ResponseFormat::createResponse(404, 'Produk tidak ditemukan', $e->getMessage());
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
        try {
            // Mendapatkan data lama
            $oldProduct = Product::findOrfail($id);
            // Memvalidasi data baru
            $validator = $this->validateProduct();
            
            // Jika ada data yang salah maka akan mengembalikan response error
            if ($validator->fails()) {
                return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->messages());
            }

            $validatedData = $validator->validated();
            $validatedData['user_id'] = auth()->user()->id;

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
                $validatedData['thumbnail'] = $request->file('thumbnail')->storeAs('product-thumbnails', "$newFileName.$fileExtension");
            }
    
            $product = [
                'old_product' => $oldProduct,
                'update_product' => $validatedData
            ];

            Product::where('id', $id)->update($validatedData);
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Something went wrong on update product', $e->getMessage());    
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
        try {
            $product = Product::findOrfail($id);
            $product->delete();
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Product id not found', $e->getMessage());    
        }
        return ResponseFormat::createResponse(201, 'OK');
    }

    // Validasi data
    public function validateProduct(){
        return Validator::make(request()->all(), [
            'title' => 'required|max:190',
            'thumbnail' => 'nullable|image|max:1024',    
            'description' => 'nullable',
            'description' => 'nullable',
            'harga' => 'required|integer',
            'stock' => 'required|integer',
            'category_id' => 'required',
        ]);
    }

}
