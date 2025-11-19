<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupportTicketService;
use App\Models\SupportTicket;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;

class SupportTicketController extends Controller
{
    protected $supportTicketService;

    public function __construct(SupportTicketService $supportTicketService)
    {
        $this->supportTicketService = $supportTicketService;
    }

    /**
     * Create a new support ticket (Customer only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string|min:10|max:5000',
                'priority' => 'nullable|in:low,medium,high,urgent',
                'category' => 'nullable|in:technical,billing,order,product,account,other',
            ]);

            $customer = $this->getAuthenticatedCustomer();

            $data = [
                'subject' => $request->subject,
                'description' => $request->description,
                'priority' => $request->priority ?? 'medium',
                'category' => $request->category ?? 'other',
            ];

            $result = $this->supportTicketService->createTicket($data, $customer->id);

            return response()->json($result, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get customer's tickets
     * If admin accesses this route, they get all tickets (same as index)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function customerTickets(Request $request): JsonResponse
    {
        try {
            // If admin, return all tickets (same as index method)
            if ($this->isAdmin()) {
                return $this->index($request);
            }
            
            // For customers, return only their tickets
            $customer = $this->getAuthenticatedCustomer();

            $filters = [
                'status' => $request->get('status'),
                'priority' => $request->get('priority'),
                'category' => $request->get('category'),
                'search' => $request->get('search'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->supportTicketService->getCustomerTickets($customer->id, $filters, $perPage);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all tickets (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'priority' => $request->get('priority'),
                'category' => $request->get('category'),
                'customer_id' => $request->get('customer_id'),
                'assigned_to' => $request->get('assigned_to'),
                'search' => $request->get('search'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
                'sort_by' => $request->get('sort_by', 'created_at'),
                'sort_order' => $request->get('sort_order', 'desc'),
            ];

            $perPage = $request->get('per_page', 15);
            $result = $this->supportTicketService->getAllTickets($filters, $perPage);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single ticket
     * 
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function show(SupportTicket $ticket): JsonResponse
    {
        try {
            // If admin, allow access to any ticket
            if ($this->isAdmin()) {
                $result = $this->supportTicketService->getTicket($ticket->id);
            } else {
                // For customers, verify ownership
                $customer = $this->getAuthenticatedCustomer();
                $result = $this->supportTicketService->getTicket($ticket->id, $customer->id);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Unauthorized access to this ticket' ? 403 : 404);
        }
    }

    /**
     * Update ticket status (Admin only)
     * 
     * @param Request $request
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:open,in_progress,resolved,closed',
            ]);

            $result = $this->supportTicketService->updateTicketStatus($ticket->id, $request->status);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Assign ticket to admin (Admin only)
     * 
     * @param Request $request
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            $request->validate([
                'admin_id' => 'required|exists:users,id',
            ]);

            $result = $this->supportTicketService->assignTicket($ticket->id, $request->admin_id);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update ticket priority (Admin only)
     * 
     * @param Request $request
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function updatePriority(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            $request->validate([
                'priority' => 'required|in:low,medium,high,urgent',
            ]);

            $result = $this->supportTicketService->updateTicketPriority($ticket->id, $request->priority);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get ticket statistics (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $filters = [
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $result = $this->supportTicketService->getTicketStats($filters);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ticket statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ticket (Admin only)
     * 
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function destroy(SupportTicket $ticket): JsonResponse
    {
        try {
            $result = $this->supportTicketService->deleteTicket($ticket->id);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get navbar count (Customer or Admin)
     * Returns count of tickets for navbar badge
     * 
     * @return JsonResponse
     */
    public function navbarCount(): JsonResponse
    {
        try {
            if ($this->isAdmin()) {
                $result = $this->supportTicketService->getNavbarCountForAdmin();
            } else {
                $customer = $this->getAuthenticatedCustomer();
                $result = $this->supportTicketService->getNavbarCountForCustomer($customer->id);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve ticket count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest tickets for navbar dropdown (Customer or Admin)
     * Returns latest tickets with limited information for dropdown display
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function navbarLatest(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);

            if ($this->isAdmin()) {
                $result = $this->supportTicketService->getNavbarLatestForAdmin($limit);
            } else {
                $customer = $this->getAuthenticatedCustomer();
                $result = $this->supportTicketService->getNavbarLatestForCustomer($customer->id, $limit);
            }

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated customer
     * 
     * @return Customer
     * @throws Exception
     */
    protected function getAuthenticatedCustomer(): Customer
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new Exception('Unauthenticated');
        }

        if ($user instanceof Customer) {
            return $user;
        }

        throw new Exception('Customer authentication required');
    }

    /**
     * Check if authenticated user is admin
     * 
     * @return bool
     */
    protected function isAdmin(): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        if ($user instanceof \App\Models\User) {
            return $user->isAdmin();
        }

        return false;
    }
}

