<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'media', 'creator', 'updater'])
            ->where('is_active', true)
            ->paginate(12);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products',
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|url',
        ]);

        $product = Product::create($request->all());
        return response()->json($product->load('category'), 201);
    }

    public function show($identifier)
    {
        // Try to find by ID first (if numeric), then by slug
        if (is_numeric($identifier)) {
            $product = Product::with(['category', 'media', 'customFields', 'creator', 'updater'])
                ->find($identifier);
        } else {
            $product = Product::with(['category', 'media', 'customFields', 'creator', 'updater'])
                ->where('slug', $identifier)
                ->first();
        }

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'image_url' => 'nullable|url',
        ]);

        $product->update($request->all());
        return response()->json($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
