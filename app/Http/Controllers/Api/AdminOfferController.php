<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminOfferController extends Controller
{
    /**
     * Get all offers for admin management
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Offer::query();

            // Filter by status
            if ($request->has('status')) {
                if ($request->get('status') === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->get('status') === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filter by featured
            if ($request->has('featured')) {
                $query->where('is_featured', $request->boolean('featured'));
            }

            // Search by title
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('title', 'like', "%{$search}%");
            }

            // Sort
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortOrder = $request->get('sort_order', 'desc');
            
            if ($sortBy === 'sort_order') {
                $query->orderBy('sort_order', $sortOrder)->orderBy('created_at', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            $perPage = $request->get('per_page', 15);
            $offers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $offers->items(),
                'pagination' => [
                    'current_page' => $offers->currentPage(),
                    'last_page' => $offers->lastPage(),
                    'per_page' => $offers->perPage(),
                    'total' => $offers->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new offer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'url' => 'nullable|url|max:500',
                'description' => 'nullable|string|max:1000',
                'is_featured' => 'boolean',
                'available_start_time' => 'nullable|date',
                'available_end_time' => 'nullable|date|after:available_start_time',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('offers', 'public');
                $validated['image'] = $imagePath;
            }

            $offer = Offer::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Offer created successfully',
                'data' => $offer
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
                'message' => 'Failed to create offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single offer for admin
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $offer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update an offer
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);

            // Debug: Log the request data to see what's being received
            \Log::info('Offer Update Request Data:', [
                'all_data' => $request->all(),
                'has_file' => $request->hasFile('image'),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method()
            ]);

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'url' => 'nullable|url|max:500',
                'description' => 'nullable|string|max:1000',
                'is_featured' => 'boolean',
                'available_start_time' => 'nullable|date',
                'available_end_time' => 'nullable|date|after:available_start_time',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($offer->image && Storage::disk('public')->exists($offer->image)) {
                    Storage::disk('public')->delete($offer->image);
                }
                
                $imagePath = $request->file('image')->store('offers', 'public');
                $validated['image'] = $imagePath;
            }

            $offer->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Offer updated successfully',
                'data' => $offer->fresh()
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
                'message' => 'Failed to update offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an offer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);

            // Delete associated image
            if ($offer->image && Storage::disk('public')->exists($offer->image)) {
                Storage::disk('public')->delete($offer->image);
            }

            $offer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Offer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle offer active status
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function toggleActive($id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);
            $offer->is_active = !$offer->is_active;
            $offer->save();

            return response()->json([
                'success' => true,
                'message' => 'Offer status updated successfully',
                'data' => [
                    'id' => $offer->id,
                    'is_active' => $offer->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update offer status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle offer featured status
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function toggleFeatured($id): JsonResponse
    {
        try {
            $offer = Offer::findOrFail($id);
            $offer->is_featured = !$offer->is_featured;
            $offer->save();

            return response()->json([
                'success' => true,
                'message' => 'Offer featured status updated successfully',
                'data' => [
                    'id' => $offer->id,
                    'is_featured' => $offer->is_featured
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update offer featured status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sort order for multiple offers
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSortOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'offers' => 'required|array',
                'offers.*.id' => 'required|exists:offers,id',
                'offers.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($validated['offers'] as $offerData) {
                Offer::where('id', $offerData['id'])
                    ->update(['sort_order' => $offerData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully'
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
                'message' => 'Failed to update sort order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get offers statistics for admin dashboard
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_offers' => Offer::count(),
                'active_offers' => Offer::where('is_active', true)->count(),
                'featured_offers' => Offer::where('is_featured', true)->count(),
                'available_offers' => Offer::active()->available()->count(),
                'expired_offers' => Offer::where('available_end_time', '<', now())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve offer statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}