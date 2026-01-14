<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Upazila;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpazilaController extends Controller
{
    /**
     * Display a listing of the upazillas.
     */
    public function index(Request $request)
    {
        try {
            $query = Upazila::query();

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Filter by district
            if ($request->filled('district_id')) {
                $query->byDistrict($request->district_id);
            }

            // Include district
            if ($request->boolean('with_district')) {
                $query->with('district');
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('name_bn', 'like', "%{$search}%");
                });
            }

            // Order by sort_order and name
            $query->ordered();

            // Pagination
            if ($request->has('per_page')) {
                $perPage = min($request->integer('per_page', 15), 100);
                $upazillas = $query->paginate($perPage);
            } else {
                $upazillas = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $upazillas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upazillas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created upazilla.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'district_id' => 'required|exists:districts,id',
                'name' => 'required|string|max:255',
                'name_bn' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check for duplicate name in the same district
            $exists = Upazila::where('district_id', $request->district_id)
                ->where('name', $request->name)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upazilla with this name already exists in the district',
                ], 422);
            }

            $upazilla = Upazila::create($validator->validated());
            $upazilla->load('district');

            return response()->json([
                'success' => true,
                'message' => 'Upazilla created successfully',
                'data' => $upazilla,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create upazilla',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified upazilla.
     */
    public function show(Request $request, $id)
    {
        try {
            $query = Upazila::query();

            // Include district if requested
            if ($request->boolean('with_district')) {
                $query->with('district');
            }

            $upazilla = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $upazilla,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upazilla not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upazilla',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified upazilla.
     */
    public function update(Request $request, $id)
    {
        try {
            $upazilla = Upazila::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'district_id' => 'sometimes|required|exists:districts,id',
                'name' => 'sometimes|required|string|max:255',
                'name_bn' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Check for duplicate name in the same district (excluding current)
            if ($request->filled('name') || $request->filled('district_id')) {
                $districtId = $request->district_id ?? $upazilla->district_id;
                $name = $request->name ?? $upazilla->name;

                $exists = Upazila::where('district_id', $districtId)
                    ->where('name', $name)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Upazilla with this name already exists in the district',
                    ], 422);
                }
            }

            $upazilla->update($validator->validated());
            $upazilla->load('district');

            return response()->json([
                'success' => true,
                'message' => 'Upazilla updated successfully',
                'data' => $upazilla,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upazilla not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update upazilla',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified upazilla.
     */
    public function destroy($id)
    {
        try {
            $upazilla = Upazila::findOrFail($id);
            $upazilla->delete();

            return response()->json([
                'success' => true,
                'message' => 'Upazilla deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upazilla not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete upazilla',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of an upazilla.
     */
    public function toggleActive($id)
    {
        try {
            $upazilla = Upazila::findOrFail($id);
            $upazilla->is_active = !$upazilla->is_active;
            $upazilla->save();

            return response()->json([
                'success' => true,
                'message' => 'Upazilla status updated successfully',
                'data' => $upazilla,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upazilla not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update upazilla status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upazillas by district ID.
     */
    public function byDistrict($districtId)
    {
        try {
            // Verify district exists
            District::findOrFail($districtId);

            $upazillas = Upazila::where('district_id', $districtId)
                ->active()
                ->ordered()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $upazillas,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'District not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upazillas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

