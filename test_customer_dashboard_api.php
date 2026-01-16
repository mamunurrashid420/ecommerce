<?php

/**
 * Test script for Customer Dashboard API endpoints
 * 
 * This script tests all the customer dashboard API endpoints
 * Make sure to update the BASE_URL and get a valid customer token
 */

// Configuration
$BASE_URL = 'http://localhost:8000/api'; // Update this to your API base URL
$CUSTOMER_TOKEN = 'YOUR_CUSTOMER_TOKEN_HERE'; // Replace with actual customer token

// Test endpoints
$endpoints = [
    'Dashboard Stats' => '/customer/dashboard/stats',
    'Recent Activity' => '/customer/dashboard/recent-activity?limit=5',
    'Profile Summary' => '/customer/dashboard/profile-summary',
    'Order Status Breakdown' => '/customer/dashboard/order-status-breakdown',
    'Spending Trend' => '/customer/dashboard/spending-trend'
];

/**
 * Make API request
 */
function makeRequest($url, $token) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

/**
 * Format JSON response for display
 */
function formatResponse($response) {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return json_encode($decoded, JSON_PRETTY_PRINT);
    }
    return $response;
}

echo "=== Customer Dashboard API Test ===\n";
echo "Base URL: $BASE_URL\n";
echo "Testing with customer token: " . substr($CUSTOMER_TOKEN, 0, 10) . "...\n\n";

// Test each endpoint
foreach ($endpoints as $name => $endpoint) {
    echo "Testing: $name\n";
    echo "Endpoint: $endpoint\n";
    echo str_repeat('-', 50) . "\n";
    
    $url = $BASE_URL . $endpoint;
    $result = makeRequest($url, $CUSTOMER_TOKEN);
    
    if ($result['error']) {
        echo "❌ cURL Error: " . $result['error'] . "\n";
    } else {
        echo "HTTP Code: " . $result['http_code'] . "\n";
        
        if ($result['http_code'] === 200) {
            echo "✅ Success!\n";
            echo "Response:\n";
            echo formatResponse($result['response']) . "\n";
        } elseif ($result['http_code'] === 401) {
            echo "❌ Unauthorized - Check your customer token\n";
        } else {
            echo "❌ Error Response:\n";
            echo formatResponse($result['response']) . "\n";
        }
    }
    
    echo "\n" . str_repeat('=', 70) . "\n\n";
}

echo "Test completed!\n";
echo "\nTo use this script:\n";
echo "1. Update the BASE_URL to match your API endpoint\n";
echo "2. Get a valid customer token by logging in via /api/customer/verify-otp\n";
echo "3. Replace YOUR_CUSTOMER_TOKEN_HERE with the actual token\n";
echo "4. Run: php test_customer_dashboard_api.php\n";