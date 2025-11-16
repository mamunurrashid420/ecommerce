<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Permission::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Group filter
        if ($request->has('group') && !empty($request->group)) {
            $query->where('group', $request->group);
        }

        // Active filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'group');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 50);
        $permissions = $query->paginate($perPage);

        return response()->json([
            'data' => $permissions->items(),
            'meta' => [
                'current_page' => $permissions->currentPage(),
                'last_page' => $permissions->lastPage(),
                'per_page' => $permissions->perPage(),
                'total' => $permissions->total(),
                'from' => $permissions->firstItem(),
                'to' => $permissions->lastItem(),
            ]
        ]);
    }

    /**
     * Get all permissions grouped by group.
     */
    public function grouped(): JsonResponse
    {
        $permissions = Permission::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        return response()->json([
            'data' => $permissions
        ]);
    }

    /**
     * Get all permission groups.
     */
    public function groups(): JsonResponse
    {
        $groups = Permission::whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values();

        return response()->json([
            'data' => $groups
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permissions,slug',
            'description' => 'nullable|string',
            'group' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $slug = $request->slug ?? Str::slug($request->name);

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'group' => $request->group,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'message' => 'Permission created successfully',
            'data' => $permission
        ], 201);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        $permission->load('roles', 'users');

        return response()->json([
            'data' => $permission
        ]);
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:permissions,slug,' . $permission->id,
            'description' => 'sometimes|nullable|string',
            'group' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = $request->only(['name', 'description', 'group', 'is_active']);

        if ($request->has('slug')) {
            $updateData['slug'] = $request->slug;
        }

        $permission->update($updateData);

        return response()->json([
            'message' => 'Permission updated successfully',
            'data' => $permission
        ]);
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        // Check if permission is assigned to any roles
        $rolesCount = $permission->roles()->count();
        if ($rolesCount > 0) {
            return response()->json([
                'message' => "Cannot delete permission. It is assigned to {$rolesCount} role(s).",
                'roles_count' => $rolesCount
            ], 422);
        }

        // Check if permission is assigned to any users
        $usersCount = $permission->users()->count();
        if ($usersCount > 0) {
            return response()->json([
                'message' => "Cannot delete permission. It is assigned to {$usersCount} user(s).",
                'users_count' => $usersCount
            ], 422);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Permission deleted successfully'
        ]);
    }

    /**
     * Toggle permission active status.
     */
    public function toggleActive(Permission $permission): JsonResponse
    {
        $permission->update(['is_active' => !$permission->is_active]);

        return response()->json([
            'message' => 'Permission status updated successfully',
            'data' => $permission
        ]);
    }
}
