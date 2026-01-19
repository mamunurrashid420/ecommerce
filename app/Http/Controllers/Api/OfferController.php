<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OfferController extends Controller
{
    /**
     * Get all offers for public/customer view
     * Returns featured and regular offers separately
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Offer::active()->available()->ordered();

            // Filter by featured if requested
            if ($request->has('featured') && $request->boolean('featured')) {
                $query->featured();
            }

            $perPage = $request->get('per_page', 20);
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
     * Get offers formatted for customer display
     * Returns featured_items and items arrays as requested
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function customerOffers(Request $request): JsonResponse
    {
        try {
            // Get featured offers
            $featuredOffers = Offer::active()
                ->available()
                ->featured()
                ->ordered()
                ->get()
                ->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'title' => $offer->title,
                        'image' => $offer->image_url,
                        'url' => $offer->url,
                        'description' => $offer->description,
                        'time_remaining' => $offer->time_remaining,
                        'is_available' => $offer->is_available,
                    ];
                });

            // Get regular offers (non-featured)
            $regularOffers = Offer::active()
                ->available()
                ->where('is_featured', false)
                ->ordered()
                ->get()
                ->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'title' => $offer->title,
                        'image' => $offer->image_url,
                        'url' => $offer->url,
                        'description' => $offer->description,
                        'time_remaining' => $offer->time_remaining,
                        'is_available' => $offer->is_available,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'featured_items' => $featuredOffers,
                    'items' => $regularOffers,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer offers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single offer
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $offer = Offer::active()->available()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'image' => $offer->image_url,
                    'url' => $offer->url,
                    'description' => $offer->description,
                    'is_featured' => $offer->is_featured,
                    'available_start_time' => $offer->available_start_time,
                    'available_end_time' => $offer->available_end_time,
                    'time_remaining' => $offer->time_remaining,
                    'is_available' => $offer->is_available,
                    'created_at' => $offer->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found or not available',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}