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
            $query = Category::with(['parent', 'children', 'creator', 'updater'])
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
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

            if ($request->has('with_children') && $request->with_children == 'true') {
                $query->with(['children' => function ($query) {
                    $query->active()->orderBy('sort_order');
                }]);
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
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:categories,slug',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048', // Icon image upload
            ]);

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
            if ($request->hasFile('icon')) {
                $iconImage = $request->file('icon');
                $filename = 'icon_' . time() . '_' . uniqid() . '.' . $iconImage->getClientOriginalExtension();
                $path = $iconImage->storeAs('categories/icons', $filename, 'public');
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
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $category->id,
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'boolean',
                'is_featured' => 'boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048', // Icon image upload
            ]);

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
            if ($request->hasFile('icon')) {
                // Delete old icon image if exists
                if ($category->icon && str_contains($category->icon, '/storage/categories/icons/')) {
                    $oldPath = str_replace('/storage/', '', $category->icon);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                // Also delete old image_url if it's the same as icon
                elseif ($category->image_url && str_contains($category->image_url, '/storage/categories/')) {
                    $oldPath = str_replace('/storage/', '', $category->image_url);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $iconImage = $request->file('icon');
                $filename = 'icon_' . time() . '_' . uniqid() . '.' . $iconImage->getClientOriginalExtension();
                $path = $iconImage->storeAs('categories/icons', $filename, 'public');
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