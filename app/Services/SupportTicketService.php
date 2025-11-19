<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class SupportTicketService
{
    /**
     * Create a new support ticket
     * 
     * @param array $data
     * @param int $customerId
     * @return array
     * @throws Exception
     */
    public function createTicket(array $data, int $customerId): array
    {
        DB::beginTransaction();
        
        try {
            // Validate customer exists
            $customer = Customer::findOrFail($customerId);
            
            // Check if customer is banned or suspended
            if ($customer->isBanned()) {
                throw new Exception("Your account has been banned. Reason: " . ($customer->ban_reason ?? 'No reason provided'));
            }
            
            if ($customer->isSuspended()) {
                throw new Exception("Your account has been suspended. Reason: " . ($customer->suspend_reason ?? 'No reason provided'));
            }
            
            // Generate unique ticket number
            $ticketNumber = SupportTicket::generateTicketNumber();
            
            // Create ticket
            $ticket = SupportTicket::create([
                'ticket_number' => $ticketNumber,
                'customer_id' => $customerId,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'status' => 'open',
                'priority' => $data['priority'] ?? 'medium',
                'category' => $data['category'] ?? 'other',
                'is_customer_read' => true,
                'is_admin_read' => false,
            ]);
            
            // Create initial message from customer
            $message = SupportMessage::create([
                'ticket_id' => $ticket->id,
                'customer_id' => $customerId,
                'message' => $data['description'],
                'sender_type' => 'customer',
                'is_read' => false,
            ]);
            
            // Update ticket message count
            $ticket->incrementMessageCount();
            
            DB::commit();
            
            // Load relationships
            $ticket->load(['customer', 'messages']);
            
            return [
                'success' => true,
                'message' => 'Support ticket created successfully',
                'ticket' => $ticket,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create support ticket', [
                'customer_id' => $customerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get customer tickets
     * 
     * @param int $customerId
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getCustomerTickets(int $customerId, array $filters = [], int $perPage = 15): array
    {
        $query = SupportTicket::where('customer_id', $customerId)
            ->with(['customer', 'assignedAdmin', 'latestMessage']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $tickets = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
            ],
        ];
    }

    /**
     * Get all tickets (Admin)
     * 
     * @param array $filters
     * @param int $perPage
     * @return array
     */
    public function getAllTickets(array $filters = [], int $perPage = 15): array
    {
        $query = SupportTicket::with(['customer', 'assignedAdmin', 'lastRepliedBy', 'latestMessage']);
        
        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        
        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);
        
        $tickets = $query->paginate($perPage);
        
        return [
            'success' => true,
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage(),
                'from' => $tickets->firstItem(),
                'to' => $tickets->lastItem(),
            ],
        ];
    }

    /**
     * Get a single ticket
     * 
     * @param int $ticketId
     * @param int|null $customerId
     * @return array
     * @throws Exception
     */
    public function getTicket(int $ticketId, ?int $customerId = null): array
    {
        $ticket = SupportTicket::with([
            'customer',
            'assignedAdmin',
            'lastRepliedBy',
            'messages.customer',
            'messages.admin',
        ])->findOrFail($ticketId);
        
        // If customer ID provided, verify ownership
        if ($customerId !== null && $ticket->customer_id !== $customerId) {
            throw new Exception('Unauthorized access to this ticket');
        }
        
        // Mark as read
        if ($customerId !== null) {
            $ticket->markAsReadByCustomer();
        }
        
        return [
            'success' => true,
            'ticket' => $ticket,
        ];
    }

    /**
     * Update ticket status (Admin)
     * 
     * @param int $ticketId
     * @param string $status
     * @return array
     * @throws Exception
     */
    public function updateTicketStatus(int $ticketId, string $status): array
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        if (!in_array($status, ['open', 'in_progress', 'resolved', 'closed'])) {
            throw new Exception('Invalid status');
        }
        
        $ticket->status = $status;
        
        if ($status === 'resolved') {
            $ticket->resolved_at = now();
        } elseif ($status === 'closed') {
            $ticket->closed_at = now();
        } else {
            $ticket->resolved_at = null;
            $ticket->closed_at = null;
        }
        
        $ticket->save();
        
        return [
            'success' => true,
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket->load(['customer', 'assignedAdmin']),
        ];
    }

    /**
     * Assign ticket to admin
     * 
     * @param int $ticketId
     * @param int $adminId
     * @return array
     * @throws Exception
     */
    public function assignTicket(int $ticketId, int $adminId): array
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        $admin = User::findOrFail($adminId);
        
        if (!$admin->isAdmin()) {
            throw new Exception('User is not an admin');
        }
        
        $ticket->update([
            'assigned_to' => $adminId,
            'status' => 'in_progress',
        ]);
        
        return [
            'success' => true,
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket->load(['customer', 'assignedAdmin']),
        ];
    }

    /**
     * Update ticket priority
     * 
     * @param int $ticketId
     * @param string $priority
     * @return array
     * @throws Exception
     */
    public function updateTicketPriority(int $ticketId, string $priority): array
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            throw new Exception('Invalid priority');
        }
        
        $ticket->update(['priority' => $priority]);
        
        return [
            'success' => true,
            'message' => 'Ticket priority updated successfully',
            'ticket' => $ticket->load(['customer', 'assignedAdmin']),
        ];
    }

    /**
     * Get ticket statistics
     * 
     * @param array $filters
     * @return array
     */
    public function getTicketStats(array $filters = []): array
    {
        $query = SupportTicket::query();
        
        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'low_priority' => (clone $query)->where('priority', 'low')->count(),
            'medium_priority' => (clone $query)->where('priority', 'medium')->count(),
            'high_priority' => (clone $query)->where('priority', 'high')->count(),
            'urgent_priority' => (clone $query)->where('priority', 'urgent')->count(),
            'unassigned' => (clone $query)->whereNull('assigned_to')->count(),
            'unread_by_admin' => (clone $query)->where('is_admin_read', false)->count(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
        
        return [
            'success' => true,
            'data' => $stats,
        ];
    }

    /**
     * Delete ticket
     * 
     * @param int $ticketId
     * @return array
     * @throws Exception
     */
    public function deleteTicket(int $ticketId): array
    {
        $ticket = SupportTicket::findOrFail($ticketId);
        
        // Only allow deletion of closed tickets
        if ($ticket->status !== 'closed') {
            throw new Exception('Only closed tickets can be deleted');
        }
        
        $ticket->delete();
        
        return [
            'success' => true,
            'message' => 'Ticket deleted successfully',
        ];
    }

    /**
     * Get navbar count for customer
     * Returns count of unread/unresolved tickets
     * 
     * @param int $customerId
     * @return array
     */
    public function getNavbarCountForCustomer(int $customerId): array
    {
        $total = SupportTicket::where('customer_id', $customerId)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        $unread = SupportTicket::where('customer_id', $customerId)
            ->where('is_customer_read', false)
            ->whereIn('status', ['open', 'in_progress', 'resolved'])
            ->count();
        
        return [
            'success' => true,
            'total_unresolved' => $total,
            'unread_count' => $unread,
            'count' => $unread > 0 ? $unread : $total, // Show unread if any, otherwise total unresolved
        ];
    }

    /**
     * Get navbar count for admin
     * Returns count of unread/unassigned tickets
     * 
     * @return array
     */
    public function getNavbarCountForAdmin(): array
    {
        $unread = SupportTicket::where('is_admin_read', false)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        $unassigned = SupportTicket::whereNull('assigned_to')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        $urgent = SupportTicket::where('priority', 'urgent')
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        
        return [
            'success' => true,
            'unread_count' => $unread,
            'unassigned_count' => $unassigned,
            'urgent_count' => $urgent,
            'count' => $unread > 0 ? $unread : ($urgent > 0 ? $urgent : $unassigned), // Priority: unread > urgent > unassigned
        ];
    }

    /**
     * Get latest tickets for navbar dropdown (Customer)
     * 
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getNavbarLatestForCustomer(int $customerId, int $limit = 5): array
    {
        $tickets = SupportTicket::where('customer_id', $customerId)
            ->with(['assignedAdmin', 'latestMessage'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'category' => $ticket->category,
                    'is_customer_read' => $ticket->is_customer_read,
                    'message_count' => $ticket->message_count,
                    'last_replied_at' => $ticket->last_replied_at?->toIso8601String(),
                    'updated_at' => $ticket->updated_at->toIso8601String(),
                    'assigned_admin' => $ticket->assignedAdmin ? [
                        'id' => $ticket->assignedAdmin->id,
                        'name' => $ticket->assignedAdmin->name,
                    ] : null,
                    'latest_message' => $ticket->latestMessage ? [
                        'id' => $ticket->latestMessage->id,
                        'message' => substr($ticket->latestMessage->message, 0, 100) . (strlen($ticket->latestMessage->message) > 100 ? '...' : ''),
                        'sender_type' => $ticket->latestMessage->sender_type,
                        'created_at' => $ticket->latestMessage->created_at->toIso8601String(),
                    ] : null,
                ];
            });
        
        return [
            'success' => true,
            'data' => $tickets,
            'count' => $tickets->count(),
        ];
    }

    /**
     * Get latest tickets for navbar dropdown (Admin)
     * 
     * @param int $limit
     * @return array
     */
    public function getNavbarLatestForAdmin(int $limit = 5): array
    {
        // Get latest unread tickets first, then latest overall
        $tickets = SupportTicket::with(['customer', 'assignedAdmin', 'latestMessage'])
            ->where(function ($query) {
                $query->where('is_admin_read', false)
                    ->orWhereNull('assigned_to')
                    ->orWhere('priority', 'urgent');
            })
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("CASE 
                WHEN is_admin_read = false THEN 1
                WHEN priority = 'urgent' THEN 2
                WHEN assigned_to IS NULL THEN 3
                ELSE 4
            END")
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                    'category' => $ticket->category,
                    'is_admin_read' => $ticket->is_admin_read,
                    'is_unassigned' => $ticket->assigned_to === null,
                    'message_count' => $ticket->message_count,
                    'last_replied_at' => $ticket->last_replied_at?->toIso8601String(),
                    'updated_at' => $ticket->updated_at->toIso8601String(),
                    'customer' => $ticket->customer ? [
                        'id' => $ticket->customer->id,
                        'name' => $ticket->customer->name,
                        'email' => $ticket->customer->email,
                    ] : null,
                    'assigned_admin' => $ticket->assignedAdmin ? [
                        'id' => $ticket->assignedAdmin->id,
                        'name' => $ticket->assignedAdmin->name,
                    ] : null,
                    'latest_message' => $ticket->latestMessage ? [
                        'id' => $ticket->latestMessage->id,
                        'message' => substr($ticket->latestMessage->message, 0, 100) . (strlen($ticket->latestMessage->message) > 100 ? '...' : ''),
                        'sender_type' => $ticket->latestMessage->sender_type,
                        'created_at' => $ticket->latestMessage->created_at->toIso8601String(),
                    ] : null,
                ];
            });
        
        return [
            'success' => true,
            'data' => $tickets,
            'count' => $tickets->count(),
        ];
    }
}

