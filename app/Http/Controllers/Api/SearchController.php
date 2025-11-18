<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Unified search for products and categories
     * 
     * This endpoint allows both authenticated and guest users to search
     * for products and categories simultaneously with paginated results.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:1|max:255',
                'type' => 'nullable|string|in:products,categories,all',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
                'category_id' => 'nullable|integer|exists:categories,id',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0|gte:min_price',
                'in_stock' => 'nullable|boolean',
                'sort_by' => 'nullable|string|in:relevance,name,price_asc,price_desc,created_at',
                'sort_order' => 'nullable|string|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = trim($request->input('query'));
            $type = $request->input('type', 'all');
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            $results = [
                'query' => $query,
                'type' => $type,
                'products' => null,
                'categories' => null,
                'meta' => [
                    'total_results' => 0,
                    'products_count' => 0,
                    'categories_count' => 0,
                ]
            ];

            // Search products
            if ($type === 'all' || $type === 'products') {
                $products = $this->searchProducts($request, $query, $perPage, $page);
                $results['products'] = $products;
                $results['meta']['products_count'] = $products['total'] ?? 0;
            }

            // Search categories
            if ($type === 'all' || $type === 'categories') {
                $categories = $this->searchCategories($request, $query, $perPage, $page);
                $results['categories'] = $categories;
                $results['meta']['categories_count'] = $categories['total'] ?? 0;
            }

            // Calculate total results
            $results['meta']['total_results'] = 
                $results['meta']['products_count'] + 
                $results['meta']['categories_count'];

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred during search'
            ], 500);
        }
    }

    /**
     * Search products
     * 
     * @param Request $request
     * @param string $query
     * @param int $perPage
     * @param int $page
     * @return array
     */
    private function searchProducts(Request $request, string $query, int $perPage, int $page): array
    {
        $productQuery = Product::with(['category', 'media'])
            ->where('is_active', true);

        // Search in multiple fields
        $productQuery->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhere('long_description', 'LIKE', "%{$query}%")
                ->orWhere('sku', 'LIKE', "%{$query}%")
                ->orWhere('brand', 'LIKE', "%{$query}%")
                ->orWhere('model', 'LIKE', "%{$query}%")
                ->orWhere('slug', 'LIKE', "%{$query}%");
            
            // Search in tags (stored as JSON array)
            $q->orWhereJsonContains('tags', $query);
        });

        // Apply filters
        if ($request->has('category_id')) {
            $productQuery->where('category_id', $request->input('category_id'));
        }

        if ($request->has('min_price')) {
            $productQuery->where('price', '>=', $request->input('min_price'));
        }

        if ($request->has('max_price')) {
            $productQuery->where('price', '<=', $request->input('max_price'));
        }

        if ($request->has('in_stock') && $request->input('in_stock')) {
            $productQuery->where('stock_quantity', '>', 0);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'relevance');
        $sortOrder = $request->input('sort_order', 'desc');

        switch ($sortBy) {
            case 'name':
                $productQuery->orderBy('name', $sortOrder);
                break;
            case 'price_asc':
                $productQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productQuery->orderBy('price', 'desc');
                break;
            case 'created_at':
                $productQuery->orderBy('created_at', $sortOrder);
                break;
            case 'relevance':
            default:
                // Relevance: prioritize exact matches, then name matches, then description
                $productQuery->orderByRaw("
                    CASE 
                        WHEN name = ? THEN 1
                        WHEN name LIKE ? THEN 2
                        WHEN sku = ? THEN 3
                        WHEN description LIKE ? THEN 4
                        ELSE 5
                    END
                ", [
                    $query,
                    "{$query}%",
                    $query,
                    "%{$query}%"
                ])
                ->orderBy('name', 'asc');
                break;
        }

        // Paginate results
        $products = $productQuery->paginate($perPage, ['*'], 'products_page', $page);

        return [
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'last_page' => $products->lastPage(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
            'has_more_pages' => $products->hasMorePages(),
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ]
        ];
    }

    /**
     * Search categories
     * 
     * @param Request $request
     * @param string $query
     * @param int $perPage
     * @param int $page
     * @return array
     */
    private function searchCategories(Request $request, string $query, int $perPage, int $page): array
    {
        $categoryQuery = Category::with(['parent', 'children'])
            ->where('is_active', true);

        // Search in multiple fields
        $categoryQuery->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->orWhere('slug', 'LIKE', "%{$query}%")
                ->orWhere('meta_keywords', 'LIKE', "%{$query}%");
        });

        // Apply sorting
        $sortBy = $request->input('sort_by', 'relevance');
        $sortOrder = $request->input('sort_order', 'asc');

        switch ($sortBy) {
            case 'name':
                $categoryQuery->orderBy('name', $sortOrder);
                break;
            case 'created_at':
                $categoryQuery->orderBy('created_at', $sortOrder);
                break;
            case 'relevance':
            default:
                // Relevance: prioritize exact matches, then name matches
                $categoryQuery->orderByRaw("
                    CASE 
                        WHEN name = ? THEN 1
                        WHEN name LIKE ? THEN 2
                        WHEN slug = ? THEN 3
                        ELSE 4
                    END
                ", [
                    $query,
                    "{$query}%",
                    $query
                ])
                ->orderBy('name', 'asc');
                break;
        }

        // Paginate results
        $categories = $categoryQuery->paginate($perPage, ['*'], 'categories_page', $page);

        return [
            'data' => $categories->items(),
            'current_page' => $categories->currentPage(),
            'per_page' => $categories->perPage(),
            'total' => $categories->total(),
            'last_page' => $categories->lastPage(),
            'from' => $categories->firstItem(),
            'to' => $categories->lastItem(),
            'has_more_pages' => $categories->hasMorePages(),
            'links' => [
                'first' => $categories->url(1),
                'last' => $categories->url($categories->lastPage()),
                'prev' => $categories->previousPageUrl(),
                'next' => $categories->nextPageUrl(),
            ]
        ];
    }
}

