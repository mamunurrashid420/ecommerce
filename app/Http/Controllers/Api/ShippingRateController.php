<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class ShippingRateController extends Controller
{
    /**
     * Get all active shipping rates (Public endpoint)
     * Returns only 3 main categories (A, B, C)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $rates = ShippingRate::active()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('subcategory')
                ->get();

            // Format the response - only 3 items: A, B, and C (first subcategory)
            $result = [];
            $categories = ['A', 'B', 'C'];
            
            foreach ($categories as $cat) {
                $categoryRates = $rates->where('category', $cat);
                
                if ($categoryRates->isEmpty()) {
                    continue;
                }
                
                // For categories A and B, take the first (and only) entry
                // For category C, take the first subcategory entry
                $rate = $categoryRates->first();
                
                $entry = [
                    'category' => $cat,
                    'description_bn' => $rate->description_bn,
                    'description_en' => $rate->description_en,
                    'rates' => [
                        'air' => (float) $rate->rate_air,
                        'ship' => (float) $rate->rate_ship,
                    ]
                ];
                
                // Add subcategory for Category C
                if ($cat === 'C' && $rate->subcategory) {
                    $entry['subcategory'] = $rate->subcategory;
                }
                
                $result[] = $entry;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipping rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping rates grouped by category (Public endpoint)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function grouped(Request $request): JsonResponse
    {
        try {
            $rates = ShippingRate::active()
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('subcategory')
                ->get();

            // Format the response - each rate as a separate entry
            $result = [];
            
            foreach ($rates as $rate) {
                $entry = [
                    'category' => $rate->category,
                    'description_bn' => $rate->description_bn,
                    'description_en' => $rate->description_en,
                    'rates' => [
                        'air' => (float) $rate->rate_air,
                        'ship' => (float) $rate->rate_ship,
                    ]
                ];
                
                // Add subcategory for Category C entries
                if ($rate->category === 'C' && $rate->subcategory) {
                    $entry['subcategory'] = $rate->subcategory;
                }
                
                $result[] = $entry;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve grouped shipping rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get shipping rate by category (Public endpoint)
     * 
     * @param Request $request
     * @param string $category
     * @return JsonResponse
     */
    public function byCategory(Request $request, string $category): JsonResponse
    {
        try {
            if (!in_array($category, ['A', 'B', 'C'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category. Must be A, B, or C.'
                ], 400);
            }

            $rates = ShippingRate::active()
                ->byCategory($category)
                ->orderBy('sort_order')
                ->orderBy('subcategory')
                ->get();

            // Format response - return all rates as flat entries
            $result = [];
            
            foreach ($rates as $rate) {
                $entry = [
                    'category' => $category,
                    'description_bn' => $rate->description_bn,
                    'description_en' => $rate->description_en,
                    'rates' => [
                        'air' => (float) $rate->rate_air,
                        'ship' => (float) $rate->rate_ship,
                    ]
                ];
                
                // Add subcategory for Category C entries
                if ($category === 'C' && $rate->subcategory) {
                    $entry['subcategory'] = $rate->subcategory;
                }
                
                $result[] = $entry;
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipping rates',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
