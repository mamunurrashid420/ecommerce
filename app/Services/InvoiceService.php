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
     * Generate PDF invoice for an order
     * 
     * @param Order $order
     * @return string Path to the generated invoice file
     * @throws Exception
     */
    public function generateInvoice(Order $order): string
    {
        try {
            // Load order relationships
            $order->load(['customer', 'orderItems', 'coupon', 'paymentMethod']);
            
            // Generate invoice filename
            $filename = 'invoice_' . $order->order_number . '_' . time() . '.pdf';
            $invoicePath = 'invoices/' . $filename;
            
            // Generate PDF using dompdf
            $pdf = Pdf::loadView('invoices.order', [
                'order' => $order,
                'customer' => $order->customer,
                'items' => $order->orderItems,
                'siteSettings' => \App\Models\SiteSetting::getInstance(),
            ]);
            
            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Set options for better rendering
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            
            // Save PDF to storage
            Storage::disk('public')->put($invoicePath, $pdf->output());
            
            return $invoicePath;
            
        } catch (Exception $e) {
            Log::error('Invoice generation failed', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to generate invoice: ' . $e->getMessage());
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

