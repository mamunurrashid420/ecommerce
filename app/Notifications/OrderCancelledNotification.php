<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
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
        $cancelledBy = $this->order->cancelled_by === 'admin' ? 'an administrator' : 'you';
        
        $mail = (new MailMessage)
            ->subject('Order Cancelled')
            ->line("Your order has been cancelled by {$cancelledBy}.")
            ->line("Order Number: {$this->order->order_number}")
            ->line("Total Amount: $" . number_format($this->order->total_amount, 2))
            ->line("Cancelled At: " . $this->order->cancelled_at->format('Y-m-d H:i:s'));

        if ($this->order->cancellation_reason) {
            $mail->line("Reason: {$this->order->cancellation_reason}");
        }

        return $mail->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('Any reserved stock has been released back to inventory.');
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
            'cancellation_reason' => $this->order->cancellation_reason,
            'message' => "Order #{$this->order->order_number} has been cancelled",
            'type' => 'order_cancelled',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
