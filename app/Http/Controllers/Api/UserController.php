<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of users with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if ($request->has('role') && !empty($request->role)) {
            $query->where('role', $request->role);
        }

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $users = $query->paginate($perPage);

        // Add computed attributes
        $users->getCollection()->transform(function ($user) {
            $user->orders_count = 0; // Will be updated when Order model exists
            $user->total_spent = 0; // Will be updated when Order model exists
            return $user;
        });

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ]
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        // Add computed attributes
        $user->orders_count = 0; // Will be updated when Order model exists
        $user->total_spent = 0; // Will be updated when Order model exists

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:customer,admin',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:active,inactive,banned',
        ]);

        $user->update($request->only([
            'name', 'email', 'role', 'phone', 'address', 'status'
        ]));

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Ban a user.
     */
    public function ban(User $user): JsonResponse
    {
        $user->update(['status' => 'banned']);

        return response()->json([
            'message' => 'User banned successfully',
            'data' => $user
        ]);
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): JsonResponse
    {
        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'User unbanned successfully',
            'data' => $user
        ]);
    }

    /**
     * Get user statistics.
     */
    public function stats(): JsonResponse
    {
        $totalUsers = User::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $activeUsers = User::where('status', 'active')->count();
        $newThisMonth = User::whereMonth('created_at', now()->month)
                           ->whereYear('created_at', now()->year)
                           ->count();

        return response()->json([
            'data' => [
                'total_users' => $totalUsers,
                'total_customers' => $totalCustomers,
                'active_users' => $activeUsers,
                'new_this_month' => $newThisMonth,
                'total_revenue' => 0, // Will be updated when Order model exists
            ]
        ]);
    }
}