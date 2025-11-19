<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancellationRejectedNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $adminNote;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, ?string $adminNote = null)
    {
        $this->order = $order;
        $this->adminNote = $adminNote;
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
        $mail = (new MailMessage)
            ->subject('Order Cancellation Request Rejected')
            ->line('Your order cancellation request has been rejected.')
            ->line("Order Number: {$this->order->order_number}")
            ->line("Total Amount: $" . number_format($this->order->total_amount, 2))
            ->line("Current Status: " . ucfirst($this->order->status));

        if ($this->adminNote) {
            $mail->line("Admin Note: {$this->adminNote}");
        }

        return $mail->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('Your order will continue to be processed as normal.');
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
            'admin_note' => $this->adminNote,
            'message' => "Your cancellation request for order #{$this->order->order_number} has been rejected",
            'type' => 'order_cancellation_rejected',
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
