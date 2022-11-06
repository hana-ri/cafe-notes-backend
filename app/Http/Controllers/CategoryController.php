<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormat;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

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
            // Mencari seluruh categories
            $categories = Category::all();
            
            // Jika produk belum ada maka akan mengembalikan response dalam if
            if ($categories->count() == 0) {
                return  ResponseFormat::createResponse(404, 'No category yet.');
            }
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on index category', $e->getMessage());    
        }

        return ResponseFormat::createResponse(200, 'OK', $categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validateCategory($request->name);

        if ($validator->fails()) {
            return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->errors());
        }

        $validatedData = $validator->validated();
        $validatedData['name'] = ucwords(strtolower($validatedData['name']));

        try {
            Category::create($validatedData);
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on store category', $e->getMessage());    
        }
        return ResponseFormat::createResponse(201, 'Created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $category = Category::findOrfail($id);
            $categories = [
                'category' => $category,
                'products' => $category->categories_product
            ];
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Category not found', $e->getMessage());
        } 
        return ResponseFormat::createResponse(200, 'OK', $categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validator = $this->validateCategory();

        if ($validator->fails()) {
            return ResponseFormat::createResponse(400, 'Format data yang dikirim salah', $validator->errors());
        }

        $validatedData = $validator->validated();
        $validatedData['name'] = ucwords(strtolower($validatedData['name']));

        try {
            $category->name = $validatedData['name'];
            $category->save();
        } catch (Exception $e) {
            return ResponseFormat::createResponse(500, 'Something went wrong on update category', $e->getMessage());
        }
        return ResponseFormat::createResponse(200, 'OK', $category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Cari id produk jika gagal lempar pada exception
            $category = Category::findOrfail($id);
            $category->delete();
        } catch (Exception $e) {
            return ResponseFormat::createResponse(404, 'Category id not found', $e->getMessage());    
        }
        return ResponseFormat::createResponse(201, 'OK');
    }

    public function validateCategory()
    {
        return Validator::make(request()->all(), [
            'name' => 'required|max:190|string|unique:categories'
        ]);
    }
}
