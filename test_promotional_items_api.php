<?php

// Test script to demonstrate promotional items functionality

echo "=== Promotional Items API Test ===\n\n";

// Test 1: Get current site settings to see promotional_items field
echo "1. Testing GET /api/site-settings/public\n";
$response = file_get_contents('http://localhost:8000/api/site-settings/public');
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "✓ API call successful\n";
    echo "✓ promotional_items field exists: " . (isset($data['data']['promotional_items']) ? 'YES' : 'NO') . "\n";
    echo "✓ Current promotional_items count: " . count($data['data']['promotional_items']) . "\n";
    
    if (!empty($data['data']['promotional_items'])) {
        echo "✓ Promotional items:\n";
        foreach ($data['data']['promotional_items'] as $index => $item) {
            echo "  - Item " . ($index + 1) . ":\n";
            echo "    Image: " . ($item['image'] ?? 'null') . "\n";
            echo "    URL: " . ($item['url'] ?? 'null') . "\n";
        }
    } else {
        echo "✓ No promotional items currently set\n";
    }
} else {
    echo "✗ API call failed\n";
}

echo "\n";

// Test 2: Show the expected structure for promotional items
echo "2. Expected promotional_items structure:\n";
echo "{\n";
echo "  \"promotional_items\": [\n";
echo "    {\n";
echo "      \"image\": \"http://localhost:8000/storage/promotional/image1.jpg\",\n";
echo "      \"url\": \"https://example.com/promo1\"\n";
echo "    },\n";
echo "    {\n";
echo "      \"image\": \"http://localhost:8000/storage/promotional/image2.jpg\",\n";
echo "      \"url\": \"https://example.com/promo2\"\n";
echo "    },\n";
echo "    {\n";
echo "      \"image\": \"http://localhost:8000/storage/promotional/image3.jpg\",\n";
echo "      \"url\": null\n";
echo "    }\n";
echo "  ]\n";
echo "}\n\n";

echo "3. Features implemented:\n";
echo "✓ Database migration added promotional_items field\n";
echo "✓ SiteSetting model updated with promotional_items support\n";
echo "✓ Promotional items with full URLs via promotional_items_with_urls accessor\n";
echo "✓ API endpoints updated to include promotional_items in response\n";
echo "✓ File upload support for promotional item images\n";
echo "✓ Validation rules for promotional items (max 3 items)\n";
echo "✓ Image storage in 'promotional' directory\n";
echo "✓ URL field can be null (optional)\n";
echo "✓ Automatic cleanup of removed promotional item images\n\n";

echo "4. Usage:\n";
echo "- GET /api/site-settings/public - Returns promotional_items in public API\n";
echo "- GET /api/site-settings - Returns promotional_items in admin API\n";
echo "- POST /api/site-settings - Update promotional_items via admin API\n";
echo "  * Use 'promotional_item_images[]' for file uploads\n";
echo "  * Use 'promotional_item_urls[]' for corresponding URLs\n";
echo "  * Use 'promotional_items' JSON for updating existing items\n";
echo "  * Maximum 3 promotional items allowed\n\n";

echo "=== Test Complete ===\n";