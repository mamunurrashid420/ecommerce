<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class AdminShippingRateController extends Controller
{
    /**
     * List all shipping rates (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ShippingRate::query();

            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->get('category'));
            }

            // Filter by subcategory
            if ($request->has('subcategory')) {
                $query->where('subcategory', $request->get('subcategory'));
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search by description
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('description_bn', 'like', "%{$search}%")
                      ->orWhere('description_en', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder)
                  ->orderBy('category')
                  ->orderBy('subcategory');

            $perPage = $request->get('per_page', 15);
            $rates = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $rates->items(),
                'pagination' => [
                    'current_page' => $rates->currentPage(),
                    'last_page' => $rates->lastPage(),
                    'per_page' => $rates->perPage(),
                    'total' => $rates->total(),
                ],
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
     * Create a new shipping rate (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category' => 'required|in:A,B,C',
                'subcategory' => 'nullable|string|max:255',
                'description_bn' => 'required|string',
                'description_en' => 'required|string',
                'rate_air' => 'required|numeric|min:0',
                'rate_ship' => 'required|numeric|min:0',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            // Validate subcategory for category C
            if ($validated['category'] === 'C' && empty($validated['subcategory'])) {
                throw ValidationException::withMessages([
                    'subcategory' => ['Subcategory is required for category C.']
                ]);
            }

            // Validate subcategory values for category C
            if ($validated['category'] === 'C' && !in_array($validated['subcategory'], ['mold_tape_garments', 'liquid_cosmetics', 'battery_powerbank', 'sunglasses'])) {
                throw ValidationException::withMessages([
                    'subcategory' => ['Invalid subcategory for category C. Must be one of: mold_tape_garments, liquid_cosmetics, battery_powerbank, sunglasses']
                ]);
            }

            // Subcategory should be null for categories A and B
            if (in_array($validated['category'], ['A', 'B']) && !empty($validated['subcategory'])) {
                $validated['subcategory'] = null;
            }

            $rate = ShippingRate::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipping rate created successfully',
                'data' => $rate
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shipping rate',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a single shipping rate (Admin only)
     */
    public function show(ShippingRate $shippingRate): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $shippingRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipping rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a shipping rate (Admin only)
     */
    public function update(Request $request, ShippingRate $shippingRate): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category' => 'sometimes|in:A,B,C',
                'subcategory' => 'nullable|string|max:255',
                'description_bn' => 'sometimes|string',
                'description_en' => 'sometimes|string',
                'rate_air' => 'sometimes|numeric|min:0',
                'rate_ship' => 'sometimes|numeric|min:0',
                'is_active' => 'sometimes|boolean',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            // Validate subcategory for category C
            $category = $validated['category'] ?? $shippingRate->category;
            if ($category === 'C' && empty($validated['subcategory'] ?? $shippingRate->subcategory)) {
                throw ValidationException::withMessages([
                    'subcategory' => ['Subcategory is required for category C.']
                ]);
            }

            // Validate subcategory values for category C
            if ($category === 'C' && isset($validated['subcategory'])) {
                if (!in_array($validated['subcategory'], ['mold_tape_garments', 'liquid_cosmetics', 'battery_powerbank', 'sunglasses'])) {
                    throw ValidationException::withMessages([
                        'subcategory' => ['Invalid subcategory for category C. Must be one of: mold_tape_garments, liquid_cosmetics, battery_powerbank, sunglasses']
                    ]);
                }
            }

            // Subcategory should be null for categories A and B
            if (in_array($category, ['A', 'B'])) {
                $validated['subcategory'] = null;
            }

            $shippingRate->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Shipping rate updated successfully',
                'data' => $shippingRate->fresh()
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping rate',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a shipping rate (Admin only)
     */
    public function destroy(ShippingRate $shippingRate): JsonResponse
    {
        try {
            $shippingRate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shipping rate deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shipping rate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle shipping rate active status (Admin only)
     */
    public function toggleActive(ShippingRate $shippingRate): JsonResponse
    {
        try {
            $shippingRate->is_active = !$shippingRate->is_active;
            $shippingRate->save();

            return response()->json([
                'success' => true,
                'message' => 'Shipping rate status updated successfully',
                'data' => $shippingRate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shipping rate status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
