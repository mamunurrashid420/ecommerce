<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .header {
            margin-bottom: 30px;
        }

        .header table {
            width: 100%;
        }

        .header td {
            vertical-align: top;
        }

        .logo {
            width: 100px;
            height: 100px;
            display: inline-block;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: right;
        }

        .invoice-details {
            text-align: right;
            color: #555;
        }

        .invoice-details td {
            padding: 3px 0;
        }

        .address-section {
            margin-bottom: 30px;
        }

        .address-section td {
            vertical-align: top;
            width: 50%;
        }

        .address-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .address-content {
            color: #555;
            font-size: 11px;
            line-height: 1.5;
        }

        .address-content .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .items-table {
            margin-bottom: 20px;
            width: 100%;
        }

        .items-table th {
            background-color: #25547B;
            color: #fff;
            padding: 10px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: normal;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .item-desc {
            color: #333;
            margin-bottom: 3px;
        }

        .item-meta {
            font-size: 9px;
            color: #777;
        }

        .total-payable {
            background-color: #f5f5f5;
            padding: 10px;
            margin-bottom: 30px;
            width: 100%;
            box-sizing: border-box;
        }

        .total-payable table {
            width: 100%;
        }

        .total-payable td {
            font-weight: bold;
            font-size: 12px;
        }

        .footer-section {
            width: 100%;
            margin-top: 10px;
        }

        .footer-section td {
            vertical-align: top;
        }

        .payment-info {
            width: 50%;
            padding-right: 20px;
        }

        .payment-info h4 {
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .payment-info .we-accept {
            font-size: 11px;
            color: #555;
            margin-bottom: 8px;
        }

        .payment-logos {
            margin-bottom: 20px;
        }

        .payment-logo {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 5px;
            font-weight: bold;
        }

        .payment-logo.bkash {
            color: #e2136e;
        }

        .payment-logo.nagad {
            color: #f37021;
        }

        .note-sec h4 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .note-sec p {
            font-size: 10px;
            color: #777;
            line-height: 1.5;
        }

        .summary-table {
            width: 100%;
        }

        .summary-table td {
            padding: 5px 0;
            font-size: 11px;
        }

        .summary-table .label {
            text-align: right;
            padding-right: 20px;
            font-weight: bold;
            color: #555;
        }

        .summary-table .value {
            text-align: left;
            width: 100px;
            color: #333;
            font-weight: bold;
        }

        .summary-table .total-row td {
            font-size: 13px;
            color: #333;
        }

        .summary-table .total-row .label {
            color: #000;
        }

        .balance-row td {
            background-color: #f5f5f5;
            padding: 8px 0;
            margin-top: 5px;
        }

        .balance-row .label,
        .balance-row .value {
            padding-left: 10px;
            padding-right: 10px;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="header">
        <table>
            <tr>
                <td style="width: 50%;">
                    @if(isset($siteSettings->logo) && $siteSettings->logo)
                        <img src="{{ url($siteSettings->logo) }}" class="logo" alt="Logo"
                            style="max-height:100px; max-width:100px;">
                    @else
                        <!-- Placeholder Logo -->
                        <div
                            style="width: 90px; height: 90px; border-radius: 50%; border: 4px solid #f37021; position: relative;">
                            <div
                                style="font-size: 40px; font-weight: bold; color: #25547B; position: absolute; top: 8px; left: 24px;">
                                e</div>
                            <div
                                style="font-size: 12px; font-weight: bold; color: #25547B; position: absolute; bottom: 12px; left: 14px;">
                                E3 SHOP</div>
                        </div>
                    @endif
                </td>
                <td style="width: 50%;" class="text-right">
                    <div class="invoice-title">Purchase Invoice</div>
                    <table class="invoice-details" style="float: right; width: 250px;">
                        <tr>
                            <td class="text-left" style="color: #777;">Invoice no:</td>
                            <td class="text-right" style="color: #333; font-weight: bold;">{{ $order->order_number }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-left" style="color: #777;">Invoice date:</td>
                            <td class="text-right" style="color: #333;">
                                {{ $order->created_at->format('D d M, Y h:i a') }}</td>
                        </tr>
                        <tr>
                            <td class="text-left" style="color: #777;">Due:</td>
                            <td class="text-right" style="color: #333;">
                                {{ $order->created_at->addDays(7)->format('F j, Y') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="address-section">
        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="address-title text-left">From</div>
                    <div class="address-content text-left">
                        <div class="company-name">
                            {{ strtoupper($siteSettings->business_name ?? $siteSettings->title ?? 'E3 SHOPBD') }}</div>
                        @if(isset($siteSettings->contact_person))
                            <div>{{ $siteSettings->contact_person }}</div>
                        @endif
                        @if(isset($siteSettings->email))
                            <div>{{ $siteSettings->email }}</div>
                        @endif
                        @if(isset($siteSettings->contact_number))
                            <div>{{ $siteSettings->contact_number }}</div>
                        @endif
                        @if(isset($siteSettings->website))
                            <div>{{ $siteSettings->website }}</div>
                        @endif
                        <div style="max-width: 200px;">{{ $siteSettings->address ?? 'First Str. 28-32, Chicago, USA' }}
                        </div>
                    </div>
                </td>
                <td style="width: 50%;" class="text-right">
                    <div class="address-title">Bill to</div>
                    <div class="address-content">
                        <div class="company-name">{{ $customer->name }}</div>
                        <div>Customer ID: {{ str_pad($customer->id, 10, '#', STR_PAD_LEFT) }}</div>
                        @if($customer->email)
                            <div>{{ $customer->email }}</div>
                        @endif
                        <div>{{ $customer->phone }}</div>
                        @php
                            $shippingAddress = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
                            $addressText = is_array($shippingAddress) ? ($shippingAddress['address'] ?? '') . ', ' . ($shippingAddress['district'] ?? '') . ', ' . ($shippingAddress['upazila'] ?? '') : $order->shipping_address;
                        @endphp
                        <div style="max-width: 250px; float: right;">
                            {{ $addressText ?: '4517 washington ave. manchester, kentucky 39495' }}</div>
                        <div style="clear: both;"></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left">DESCRIPTION</th>
                <th class="text-center">Quantity</th>
                <th class="text-right">Unit Price BDT</th>
                <th class="text-right">Total Price BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td style="width: 55%;">
                        <table style="width: 100%;">
                            <tr>
                                @if(isset($item->product->image_url) && !empty($item->product->image_url))
                                    <td style="width: 60px; padding-right: 15px;"><img src="{{ $item->product->image_url }}"
                                            style="width: 60px; height: 60px; object-fit: cover;" alt=""></td>
                                @else
                                    <td style="width: 60px; padding-right: 15px;">
                                        <div
                                            style="width: 60px; height: 60px; background-color: #eee; text-align: center; line-height: 60px; font-size: 10px; color: #999;">
                                            Image</div>
                                    </td>
                                @endif
                                <td style="vertical-align: top;">
                                    <div class="item-meta">Order no: {{ $order->order_number }}</div>
                                    <div class="item-desc font-bold">{{ $item->product_name ?? 'Product Description' }}
                                    </div>
                                    <div class="item-meta">
                                        @if(isset($item->variant->sku))
                                            Color: {{ $item->variant->color->name ?? '--' }} &nbsp;&nbsp; Size:
                                            {{ $item->variant->size->name ?? '--' }} &nbsp;&nbsp; Model:
                                            {{ $item->product->model ?? '--' }}
                                        @else
                                            Color: -- &nbsp;&nbsp; Size: -- &nbsp;&nbsp; Model:
                                            {{ $item->product->model ?? '--' }}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        {{ str_pad($item->quantity, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-right" style="vertical-align: middle;">{{ number_format($item->price, 2) }} <span
                            style="font-size: 9px; color: #777;">BDT</span></td>
                    <td class="text-right" style="vertical-align: middle;">{{ number_format($item->total, 2) }} <span
                            style="font-size: 9px; color: #777;">BDT</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-payable">
        <table>
            <tr>
                <td class="text-left" style="color: #555;">Total Payable</td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }} <span
                        style="font-size: 10px; color: #777;">BDT</span></td>
            </tr>
        </table>
    </div>

    <table class="footer-section">
        <tr>
            <td class="payment-info">
                <h4>Payment instruction</h4>
                <div class="we-accept">We accept</div>
                <div class="payment-logos">
                    <span class="payment-logo bkash" style="color:#e2136e;">bKash</span>
                    <span class="payment-logo nagad" style="color:#f37021;">Nagad</span>
                    <span class="payment-logo" style="color: #333;">BANK</span>
                </div>
                <div class="note-sec">
                    <h4>Note:</h4>
                    <p>{{ $siteSettings->invoice_note ?? 'Thank you for your business. Please retain this invoice for your records. For any inquiries, contact our support team.' }}
                    </p>
                </div>
            </td>
            <td style="width: 50%;">
                <table class="summary-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">BDT {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td class="label">Discount @if($order->coupon_code) ({{ $order->coupon_code }}) @endif:</td>
                            <td class="value">BDT {{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Shipping Cost:</td>
                        <td class="value">BDT {{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Sales Tax:</td>
                        <td class="value">BDT {{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="label">Total:</td>
                        <td class="value">BDT {{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label" style="font-weight: normal;">Amount paid:</td>
                        <td class="value" style="font-weight: normal;">BDT {{ number_format($order->paid_amount, 2) }}
                        </td>
                    </tr>
                    <tr class="balance-row">
                        <td class="label">Balance Due:</td>
                        <td class="value">BDT {{ number_format($order->due_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>