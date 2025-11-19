<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'customer_id',
        'assigned_to',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'resolved_at',
        'closed_at',
        'last_replied_at',
        'last_replied_by',
        'message_count',
        'is_customer_read',
        'is_admin_read',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_replied_at' => 'datetime',
        'is_customer_read' => 'boolean',
        'is_admin_read' => 'boolean',
        'message_count' => 'integer',
    ];

    /**
     * Get the customer that owns the ticket
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the admin assigned to the ticket
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the admin who last replied
     */
    public function lastRepliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_replied_by');
    }

    /**
     * Get all messages for this ticket
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(SupportMessage::class, 'ticket_id')->latestOfMany();
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber(): string
    {
        do {
            $ticketNumber = 'TKT-' . strtoupper(uniqid());
        } while (self::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    /**
     * Mark ticket as resolved
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark ticket as closed
     */
    public function markAsClosed(): void
    {
        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);
    }

    /**
     * Reopen ticket
     */
    public function reopen(): void
    {
        $this->update([
            'status' => 'open',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    /**
     * Mark as read by customer
     */
    public function markAsReadByCustomer(): void
    {
        $this->update(['is_customer_read' => true]);
    }

    /**
     * Mark as read by admin
     */
    public function markAsReadByAdmin(): void
    {
        $this->update(['is_admin_read' => true]);
    }

    /**
     * Increment message count
     */
    public function incrementMessageCount(): void
    {
        $this->increment('message_count');
    }

    /**
     * Update last replied information
     */
    public function updateLastReplied($adminId = null): void
    {
        $this->update([
            'last_replied_at' => now(),
            'last_replied_by' => $adminId,
        ]);
    }
}
