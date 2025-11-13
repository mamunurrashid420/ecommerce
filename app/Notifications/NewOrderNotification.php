<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
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
            ->subject('New Order Received')
            ->line('A new order has been placed.')
            ->line("Order Number: {$this->order->order_number}")
            ->line("Customer: {$this->order->customer->name}")
            ->line("Total Amount: $" . number_format($this->order->total_amount, 2))
            ->action('View Order', url('/admin/orders/' . $this->order->id))
            ->line('Thank you for using our application!');
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
            'customer_id' => $this->order->customer_id,
            'customer_name' => $this->order->customer->name,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
            'message' => "New order #{$this->order->order_number} from {$this->order->customer->name}",
            'created_at' => $this->order->created_at->toDateTimeString(),
        ];
    }
}
