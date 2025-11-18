# Site Settings API Documentation

Complete documentation for the Site Settings management API endpoint.

## Table of Contents
1. [Overview](#overview)
2. [Create or Update Site Settings](#create-or-update-site-settings)
3. [Request Parameters](#request-parameters)
4. [Response Format](#response-format)
5. [Examples](#examples)
6. [Error Responses](#error-responses)

---

## Overview

The Site Settings API allows administrators to manage all site-wide configuration settings including business information, branding, ecommerce settings, payment methods, shipping options, and more.

### Base URL
```
http://your-domain.com/api
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {token}`

---

## Create or Update Site Settings

Create or update site settings. This endpoint uses a singleton pattern - there is only one site settings record in the database. If no settings exist, they will be created; otherwise, existing settings will be updated.

### Endpoint
```
POST /api/site-settings
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

### Content-Type
- `application/json` (for JSON data)
- `multipart/form-data` (for file uploads - logos and favicon)

---

## Request Parameters

All parameters are optional. Only include the fields you want to update.

### Basic Site Information

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `title` | string | No | max:255 | Site title |
| `tagline` | string | No | max:255 | Site tagline/slogan |
| `description` | string | No | - | Site description |

### Contact Information

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `contact_number` | string | No | max:20 | Contact phone number |
| `email` | No | email, max:255 | Primary contact email |
| `support_email` | string | No | email, max:255 | Support email address |
| `address` | string | No | - | Business address |

### Business Information

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `business_name` | string | No | max:255 | Legal business name |
| `business_registration_number` | string | No | max:100 | Business registration number |
| `tax_number` | string | No | max:100 | Tax identification number |

### Logos and Branding

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `header_logo` | file/string | No | image (jpeg,png,jpg,gif,svg), max:2MB | Header logo file or existing path |
| `footer_logo` | file/string | No | image (jpeg,png,jpg,gif,svg), max:2MB | Footer logo file or existing path |
| `favicon` | file/string | No | image (ico,png), max:1MB | Favicon file or existing path |
| `slider_images` | array | No | array of images (jpeg,png,jpg,gif,svg), max:2MB each | Multiple slider images (files or paths array) |

**Note**: For file uploads, send as `multipart/form-data`. For existing paths, send as string in JSON. For slider images, send as array of files or array of paths.

**Slider Images Details:**
- Upload multiple images: Send as `slider_images[]` in multipart/form-data (replaces all existing)
- Update/reorder: Send as `slider_images` array of paths in JSON (keeps only included images)
- Delete all: Send empty array `[]`
- See [Slider Images Documentation](./SLIDER_IMAGES_API_DOCUMENTATION.md) for complete details

### Social Media Links

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `social_links` | object | No | - | Social media links object |
| `social_links.facebook` | string | No | url | Facebook page URL |
| `social_links.twitter` | string | No | url | Twitter profile URL |
| `social_links.instagram` | string | No | url | Instagram profile URL |
| `social_links.linkedin` | string | No | url | LinkedIn company URL |
| `social_links.youtube` | string | No | url | YouTube channel URL |
| `social_links.tiktok` | string | No | url | TikTok profile URL |
| `social_links.whatsapp` | string | No | string | WhatsApp number |

### SEO Settings

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `meta_title` | string | No | max:255 | SEO meta title |
| `meta_description` | string | No | max:500 | SEO meta description |
| `meta_keywords` | string | No | - | SEO meta keywords (comma-separated) |

### Ecommerce Settings

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `currency` | string | No | size:3 | Currency code (e.g., USD, EUR, GBP) |
| `currency_symbol` | string | No | max:10 | Currency symbol (e.g., $, €, £) |
| `currency_position` | string | No | in:before,after | Position of currency symbol |
| `shipping_cost` | number | No | numeric, min:0 | Default shipping cost |
| `free_shipping_threshold` | number | No | numeric, min:0 | Order amount for free shipping |
| `tax_rate` | number | No | numeric, min:0, max:100 | Tax rate percentage |
| `tax_inclusive` | boolean | No | boolean | Whether prices include tax |

### Store Settings

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `store_enabled` | boolean | No | boolean | Whether store is enabled |
| `store_mode` | string | No | in:live,maintenance,coming_soon | Store mode |
| `maintenance_message` | string | No | - | Message shown during maintenance |

### Business Hours

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `business_hours` | object | No | - | Business hours object |
| `business_hours.monday` | object | No | - | Monday hours: `{open: "09:00", close: "17:00", closed: false}` |
| `business_hours.tuesday` | object | No | - | Tuesday hours |
| `business_hours.wednesday` | object | No | - | Wednesday hours |
| `business_hours.thursday` | object | No | - | Thursday hours |
| `business_hours.friday` | object | No | - | Friday hours |
| `business_hours.saturday` | object | No | - | Saturday hours |
| `business_hours.sunday` | object | No | - | Sunday hours |

Each day object structure:
```json
{
  "open": "09:00",    // Opening time (24-hour format)
  "close": "17:00",   // Closing time (24-hour format)
  "closed": false     // Whether the day is closed
}
```

### Payment & Shipping

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `payment_methods` | array | No | - | Array of enabled payment methods |
| `shipping_methods` | array | No | - | Array of enabled shipping methods |
| `accepted_countries` | array | No | - | Array of accepted country codes |

### Notifications

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `email_notifications` | boolean | No | boolean | Enable email notifications |
| `sms_notifications` | boolean | No | boolean | Enable SMS notifications |
| `notification_email` | string | No | email, max:255 | Email for notifications |

### Analytics & Tracking

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `google_analytics_id` | string | No | max:50 | Google Analytics tracking ID |
| `facebook_pixel_id` | string | No | max:50 | Facebook Pixel ID |
| `custom_scripts` | string | No | - | Custom JavaScript/HTML scripts |

### Legal & Policies

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `terms_of_service` | string | No | - | Terms of service content |
| `privacy_policy` | string | No | - | Privacy policy content |
| `return_policy` | string | No | - | Return policy content |
| `shipping_policy` | string | No | - | Shipping policy content |

### Additional Settings

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `additional_settings` | object | No | - | Additional custom settings (JSON object) |

---

## Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Site settings updated successfully",
  "data": {
    "id": 1,
    "title": "My Ecommerce Store",
    "tagline": "Your one-stop shop for everything",
    "description": "Welcome to our amazing ecommerce store...",
    "contact_number": "+1-234-567-8900",
    "email": "info@mystore.com",
    "support_email": "support@mystore.com",
    "address": "123 Main Street, City, State 12345",
    "business_name": "My Ecommerce Business LLC",
    "business_registration_number": "REG123456789",
    "tax_number": "TAX987654321",
    "header_logo": "http://localhost:8000/storage/logos/header_logo.jpg",
    "footer_logo": "http://localhost:8000/storage/logos/footer_logo.jpg",
    "favicon": "http://localhost:8000/storage/logos/favicon.ico",
    "slider_images": [
      "http://localhost:8000/storage/sliders/slider1.jpg",
      "http://localhost:8000/storage/sliders/slider2.png",
      "http://localhost:8000/storage/sliders/slider3.jpg"
    ],
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
    "formatted_currency": "$ ",
    "shipping_cost": "9.99",
    "free_shipping_threshold": "50.00",
    "tax_rate": "8.25",
    "tax_inclusive": false,
    "store_enabled": true,
    "store_mode": "live",
    "maintenance_message": null,
    "business_hours": {
      "monday": {"open": "09:00", "close": "17:00", "closed": false},
      "tuesday": {"open": "09:00", "close": "17:00", "closed": false},
      "wednesday": {"open": "09:00", "close": "17:00", "closed": false},
      "thursday": {"open": "09:00", "close": "17:00", "closed": false},
      "friday": {"open": "09:00", "close": "17:00", "closed": false},
      "saturday": {"open": "10:00", "close": "16:00", "closed": false},
      "sunday": {"open": "10:00", "close": "16:00", "closed": true}
    },
    "payment_methods": ["credit_card", "paypal", "bank_transfer"],
    "shipping_methods": ["standard", "express", "overnight"],
    "accepted_countries": ["US", "CA", "GB", "AU"],
    "email_notifications": true,
    "sms_notifications": false,
    "notification_email": "notifications@mystore.com",
    "google_analytics_id": "UA-123456789-1",
    "facebook_pixel_id": "123456789012345",
    "custom_scripts": "<script>console.log('Custom script');</script>",
    "terms_of_service": "Terms of service content...",
    "privacy_policy": "Privacy policy content...",
    "return_policy": "Return policy content...",
    "shipping_policy": "Shipping policy content...",
    "additional_settings": {
      "custom_field_1": "value1",
      "custom_field_2": "value2"
    },
    "updated_at": "2025-11-12T12:00:00.000000Z"
  }
}
```

---

## Examples

### Example 1: Update Basic Information (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My Ecommerce Store",
    "tagline": "Your one-stop shop",
    "description": "Welcome to our store",
    "contact_number": "+1-234-567-8900",
    "email": "info@mystore.com",
    "address": "123 Main Street, City, State 12345"
  }'
```

### Example 2: Update Ecommerce Settings (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "currency": "USD",
    "currency_symbol": "$",
    "currency_position": "before",
    "shipping_cost": 9.99,
    "free_shipping_threshold": 50.00,
    "tax_rate": 8.25,
    "tax_inclusive": false
  }'
```

### Example 3: Update Social Media Links (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "social_links": {
      "facebook": "https://facebook.com/mystore",
      "twitter": "https://twitter.com/mystore",
      "instagram": "https://instagram.com/mystore",
      "linkedin": "https://linkedin.com/company/mystore",
      "youtube": "https://youtube.com/mystore",
      "whatsapp": "+1234567890"
    }
  }'
```

### Example 4: Update Business Hours (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "business_hours": {
      "monday": {"open": "09:00", "close": "17:00", "closed": false},
      "tuesday": {"open": "09:00", "close": "17:00", "closed": false},
      "wednesday": {"open": "09:00", "close": "17:00", "closed": false},
      "thursday": {"open": "09:00", "close": "17:00", "closed": false},
      "friday": {"open": "09:00", "close": "17:00", "closed": false},
      "saturday": {"open": "10:00", "close": "16:00", "closed": false},
      "sunday": {"open": "10:00", "close": "16:00", "closed": true}
    }
  }'
```

### Example 5: Upload Logos (Form Data)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=My Store" \
  -F "header_logo=@/path/to/header_logo.jpg" \
  -F "footer_logo=@/path/to/footer_logo.png" \
  -F "favicon=@/path/to/favicon.ico"
```

### Example 5a: Upload Slider Images (Form Data)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "slider_images[]=@/path/to/slider1.jpg" \
  -F "slider_images[]=@/path/to/slider2.png" \
  -F "slider_images[]=@/path/to/slider3.jpg"
```

**Note**: See [Slider Images Documentation](./SLIDER_IMAGES_API_DOCUMENTATION.md) for complete slider images management guide.

### Example 6: Update Store Mode (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "store_mode": "maintenance",
    "maintenance_message": "We are currently performing maintenance. Please check back soon."
  }'
```

### Example 7: Update Payment and Shipping Methods (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_methods": ["credit_card", "paypal", "stripe", "bank_transfer"],
    "shipping_methods": ["standard", "express", "overnight", "pickup"],
    "accepted_countries": ["US", "CA", "GB", "AU", "DE", "FR"]
  }'
```

### Example 8: Update Analytics (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "google_analytics_id": "UA-123456789-1",
    "facebook_pixel_id": "123456789012345",
    "custom_scripts": "<script async src=\"https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID\"></script>"
  }'
```

### Example 9: Update Legal Policies (JSON)

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "terms_of_service": "By using this website, you agree to our terms...",
    "privacy_policy": "We respect your privacy and are committed to protecting...",
    "return_policy": "We offer a 30-day return policy on all products...",
    "shipping_policy": "We ship to all major countries within 5-7 business days..."
  }'
```

### JavaScript Example (JSON)

```javascript
const token = 'your_admin_token';

const response = await fetch('http://localhost:8000/api/site-settings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: 'My Ecommerce Store',
    tagline: 'Your one-stop shop',
    email: 'info@mystore.com',
    currency: 'USD',
    currency_symbol: '$',
    shipping_cost: 9.99,
    free_shipping_threshold: 50.00,
    tax_rate: 8.25,
    social_links: {
      facebook: 'https://facebook.com/mystore',
      twitter: 'https://twitter.com/mystore',
      instagram: 'https://instagram.com/mystore'
    }
  })
});

const data = await response.json();
console.log(data);
```

### JavaScript Example (Form Data with File Upload)

```javascript
const token = 'your_admin_token';
const formData = new FormData();

formData.append('title', 'My Store');
formData.append('header_logo', fileInput.files[0]); // File input element
formData.append('footer_logo', footerLogoFile);
formData.append('favicon', faviconFile);

const response = await fetch('http://localhost:8000/api/site-settings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
});

const data = await response.json();
console.log(data);
```

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email must be a valid email address."
    ],
    "currency": [
      "The currency must be exactly 3 characters."
    ],
    "tax_rate": [
      "The tax rate must be between 0 and 100."
    ],
    "header_logo": [
      "The header logo must be an image.",
      "The header logo must not be greater than 2048 kilobytes."
    ]
  }
}
```

### Unauthenticated (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

### Unauthorized - Not Admin (403 Forbidden)

```json
{
  "message": "Unauthorized. Admin access required."
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to update site settings",
  "error": "Error message details"
}
```

---

## Important Notes

### File Uploads
- **Header Logo**: Accepts JPEG, PNG, JPG, GIF, SVG (max 2MB)
- **Footer Logo**: Accepts JPEG, PNG, JPG, GIF, SVG (max 2MB)
- **Favicon**: Accepts ICO, PNG (max 1MB)
- **Slider Images**: Accepts JPEG, PNG, JPG, GIF, SVG (max 2MB each, multiple files)
- Files are stored in `storage/app/public/logos/` (logos) and `storage/app/public/sliders/` (slider images)
- Old files are automatically deleted when new ones are uploaded
- Access uploaded files via: `http://your-domain.com/storage/logos/{filename}` or `http://your-domain.com/storage/sliders/{filename}`
- **Slider Images**: See [Slider Images Documentation](./SLIDER_IMAGES_API_DOCUMENTATION.md) for detailed management guide

