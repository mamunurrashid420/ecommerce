<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Get all categories (Public - for frontend display)
     */
    public function index(Request $request)
    {
        try {
            $query = Category::with(['creator', 'updater']);

            // Always load children categories by default
            $query->with(['children' => function ($query) {
                $query->active()->orderBy('sort_order')->with(['creator', 'updater']);
            }]);

            // Apply filters
            if ($request->has('featured') && $request->featured == 'true') {
                $query->featured();
            }

            // By default, only show parent categories (parent_id is null) unless explicitly requested
            if (!$request->has('parent_only') || $request->parent_only !== 'false') {
                $query->parent(); // Only parent categories in main array
            }

            if ($request->has('active') && $request->active == 'true') {
                $query->active();
            }

            // Option to exclude children if needed
            if ($request->has('with_children') && $request->with_children == 'false') {
                $query = Category::with(['creator', 'updater']); // Reset query without children
                
                // Reapply filters
                if ($request->has('featured') && $request->featured == 'true') {
                    $query->featured();
                }

                if (!$request->has('parent_only') || $request->parent_only !== 'false') {
                    $query->parent();
                }

                if ($request->has('active') && $request->active == 'true') {
                    $query->active();
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination or get all
            if ($request->has('paginate') && $request->paginate == 'true') {
                $perPage = $request->get('per_page', 15);
                $categories = $query->paginate($perPage);
            } else {
                $categories = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for dropdown (simplified structure)
     */
    public function dropdown()
    {
        try {
            $categories = Category::active()
                ->select('id', 'name', 'parent_id')
                ->with(['parent:id,name'])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->hierarchy_path,
                        'parent_id' => $category->parent_id
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories dropdown',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for customers (simplified structure with essential fields only)
     */
    public function customerIndex(Request $request)
    {
        try {
            $query = Category::active()
                ->select('id', 'name', 'icon', 'slug', 'image_url', 'meta_title', 'meta_description', 'meta_keywords', 'parent_id');

            // Load children with same essential fields
            $query->with(['children' => function ($query) {
                $query->active()
                    ->select('id', 'name', 'icon', 'slug', 'image_url', 'meta_title', 'meta_description', 'meta_keywords', 'parent_id')
                    ->orderBy('sort_order');
            }]);

            // Apply filters
            if ($request->has('featured') && $request->featured == 'true') {
                $query->featured();
            }

            // By default, only show parent categories unless explicitly requested
            if (!$request->has('parent_only') || $request->parent_only !== 'false') {
                $query->parent();
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $categories = $query->get();

            // Transform the data to include full_image_url
            $transformedCategories = $categories->map(function ($category) {
                $data = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'full_image_url' => $category->icon ? url($category->icon) : ($category->image_url ? url($category->image_url) : null),
                    'meta_title' => $category->meta_title,
                    'meta_description' => $category->meta_description,
                    'meta_keywords' => $category->meta_keywords,
                ];

                // Transform children with same structure
                if ($category->children && $category->children->count() > 0) {
                    $data['children'] = $category->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'full_image_url' => $child->icon ? url($child->icon) : ($child->image_url ? url($child->image_url) : null),
                            'meta_title' => $child->meta_title,
                            'meta_description' => $child->meta_description,
                            'meta_keywords' => $child->meta_keywords,
                        ];
                    });
                }

                return $data;
            });

            return response()->json([
                'success' => true,
                'data' => $transformedCategories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customer categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories with latest products (Public - for categories page)
     * Returns categories with their 8 latest active products
     */
    public function withLatestProducts(Request $request)
    {
        try {
            $query = Category::with(['parent', 'creator', 'updater'])
                ->whereHas('products', function ($query) {
                    $query->where('is_active', true);
                })
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->with(['products' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['media', 'category'])
                        ->orderBy('created_at', 'desc')
                        ->limit(8);
                }]);

            // Apply filters
            if ($request->has('featured') && $request->featured == 'true') {
                $query->featured();
            }

            if ($request->has('parent_only') && $request->parent_only == 'true') {
                $query->parent();
            }

            if ($request->has('active') && $request->active == 'true') {
                $query->active();
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination or get all
            if ($request->has('paginate') && $request->paginate == 'true') {
                $perPage = $request->get('per_page', 15);
                $categories = $query->paginate($perPage);
            } else {
                $categories = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories with products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get featured categories (Public)
     */
    public function featured()
    {
        try {
            $categories = Category::active()
                ->featured()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order');
                }])
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new category (Admin only)
     */
    public function store(Request $request)
    {
        try {
            // Check for icon file upload first (handles method spoofing)
            $hasIconFile = false;
            $files = $request->files->all();
            if (isset($files['icon']) && $files['icon']) {
                $hasIconFile = true;
            } elseif ($request->hasFile('icon')) {
                $hasIconFile = true;
            } elseif (!empty($request->allFiles()) && isset($request->allFiles()['icon'])) {
                $hasIconFile = true;
            } elseif ($request->file('icon')) {
                $hasIconFile = true;
            }

            // Build validation rules
            $validationRules = [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:categories,slug',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'sort_order' => 'nullable|integer|min:0', // Accepts both string and integer
                'is_active' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ];

            // Add icon validation only if file is present
            // Using 'file' instead of 'image' to support SVG files
            if ($hasIconFile) {
                $validationRules['icon'] = 'required|file|mimes:jpeg,png,jpg,gif,webp,svg|max:2048';
            } else {
                $validationRules['icon'] = 'nullable|file|mimes:jpeg,png,jpg,gif,webp,svg|max:2048';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->except(['icon']);

            // Handle icon image upload
            // Check for file upload - handle method spoofing (PUT via POST with _method)
            $iconFile = null;
            $hasIconFile = false;
            
            // Method 1: Check Symfony's file bag directly (works with method spoofing)
            $files = $request->files->all();
            if (isset($files['icon']) && $files['icon']) {
                $iconFile = $files['icon'];
                $hasIconFile = true;
            }
            
            // Method 2: Check Laravel's hasFile() method
            if (!$hasIconFile && $request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $hasIconFile = true;
            }
            
            // Method 3: Check allFiles() array
            if (!$hasIconFile) {
                $allFiles = $request->allFiles();
                if (!empty($allFiles) && isset($allFiles['icon'])) {
                    $iconFile = $allFiles['icon'];
                    $hasIconFile = true;
                }
            }
            
            // Method 4: Check file() method directly
            if (!$hasIconFile && $request->file('icon')) {
                $iconFile = $request->file('icon');
                $hasIconFile = true;
            }
            
            if ($hasIconFile && $iconFile && $iconFile->isValid()) {
                $filename = 'icon_' . time() . '_' . uniqid() . '.' . $iconFile->getClientOriginalExtension();
                // Use Storage facade which works with both Symfony and Laravel UploadedFile
                $path = Storage::disk('public')->putFileAs('categories/icons', $iconFile, $filename);
                $data['icon'] = Storage::url($path);
                // Also set image_url for backward compatibility
                $data['image_url'] = Storage::url($path);
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

            // Set default sort_order if not provided
            if (!isset($data['sort_order'])) {
                $maxSortOrder = Category::max('sort_order') ?? 0;
                $data['sort_order'] = $maxSortOrder + 1;
            }

            $category = Category::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category->load(['parent', 'children', 'creator', 'updater'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single category (Public)
     */
    public function show($identifier)
    {
        try {
            // Try to find by ID first (if numeric), then by slug
            if (is_numeric($identifier)) {
                $category = Category::with([
                    'parent', 
                    'children' => function ($query) {
                        $query->active()->orderBy('sort_order');
                    },
                    'creator', 
                    'updater'
                ])
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->find($identifier);
            } else {
                $category = Category::with([
                    'parent', 
                    'children' => function ($query) {
                        $query->active()->orderBy('sort_order');
                    },
                    'creator', 
                    'updater'
                ])
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->where('slug', $identifier)
                ->first();
            }

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a category (Admin only)
     */
    public function update(Request $request, Category $category)
    {
        try {
            // Check for icon file upload first (handles method spoofing)
            $hasIconFile = false;
            $files = $request->files->all();
            if (isset($files['icon']) && $files['icon']) {
                $hasIconFile = true;
            } elseif ($request->hasFile('icon')) {
                $hasIconFile = true;
            } elseif (!empty($request->allFiles()) && isset($request->allFiles()['icon'])) {
                $hasIconFile = true;
            } elseif ($request->file('icon')) {
                $hasIconFile = true;
            }

            // Build validation rules
            $validationRules = [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $category->id,
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'sort_order' => 'nullable|integer|min:0', // Accepts both string and integer
                'is_active' => 'nullable|boolean',
                'is_featured' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ];

            // Add icon validation only if file is present, otherwise skip icon validation entirely
            // Using 'file' instead of 'image' to support SVG files
            if ($hasIconFile) {
                $validationRules['icon'] = 'required|file|mimes:jpeg,png,jpg,gif,webp,svg|max:2048';
            }
            // If no file is uploaded and icon field contains a string path, don't validate it as an image

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Prevent setting parent as itself or its own child
            if (isset($request->parent_id) && $request->parent_id) {
                if ($request->parent_id == $category->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Category cannot be its own parent'
                    ], 422);
                }

                // Check if the new parent is a child of current category
                $isChild = $this->isChildCategory($category->id, $request->parent_id);
                if ($isChild) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set a child category as parent'
                    ], 422);
                }
            }

            DB::beginTransaction();

            $data = $request->except(['icon']);

            // Handle icon image upload
            // Check for file upload - handle method spoofing (PUT via POST with _method)
            $iconFile = null;
            $hasIconFile = false;
            
            // Method 1: Check Symfony's file bag directly (works with method spoofing)
            $files = $request->files->all();
            if (isset($files['icon']) && $files['icon']) {
                $iconFile = $files['icon'];
                $hasIconFile = true;
            }
            
            // Method 2: Check Laravel's hasFile() method
            if (!$hasIconFile && $request->hasFile('icon')) {
                $iconFile = $request->file('icon');
                $hasIconFile = true;
            }
            
            // Method 3: Check allFiles() array
            if (!$hasIconFile) {
                $allFiles = $request->allFiles();
                if (!empty($allFiles) && isset($allFiles['icon'])) {
                    $iconFile = $allFiles['icon'];
                    $hasIconFile = true;
                }
            }
            
            // Method 4: Check file() method directly
            if (!$hasIconFile && $request->file('icon')) {
                $iconFile = $request->file('icon');
                $hasIconFile = true;
            }
            
            if ($hasIconFile && $iconFile && $iconFile->isValid()) {
                // Delete old icon image if exists
                if ($category->icon) {
                    // Handle both /storage/ and full URL paths
                    $oldIconPath = $category->icon;
                    if (str_contains($oldIconPath, '/storage/')) {
                        $oldPath = str_replace('/storage/', '', $oldIconPath);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    } elseif (str_contains($oldIconPath, 'categories/icons/')) {
                        // Extract filename from URL
                        $filenameFromUrl = basename(parse_url($oldIconPath, PHP_URL_PATH));
                        $oldPath = 'categories/icons/' . $filenameFromUrl;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }
                
                // Also delete old image_url if it's different from icon
                if ($category->image_url && $category->image_url !== $category->icon) {
                    if (str_contains($category->image_url, '/storage/')) {
                        $oldPath = str_replace('/storage/', '', $category->image_url);
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }
                }

                $filename = 'icon_' . time() . '_' . uniqid() . '.' . $iconFile->getClientOriginalExtension();
                // Use Storage facade which works with both Symfony and Laravel UploadedFile
                $path = Storage::disk('public')->putFileAs('categories/icons', $iconFile, $filename);
                $data['icon'] = Storage::url($path);
                // Also set image_url for backward compatibility
                $data['image_url'] = Storage::url($path);
            }

            // Set updated_by if authenticated and user exists
            if (auth()->check()) {
                $userId = auth()->id();
                // Only set if user exists in database (field is nullable)
                if ($userId && User::where('id', $userId)->exists()) {
                    $data['updated_by'] = $userId;
                }
            }

            $category->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category->fresh()->load(['parent', 'children', 'creator', 'updater'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a category (Admin only)
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing products. Move products to another category first.'
                ], 409);
            }

            // Check if category has child categories
            if ($category->children()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with child categories. Delete or move child categories first.'
                ], 409);
            }

            DB::beginTransaction();

            // Store category data for response
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ];

            // Delete image if exists
            if ($category->image_url && str_contains($category->image_url, '/storage/categories/')) {
                $imagePath = str_replace('/storage/', '', $category->image_url);
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
                'data' => $categoryData
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update sort order (Admin only)
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'categories' => 'required|array',
                'categories.*.id' => 'required|exists:categories,id',
                'categories.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            foreach ($request->categories as $categoryData) {
                Category::where('id', $categoryData['id'])
                    ->update(['sort_order' => $categoryData['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status (Admin only)
     */
    public function toggleFeatured(Category $category)
    {
        try {
            $updateData = ['is_featured' => !$category->is_featured];
            if (auth()->check()) {
                $userId = auth()->id();
                if ($userId && User::where('id', $userId)->exists()) {
                    $updateData['updated_by'] = $userId;
                }
            }
            $category->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Featured status updated successfully',
                'data' => [
                    'id' => $category->id,
                    'is_featured' => $category->is_featured
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update featured status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status (Admin only)
     */
    public function toggleActive(Category $category)
    {
        try {
            $updateData = ['is_active' => !$category->is_active];
            if (auth()->check()) {
                $userId = auth()->id();
                if ($userId && User::where('id', $userId)->exists()) {
                    $updateData['updated_by'] = $userId;
                }
            }
            $category->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Active status updated successfully',
                'data' => [
                    'id' => $category->id,
                    'is_active' => $category->is_active
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update active status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category tree structure (Admin)
     */
    public function tree()
    {
        try {
            $categories = Category::with(['children' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->withCount(['products as active_products_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->parent()
            ->orderBy('sort_order')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category tree',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to check if a category is a child of another
     */
    private function isChildCategory($parentId, $childId)
    {
        $category = Category::find($childId);
        
        while ($category && $category->parent_id) {
            if ($category->parent_id == $parentId) {
                return true;
            }
            $category = $category->parent;
        }
        
        return false;
    }
}