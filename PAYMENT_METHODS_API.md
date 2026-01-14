# Payment Methods API Documentation

## Overview
This API provides endpoints to manage payment methods for your eCommerce platform. The system includes both public endpoints (for customer frontend) and admin-only endpoints (for CRUD operations).

## Database Schema

### Payment Methods Table
- `id` - Primary key
- `name` - Payment method name (e.g., Bkash, Nagad, Rocket)
- `name_bn` - Payment method name in Bengali
- `logo` - Logo image path (stored in `storage/app/public/payment-methods/`)
- `information` - JSON array of payment details (label_name & label_value pairs)
- `description` - Optional description in English
- `description_bn` - Optional description in Bengali
- `sort_order` - For custom sorting (default: 0)
- `is_active` - Active status (default: true)
- `created_at`, `updated_at` - Timestamps

## Data Structure

### Information Field Format
The `information` field is a JSON array containing payment details:

```json
[
  {
    "label_name": "Merchant Number",
    "label_value": "+880 1XXXXXXXXX"
  },
  {
    "label_name": "Account Type",
    "label_value": "Merchant"
  },
  {
    "label_name": "Payment Instructions",
    "label_value": "Send money and provide transaction ID"
  }
]
```

### Complete Payment Method Object
```json
{
  "id": 1,
  "name": "Bkash",
  "name_bn": "বিকাশ",
  "logo": "payment-methods/bkash-logo.png",
  "logo_url": "http://your-domain/storage/payment-methods/bkash-logo.png",
  "information": [
    {
      "label_name": "Merchant Number",
      "label_value": "+880 1XXXXXXXXX"
    },
    {
      "label_name": "Account Type",
      "label_value": "Merchant"
    }
  ],
  "description": "Pay using Bkash mobile banking service...",
  "description_bn": "বিকাশ মোবাইল ব্যাংকিং সেবা...",
  "sort_order": 1,
  "is_active": true,
  "created_at": "2026-01-14T...",
  "updated_at": "2026-01-14T..."
}
```

## Seeded Data
The database has been seeded with 6 sample payment methods:
1. **Bkash** (বিকাশ)
2. **Nagad** (নগদ)
3. **Rocket** (রকেট)
4. **Bank Transfer** (ব্যাংক ট্রান্সফার)
5. **Cash on Delivery** (ক্যাশ অন ডেলিভারি)
6. **Upay** (উপায়)

## Public API Endpoints (No Authentication Required)

### 1. Get All Payment Methods
```http
GET /api/payment-methods
```

**Query Parameters:**
- `active` (boolean) - Filter by active status (default: all)
- `search` (string) - Search in name, name_bn, description
- `per_page` (integer, max: 100) - Enable pagination

**Example Requests:**
```bash
# Get all active payment methods
GET /api/payment-methods?active=1

# Search payment methods
GET /api/payment-methods?search=bkash

# Paginated results
GET /api/payment-methods?per_page=10
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Bkash",
      "name_bn": "বিকাশ",
      "logo": "payment-methods/bkash.png",
      "logo_url": "http://your-domain/storage/payment-methods/bkash.png",
      "information": [
        {
          "label_name": "Merchant Number",
          "label_value": "+880 1XXXXXXXXX"
        }
      ],
      "description": "Pay using Bkash...",
      "description_bn": "বিকাশ...",
      "sort_order": 1,
      "is_active": true,
      "created_at": "2026-01-14T...",
      "updated_at": "2026-01-14T..."
    }
  ]
}
```

### 2. Get Single Payment Method
```http
GET /api/payment-methods/{id}
```

**Example:**
```bash
GET /api/payment-methods/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Bkash",
    "name_bn": "বিকাশ",
    "logo": "payment-methods/bkash.png",
    "logo_url": "http://your-domain/storage/payment-methods/bkash.png",
    "information": [...],
    "description": "...",
    "sort_order": 1,
    "is_active": true
  }
}
```

## Admin API Endpoints (Authentication Required)

All admin endpoints require `auth:sanctum` middleware and admin role.

### 1. Get All Payment Methods (Admin)
```http
GET /api/admin/payment-methods
```
Same query parameters as public endpoint.

### 2. Create Payment Method
```http
POST /api/admin/payment-methods
```

**Content-Type:** `multipart/form-data` (for logo upload) or `application/json`

**Request Body (Form Data):**
```
name: Bkash
name_bn: বিকাশ
logo: [file upload]
information[0][label_name]: Merchant Number
information[0][label_value]: +880 1XXXXXXXXX
information[1][label_name]: Account Type
information[1][label_value]: Merchant
description: Pay using Bkash mobile banking service
description_bn: বিকাশ মোবাইল ব্যাংকিং
sort_order: 1
is_active: true
```

