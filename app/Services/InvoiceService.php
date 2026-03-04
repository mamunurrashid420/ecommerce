<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    /**
     * Build the PDF object for an order (shared logic).
     *
     * @param Order $order
     * @return \Barryvdh\DomPDF\PDF
     */
    private function buildPdf(Order $order)
    {
        $order->load(['customer', 'orderItems', 'coupon', 'paymentMethod']);

        $pdf = Pdf::loadView('invoices.order', [
            'order'          => $order,
            'customer'       => $order->customer,
            'items'          => $order->orderItems,
            'siteSettings'   => \App\Models\SiteSetting::getInstance(),
            'paymentMethods' => \App\Models\PaymentMethod::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->get(),
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption('enable-local-file-access', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    /**
     * Generate PDF invoice for an order and save it to disk.
     *
     * @param Order $order
     * @return string Path to the generated invoice file
     * @throws Exception
     */
    public function generateInvoice(Order $order): string
    {
        try {
            $pdf = $this->buildPdf($order);

            $filename    = 'invoice_' . $order->order_number . '_' . time() . '.pdf';
            $invoicePath = 'invoices/' . $filename;

            Storage::disk('public')->put($invoicePath, $pdf->output());

            return $invoicePath;

        } catch (Exception $e) {
            Log::error('Invoice generation failed', [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'error'        => $e->getMessage()
            ]);
            throw new Exception('Failed to generate invoice: ' . $e->getMessage());
        }
    }

    /**
     * Stream the invoice PDF directly to the browser (inline view).
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function streamInvoice(Order $order): \Illuminate\Http\Response
    {
        try {
            $pdf = $this->buildPdf($order);
            return $pdf->stream('invoice_' . $order->order_number . '.pdf');
        } catch (Exception $e) {
            Log::error('Invoice stream failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage()
            ]);
            throw new Exception('Failed to stream invoice: ' . $e->getMessage());
        }
    }

    /**
     * Download the invoice PDF.
     *
     * @param Order $order
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function downloadInvoicePdf(Order $order): \Illuminate\Http\Response
    {
        try {
            $pdf = $this->buildPdf($order);
            return $pdf->download('invoice_' . $order->order_number . '.pdf');
        } catch (Exception $e) {
            Log::error('Invoice download failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage()
            ]);
            throw new Exception('Failed to download invoice: ' . $e->getMessage());
        }
    }

    /**
     * Get invoice URL
     * 
     * @param string $invoicePath
     * @return string|null
     */
    public function getInvoiceUrl(string $invoicePath): ?string
    {
        if (empty($invoicePath)) {
            return null;
        }

        // If URL already starts with http/https, return as is
        if (str_starts_with($invoicePath, 'http://') || str_starts_with($invoicePath, 'https://')) {
            return $invoicePath;
        }

        // Return storage URL
        return Storage::disk('public')->url($invoicePath);
    }
}

