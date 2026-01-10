<?php

// Test to see the actual API response structure
$baseUrl = 'https://api.e3shopbd.com/api';
$productListUrl = $baseUrl . '/product-list?search=phone&page=1&page_size=1';

echo "Testing actual API response structure...\n\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents($productListUrl, false, $context);

if ($response) {
    $data = json_decode($response, true);
    
    echo "Full API Response Structure:\n";
    echo "============================\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "Failed to get API response\n";
}