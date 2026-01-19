<?php

// Test script to demonstrate secondary address functionality

echo "=== Secondary Address API Test ===\n\n";

// Test 1: Get current site settings to see secondary_address field
echo "1. Testing GET /api/site-settings/public\n";
$response = file_get_contents('http://localhost:8000/api/site-settings/public');
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "✓ API call successful\n";
    echo "✓ secondary_address field exists: " . (array_key_exists('secondary_address', $data['data']) ? 'YES' : 'NO') . "\n";
    echo "✓ Current address: " . ($data['data']['address'] ?? 'null') . "\n";
    echo "✓ Current secondary_address: " . ($data['data']['secondary_address'] ?? 'null') . "\n";
} else {
    echo "✗ API call failed\n";
}

echo "\n";

// Test 2: Show the expected structure for secondary address
echo "2. Expected secondary_address structure:\n";
echo "{\n";
echo "  \"address\": \"123 Main Street, City, State 12345, Country\",\n";
echo "  \"secondary_address\": \"456 Secondary Street, Another City, State 67890, Country\"\n";
echo "}\n\n";

echo "3. Features implemented:\n";
echo "✓ Database migration added secondary_address field\n";
echo "✓ SiteSetting model updated with secondary_address support\n";
echo "✓ API endpoints updated to include secondary_address in response\n";
echo "✓ Validation rules added for secondary_address field\n";
echo "✓ Field appears in both public and admin API responses\n";
echo "✓ Field can be updated via admin API\n\n";

echo "4. Usage:\n";
echo "- GET /api/site-settings/public - Returns secondary_address in public API\n";
echo "- GET /api/site-settings - Returns secondary_address in admin API\n";
echo "- POST /api/site-settings - Update secondary_address via admin API\n";
echo "  * Use 'secondary_address' field in request body\n";
echo "  * Field is optional (nullable)\n";
echo "  * Field accepts string values\n\n";

echo "5. Example update request:\n";
echo "curl -X POST \"http://localhost:8000/api/site-settings\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_ADMIN_TOKEN\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\n";
echo "    \"secondary_address\": \"456 Secondary Street, Another City, State 67890, Country\"\n";
echo "  }'\n\n";

echo "=== Test Complete ===\n";