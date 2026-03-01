<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/api/admin/orders', 'GET');
$controller = app(\App\Http\Controllers\OrderController::class);

$response = $controller->index($request);
$data = json_decode($response->getContent(), true);

foreach ($data['data'] as $order) {
    echo "ID: " . $order['id'] . "\n";
    echo "Status: " . $order['status'] . "\n";
    echo "Payment Status: " . $order['payment_status'] . "\n";
    echo "---\n";
}
