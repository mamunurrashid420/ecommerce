<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Get all customers with search and filters
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::withCount('orders');

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('is_banned') && $request->is_banned !== null) {
                $query->where('is_banned', filter_var($request->is_banned, FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->has('is_suspended') && $request->is_suspended !== null) {
                $query->where('is_suspended', filter_var($request->is_suspended, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by role
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 15);
            $customers = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $customers->items(),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new customer
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers',
                'password' => 'required|string|min:8',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'role' => 'required|in:admin,customer',
            ]);

            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer
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
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get a single customer with order count
     * 
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Customer $customer)
    {
        try {
            $customer->loadCount('orders');
            
            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer
     * 
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'role' => 'required|in:admin,customer',
            ]);

            $customer->update($request->only(['name', 'email', 'phone', 'address', 'role']));
            
            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $customer
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
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete customer
     * 
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Customer $customer)
    {
        try {
            $customer->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ban a customer
     * 
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function ban(Request $request, Customer $customer)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            if ($customer->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is already banned'
                ], 400);
            }

            $customer->update([
                'is_banned' => true,
                'banned_at' => now(),
                'ban_reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer banned successfully',
                'data' => $customer->fresh()
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
                'message' => 'Failed to ban customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Unban a customer
     * 
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function unban(Customer $customer)
    {
        try {
            if (!$customer->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is not banned'
                ], 400);
            }

            $customer->update([
                'is_banned' => false,
                'banned_at' => null,
                'ban_reason' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer unbanned successfully',
                'data' => $customer->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unban customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Suspend a customer
     * 
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function suspend(Request $request, Customer $customer)
    {
        try {
            $request->validate([
                'reason' => 'nullable|string|max:1000',
            ]);

            if ($customer->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is already suspended'
                ], 400);
            }

            $customer->update([
                'is_suspended' => true,
                'suspended_at' => now(),
                'suspend_reason' => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer suspended successfully',
                'data' => $customer->fresh()
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
                'message' => 'Failed to suspend customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Unsuspend a customer
     * 
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function unsuspend(Customer $customer)
    {
        try {
            if (!$customer->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer is not suspended'
                ], 400);
            }

            $customer->update([
                'is_suspended' => false,
                'suspended_at' => null,
                'suspend_reason' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer unsuspended successfully',
                'data' => $customer->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsuspend customer',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get customer order history
     * 
     * @param Request $request
     * @param Customer $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderHistory(Request $request, Customer $customer)
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->orderService->getCustomerOrders($customer->id, $filters, $perPage);

            return response()->json([
                'success' => true,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
                'orders' => $result['data'],
                'pagination' => $result['pagination'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer order history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1',
            ]);

            $query = $request->query;
            $customers = Customer::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%")
                ->withCount('orders')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $customers,
                'count' => $customers->count(),
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
                'message' => 'Failed to search customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
