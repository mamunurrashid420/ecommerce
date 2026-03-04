<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    public function __construct(
        protected int $orderId,
        protected bool $deleteOldInvoice = true
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        $order = Order::with(['customer', 'orderItems', 'coupon', 'paymentMethod'])->find($this->orderId);

        if (!$order) {
            Log::warning('GenerateInvoiceJob: order not found', ['order_id' => $this->orderId]);
            return;
        }

        try {
            // Delete old invoice file if requested and exists
            if ($this->deleteOldInvoice && !empty($order->invoice_path)) {
                if (Storage::disk('public')->exists($order->invoice_path)) {
                    Storage::disk('public')->delete($order->invoice_path);
                }
            }

            $invoicePath = $invoiceService->generateInvoice($order);

            $order->invoice_path = $invoicePath;
            $order->saveQuietly();

            Log::info('GenerateInvoiceJob: invoice generated', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'path' => $invoicePath,
            ]);

        } catch (\Exception $e) {
            Log::error('GenerateInvoiceJob: failed to generate invoice', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            throw $e; // allow retry
        }
    }
}

