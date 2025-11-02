# Site Settings API Documentation

## Endpoints

### 1. Get Public Site Settings
**GET** `/api/site-settings/public`

Returns public site settings for frontend display.

**Response:**
```json
{
    "success": true,
    "data": {
        "title": "My Ecommerce Store",
        "tagline": "Your one-stop shop for everything",
        "description": "Welcome to our amazing ecommerce store...",
        "contact_number": "+1-234-567-8900",
        "email": "info@mystore.com",
        "address": "123 Main Street, City, State 12345, Country",
        "business_name": "My Ecommerce Business LLC",
        "header_logo": "http://localhost/storage/logos/header.png",
        "footer_logo": "http://localhost/storage/logos/footer.png",
        "favicon": "http://localhost/storage/logos/favicon.ico",
        "social_links": {
            "facebook": "https://facebook.com/mystore",
            "twitter": "https://twitter.com/mystore",
            "instagram": "https://instagram.com/mystore",
            "linkedin": "https://linkedin.com/company/mystore",
            "youtube": "https://youtube.com/mystore",
            "tiktok": "",
            "whatsapp": "+1234567890"
        },
        "meta_title": "My Ecommerce Store - Best Products Online",
        "meta_description": "Shop the best products online...",
        "meta_keywords": "ecommerce, online shopping, products",
        "currency": "USD",
        "currency_symbol": "$",
        "currency_position": "before",
        "store_enabled": true,
        "store_mode": "live",
        "maintenance_message": null,
        "business_hours": {
            "monday": {"open": "09:00", "close": "17:00", "closed": false},
            "tuesday": {"open": "09:00", "close": "17:00", "closed": false}
        },
        "google_analytics_id": null,
        "facebook_pixel_id": null
    }
}
```

### 2. Get Complete Site Settings (Admin)
**GET** `/api/site-settings`

**Headers:** `Authorization: Bearer {admin_token}`

Returns all site settings including admin-only fields.

**Response:**
```json
{
    "success": true,
    "message": "Site settings retrieved successfully",
    "data": {
        "id": 1,
        "title": "My Ecommerce Store",
        "tagline": "Your one-stop shop for everything",
        "description": "Welcome to our amazing ecommerce store...",
        "contact_number": "+1-234-567-8900",
        "email": "info@mystore.com",
        "support_email": "support@mystore.com",
        "address": "123 Main Street, City, State 12345, Country",
        "business_name": "My Ecommerce Business LLC",
        "business_registration_number": "REG123456789",
        "tax_number": "TAX987654321",
        "header_logo": "http://localhost/storage/logos/header.png",
        "footer_logo": "http://localhost/storage/logos/footer.png",
        "favicon": "http://localhost/storage/logos/favicon.ico",
        "social_links": {
            "facebook": "https://facebook.com/mystore",
            "twitter": "https://twitter.com/mystore",
            "instagram": "https://instagram.com/mystore",
            "linkedin": "https://linkedin.com/company/mystore",
            "youtube": "https://youtube.com/mystore",
            "tiktok": "",
            "whatsapp": "+1234567890"
        },
        "meta_title": "My Ecommerce Store - Best Products Online",
        "meta_description": "Shop the best products online...",
        "meta_keywords": "ecommerce, online shopping, products",
        "currency": "USD",
        "currency_symbol": "$",
        "currency_position": "before",
        "shipping_cost": "9.99",
        "free_shipping_threshold": "50.00",
        "tax_rate": "8.25",
        "tax_inclusive": false,
        "store_enabled": true,
        "store_mode": "live",
        "maintenance_message": null,
        "business_hours": {
            "monday": {"open": "09:00", "close": "17:00", "closed": false},
            "tuesday": {"open": "09:00", "close": "17:00", "closed": false}
        },
        "payment_methods": {
            "credit_card": {"enabled": true, "name": "Credit Card"},
            "paypal": {"enabled": true, "name": "PayPal"},
            "stripe": {"enabled": true, "name": "Stripe"}
        },
        "shipping_methods": {
            "standard": {"enabled": true, "name": "Standard Shipping", "cost": 9.99, "days": "5-7"},
            "express": {"enabled": true, "name": "Express Shipping", "cost": 19.99, "days": "2-3"}
        },
        "accepted_countries": {
            "US": "United States",
            "CA": "Canada",
            "GB": "United Kingdom"
        },
        "email_notifications": true,
        "sms_notifications": false,
        "notification_email": "notifications@mystore.com",
        "google_analytics_id": null,
        "facebook_pixel_id": null,
        "custom_scripts": null,
        "terms_of_service": "By using our website, you agree to our terms...",
        "privacy_policy": "We respect your privacy and are committed...",
        "return_policy": "We offer a 30-day return policy...",
        "shipping_policy": "We ship worldwide and offer various options...",
        "additional_settings": {
            "allow_guest_checkout": true,
            "require_account_activation": false,
            "min_order_amount": 10.00,
            "inventory_tracking": true
        },
        "created_at": "2025-11-02T18:30:00.000000Z",
        "updated_at": "2025-11-02T18:30:00.000000Z"
    }
}
```

