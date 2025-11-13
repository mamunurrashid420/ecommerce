<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CustomerAuthController extends Controller
{
    /**
     * Send OTP to phone number
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $defaultOtp = '654321';
        $otpExpiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

        // Check if customer exists
        $customer = Customer::where('phone', $request->phone)->first();

        if ($customer) {
            // Update existing customer's OTP
            $customer->update([
                'otp' => $defaultOtp,
                'otp_expires_at' => $otpExpiresAt,
            ]);
        } else {
            // Create new customer with OTP (temporary, will be completed in register)
            $customer = Customer::create([
                'phone' => $request->phone,
                'otp' => $defaultOtp,
                'otp_expires_at' => $otpExpiresAt,
                'role' => 'customer',
            ]);
        }

        // In production, you would send OTP via SMS here
        // For now, we just return success message

        return response()->json([
            'message' => 'OTP sent successfully',
            'otp' => $defaultOtp, // Remove this in production
        ], 200);
    }

    /**
     * Register customer with phone and OTP
     */
    public function register(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Please request OTP first',
            ], 400);
        }

        // Verify OTP
        if ($customer->otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }

        // Check if OTP is expired
        if ($customer->otp_expires_at && Carbon::now()->gt($customer->otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new one.'],
            ]);
        }

        // Clear OTP after successful verification
        $customer->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // Create a token for the customer
        $token = $customer->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'customer' => $customer,
            'token' => $token,
        ], 201);
    }

    /**
     * Login customer with phone and OTP
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'phone' => ['Customer not found. Please register first.'],
            ]);
        }

        // Verify OTP
        if ($customer->otp !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }

        // Check if OTP is expired
        if ($customer->otp_expires_at && Carbon::now()->gt($customer->otp_expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new one.'],
            ]);
        }

        // Clear OTP after successful verification
        $customer->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // Create a token for the customer
        $token = $customer->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'customer' => $customer,
            'token' => $token,
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get authenticated customer (middleware ensures it's a Customer instance)
        $customer = $request->user();
        
        return response()->json(' na afsoidm fosidmf o');

        if (!$customer || !($customer instanceof Customer)) {
            return response()->json([
                'message' => 'Unauthenticated. Customer authentication required.',
            ], 401);
        }
        
        // Get the allowed fields from the request
        // Try only() first, then fallback to json() if needed
        $updateData = $request->only(['name', 'email', 'address']);
        
        // If only() returned all nulls, try json() method (for JSON requests)
        if (empty(array_filter($updateData, function($v) { return $v !== null; })) && $request->isJson()) {
            $jsonData = $request->json()->all();
            $updateData = array_intersect_key($jsonData, array_flip(['name', 'email', 'address']));
        }
        
        // Remove only null values (fields not in request)
        // Keep all non-null values including empty strings
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });
        
        // Validate email uniqueness if email is being updated and different from current
        if (isset($updateData['email']) && $updateData['email'] !== $customer->email) {
            $request->validate([
                'email' => 'unique:customers,email',
            ]);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($customer->profile_picture) {
                Storage::disk('public')->delete($customer->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $updateData['profile_picture'] = $path;
        }

        // Update customer with the data
        if (!empty($updateData)) {
            $customer->update($updateData);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
