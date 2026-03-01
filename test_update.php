<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = \App\Models\Order::latest('id')->first();
$order->status = 'pending';
$order->payment_status = 'pending';
$order->total_amount = 1000;
$order->subtotal = 500;
$order->save();

echo "Before bulk status: " . $order->status . "\n";
echo "Before bulk payment: " . $order->payment_status . "\n";

app(\App\Services\OrderService::class)->bulkUpdatePartialPayment([$order->id]);

$order->refresh();
echo "After bulk status: " . $order->status . "\n";
echo "After bulk payment: " . $order->payment_status . "\n";
