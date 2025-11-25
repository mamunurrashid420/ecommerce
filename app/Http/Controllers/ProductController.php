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
    public function index(Request $request)
    {
        try {
            // Validate request parameters
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0|gte:min_price',
                'brand' => 'nullable|string|max:100',
                'in_stock' => 'nullable|boolean',
                'sort_by' => 'nullable|string|in:name,price,created_at,stock_quantity',
                'sort_order' => 'nullable|string|in:asc,desc',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Product::with(['category', 'media', 'creator', 'updater'])
                ->where('is_active', true);

            // Search functionality - search across multiple fields
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('long_description', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('brand', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('model', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('meta_keywords', 'LIKE', "%{$searchTerm}%")
                        ->orWhereJsonContains('tags', $searchTerm);
                });
            }

            // Filter by category
            if ($request->has('category_id') && $request->category_id) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by price range
            if ($request->has('min_price') && $request->min_price !== null) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price') && $request->max_price !== null) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filter by brand
            if ($request->has('brand') && !empty($request->brand)) {
                $query->where('brand', 'LIKE', "%{$request->brand}%");
            }

            // Filter by stock availability
            if ($request->has('in_stock')) {
                if ($request->in_stock == true || $request->in_stock === 'true' || $request->in_stock === '1') {
                    $query->where('stock_quantity', '>', 0);
                } elseif ($request->in_stock == false || $request->in_stock === 'false' || $request->in_stock === '0') {
                    $query->where('stock_quantity', '<=', 0);
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 12);
            $products = $query->paginate($perPage);

            return response()->json($products);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'tags' => 'nullable|string|array',
                'is_active' => 'boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'image_url' => 'nullable|url',
                'images' => 'nullable|array|max:10',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'media' => 'nullable|array',
                'media.*.type' => 'required_with:media|string|in:image,video',
                'media.*.url' => 'required_with:media|url',
                'media.*.alt_text' => 'nullable|string|max:255',
                'media.*.title' => 'nullable|string|max:255',
                'media.*.is_thumbnail' => 'nullable|boolean',
                'media.*.sort_order' => 'nullable|integer|min:0',
                'custom_fields' => 'nullable|array',
                'custom_fields.*.label_name' => 'required_with:custom_fields|string|max:255',
                'custom_fields.*.value' => 'required_with:custom_fields|string',
                'custom_fields.*.field_type' => 'nullable|string|max:50',
                'custom_fields.*.sort_order' => 'nullable|integer|min:0',
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

            // Convert tags to array if provided as string or array
            if (isset($data['tags'])) {
                if (is_string($data['tags'])) {
                    $data['tags'] = array_map('trim', explode(',', $data['tags']));
                } elseif (is_array($data['tags'])) {
                    $data['tags'] = array_map('trim', $data['tags']);
                }
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

            // Handle media URLs (for seeder-like data)
            if ($request->has('media') && is_array($request->media)) {
                $this->handleMediaUrls($product, $request->media);
            }

            // Handle multiple image file uploads
            if ($request->hasFile('images')) {
                $this->handleImageUploads($product, $request->file('images'));
            }

            // Handle custom fields
            if ($request->has('custom_fields') && is_array($request->custom_fields)) {
                $this->handleCustomFields($product, $request->custom_fields);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'media', 'customFields', 'creator', 'updater'])
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

    /**
     * Handle media URLs (for seeder-like data insertion)
     */
    private function handleMediaUrls($product, $mediaArray)
    {
        $existingMediaCount = $product->media()->count();
        $hasThumbnail = $product->media()->where('is_thumbnail', true)->exists();

        foreach ($mediaArray as $index => $mediaItem) {
            $mediaData = [
                'product_id' => $product->id,
                'type' => $mediaItem['type'] ?? 'image',
                'url' => $mediaItem['url'],
                'alt_text' => $mediaItem['alt_text'] ?? null,
                'title' => $mediaItem['title'] ?? null,
                'is_thumbnail' => isset($mediaItem['is_thumbnail']) 
                    ? (bool)$mediaItem['is_thumbnail'] 
                    : (!$hasThumbnail && $index === 0 && ($mediaItem['type'] ?? 'image') === 'image'),
                'sort_order' => $mediaItem['sort_order'] ?? ($existingMediaCount + $index + 1),
            ];

            ProductMedia::create($mediaData);

            // Mark that we have a thumbnail if this one is set as thumbnail
            if ($mediaData['is_thumbnail']) {
                $hasThumbnail = true;
            }
        }
    }

    /**
     * Handle custom fields
     */
    private function handleCustomFields($product, $customFieldsArray)
    {
        foreach ($customFieldsArray as $index => $field) {
            \App\Models\ProductCustomField::create([
                'product_id' => $product->id,
                'label_name' => $field['label_name'],
                'value' => $field['value'],
                'field_type' => $field['field_type'] ?? 'text',
                'sort_order' => $field['sort_order'] ?? ($index + 1),
            ]);
        }
    }

    /**
     * Bulk insert products (useful for seeder data)
     * If no products are provided in request, imports from products.php file
     */
    public function bulkStore(Request $request)
    {
        try {
            $products = $request->input('products');
            
            // If no products provided, import from products.php file
            if (empty($products)) {
                $products = $this->importProductsFromFile();
            }

            // Validate products
            $validator = \Illuminate\Support\Facades\Validator::make(['products' => $products], [
                'products' => 'required|array|min:1',
                'products.*.name' => 'required|string|max:255',
                'products.*.slug' => 'nullable|string|max:255',
                'products.*.description' => 'nullable|string',
                'products.*.long_description' => 'nullable|string',
                'products.*.price' => 'required|numeric|min:0',
                'products.*.stock_quantity' => 'required|integer|min:0',
                'products.*.sku' => 'required|string|max:100',
                'products.*.weight' => 'nullable|numeric|min:0',
                'products.*.dimensions' => 'nullable|string|max:100',
                'products.*.brand' => 'nullable|string|max:100',
                'products.*.model' => 'nullable|string|max:100',
                'products.*.category_id' => 'required|exists:categories,id',
                'products.*.tags' => 'nullable',
                'products.*.is_active' => 'nullable|boolean',
                'products.*.meta_title' => 'nullable|string|max:255',
                'products.*.meta_description' => 'nullable|string|max:500',
                'products.*.meta_keywords' => 'nullable|string|max:500',
                'products.*.media' => 'nullable|array',
                'products.*.media.*.type' => 'required_with:products.*.media|string|in:image,video',
                'products.*.media.*.url' => 'required_with:products.*.media|url',
                'products.*.media.*.alt_text' => 'nullable|string|max:255',
                'products.*.media.*.title' => 'nullable|string|max:255',
                'products.*.media.*.is_thumbnail' => 'nullable|boolean',
                'products.*.media.*.sort_order' => 'nullable|integer|min:0',
                'products.*.custom_fields' => 'nullable|array',
                'products.*.custom_fields.*.label_name' => 'required_with:products.*.custom_fields|string|max:255',
                'products.*.custom_fields.*.value' => 'required_with:products.*.custom_fields|string',
                'products.*.custom_fields.*.field_type' => 'nullable|string|max:50',
                'products.*.custom_fields.*.sort_order' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $createdProducts = [];
            $skippedProducts = [];
            $errors = [];

            DB::beginTransaction();

            try {
                foreach ($products as $index => $productData) {
                    try {
                        // Check if product already exists by SKU
                        $existingProduct = Product::where('sku', $productData['sku'])->first();
                        
                        if ($existingProduct) {
                            $skippedProducts[] = [
                                'index' => $index,
                                'sku' => $productData['sku'],
                                'name' => $productData['name'] ?? 'Unknown',
                                'reason' => 'Product with this SKU already exists',
                                'existing_id' => $existingProduct->id
                            ];
                            continue;
                        }

                        // Generate slug if not provided
                        if (empty($productData['slug'])) {
                            $productData['slug'] = \Illuminate\Support\Str::slug($productData['name']);
                            
                            // Check if slug already exists (but product doesn't exist by SKU)
                            $originalSlug = $productData['slug'];
                            $counter = 1;
                            while (Product::where('slug', $productData['slug'])->exists()) {
                                $productData['slug'] = $originalSlug . '-' . $counter;
                                $counter++;
                            }
                        } else {
                            // If slug is provided, check if it already exists
                            $existingBySlug = Product::where('slug', $productData['slug'])->first();
                            if ($existingBySlug) {
                                $skippedProducts[] = [
                                    'index' => $index,
                                    'sku' => $productData['sku'],
                                    'name' => $productData['name'] ?? 'Unknown',
                                    'slug' => $productData['slug'],
                                    'reason' => 'Product with this slug already exists',
                                    'existing_id' => $existingBySlug->id
                                ];
                                continue;
                            }
                        }

                        // Convert tags to array if provided
                        if (isset($productData['tags'])) {
                            if (is_string($productData['tags'])) {
                                // Try to decode JSON first (for products.php format)
                                $decoded = json_decode($productData['tags'], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                    $productData['tags'] = $decoded;
                                } else {
                                    // If not JSON, treat as comma-separated string
                                    $productData['tags'] = array_map('trim', explode(',', $productData['tags']));
                                }
                            } elseif (is_array($productData['tags'])) {
                                $productData['tags'] = array_map('trim', $productData['tags']);
                            }
                        }

                        // Convert images array to media array if images exist
                        if (isset($productData['images']) && is_array($productData['images'])) {
                            $mediaArray = [];
                            foreach ($productData['images'] as $index => $imageUrl) {
                                $mediaArray[] = [
                                    'type' => 'image',
                                    'url' => $imageUrl,
                                    'alt_text' => $productData['name'] ?? null,
                                    'title' => $productData['name'] ?? null,
                                    'is_thumbnail' => $index === 0, // First image is thumbnail
                                    'sort_order' => $index + 1,
                                ];
                            }
                            $productData['media'] = $mediaArray;
                            unset($productData['images']); // Remove images key
                        }

                        // Set created_by and updated_by if authenticated
                        if (auth()->check()) {
                            $userId = auth()->id();
                            if ($userId && User::where('id', $userId)->exists()) {
                                $productData['created_by'] = $userId;
                                $productData['updated_by'] = $userId;
                            }
                        }

                        // Set default is_active if not provided
                        if (!isset($productData['is_active'])) {
                            $productData['is_active'] = true;
                        }

                        $product = Product::create($productData);

                        // Handle media URLs
                        if (isset($productData['media']) && is_array($productData['media'])) {
                            $this->handleMediaUrls($product, $productData['media']);
                        }

                        // Handle custom fields
                        if (isset($productData['custom_fields']) && is_array($productData['custom_fields'])) {
                            $this->handleCustomFields($product, $productData['custom_fields']);
                        }

                        $createdProducts[] = $product->load(['category', 'media', 'customFields']);

                    } catch (\Exception $e) {
                        $errors[] = [
                            'index' => $index,
                            'name' => $productData['name'] ?? 'Unknown',
                            'error' => $e->getMessage()
                        ];
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bulk product insertion completed',
                    'data' => [
                        'created_count' => count($createdProducts),
                        'skipped_count' => count($skippedProducts),
                        'failed_count' => count($errors),
                        'total_processed' => count($products),
                        'products' => $createdProducts,
                        'skipped' => $skippedProducts,
                        'errors' => $errors
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk insert products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import products from products.php file
     */
    private function importProductsFromFile()
    {
        $filePath = base_path('products.php');
        
        if (!file_exists($filePath)) {
            throw new \Exception('products.php file not found at: ' . $filePath);
        }

        // Read the file content
        $fileContent = file_get_contents($filePath);
        
        // Find the start of $products = [
        $startPos = strpos($fileContent, '$products');
        if ($startPos === false) {
            throw new \Exception('Could not find $products array in file.');
        }
        
        // Find the opening bracket after $products =
        $bracketPos = strpos($fileContent, '[', $startPos);
        if ($bracketPos === false) {
            throw new \Exception('Could not find opening bracket for products array.');
        }
        
        // Extract array content by counting brackets
        $arrayStart = $bracketPos + 1;
        $bracketCount = 1;
        $currentPos = $arrayStart;
        $arrayEnd = null;
        
        while ($bracketCount > 0 && $currentPos < strlen($fileContent)) {
            $char = $fileContent[$currentPos];
            
            if ($char === '[') {
                $bracketCount++;
            } elseif ($char === ']') {
                $bracketCount--;
                if ($bracketCount === 0) {
                    $arrayEnd = $currentPos;
                    break;
                }
            }
            
            $currentPos++;
        }
        
        if ($arrayEnd === null) {
            throw new \Exception('Could not find matching closing bracket for products array.');
        }
        
        // Extract the array content (without the brackets)
        $arrayContent = substr($fileContent, $arrayStart, $arrayEnd - $arrayStart);
        
        // Create a temporary PHP file with just the return statement
        $tempFile = tempnam(sys_get_temp_dir(), 'products_import_');
        $tempFile .= '.php';
        
        // Write the array content to temp file
        file_put_contents($tempFile, '<?php return [' . $arrayContent . '];');
        
        try {
            // Include the temp file to get the array
            $products = include $tempFile;
            
            if (!is_array($products)) {
                throw new \Exception('Failed to parse products array from file. Invalid format.');
            }
            
            return $products;
        } finally {
            // Clean up temp file
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }
}
