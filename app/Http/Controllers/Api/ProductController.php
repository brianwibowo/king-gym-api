<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('product-images', 'public');
            $data['image_path'] = $path;
        }

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    public function show($id)
    {
        return response()->json(Product::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->except(['image']);

        if ($request->hasFile('image')) {
            // Delete old image code if needed, but for now just overwrite path
            $path = $request->file('image')->store('product-images', 'public');
            $data['image_path'] = $path;
        }

        $product->update($data);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