### Partial Updates
- All fields are optional
- Only include fields you want to update
- Fields not included in the request will remain unchanged
- You can update a single field or multiple fields in one request

### Singleton Pattern
- There is only one site settings record in the database
- The first call creates the record; subsequent calls update it
- Use `GET /api/site-settings` to retrieve current settings

### Array Fields
- `social_links`, `business_hours`, `payment_methods`, `shipping_methods`, `accepted_countries`, and `additional_settings` are JSON/array fields
- Send them as JSON objects/arrays in the request
- They will be merged with existing values (not replaced) if you only send partial data

### Currency Format
- `currency` must be a 3-character ISO code (e.g., USD, EUR, GBP)
- `currency_symbol` can be any string (e.g., $, €, £, ₹)
- `currency_position` determines where the symbol appears: `before` or `after`
- The API returns `formatted_currency` which combines symbol and position

### Store Modes
- `live`: Store is fully operational
- `maintenance`: Store is in maintenance mode (show `maintenance_message`)
- `coming_soon`: Store is coming soon

### Business Hours Format
- Times must be in 24-hour format (HH:MM)
- Example: `09:00`, `17:00`, `23:30`
- Each day must have `open`, `close`, and `closed` properties
- Set `closed: true` to mark a day as closed

---

## Complete Example: Full Settings Update

