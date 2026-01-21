<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index(Request $request)
    {
        try {
            $query = PaymentMethod::query();

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('name_bn', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Order by sort_order and name
            $query->ordered();

            // Pagination
            if ($request->has('per_page')) {
                $perPage = min($request->integer('per_page', 15), 100);
                $paymentMethods = $query->paginate($perPage);
            } else {
                $paymentMethods = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $paymentMethods,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:payment_methods,name',
                'name_bn' => 'nullable|string|max:255',
                'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'information' => 'nullable|array',
                'information.*.label_name' => 'required|string|max:255',
                'information.*.label_value' => 'required|string|max:500',
                'description' => 'nullable|string',
                'description_bn' => 'nullable|string',
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

            $data = $validator->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoPath = $logo->store('payment-methods', 'public');
                $data['logo'] = $logoPath;
            }

            // Handle information as JSON
            if ($request->has('information')) {
                $data['information'] = $request->information;
            }

            $paymentMethod = PaymentMethod::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully',
                'data' => $paymentMethod,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment method.
     */
    public function show($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $paymentMethod,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, $id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:payment_methods,name,' . $id,
                'name_bn' => 'nullable|string|max:255',
                'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
                'information' => 'nullable|array',
                'information.*.label_name' => 'required|string|max:255',
                'information.*.label_value' => 'required|string|max:500',
                'description' => 'nullable|string',
                'description_bn' => 'nullable|string',
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

            $data = $validator->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($paymentMethod->logo && !preg_match('/^https?:\/\//', $paymentMethod->logo)) {
                    Storage::disk('public')->delete($paymentMethod->logo);
                }
                
                $logo = $request->file('logo');
                $logoPath = $logo->store('payment-methods', 'public');
                $data['logo'] = $logoPath;
            }

            // Handle information as JSON
            if ($request->has('information')) {
                $data['information'] = $request->information;
            }

            $paymentMethod->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => $paymentMethod->fresh(),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of a payment method.
     */
    public function toggleActive($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            $paymentMethod->is_active = !$paymentMethod->is_active;
            $paymentMethod->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment method status updated successfully',
                'data' => $paymentMethod,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete payment method logo.
     */
    public function deleteLogo($id)
    {
        try {
            $paymentMethod = PaymentMethod::findOrFail($id);
            
            if ($paymentMethod->logo && !preg_match('/^https?:\/\//', $paymentMethod->logo)) {
                Storage::disk('public')->delete($paymentMethod->logo);
            }
            
            $paymentMethod->logo = null;
            $paymentMethod->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment method logo deleted successfully',
                'data' => $paymentMethod,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update sort order of payment methods.
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'orders' => 'required|array',
                'orders.*.id' => 'required|exists:payment_methods,id',
                'orders.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            foreach ($request->orders as $order) {
                PaymentMethod::where('id', $order['id'])
                    ->update(['sort_order' => $order['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sort order updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