### 3. Create or Update Site Settings (Admin)
**POST** `/api/site-settings`

**Headers:** `Authorization: Bearer {admin_token}`

**Request Body (JSON):**
```json
{
    "title": "My Store",
    "tagline": "Best products online",
    "description": "Complete store description",
    "contact_number": "+1-555-123-4567",
    "email": "contact@mystore.com",
    "support_email": "support@mystore.com",
    "address": "456 New Street, City, State",
    "business_name": "My Business LLC",
    "business_registration_number": "REG123456789",
    "tax_number": "TAX987654321",
    "social_links": {
        "facebook": "https://facebook.com/mystore",
        "twitter": "https://twitter.com/mystore",
        "instagram": "https://instagram.com/mystore",
        "linkedin": "https://linkedin.com/company/mystore",
        "youtube": "https://youtube.com/mystore",
        "tiktok": "https://tiktok.com/@mystore",
        "whatsapp": "+15551234567"
    },
    "meta_title": "My Store - Best Products Online",
    "meta_description": "Shop the best products at My Store",
    "meta_keywords": "ecommerce, online shopping, products",
    "currency": "USD",
    "currency_symbol": "$",
    "currency_position": "before",
    "shipping_cost": 9.99,
    "free_shipping_threshold": 50.00,
    "tax_rate": 8.25,
    "tax_inclusive": false,
    "store_enabled": true,
    "store_mode": "live",
    "maintenance_message": "We are currently under maintenance",
    "business_hours": {
        "monday": {"open": "09:00", "close": "17:00", "closed": false},
        "tuesday": {"open": "09:00", "close": "17:00", "closed": false}
    },
    "payment_methods": {
        "credit_card": {"enabled": true, "name": "Credit Card"},
        "paypal": {"enabled": true, "name": "PayPal"},
        "stripe": {"enabled": true, "name": "Stripe"}
    },
    "shipping_methods": {
        "standard": {"enabled": true, "name": "Standard Shipping", "cost": 9.99, "days": "5-7"},
        "express": {"enabled": true, "name": "Express Shipping", "cost": 19.99, "days": "2-3"}
    },
    "accepted_countries": {
        "US": "United States",
        "CA": "Canada",
        "GB": "United Kingdom"
    },
    "email_notifications": true,
    "sms_notifications": false,
    "notification_email": "notifications@mystore.com",
    "google_analytics_id": "GA-123456789",
    "facebook_pixel_id": "FB-987654321",
    "custom_scripts": "<script>console.log('Custom script');</script>",
    "terms_of_service": "By using our website, you agree to our terms...",
    "privacy_policy": "We respect your privacy and are committed...",
    "return_policy": "We offer a 30-day return policy...",
    "shipping_policy": "We ship worldwide and offer various options...",
    "additional_settings": {
        "allow_guest_checkout": true,
        "require_account_activation": false,
        "min_order_amount": 10.00,
        "max_order_amount": 10000.00,
        "inventory_tracking": true,
        "show_out_of_stock": true,
        "allow_backorders": false
    }
}
```

**File Upload (multipart/form-data):**
```
title: "My Store"
tagline: "Best products online"
header_logo: [file upload - image file]
footer_logo: [file upload - image file]
favicon: [file upload - ico/png file]
social_links[facebook]: "https://facebook.com/mystore"
social_links[twitter]: "https://twitter.com/mystore"
```

