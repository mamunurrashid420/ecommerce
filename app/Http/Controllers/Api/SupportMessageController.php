<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupportTicketService;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Exception;

class SupportMessageController extends Controller
{
    protected $supportTicketService;

    public function __construct(SupportTicketService $supportTicketService)
    {
        $this->supportTicketService = $supportTicketService;
    }

    /**
     * Get messages for a ticket
     * 
     * @param Request $request
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function index(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            // Verify access
            if ($this->isAdmin()) {
                // Admin can access any ticket
                $ticket->markAsReadByAdmin();
            } else {
                // Customer can only access their own tickets
                $customer = $this->getAuthenticatedCustomer();
                if ($ticket->customer_id !== $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this ticket'
                    ], 403);
                }
                $ticket->markAsReadByCustomer();
            }

            $messages = $ticket->messages()
                ->with(['customer', 'admin'])
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $messages,
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message (Customer or Admin)
     * 
     * @param Request $request
     * @param SupportTicket $ticket
     * @return JsonResponse
     */
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|min:1|max:5000',
                'attachments' => 'nullable|array',
                'attachments.*' => 'string|max:255',
            ]);

            DB::beginTransaction();

            $user = auth()->user();
            $senderType = null;
            $customerId = null;
            $adminId = null;

            if ($this->isAdmin()) {
                // Admin sending message
                $senderType = 'admin';
                $adminId = $user->id;
                
                // Update ticket status if it's closed/resolved
                if (in_array($ticket->status, ['resolved', 'closed'])) {
                    $ticket->update(['status' => 'in_progress']);
                }
                
                // Mark as unread by customer
                $ticket->update([
                    'is_customer_read' => false,
                    'is_admin_read' => true,
                ]);
            } else {
                // Customer sending message
                $customer = $this->getAuthenticatedCustomer();
                
                // Verify ticket ownership
                if ($ticket->customer_id !== $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this ticket'
                    ], 403);
                }
                
                $senderType = 'customer';
                $customerId = $customer->id;
                
                // Mark as unread by admin
                $ticket->update([
                    'is_customer_read' => true,
                    'is_admin_read' => false,
                ]);
            }

            // Check if ticket is closed
            if ($ticket->status === 'closed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot send message to a closed ticket'
                ], 400);
            }

            // Create message
            $message = SupportMessage::create([
                'ticket_id' => $ticket->id,
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'message' => $request->message,
                'sender_type' => $senderType,
                'attachments' => $request->attachments ?? null,
                'is_read' => false,
            ]);

            // Update ticket
            $ticket->incrementMessageCount();
            $ticket->updateLastReplied($adminId);

            // If customer sent message, reopen ticket if it was resolved
            if ($senderType === 'customer' && $ticket->status === 'resolved') {
                $ticket->reopen();
            }

            DB::commit();

            // Load relationships
            $message->load(['customer', 'admin']);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => $message
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mark message as read
     * 
     * @param SupportMessage $message
     * @return JsonResponse
     */
    public function markAsRead(SupportMessage $message): JsonResponse
    {
        try {
            // Verify access to ticket
            $ticket = $message->ticket;
            
            if ($this->isAdmin()) {
                // Admin can mark any message as read
            } else {
                // Customer can only mark messages in their own tickets
                $customer = $this->getAuthenticatedCustomer();
                if ($ticket->customer_id !== $customer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access'
                    ], 403);
                }
            }

            $message->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Message marked as read'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

