<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class CustomerAuthController extends Controller
{
    /**
     * Register customer with email and password
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'customer',
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
     * Login customer with mobile number - sends OTP via SMS
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|max:20',
        ]);

        // Find or create customer by mobile number
        $customer = Customer::where('phone', $request->mobile)->first();

        if (!$customer) {
            // Create new customer with mobile number
            // Provide default values for required fields (name, email, password)
            $customer = Customer::create([
                'phone' => $request->mobile,
                'name' => 'Customer', // Default name, can be updated later
                'email' => 'customer_' . $request->mobile . '@temp.com', // Temporary email
                'password' => Hash::make(Str::random(32)), // Random password (not used for OTP auth)
                'role' => 'customer',
            ]);
        }

        // Check if customer is banned
        if ($customer->isBanned()) {
            throw ValidationException::withMessages([
                'mobile' => ['Your account has been banned.'],
            ]);
        }

        // Check if customer is suspended
        if ($customer->isSuspended()) {
            throw ValidationException::withMessages([
                'mobile' => ['Your account has been suspended.'],
            ]);
        }

        // Generate OTP (default: 1234 since no SMS gateway is configured)
        $defaultOtp = '1234';
        $otpExpiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

        // Update customer's OTP
        $customer->update([
            'otp' => $defaultOtp,
            'otp_expires_at' => $otpExpiresAt,
        ]);

        // In production, send OTP via SMS gateway here
        // For now, we return the OTP in the response (remove this in production)

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to your mobile number',
            'data' => [
                'expires_at' => $otpExpiresAt->toDateTimeString(),
            ],

        ], 200);
    }

    /**
     * Verify OTP and return authentication token
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|max:20',
            'otp' => 'required|string',
        ]);

        $customer = Customer::where('phone', $request->mobile)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'mobile' => ['Customer not found with this mobile number.'],
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

        // Check if customer is banned
        if ($customer->isBanned()) {
            throw ValidationException::withMessages([
                'mobile' => ['Your account has been banned.'],
            ]);
        }

        // Check if customer is suspended
        if ($customer->isSuspended()) {
            throw ValidationException::withMessages([
                'mobile' => ['Your account has been suspended.'],
            ]);
        }

        // Clear OTP after successful verification
        $customer->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        // Create authentication token
        $token = $customer->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => $customer,
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Send password reset OTP to email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            // Return success even if customer doesn't exist (security best practice)
            return response()->json([
                'message' => 'If the email exists, a password reset OTP has been sent.',
            ], 200);
        }

        $defaultOtp = '123456';
        $otpExpiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

        // Update customer's OTP
        $customer->update([
            'otp' => $defaultOtp,
            'otp_expires_at' => $otpExpiresAt,
        ]);

        // In production, you would send OTP via email here
        // For now, we just return success message

        return response()->json([
            'message' => 'If the email exists, a password reset OTP has been sent.',
            'otp' => $defaultOtp, // Remove this in production
        ], 200);
    }

    /**
     * Reset password using OTP
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'email' => ['Customer not found.'],
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

        // Update password and clear OTP
        $customer->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password reset successful. You can now login with your new password.',
        ], 200);
    }

    /**
     * Get authenticated customer profile
     */
    public function profile(Request $request)
    {
        $customer = $request->user();

        if (!$customer || !($customer instanceof Customer)) {
            return response()->json([
                'message' => 'Unauthenticated. Customer authentication required.',
            ], 401);
        }

        return response()->json([
            'data' => $customer
        ]);
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        // return $request;
        // Get authenticated customer (middleware ensures it's a Customer instance)
        $customer = $request->user();

        if (!$customer || !($customer instanceof Customer)) {
            return response()->json([
                'message' => 'Unauthenticated. Customer authentication required.',
            ], 401);
        }

        // Check for file upload - handle method spoofing (PUT via POST with _method)
        // When using _method: PUT, the actual HTTP method is POST, so files should be accessible
        $profilePictureFile = null;
        $hasProfilePictureFile = false;
        
        // Method 1: Check Symfony's file bag directly (works with method spoofing)
        $files = $request->files->all();
        if (isset($files['profile_picture']) && $files['profile_picture']) {
            $profilePictureFile = $files['profile_picture'];
            $hasProfilePictureFile = true;
        }
        
        // Method 2: Check Laravel's hasFile() method
        if (!$hasProfilePictureFile && $request->hasFile('profile_picture')) {
            $profilePictureFile = $request->file('profile_picture');
            $hasProfilePictureFile = true;
        }
        
        // Method 3: Check allFiles() array
        if (!$hasProfilePictureFile) {
            $allFiles = $request->allFiles();
            if (!empty($allFiles) && isset($allFiles['profile_picture'])) {
                $profilePictureFile = $allFiles['profile_picture'];
                $hasProfilePictureFile = true;
            }
        }
        
        // Method 4: Check file() method directly
        if (!$hasProfilePictureFile && $request->file('profile_picture')) {
            $profilePictureFile = $request->file('profile_picture');
            $hasProfilePictureFile = true;
        }
        
        // Method 5: Last resort - check $_FILES directly and recreate request file
        if (!$hasProfilePictureFile && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            try {
                // Create UploadedFile from $_FILES
                $file = \Illuminate\Http\UploadedFile::createFromBase(
                    new \Symfony\Component\HttpFoundation\File\UploadedFile(
                        $_FILES['profile_picture']['tmp_name'],
                        $_FILES['profile_picture']['name'],
                        $_FILES['profile_picture']['type'],
                        $_FILES['profile_picture']['error'],
                        true // test mode
                    )
                );
                if ($file && $file->isValid()) {
                    $profilePictureFile = $file;
                    $hasProfilePictureFile = true;
                }
            } catch (\Exception $e) {
                // Silently fail - file might not be accessible this way
            }
        }

        // Validate request data
        $validationRules = [
            'name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|nullable|email|max:255|unique:customers,email,' . $customer->id,
            'address' => 'sometimes|nullable|string',
        ];

        // Add file validation if file is present
        if ($hasProfilePictureFile && $profilePictureFile) {
            $validationRules['profile_picture'] = 'image|mimes:jpeg,png,jpg,gif,webp|max:2048';
        }

        $request->validate($validationRules);

        // Get update data - use only() which works for both JSON and form data
        // This will only include fields that are present in the request
        $updateData = $request->only(['name', 'email', 'address']);
        
        // Remove null values (fields not provided or explicitly set to null)
        // Keep empty strings as they are valid values
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        // Handle profile picture upload
        if ($hasProfilePictureFile && $profilePictureFile) {
            try {
                // Validate the file is valid
                if (!$profilePictureFile->isValid()) {
                    throw new \Exception('Invalid file upload: ' . $profilePictureFile->getErrorMessage());
                }

                // Delete old profile picture if exists
                if ($customer->profile_picture) {
                    $oldPath = $customer->profile_picture;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // Store new profile picture with a unique name
                $filename = time() . '_' . uniqid() . '.' . $profilePictureFile->getClientOriginalExtension();
                $path = Storage::disk('public')->putFileAs('profile_pictures', $profilePictureFile, $filename);
                $updateData['profile_picture'] = $path;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Failed to upload profile picture',
                    'error' => $e->getMessage(),
                    'debug' => [
                        'has_file' => $hasProfilePictureFile,
                        'file_exists' => $profilePictureFile ? true : false,
                        'file_valid' => $profilePictureFile && $profilePictureFile->isValid(),
                        'all_files_keys' => array_keys($request->allFiles()),
                        'symfony_files_keys' => array_keys($request->files->all()),
                    ]
                ], 422);
            }
        }

        // Update customer with the data
        if (!empty($updateData)) {
            $customer->update($updateData);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * Update customer profile picture
     */
    public function updateProfilePicture(Request $request)
    {
        $customer = $request->user();

        if (!$customer || !($customer instanceof Customer)) {
            return response()->json([
                'message' => 'Unauthenticated. Customer authentication required.',
            ], 401);
        }

        // Validate profile picture
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $profilePictureFile = $request->file('profile_picture');

            // Validate the file is valid
            if (!$profilePictureFile->isValid()) {
                throw new \Exception('Invalid file upload: ' . $profilePictureFile->getErrorMessage());
            }

            // Delete old profile picture if exists
            if ($customer->profile_picture) {
                $oldPath = $customer->profile_picture;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store new profile picture with a unique name
            $filename = time() . '_' . uniqid() . '.' . $profilePictureFile->getClientOriginalExtension();
            $path = Storage::disk('public')->putFileAs('profile_pictures', $profilePictureFile, $filename);

            // Update customer profile picture
            $customer->update(['profile_picture' => $path]);

            return response()->json([
                'message' => 'Profile picture updated successfully',
                'data' => $customer->fresh(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload profile picture',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Logout customer
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}