**Request Body (JSON - without file):**
```json
{
  "name": "Bkash",
  "name_bn": "বিকাশ",
  "information": [
    {
      "label_name": "Merchant Number",
      "label_value": "+880 1XXXXXXXXX"
    },
    {
      "label_name": "Account Type",
      "label_value": "Merchant"
    }
  ],
  "description": "Pay using Bkash mobile banking service",
  "description_bn": "বিকাশ মোবাইল ব্যাংকিং সেবা",
  "sort_order": 1,
  "is_active": true
}
```

**Validation Rules:**
- `name` - required, string, max:255, unique
- `name_bn` - nullable, string, max:255
- `logo` - nullable, image (jpeg,png,jpg,gif,svg,webp), max:2MB
- `information` - nullable, array
- `information.*.label_name` - required (if information provided), string, max:255
- `information.*.label_value` - required (if information provided), string, max:500
- `description` - nullable, string
- `description_bn` - nullable, string
- `sort_order` - nullable, integer, min:0
- `is_active` - nullable, boolean

**Response:**
```json
{
  "success": true,
  "message": "Payment method created successfully",
  "data": {
    "id": 1,
    "name": "Bkash",
    ...
  }
}
```

### 3. Get Single Payment Method (Admin)
```http
GET /api/admin/payment-methods/{id}
```

### 4. Update Payment Method
```http
PUT /api/admin/payment-methods/{id}
POST /api/admin/payment-methods/{id}
```

**Note:** Use POST with `multipart/form-data` when updating logo, use PUT with JSON for other updates.

**Request Body:** Same as create, all fields optional

**Example (Update with logo):**
```bash
POST /api/admin/payment-methods/1
Content-Type: multipart/form-data

name: Bkash Updated
logo: [new file upload]
```

**Example (Update without logo):**
```bash
PUT /api/admin/payment-methods/1
Content-Type: application/json

{
  "name": "Bkash Updated",
  "information": [
    {
      "label_name": "Merchant Number",
      "label_value": "+880 1XXXXXXXXX"
    }
  ]
}
```

### 5. Delete Payment Method
```http
DELETE /api/admin/payment-methods/{id}
```

**Note:** This will also delete the associated logo file.

**Response:**
```json
{
  "success": true,
  "message": "Payment method deleted successfully"
}
```

### 6. Toggle Active Status
```http
POST /api/admin/payment-methods/{id}/toggle-active
```

**Response:**
```json
{
  "success": true,
  "message": "Payment method status updated successfully",
  "data": {
    "id": 1,
    "is_active": false,
    ...
  }
}
```

### 7. Delete Logo Only
```http
DELETE /api/admin/payment-methods/{id}/logo
```

**Description:** Delete only the logo file without deleting the payment method.

**Response:**
```json
{
  "success": true,
  "message": "Payment method logo deleted successfully",
  "data": {
    "id": 1,
    "logo": null,
    "logo_url": null,
    ...
  }
}
```

### 8. Update Sort Order (Bulk)
```http
POST /api/admin/payment-methods/sort-order
```

**Request Body:**
```json
{
  "orders": [
    {
      "id": 1,
      "sort_order": 0
    },
    {
      "id": 2,
      "sort_order": 1
    },
    {
      "id": 3,
      "sort_order": 2
    }
  ]
}
```

**Validation Rules:**
- `orders` - required, array
- `orders.*.id` - required, exists in payment_methods table
- `orders.*.sort_order` - required, integer, min:0

**Response:**
```json
{
  "success": true,
  "message": "Sort order updated successfully"
}
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors if applicable
  }
}
```

**HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## File Upload

### Logo Upload
- Logos are stored in `storage/app/public/payment-methods/`
- Supported formats: jpeg, png, jpg, gif, svg, webp
- Maximum size: 2MB
- Files are automatically deleted when payment method is deleted
- Previous logo is replaced when uploading a new one

### Access Logo Files
Make sure the storage link is created:
```bash
php artisan storage:link
```

Logos will be accessible at:
```
http://your-domain/storage/payment-methods/filename.png
```

The `logo_url` attribute in the response provides the full URL automatically.

## Frontend Integration Examples

### Display Payment Methods in Checkout

```javascript
// Fetch active payment methods
const response = await fetch('/api/payment-methods?active=1');
const { data: paymentMethods } = await response.json();

// Display in UI
paymentMethods.forEach(method => {
  console.log(`
    Name: ${method.name} (${method.name_bn})
    Logo: ${method.logo_url}
    Information:
  `);
  
  method.information.forEach(info => {
    console.log(`  ${info.label_name}: ${info.label_value}`);
  });
});
```

