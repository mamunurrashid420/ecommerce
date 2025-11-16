<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Store a newly created admin user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,banned',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => 'admin', // Only create admin users
            'phone' => $request->phone,
            'address' => $request->address,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'message' => 'Admin user created successfully',
            'data' => $user
        ], 201);
    }

    /**
     * Display a listing of admin users with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        // Only return admin users
        $query = User::where('role', 'admin');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
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
        // Only allow viewing admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        // Only allow updating admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:active,inactive,banned',
        ]);

        $user->update($request->only([
            'name', 'email', 'phone', 'address', 'status'
        ]));

        return response()->json([
            'message' => 'Admin user updated successfully',
            'data' => $user
        ]);
    }

    /**
     * Remove the specified admin user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Only allow deleting admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'Admin user deleted successfully'
        ]);
    }

    /**
     * Ban an admin user.
     */
    public function ban(User $user): JsonResponse
    {
        // Only allow banning admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->update(['status' => 'banned']);

        return response()->json([
            'message' => 'Admin user banned successfully',
            'data' => $user
        ]);
    }

    /**
     * Unban an admin user.
     */
    public function unban(User $user): JsonResponse
    {
        // Only allow unbanning admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $user->update(['status' => 'active']);

        return response()->json([
            'message' => 'Admin user unbanned successfully',
            'data' => $user
        ]);
    }

    /**
     * Get admin user statistics.
     */
    public function stats(): JsonResponse
    {
        $totalAdmins = User::where('role', 'admin')->count();
        $activeAdmins = User::where('role', 'admin')->where('status', 'active')->count();
        $newAdminsThisMonth = User::where('role', 'admin')
                                   ->whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->count();
        $bannedAdmins = User::where('role', 'admin')->where('status', 'banned')->count();

        return response()->json([
            'data' => [
                'total_admins' => $totalAdmins,
                'active_admins' => $activeAdmins,
                'banned_admins' => $bannedAdmins,
                'new_this_month' => $newAdminsThisMonth,
            ]
        ]);
    }

    /**
     * Update admin user password.
     */
    public function updatePassword(Request $request, User $user): JsonResponse
    {
        // Only allow updating admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }

    /**
     * Assign role to admin user.
     */
    public function assignRole(Request $request, User $user): JsonResponse
    {
        // Only allow updating admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'role_id' => $request->role_id
        ]);

        $user->load('roleModel');

        return response()->json([
            'message' => 'Role assigned successfully',
            'data' => $user
        ]);
    }

    /**
     * Change admin user role.
     */
    public function changeRole(Request $request, User $user): JsonResponse
    {
        // Only allow updating admin users
        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'role_id' => $request->role_id
        ]);

        $user->load('roleModel');

        return response()->json([
            'message' => 'Role changed successfully',
            'data' => $user
        ]);
    }
}