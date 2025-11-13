<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:products,slug',
                'description' => 'nullable|string',
                'long_description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'sku' => 'required|string|max:100|unique:products,sku',
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string|max:100',
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'category_id' => 'required|exists:categories,id',
                'tags' => 'nullable|string',
                'is_active' => 'boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'image_url' => 'nullable|url',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
                
                // Ensure slug uniqueness
                $originalSlug = $data['slug'];
                $counter = 1;
                while (Product::where('slug', $data['slug'])->exists()) {
                    $data['slug'] = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Convert tags string to array if provided
            if (isset($data['tags']) && is_string($data['tags'])) {
                $data['tags'] = array_map('trim', explode(',', $data['tags']));
            }

            // Set created_by and updated_by if authenticated and user exists
            if (auth()->check()) {
                $userId = auth()->id();
                // Only set if user exists in database (fields are nullable)
                if ($userId && User::where('id', $userId)->exists()) {
                    $data['created_by'] = $userId;
                    $data['updated_by'] = $userId;
                }
            }

            DB::beginTransaction();
            
            $product = Product::create($data);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'media', 'creator', 'updater'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
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
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:products,slug,' . $product->id,
                'description' => 'nullable|string',
                'long_description' => 'nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'stock_quantity' => 'sometimes|required|integer|min:0',
                'sku' => 'sometimes|required|string|max:100|unique:products,sku,' . $product->id,
                'weight' => 'nullable|numeric|min:0',
                'dimensions' => 'nullable|string|max:100',
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'category_id' => 'sometimes|required|exists:categories,id',
                'tags' => 'nullable|string',
                'is_active' => 'boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'image_url' => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Update slug if name is changed and slug is not provided
            if (isset($data['name']) && !isset($data['slug'])) {
                $newSlug = \Illuminate\Support\Str::slug($data['name']);
                
                // Ensure slug uniqueness (excluding current product)
                $originalSlug = $newSlug;
                $counter = 1;
                while (Product::where('slug', $newSlug)->where('id', '!=', $product->id)->exists()) {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $data['slug'] = $newSlug;
            }

            // Convert tags string to array if provided
            if (isset($data['tags']) && is_string($data['tags'])) {
                $data['tags'] = array_map('trim', explode(',', $data['tags']));
            }

            // Set updated_by if authenticated and user exists
            if (auth()->check()) {
                $userId = auth()->id();
                // Only set if user exists in database (field is nullable)
                if ($userId && User::where('id', $userId)->exists()) {
                    $data['updated_by'] = $userId;
                }
            }

            $product->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->fresh()->load(['category', 'media', 'creator', 'updater'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Check if product has any orders (optional business logic)
            if ($product->orderItems()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders. Consider deactivating instead.',
                ], 409);
            }

            // Store product data before deletion for response
            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'slug' => $product->slug
            ];

            // Delete associated media files if any
            if ($product->media()->exists()) {
                foreach ($product->media as $media) {
                    // Delete physical files if stored locally
                    if ($media->file_path && \Illuminate\Support\Facades\Storage::exists($media->file_path)) {
                        \Illuminate\Support\Facades\Storage::delete($media->file_path);
                    }
                }
                // Delete media records
                $product->media()->delete();
            }

            // Delete custom fields
            $product->customFields()->delete();

            // Delete the product
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => $productData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple images for a product
     */
    public function uploadImages(Request $request, Product $product)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'images' => 'required|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'alt_texts' => 'nullable|array',
                'alt_texts.*' => 'nullable|string|max:255',
                'titles' => 'nullable|array',
                'titles.*' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $uploadedImages = $this->handleImageUploads(
                $product, 
                $request->file('images'),
                $request->input('alt_texts', []),
                $request->input('titles', [])
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Images uploaded successfully',
                'data' => [
                    'product_id' => $product->id,
                    'uploaded_images' => $uploadedImages,
                    'total_images' => $product->media()->count()
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a specific image from product
     */
    public function removeImage(Product $product, ProductMedia $media)
    {
        try {
            // Verify the media belongs to this product
            if ($media->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image does not belong to this product'
                ], 403);
            }

            // Delete physical file if stored locally
            if ($media->file_path && Storage::exists($media->file_path)) {
                Storage::delete($media->file_path);
            }

            $mediaData = [
                'id' => $media->id,
                'url' => $media->url,
                'alt_text' => $media->alt_text
            ];

            $media->delete();

            // If this was the thumbnail, set the first remaining image as thumbnail
            if ($media->is_thumbnail) {
                $firstImage = $product->media()->first();
                if ($firstImage) {
                    $firstImage->update(['is_thumbnail' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Image removed successfully',
                'data' => [
                    'removed_image' => $mediaData,
                    'remaining_images' => $product->media()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set thumbnail image for product
     */
    public function setThumbnail(Product $product, ProductMedia $media)
    {
        try {
            // Verify the media belongs to this product
            if ($media->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image does not belong to this product'
                ], 403);
            }

            DB::beginTransaction();

            // Remove thumbnail flag from all images
            $product->media()->update(['is_thumbnail' => false]);

            // Set new thumbnail
            $media->update(['is_thumbnail' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thumbnail set successfully',
                'data' => [
                    'thumbnail_id' => $media->id,
                    'thumbnail_url' => $media->url
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to set thumbnail',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update image details (alt text, title, sort order)
     */
    public function updateImage(Request $request, Product $product, ProductMedia $media)
    {
        try {
            // Verify the media belongs to this product
            if ($media->product_id !== $product->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image does not belong to this product'
                ], 403);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'alt_text' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $media->update($request->only(['alt_text', 'title', 'sort_order']));

            return response()->json([
                'success' => true,
                'message' => 'Image updated successfully',
                'data' => $media->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle multiple image uploads
     */
    private function handleImageUploads($product, $images, $altTexts = [], $titles = [])
    {
        $uploadedImages = [];
        $isFirstImage = $product->media()->count() === 0;

        foreach ($images as $index => $image) {
            $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('products/' . $product->id, $filename, 'public');
            
            $mediaData = [
                'product_id' => $product->id,
                'type' => 'image',
                'url' => Storage::url($path),
                'file_path' => $path,
                'alt_text' => $altTexts[$index] ?? null,
                'title' => $titles[$index] ?? null,
                'is_thumbnail' => $isFirstImage && $index === 0, // First image of first upload is thumbnail
                'sort_order' => $product->media()->count() + $index,
                'file_size' => $image->getSize(),
                'mime_type' => $image->getMimeType(),
            ];

            $productMedia = ProductMedia::create($mediaData);
            $uploadedImages[] = $productMedia;
        }

        return $uploadedImages;
    }
}