### React Component Example

```jsx
import React, { useState, useEffect } from 'react';

function PaymentMethodSelector() {
  const [paymentMethods, setPaymentMethods] = useState([]);
  const [selectedMethod, setSelectedMethod] = useState(null);

  useEffect(() => {
    fetchPaymentMethods();
  }, []);

  const fetchPaymentMethods = async () => {
    const response = await fetch('/api/payment-methods?active=1');
    const { data } = await response.json();
    setPaymentMethods(data);
  };

  return (
    <div className="payment-methods">
      <h3>Select Payment Method</h3>
      {paymentMethods.map(method => (
        <div 
          key={method.id} 
          className={`payment-option ${selectedMethod?.id === method.id ? 'selected' : ''}`}
          onClick={() => setSelectedMethod(method)}
        >
          {method.logo_url && (
            <img src={method.logo_url} alt={method.name} />
          )}
          <h4>{method.name}</h4>
          
          {selectedMethod?.id === method.id && (
            <div className="payment-details">
              <p>{method.description}</p>
              <div className="payment-info">
                {method.information.map((info, index) => (
                  <div key={index} className="info-row">
                    <strong>{info.label_name}:</strong>
                    <span>{info.label_value}</span>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>
      ))}
    </div>
  );
}

export default PaymentMethodSelector;
```

### Admin Form Example (Create/Update)

```javascript
async function createPaymentMethod(formData) {
  const form = new FormData();
  
  form.append('name', 'Bkash');
  form.append('name_bn', 'বিকাশ');
  form.append('logo', fileInput.files[0]); // File from input
  
  // Add information array
  const information = [
    { label_name: 'Merchant Number', label_value: '+880 1XXXXXXXXX' },
    { label_name: 'Account Type', label_value: 'Merchant' }
  ];
  
  information.forEach((info, index) => {
    form.append(`information[${index}][label_name]`, info.label_name);
    form.append(`information[${index}][label_value]`, info.label_value);
  });
  
  form.append('description', 'Pay using Bkash...');
  form.append('sort_order', 1);
  form.append('is_active', true);
  
  const response = await fetch('/api/admin/payment-methods', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: form
  });
  
  const result = await response.json();
  return result;
}
```

## Running Migrations and Seeders

### Run Migration
```bash
php artisan migrate
```

### Run Seeder
```bash
php artisan db:seed --class=PaymentMethodSeeder
```

### Refresh Everything
```bash
php artisan migrate:fresh --seed
```

## Model Features

### Automatic Logo URL
The model automatically appends `logo_url` attribute with the full storage URL.

### Cascade Delete
When a payment method is deleted, its logo file is automatically deleted from storage.

### JSON Casting
The `information` field is automatically cast to/from array, so you can work with it as a PHP array.

## Scopes

### PaymentMethod Scopes
- `active()` - Only active payment methods
- `ordered()` - Order by sort_order and name

**Example:**
```php
$activeMethods = PaymentMethod::active()->ordered()->get();
```

## Security Notes

1. **Logo Upload**: Validates file type and size
2. **Admin Only**: All CRUD operations require admin authentication
3. **SQL Injection**: Uses Eloquent ORM with parameter binding
4. **XSS Protection**: Laravel automatically escapes output
5. **File Storage**: Files stored outside web root, accessed via storage link

## Files Created

1. **Migration:**
   - `database/migrations/2026_01_14_100000_create_payment_methods_table.php`

2. **Model:**
   - `app/Models/PaymentMethod.php`

3. **Controller:**
   - `app/Http/Controllers/Api/PaymentMethodController.php`

4. **Seeder:**
   - `database/seeders/PaymentMethodSeeder.php`

5. **Routes:**
   - Updated `routes/api.php` with public and admin routes

## Testing the API

### Test with cURL

```bash
# Get all payment methods
curl http://your-domain/api/payment-methods

# Get active payment methods only
curl http://your-domain/api/payment-methods?active=1

# Create payment method (admin)
curl -X POST http://your-domain/api/admin/payment-methods \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Bkash" \
  -F "name_bn=বিকাশ" \
  -F "logo=@/path/to/logo.png" \
  -F "information[0][label_name]=Merchant Number" \
  -F "information[0][label_value]=+880 1XXXXXXXXX"
```

## Notes

1. The `logo_url` is automatically appended to all responses
2. Both POST and PUT methods support the update endpoint for flexibility
3. Information field structure is flexible - add as many label/value pairs as needed
4. Supports bilingual content (English and Bengali)
5. Sort order allows custom arrangement of payment methods
6. Active/inactive status for showing/hiding methods without deletion

## Support

For issues or questions, please refer to the Laravel documentation or contact the development team.

