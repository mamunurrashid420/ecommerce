<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-info h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }
        .company-info p {
            margin: 3px 0;
            color: #666;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h2 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        .invoice-info p {
            margin: 3px 0;
            color: #666;
        }
        .billing-section {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .billing-section td {
            padding: 0;
        }
        .billing-box {
            width: 100%;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            vertical-align: top;
        }
        .billing-box h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .billing-box p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #333;
            color: #fff;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            margin-top: 20px;
            margin-left: auto;
            width: 300px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        .summary-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            padding: 12px 0;
            margin-top: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 11px;
        }
        .status-partially-paid {
            background: #ffc107;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <div class="company-info">
                <h1>{{ $siteSettings->business_name ?? $siteSettings->title ?? 'e3shopbd' }}</h1>
                <p>{{ $siteSettings->address ?? '' }}</p>
                @if($siteSettings->secondary_address)
                    <p>{{ $siteSettings->secondary_address }}</p>
                @endif
                @if($siteSettings->contact_number)
                    <p>Phone: {{ $siteSettings->contact_number }}</p>
                @endif
                @if($siteSettings->email)
                    <p>Email: {{ $siteSettings->email }}</p>
                @endif
            </div>
            <div class="invoice-info">
                <h2>INVOICE</h2>
                <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
                <p><strong>Date:</strong> {{ $order->created_at->format('d M Y') }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge status-partially-paid">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                </p>
            </div>
        </div>

        <table class="billing-section">
            <tr>
                <td class="billing-box" style="width: 50%;">
                    <h3>Bill To:</h3>
                    <p><strong>{{ $customer->name }}</strong></p>
                    <p>{{ $customer->phone }}</p>
                    @if($customer->email)
                        <p>{{ $customer->email }}</p>
                    @endif
                    @if($customer->address)
                        <p>{{ $customer->address }}</p>
                    @endif
                </td>
                <td class="billing-box" style="width: 50%;">
                    <h3>Shipping Address:</h3>
                    @php
                        $shippingAddress = is_string($order->shipping_address) 
                            ? json_decode($order->shipping_address, true) 
                            : $order->shipping_address;
                    @endphp
                    @if(is_array($shippingAddress))
                        <p><strong>{{ $shippingAddress['name'] ?? $customer->name }}</strong></p>
                        <p>{{ $shippingAddress['phone'] ?? $customer->phone }}</p>
                        <p>{{ $shippingAddress['address'] ?? '' }}</p>
                        @if(isset($shippingAddress['district']))
                            <p>{{ $shippingAddress['district'] }}</p>
                        @endif
                        @if(isset($shippingAddress['upazila']))
                            <p>{{ $shippingAddress['upazila'] }}</p>
                        @endif
                    @else
                        <p>{{ $order->shipping_address }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name ?? 'Product' }}</strong>
                        @if($item->product_code)
                            <br><small>Code: {{ $item->product_code }}</small>
                        @endif
                        @if($item->product_sku)
                            <br><small>SKU: {{ $item->product_sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span>{{ number_format($order->subtotal, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="summary-row">
                <span class="summary-label">Discount 
                    @if($order->coupon_code)
                        ({{ $order->coupon_code }})
                    @endif:
                </span>
                <span>-{{ number_format($order->discount_amount, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @endif
            @if($order->shipping_cost > 0)
            <div class="summary-row">
                <span class="summary-label">Shipping Cost:</span>
                <span>{{ number_format($order->shipping_cost, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @endif
            @if($order->tax_amount > 0)
            <div class="summary-row">
                <span class="summary-label">Tax 
                    @if($order->tax_rate)
                        ({{ $order->tax_rate }}%)
                    @endif:
                </span>
                <span>{{ number_format($order->tax_amount, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span>Total Amount:</span>
                <span>{{ number_format($order->total_amount, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @if($order->paid_amount > 0)
            <div class="summary-row" style="margin-top: 10px;">
                <span class="summary-label">Paid Amount:</span>
                <span style="color: #28a745; font-weight: bold;">{{ number_format($order->paid_amount, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @endif
            @if($order->due_amount > 0)
            <div class="summary-row">
                <span class="summary-label">Due Amount:</span>
                <span style="color: #dc3545; font-weight: bold;">{{ number_format($order->due_amount, 2) }} {{ $siteSettings->currency_symbol ?? '৳' }}</span>
            </div>
            @endif
        </div>

        @if($order->notes)
        <div style="margin-top: 20px; padding: 10px; background: #f9f9f9; border-radius: 5px;">
            <strong>Notes:</strong> {{ $order->notes }}
        </div>
        @endif

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>{{ $siteSettings->business_name ?? $siteSettings->title ?? 'e3shopbd' }}</p>
            @if($siteSettings->business_registration_number)
                <p>Registration: {{ $siteSettings->business_registration_number }}</p>
            @endif
            @if($siteSettings->tax_number)
                <p>Tax ID: {{ $siteSettings->tax_number }}</p>
            @endif
        </div>
    </div>
</body>
</html>