## All Available Fields

### Basic Site Information
- `id` - Unique identifier (auto-generated)
- `title` - Site/store name (default: "My Store")
- `tagline` - Short descriptive tagline
- `description` - Longer site description

### Contact Information
- `contact_number` - Primary contact phone number
- `email` - Primary contact email
- `support_email` - Customer support email
- `address` - Physical business address

### Business Information
- `business_name` - Legal business name
- `business_registration_number` - Business registration/license number
- `tax_number` - Tax identification number

### Logos and Branding
- `header_logo` - Header logo (file upload - jpeg,png,jpg,gif,svg, max 2MB)
- `footer_logo` - Footer logo (file upload - jpeg,png,jpg,gif,svg, max 2MB)
- `favicon` - Site favicon (file upload - ico,png, max 1MB)

### Social Media Links (JSON)
- `social_links` - Object containing social media URLs:
  - `facebook` - Facebook page URL
  - `twitter` - Twitter profile URL
  - `instagram` - Instagram profile URL
  - `linkedin` - LinkedIn company page URL
  - `youtube` - YouTube channel URL
  - `tiktok` - TikTok profile URL
  - `whatsapp` - WhatsApp number (with country code)

### SEO Settings
- `meta_title` - Default page title for SEO
- `meta_description` - Default meta description
- `meta_keywords` - SEO keywords

### Ecommerce Settings
- `currency` - 3-letter currency code (USD, EUR, etc.)
- `currency_symbol` - Currency symbol ($, €, etc., max 10 chars)
- `currency_position` - "before" or "after" price
- `shipping_cost` - Default shipping cost (decimal 10,2)
- `free_shipping_threshold` - Minimum order for free shipping (decimal 10,2)
- `tax_rate` - Tax percentage 0-100 (decimal 5,2)
- `tax_inclusive` - Whether prices include tax (boolean)

### Store Settings
- `store_enabled` - Whether store is active (boolean, default: true)
- `store_mode` - Store status: "live", "maintenance", "coming_soon"
- `maintenance_message` - Message shown during maintenance
- `business_hours` - Operating hours for each day (JSON object)

### Payment & Shipping
- `payment_methods` - Available payment options (JSON object)
- `shipping_methods` - Available shipping options (JSON object)
- `accepted_countries` - Countries where you ship (JSON object)

### Notifications
- `email_notifications` - Enable email notifications (boolean, default: true)
- `sms_notifications` - Enable SMS notifications (boolean, default: false)
- `notification_email` - Email for system notifications

### Analytics & Tracking
- `google_analytics_id` - Google Analytics tracking ID (max 50 chars)
- `facebook_pixel_id` - Facebook Pixel ID (max 50 chars)
- `custom_scripts` - Custom tracking/analytics scripts (text)

### Legal & Policies
- `terms_of_service` - Terms of service content (text)
- `privacy_policy` - Privacy policy content (text)
- `return_policy` - Return/refund policy (text)
- `shipping_policy` - Shipping policy details (text)

### Additional Settings
- `additional_settings` - Flexible JSON object for custom settings:
  - `allow_guest_checkout` - Allow checkout without account
  - `require_account_activation` - Require email verification
  - `min_order_amount` - Minimum order value
  - `max_order_amount` - Maximum order value
  - `inventory_tracking` - Enable inventory management
  - `show_out_of_stock` - Show out-of-stock products
  - `allow_backorders` - Allow ordering out-of-stock items

### System Fields
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

## Usage Examples

### JavaScript
```javascript
// Get public settings
const settings = await fetch('/api/site-settings/public')
    .then(res => res.json());

// Update settings (admin)
const response = await fetch('/api/site-settings', {
    method: 'POST',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        title: 'Updated Store Name',
        email: 'new@email.com'
    })
});
```

### cURL
```bash
# Get public settings
curl -X GET "http://localhost/api/site-settings/public"

# Update settings
curl -X POST "http://localhost/api/site-settings" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title": "My Store", "email": "contact@store.com"}'

# Upload logo
curl -X POST "http://localhost/api/site-settings" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "header_logo=@logo.png" \
  -F "title=My Store"
```

## Error Responses

**Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field must be a valid email address."]
    }
}
```

**Unauthorized (401):**
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```
