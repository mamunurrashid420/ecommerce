<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
            padding: 30px 35px;
            background: #fff;
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

        /* ── HEADER ─────────────────────────────── */
        .header-table td {
            vertical-align: top;
        }

        .invoice-title {
            font-size: 30px;
            font-weight: bold;
            color: #111;
            text-align: right;
            margin-bottom: 12px;
        }

        .invoice-meta {
            float: right;
            width: 260px;
        }

        .invoice-meta table td {
            padding: 3px 0;
            font-size: 11px;
        }

        .invoice-meta .meta-label {
            color: #777;
            width: 100px;
        }

        .invoice-meta .meta-value {
            color: #333;
            font-weight: bold;
            text-align: right;
        }

        /* ── ADDRESS SECTION ─────────────────────── */
        .address-section {
            margin: 24px 0 20px;
        }

        .address-section table td {
            vertical-align: top;
            width: 50%;
        }

        .addr-tag {
            font-size: 11px;
            color: #777;
            margin-bottom: 4px;
        }

        .addr-company {
            font-size: 18px;
            font-weight: bold;
            color: #111;
            margin-bottom: 6px;
        }

        .addr-line {
            font-size: 11px;
            color: #555;
            line-height: 1.55;
        }

        /* ── ITEMS TABLE ─────────────────────────── */
        .items-table {
            margin-bottom: 0;
            width: 100%;
        }

        .items-table thead tr th {
            background-color: #25547B;
            color: #fff;
            padding: 9px 10px;
            font-size: 10px;
            text-transform: uppercase;
            font-weight: normal;
            letter-spacing: 0.3px;
        }

        .items-table tbody tr td {
            padding: 11px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .item-order-no {
            font-size: 9px;
            color: #888;
            margin-bottom: 3px;
        }

        .item-name {
            font-size: 11px;
            color: #222;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        .item-meta {
            font-size: 9.5px;
            color: #777;
        }

        /* ── TOTAL PAYABLE BAR ──────────────────── */
        .total-payable-row {
            background: #f5f5f5;
        }

        .total-payable-row td {
            padding: 10px 10px;
            font-weight: bold;
            font-size: 12px;
        }

        /* ── FOOTER SECTION ─────────────────────── */
        .footer-wrap {
            margin-top: 24px;
            width: 100%;
        }

        .footer-wrap table td {
            vertical-align: top;
        }

        .payment-col {
            width: 50%;
            padding-right: 20px;
        }

        .payment-col h4 {
            font-size: 14px;
            font-weight: bold;
            color: #222;
            margin-bottom: 6px;
        }

        .pm-block {
            margin-bottom: 10px;
        }

        .pm-name {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
        }

        .pm-details-heading {
            background-color: #00e5e5;
            font-size: 10px;
            font-weight: bold;
            text-decoration: underline;
            color: #000;
            padding: 3px 6px;
            margin-bottom: 4px;
        }

        .pm-info-row {
            font-size: 10px;
            color: #222;
            line-height: 1.7;
        }

        .note-section h4 {
            font-size: 12px;
            font-weight: bold;
            color: #222;
            margin-bottom: 5px;
        }

        .note-section p {
            font-size: 10px;
            color: #777;
            line-height: 1.6;
        }

        /* ── SUMMARY TABLE ──────────────────────── */
        .summary-col {
            width: 50%;
        }

        .summary-table td {
            padding: 5px 0;
            font-size: 11px;
        }

        .summary-table .s-label {
            text-align: right;
            padding-right: 16px;
            color: #555;
        }

        .summary-table .s-value {
            text-align: right;
            font-weight: bold;
            color: #222;
            width: 120px;
        }

        .summary-table .s-total .s-label {
            font-size: 13px;
            font-weight: bold;
            color: #111;
        }

        .summary-table .s-total .s-value {
            font-size: 13px;
            color: #111;
        }

        .balance-row td {
            background-color: #f5f5f5;
            padding: 8px 10px;
        }

        .balance-row .s-label {
            font-weight: bold;
            font-size: 12px;
            color: #111;
        }

        .balance-row .s-value {
            font-weight: bold;
            font-size: 12px;
            color: #111;
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e5e5;
            margin: 20px 0;
        }
    </style>
</head>

<body>

    {{-- ══ HEADER ══ --}}
    <table class="header-table">
        <tr>
            <td style="width:50%; vertical-align:top;">
                @if(isset($siteSettings->header_logo) && $siteSettings->header_logo)
                    <img src="{{ $siteSettings->header_logo_url }}" style="max-height:90px; max-width:120px;" alt="Logo">
                @else
                    {{-- Placeholder circular logo --}}
                    <div
                        style="width:90px; height:90px; border-radius:50%; border:4px solid #f37021; position:relative; display:inline-block;">
                        <div
                            style="font-size:38px; font-weight:bold; color:#25547B; position:absolute; top:8px; left:22px;">
                            e</div>
                        <div
                            style="font-size:11px; font-weight:bold; color:#25547B; position:absolute; bottom:12px; left:10px;">
                            E3 SHOP</div>
                    </div>
                @endif
            </td>
            <td style="width:50%; vertical-align:top;">
                <div class="invoice-title">Purchase Invoice</div>
                <div class="invoice-meta">
                    <table>
                        <tr>
                            <td class="meta-label">Invoice no:</td>
                            <td class="meta-value">{{ $order->order_number }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Invoice date:</td>
                            <td class="meta-value">{{ $order->created_at->format('D d M, Y h:i a') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Due:</td>
                            <td class="meta-value">{{ $order->created_at->addDays(7)->format('F j, Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div style="clear:both;"></div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ══ ADDRESS SECTION ══ --}}
    <div class="address-section">
        <table>
            <tr>
                {{-- FROM --}}
                <td>
                    <div class="addr-tag">From</div>
                    <div class="addr-company">
                        {{ strtoupper($siteSettings->business_name ?? $siteSettings->title ?? 'E3 SHOPBD') }}
                    </div>
                    <div class="addr-line">
                        @if(isset($siteSettings->contact_person) && $siteSettings->contact_person)
                            {{ $siteSettings->contact_person }}<br>
                        @endif
                        @if(isset($siteSettings->email) && $siteSettings->email)
                            {{ $siteSettings->email }}<br>
                        @endif
                        @if(isset($siteSettings->contact_number) && $siteSettings->contact_number)
                            {{ $siteSettings->contact_number }}<br>
                        @endif
                        @if(isset($siteSettings->website) && $siteSettings->website)
                            {{ $siteSettings->website }}<br>
                        @endif
                        {{ $siteSettings->address ?? 'First Str. 28-32, Chicago, USA' }}
                    </div>
                </td>

                {{-- BILL TO --}}
                <td class="text-right">
                    <div class="addr-tag">Bill to</div>
                    <div class="addr-company">{{ $customer->name }}</div>
                    <div class="addr-line">
                        Customer ID: {{ str_pad($customer->id, 10, '#', STR_PAD_LEFT) }}<br>
                        @if($customer->email)
                            {{ $customer->email }}<br>
                        @endif
                        {{ $customer->phone }}<br>
                        @php
                            $shippingAddress = is_string($order->shipping_address)
                                ? json_decode($order->shipping_address, true)
                                : $order->shipping_address;
                            $addressParts = [];
                            if (is_array($shippingAddress)) {
                                if (!empty($shippingAddress['address']))
                                    $addressParts[] = $shippingAddress['address'];
                                if (!empty($shippingAddress['upazila']))
                                    $addressParts[] = $shippingAddress['upazila'];
                                if (!empty($shippingAddress['district']))
                                    $addressParts[] = $shippingAddress['district'];
                            }
                            $addressText = !empty($addressParts) ? implode(', ', $addressParts) : ($order->shipping_address ?? '');
                        @endphp
                        {{ $addressText }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ══ ITEMS TABLE ══ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width:55%;">DESCRIPTION</th>
                <th class="text-center" style="width:12%;">Quantity</th>
                <th class="text-right" style="width:16%;">Unit Price BDT</th>
                <th class="text-right" style="width:17%;">Total Price BDT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>
                        <div class="item-order-no">Order no: {{ $order->order_number }}</div>
                        <div class="item-name">{{ $item->product_name ?? 'Product Description' }}</div>
                        <div class="item-meta">
                            @if(isset($item->variant->sku))
                                Color: {{ $item->variant->color->name ?? '--' }}
                                &nbsp;&nbsp;
                                Size: {{ $item->variant->size->name ?? '--' }}
                                &nbsp;&nbsp;
                                Model: {{ $item->product->model ?? '--' }}
                            @else
                                Color: --&nbsp;&nbsp;Size: --&nbsp;&nbsp;Model: {{ $item->product->model ?? '--' }}
                            @endif
                        </div>
                    </td>
                    <td class="text-center">{{ str_pad($item->quantity, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }} <span
                            style="font-size:9px;color:#999;">BDT</span></td>
                    <td class="text-right">{{ number_format($item->total, 2) }} <span
                            style="font-size:9px;color:#999;">BDT</span></td>
                </tr>
            @endforeach

            {{-- Total Payable row inside the table --}}
            <tr class="total-payable-row">
                <td colspan="3" class="text-left" style="color:#555; font-weight:bold; font-size:11.5px;">Total Payable
                </td>
                <td class="text-right" style="font-weight:bold; font-size:12px;">
                    {{ number_format($order->total_amount, 2) }} <span style="font-size:9px;color:#999;">BDT</span>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ══ FOOTER ══ --}}
    <table class="footer-wrap" style="margin-top:24px;">
        <tr>
            {{-- Payment & Note column --}}
            <td class="payment-col">
                <h4>Payment instruction</h4>
                @if(isset($paymentMethods) && $paymentMethods->count() > 0)
                    @foreach($paymentMethods as $pm)
                        <div class="pm-block">
                            <div class="pm-name">{{ $pm->name }}</div>
                            <div class="pm-details-heading">The Beneficiary's details are as below.</div>
                            @if(!empty($pm->information))
                                @foreach($pm->information as $info)
                                    <div class="pm-info-row">{{ $info['label_name'] }}: {{ $info['label_value'] }}</div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                @endif
                <div class="note-section">
                    <h4>Note:</h4>
                    <p>{{ $siteSettings->invoice_note ?? 'Thank you for your business. Please retain this invoice for your records. For any inquiries, contact our support team.' }}
                    </p>
                </div>
            </td>

            {{-- Summary column --}}
            <td class="summary-col">
                <table class="summary-table">
                    <tr>
                        <td class="s-label">Subtotal:</td>
                        <td class="s-value">BDT {{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr>
                            <td class="s-label">
                                Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}:
                            </td>
                            <td class="s-value">BDT {{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="s-label">Discount{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}:
                            </td>
                            <td class="s-value">BDT 0.00</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="s-label">Shipping Cost</td>
                        <td class="s-value">BDT {{ number_format($order->shipping_cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="s-label">Sales Tax</td>
                        <td class="s-value">BDT {{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                    <tr class="s-total">
                        <td class="s-label"><strong>Total:</strong></td>
                        <td class="s-value"><strong>BDT {{ number_format($order->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="s-label" style="font-weight:normal;">Amount paid</td>
                        <td class="s-value" style="font-weight:normal;">BDT {{ number_format($order->paid_amount, 2) }}
                        </td>
                    </tr>
                    <tr class="balance-row">
                        <td class="s-label"><strong>Balance Due:</strong></td>
                        <td class="s-value"><strong>BDT {{ number_format($order->due_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>