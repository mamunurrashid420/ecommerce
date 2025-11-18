<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Contact;
use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Get contact information (Public)
     */
    public function getContactInfo(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'address' => $settings->address,
                    'email' => $settings->email,
                    'support_email' => $settings->support_email,
                    'contact_number' => $settings->contact_number,
                    'business_name' => $settings->business_name,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contact information',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit contact form (Public)
     */
    public function submitContactForm(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'subject' => 'nullable|string|max:255',
                'message' => 'required|string|min:10|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create contact record
            $contact = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'new',
            ]);

            // Get site settings to get the email address
            $settings = SiteSetting::getInstance();
            $recipientEmail = $settings->email ?? $settings->support_email;

            // Send email if recipient email is configured
            if ($recipientEmail) {
                try {
                    Mail::to($recipientEmail)->send(new ContactFormMail($contact));
                } catch (\Exception $mailException) {
                    // Log the error but don't fail the request
                    \Log::error('Failed to send contact form email: ' . $mailException->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us! We will get back to you soon.',
                'data' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'submitted_at' => $contact->created_at,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit contact form',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all contacts (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Contact::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by name, email, or subject
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('subject', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $contacts = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $contacts->items(),
                'pagination' => [
                    'current_page' => $contacts->currentPage(),
                    'per_page' => $contacts->perPage(),
                    'total' => $contacts->total(),
                    'last_page' => $contacts->lastPage(),
                    'from' => $contacts->firstItem(),
                    'to' => $contacts->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contacts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single contact (Admin only)
     */
    public function show(Contact $contact): JsonResponse
    {
        try {
            // Mark as read if not already read
            if ($contact->status === 'new') {
                $contact->markAsRead();
            }

            return response()->json([
                'success' => true,
                'data' => $contact
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update contact status (Admin only)
     */
    public function updateStatus(Request $request, Contact $contact): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:new,read,replied,archived',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = $request->status;

            switch ($status) {
                case 'read':
                    $contact->markAsRead();
                    break;
                case 'replied':
                    $contact->markAsReplied();
                    break;
                case 'archived':
                    $contact->archive();
                    break;
                case 'new':
                    $contact->update([
                        'status' => 'new',
                        'read_at' => null,
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Contact status updated successfully',
                'data' => $contact->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a contact (Admin only)
     */
    public function destroy(Contact $contact): JsonResponse
    {
        try {
            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get contact statistics (Admin only)
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total' => Contact::count(),
                'new' => Contact::where('status', 'new')->count(),
                'read' => Contact::where('status', 'read')->count(),
                'replied' => Contact::where('status', 'replied')->count(),
                'archived' => Contact::where('status', 'archived')->count(),
                'today' => Contact::whereDate('created_at', today())->count(),
                'this_week' => Contact::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'this_month' => Contact::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve contact statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