```json
{
  "title": "My Ecommerce Store",
  "tagline": "Your one-stop shop for everything",
  "description": "Welcome to our amazing ecommerce store where you can find the best products at great prices.",
  "contact_number": "+1-234-567-8900",
  "email": "info@mystore.com",
  "support_email": "support@mystore.com",
  "address": "123 Main Street, City, State 12345, Country",
  "business_name": "My Ecommerce Business LLC",
  "business_registration_number": "REG123456789",
  "tax_number": "TAX987654321",
  "social_links": {
    "facebook": "https://facebook.com/mystore",
    "twitter": "https://twitter.com/mystore",
    "instagram": "https://instagram.com/mystore",
    "linkedin": "https://linkedin.com/company/mystore",
    "youtube": "https://youtube.com/mystore",
    "tiktok": "https://tiktok.com/@mystore",
    "whatsapp": "+1234567890"
  },
  "meta_title": "My Ecommerce Store - Best Products Online",
  "meta_description": "Shop the best products online at My Ecommerce Store. Fast shipping, great prices, and excellent customer service.",
  "meta_keywords": "ecommerce, online shopping, products, store, buy online",
  "currency": "USD",
  "currency_symbol": "$",
  "currency_position": "before",
  "shipping_cost": 9.99,
  "free_shipping_threshold": 50.00,
  "tax_rate": 8.25,
  "tax_inclusive": false,
  "store_enabled": true,
  "store_mode": "live",
  "maintenance_message": null,
  "business_hours": {
    "monday": {"open": "09:00", "close": "17:00", "closed": false},
    "tuesday": {"open": "09:00", "close": "17:00", "closed": false},
    "wednesday": {"open": "09:00", "close": "17:00", "closed": false},
    "thursday": {"open": "09:00", "close": "17:00", "closed": false},
    "friday": {"open": "09:00", "close": "17:00", "closed": false},
    "saturday": {"open": "10:00", "close": "16:00", "closed": false},
    "sunday": {"open": "10:00", "close": "16:00", "closed": true}
  },
  "payment_methods": ["credit_card", "paypal", "stripe", "bank_transfer"],
  "shipping_methods": ["standard", "express", "overnight", "pickup"],
  "accepted_countries": ["US", "CA", "GB", "AU", "DE", "FR"],
  "email_notifications": true,
  "sms_notifications": false,
  "notification_email": "notifications@mystore.com",
  "google_analytics_id": "UA-123456789-1",
  "facebook_pixel_id": "123456789012345",
  "custom_scripts": "<script>console.log('Custom script');</script>",
  "terms_of_service": "By using this website, you agree to our terms of service...",
  "privacy_policy": "We respect your privacy and are committed to protecting your personal information...",
  "return_policy": "We offer a 30-day return policy on all products...",
  "shipping_policy": "We ship to all major countries within 5-7 business days...",
  "additional_settings": {
    "custom_field_1": "value1",
    "custom_field_2": "value2"
  }
}
```

---

## Support

For issues or questions, please refer to the main project documentation or contact the development team.

