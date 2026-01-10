<?php

// Script to update offer dates to make it active today
require_once 'vendor/autoload.php';

// You can run this via artisan tinker or create a simple script
echo "To make the offer active today, update the offer start_date to 2026-01-09\n\n";

echo "Option 1: Via API (if you have admin access):\n";
echo "curl -X POST http://localhost:8000/api/site-settings \\\n";
echo "  -H \"Authorization: Bearer YOUR_ADMIN_TOKEN\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\n";
echo "    \"offer\": {\n";
echo "      \"offer_name\": \"Winter Offer\",\n";
echo "      \"description\": \"Lorem ipsum dolor sit amet consectetur adipiscing elit vulputate...\",\n";
echo "      \"amount\": 10,\n";
echo "      \"start_date\": \"2026-01-09\",\n";
echo "      \"end_date\": \"2026-01-31\"\n";
echo "    }\n";
echo "  }'\n\n";

echo "Option 2: Via Database:\n";
echo "UPDATE site_settings SET offer = JSON_SET(offer, '$.start_date', '2026-01-09') WHERE id = 1;\n\n";

echo "Option 3: Via Laravel Tinker:\n";
echo "php artisan tinker\n";
echo "\$settings = App\\Models\\SiteSetting::first();\n";
echo "\$offer = \$settings->offer;\n";
echo "\$offer['start_date'] = '2026-01-09';\n";
echo "\$settings->offer = \$offer;\n";
echo "\$settings->save();\n";
echo "exit\n\n";

echo "After updating, the discount should work immediately!\n";