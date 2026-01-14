<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    /**
     * Display a listing of the districts.
     */
    public function index(Request $request)
    {
        try {
            $query = District::query();

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Filter by division
            if ($request->filled('division')) {
                $query->byDivision($request->division);
            }

            // Include upazillas
            if ($request->boolean('with_upazillas')) {
                if ($request->boolean('active_only')) {
                    $query->with(['activeUpazillas']);
                } else {
                    $query->with(['upazillas' => function ($q) {
                        $q->ordered();
                    }]);
                }
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('name_bn', 'like', "%{$search}%")
                      ->orWhere('division', 'like', "%{$search}%")
                      ->orWhere('division_bn', 'like', "%{$search}%");
                });
            }

            // Order by sort_order and name
            $query->ordered();

            // Pagination
            if ($request->has('per_page')) {
                $perPage = min($request->integer('per_page', 15), 100);
                $districts = $query->paginate($perPage);
            } else {
                $districts = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $districts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch districts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created district.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:districts,name',
                'name_bn' => 'nullable|string|max:255',
                'division' => 'nullable|string|max:255',
                'division_bn' => 'nullable|string|max:255',
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

            $district = District::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'District created successfully',
                'data' => $district,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create district',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified district.
     */
    public function show(Request $request, $id)
    {
        try {
            $query = District::query();

            // Include upazillas if requested
            if ($request->boolean('with_upazillas')) {
                if ($request->boolean('active_only')) {
                    $query->with(['activeUpazillas']);
                } else {
                    $query->with(['upazillas' => function ($q) {
                        $q->ordered();
                    }]);
                }
            }

            $district = $query->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $district,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'District not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch district',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified district.
     */
    public function update(Request $request, $id)
    {
        try {
            $district = District::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:districts,name,' . $id,
                'name_bn' => 'nullable|string|max:255',
                'division' => 'nullable|string|max:255',
                'division_bn' => 'nullable|string|max:255',
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

            $district->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'District updated successfully',
                'data' => $district,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'District not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update district',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified district.
     */
    public function destroy($id)
    {
        try {
            $district = District::findOrFail($id);
            $district->delete();

            return response()->json([
                'success' => true,
                'message' => 'District deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'District not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete district',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of a district.
     */
    public function toggleActive($id)
    {
        try {
            $district = District::findOrFail($id);
            $district->is_active = !$district->is_active;
            $district->save();

            return response()->json([
                'success' => true,
                'message' => 'District status updated successfully',
                'data' => $district,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'District not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update district status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of divisions.
     */
    public function divisions()
    {
        try {
            $divisions = District::query()
                ->select('division', 'division_bn')
                ->distinct()
                ->whereNotNull('division')
                ->orderBy('division')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $divisions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch divisions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

