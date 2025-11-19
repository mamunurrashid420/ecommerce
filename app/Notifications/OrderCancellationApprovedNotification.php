<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancellationApprovedNotification extends Notification
{
    use Queueable;

    protected $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Cancellation Approved')
            ->line('Your order cancellation request has been approved.')
            ->line("Order Number: {$this->order->order_number}")
            ->line("Total Amount: $" . number_format($this->order->total_amount, 2))
            ->line("Cancelled At: " . $this->order->cancelled_at->format('Y-m-d H:i:s'))
            ->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('Your order has been cancelled and any reserved stock has been released.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
            'cancelled_at' => $this->order->cancelled_at?->toDateTimeString(),
            'cancelled_by' => $this->order->cancelled_by,
            'message' => "Your cancellation request for order #{$this->order->order_number} has been approved",
            'type' => 'order_cancellation_approved',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
