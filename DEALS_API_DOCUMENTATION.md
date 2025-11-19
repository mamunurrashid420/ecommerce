# Deals API Documentation

## Overview

The Deals API provides comprehensive functionality for managing promotional deals in the ecommerce system. It supports multiple deal types including product discounts, category discounts, flash sales, buy X get Y offers, and minimum purchase deals.

## Table of Contents

1. [Authentication](#authentication)
2. [Public Endpoints](#public-endpoints)
3. [Customer Endpoints](#customer-endpoints)
4. [Admin Endpoints](#admin-endpoints)
5. [Data Models](#data-models)
6. [Error Handling](#error-handling)
7. [Examples](#examples)

---

## Authentication

### Public Endpoints
No authentication required. Available to all users.

### Customer Endpoints
Requires customer authentication via Sanctum token. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

### Admin Endpoints
Requires admin authentication via Sanctum token. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

---

## Public Endpoints

### 1. Get All Available Deals

**Endpoint:** `GET /api/deals`

**Description:** Retrieve all active and valid deals available to customers.

**Query Parameters:**
- `type` (optional): Filter by deal type (`product`, `category`, `flash`, `buy_x_get_y`, `minimum_purchase`)
- `featured` (optional): Filter featured deals (`true`/`false`)
- `product_id` (optional): Get deals applicable to a specific product
- `category_id` (optional): Get deals applicable to a specific category
- `sort_by` (optional): Sort field (default: `priority`)
- `sort_order` (optional): Sort direction (`asc`/`desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 12)
- `page` (optional): Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Sale - 50% Off",
      "slug": "summer-sale-50-off",
      "description": "Get 50% off on all summer products",
      "short_description": "50% off summer collection",
      "type": "product",
      "discount_type": "percentage",
      "discount_value": "50.00",
      "original_price": null,
      "deal_price": null,
      "minimum_purchase_amount": null,
      "maximum_discount": null,
      "applicable_products": [1, 2, 3],
      "applicable_categories": null,
      "start_date": "2025-11-18T00:00:00.000000Z",
      "end_date": "2025-12-31T23:59:59.000000Z",
      "is_active": true,
      "is_featured": true,
      "priority": 10,
      "image_url": "https://example.com/deal-image.jpg",
      "banner_image_url": "https://example.com/deal-banner.jpg",
      "usage_limit": 1000,
      "usage_count": 45,
      "usage_limit_per_customer": 1,
      "is_valid": true,
      "time_remaining": {
        "days": 43,
        "hours": 12,
        "minutes": 30,
        "seconds": 15,
        "expired": false,
        "total_seconds": 3760215
      },
      "discount_percentage": 50.0,
      "created_at": "2025-11-18T09:00:00.000000Z",
      "updated_at": "2025-11-18T09:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 58
  }
}
```

---

### 2. Get Featured Deals

**Endpoint:** `GET /api/deals/featured`

**Description:** Retrieve featured deals (typically displayed on homepage).

**Query Parameters:**
- `limit` (optional): Maximum number of deals to return (default: 6)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Sale - 50% Off",
      ...
    }
  ]
}
```

---

### 3. Get Flash Deals

**Endpoint:** `GET /api/deals/flash`

**Description:** Retrieve time-limited flash deals.

**Query Parameters:**
- `per_page` (optional): Items per page (default: 12)
- `page` (optional): Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "title": "Flash Sale - Limited Time",
      "type": "flash",
      ...
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 12,
    "total": 15
  }
}
```

---

### 4. Get Deal by ID or Slug

**Endpoint:** `GET /api/deals/{identifier}`

**Description:** Retrieve a specific deal by ID or slug.

**Parameters:**
- `identifier`: Deal ID (integer) or slug (string)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Summer Sale - 50% Off",
    "slug": "summer-sale-50-off",
    ...
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Deal not found or expired"
}
```

---

### 5. Get Deals for Product

**Endpoint:** `GET /api/deals/product/{productId}`

**Description:** Get all valid deals applicable to a specific product.

**Parameters:**
- `productId`: Product ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Product Discount",
      ...
    }
  ]
}
```

---

### 6. Get Deals for Category

**Endpoint:** `GET /api/deals/category/{categoryId}`

**Description:** Get all valid deals applicable to a specific category.

**Parameters:**
- `categoryId`: Category ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "title": "Category Discount",
      ...
    }
  ]
}
```

---

## Customer Endpoints

### 1. Validate Deal and Calculate Discount

**Endpoint:** `POST /api/deals/validate`

**Description:** Validate a deal and calculate discount for cart items. Requires customer authentication.

**Request Body:**
```json
{
  "deal_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Deal is valid",
  "data": {
    "deal": {
      "id": 1,
      "title": "Summer Sale - 50% Off",
      "slug": "summer-sale-50-off",
      "description": "Get 50% off on all summer products",
      "type": "product",
      "discount_type": "percentage",
      "discount_value": "50.00",
      "time_remaining": {
        "days": 43,
        "hours": 12,
        "minutes": 30,
        "seconds": 15,
        "expired": false,
        "total_seconds": 3760215
      }
    },
    "subtotal": 150.00,
    "discount_amount": 75.00,
    "total_after_discount": 75.00,
    "applicable_items": [
      {
        "product_id": 1,
        "product_name": "Summer T-Shirt",
        "quantity": 2,
        "price": 50.00,
        "total": 100.00
      },
      {
        "product_id": 2,
        "product_name": "Summer Shorts",
        "quantity": 1,
        "price": 50.00,
        "total": 50.00
      }
    ]
  }
}
```

**Error Responses:**

**400 - Deal not valid:**
```json
{
  "success": false,
  "message": "Deal is not valid or has expired"
}
```

**400 - Minimum purchase not met:**
```json
{
  "success": false,
  "message": "Minimum purchase amount of 100.00 required for this deal"
}
```

**400 - Customer usage limit exceeded:**
```json
{
  "success": false,
  "message": "Deal cannot be used by this customer"
}
```

---

## Admin Endpoints

### 1. List All Deals (Admin)

**Endpoint:** `GET /api/deals`

**Description:** Retrieve all deals with admin-level details. Requires admin authentication.

**Query Parameters:**
- `is_active` (optional): Filter by active status (`true`/`false`)
- `is_featured` (optional): Filter by featured status (`true`/`false`)
- `type` (optional): Filter by deal type
- `valid_only` (optional): Show only valid deals (`true`/`false`)
- `search` (optional): Search in title, description, or slug
- `sort_by` (optional): Sort field (default: `created_at`)
- `sort_order` (optional): Sort direction (`asc`/`desc`, default: `desc`)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Summer Sale - 50% Off",
      ...
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "updater": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 58
  }
}
```

---

### 2. Create Deal (Admin)

**Endpoint:** `POST /api/deals`

**Description:** Create a new deal. Requires admin authentication.

**Request Body:**
```json
{
  "title": "Summer Sale - 50% Off",
  "slug": "summer-sale-50-off",
  "description": "Get 50% off on all summer products",
  "short_description": "50% off summer collection",
  "type": "product",
  "discount_type": "percentage",
  "discount_value": 50,
  "original_price": null,
  "deal_price": null,
  "minimum_purchase_amount": null,
  "maximum_discount": 100,
  "applicable_products": [1, 2, 3],
  "applicable_categories": null,
  "buy_quantity": null,
  "get_quantity": null,
  "get_product_id": null,
  "start_date": "2025-11-18T00:00:00Z",
  "end_date": "2025-12-31T23:59:59Z",
  "is_active": true,
  "is_featured": true,
  "priority": 10,
  "image_url": "https://example.com/deal-image.jpg",
  "banner_image_url": "https://example.com/deal-banner.jpg",
  "usage_limit": 1000,
  "usage_limit_per_customer": 1,
  "meta_title": "Summer Sale - 50% Off",
  "meta_description": "Get amazing discounts on summer products",
  "meta_keywords": "summer, sale, discount"
}
```

**Field Descriptions:**
- `title` (required): Deal title
- `slug` (optional): URL-friendly slug (auto-generated if not provided)
- `type` (required): Deal type - `product`, `category`, `flash`, `buy_x_get_y`, `minimum_purchase`
- `discount_type` (required): `percentage` or `fixed`
- `discount_value` (required): Discount amount (percentage or fixed amount)
- `applicable_products` (required for `product` type): Array of product IDs
- `applicable_categories` (required for `category` type): Array of category IDs
- `buy_quantity`, `get_quantity`, `get_product_id` (required for `buy_x_get_y` type)

**Response (201):**
```json
{
  "success": true,
  "message": "Deal created successfully",
  "data": {
    "id": 1,
    "title": "Summer Sale - 50% Off",
    ...
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "discount_value": ["Percentage discount cannot exceed 100%."]
  }
}
```

---

### 3. Get Deal Details (Admin)

**Endpoint:** `GET /api/deals/{deal}`

**Description:** Get detailed information about a specific deal. Requires admin authentication.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Summer Sale - 50% Off",
    ...
    "creator": {...},
    "updater": {...}
  }
}
```

---

### 4. Update Deal (Admin)

**Endpoint:** `PUT /api/deals/{deal}`

**Description:** Update an existing deal. Requires admin authentication.

**Request Body:** Same as create, but all fields are optional (use `sometimes` validation).

**Response:**
```json
{
  "success": true,
  "message": "Deal updated successfully",
  "data": {
    "id": 1,
    "title": "Updated Deal Title",
    ...
  }
}
```

---

### 5. Delete Deal (Admin)

**Endpoint:** `DELETE /api/deals/{deal}`

**Description:** Delete a deal. Cannot delete deals that have been used. Requires admin authentication.

**Response:**
```json
{
  "success": true,
  "message": "Deal deleted successfully"
}
```

**Error Response (409):**
```json
{
  "success": false,
  "message": "Cannot delete deal that has been used. Consider deactivating it instead."
}
```

---

### 6. Toggle Deal Active Status (Admin)

**Endpoint:** `POST /api/deals/{deal}/toggle-active`

**Description:** Toggle the active status of a deal. Requires admin authentication.

**Response:**
```json
{
  "success": true,
  "message": "Deal status updated successfully",
  "data": {
    "id": 1,
    "is_active": false,
    ...
  }
}
```

---

### 7. Toggle Deal Featured Status (Admin)

**Endpoint:** `POST /api/deals/{deal}/toggle-featured`

**Description:** Toggle the featured status of a deal. Requires admin authentication.

**Response:**
```json
{
  "success": true,
  "message": "Deal featured status updated successfully",
  "data": {
    "id": 1,
    "is_featured": true,
    ...
  }
}
```

---

### 8. Get Deal Statistics (Admin)

**Endpoint:** `GET /api/deals/stats`

**Description:** Get statistics about deals. Requires admin authentication.

**Query Parameters:**
- `deal_id` (optional): Get stats for a specific deal, or overall stats if omitted

**Response (Overall Stats):**
```json
{
  "success": true,
  "data": {
    "total_deals": 58,
    "active_deals": 45,
    "valid_deals": 32,
    "total_usages": 1250,
    "total_discount_given": 12500.50,
    "total_orders_with_deals": 850
  }
}
```

**Response (Specific Deal Stats):**
```json
{
  "success": true,
  "data": {
    "deal_id": 1,
    "deal_title": "Summer Sale - 50% Off",
    "deal_slug": "summer-sale-50-off",
    "total_usages": 45,
    "total_discount_given": 2250.00,
    "total_orders": 45,
    "usage_limit": 1000,
    "usage_count": 45,
    "remaining_uses": 955
  }
}
```

---

## Data Models

### Deal Types

1. **product**: Discount applies to specific products
   - Requires: `applicable_products` array

2. **category**: Discount applies to products in specific categories
   - Requires: `applicable_categories` array

3. **flash**: Time-limited flash sale deals
   - Requires: `start_date` and `end_date`

4. **buy_x_get_y**: Buy X items, get Y items free
   - Requires: `buy_quantity`, `get_quantity`, `get_product_id`

5. **minimum_purchase**: Discount applies when minimum purchase amount is met
   - Requires: `minimum_purchase_amount`

### Discount Types

1. **percentage**: Percentage-based discount
   - `discount_value`: Percentage (0-100)
   - Optional: `maximum_discount` to cap the discount amount

2. **fixed**: Fixed amount discount
   - `discount_value`: Fixed discount amount

### Deal Status

- `is_valid`: Computed attribute indicating if deal is currently valid
  - Checks: `is_active`, date range, usage limits
- `time_remaining`: Object with days, hours, minutes, seconds until deal expires
- `discount_percentage`: Computed discount percentage for display

---

## Error Handling

All endpoints return consistent error responses:

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Error message here"
}
```

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Deal not found or expired"
}
```

**409 Conflict:**
```json
{
  "success": false,
  "message": "Cannot delete deal that has been used. Consider deactivating it instead."
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

**500 Server Error:**
```json
{
  "success": false,
  "message": "Failed to retrieve deals",
  "error": "Detailed error message"
}
```

---

## Examples

### Example 1: Create a Product Deal

```bash
POST /api/deals
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Electronics Sale",
  "type": "product",
  "discount_type": "percentage",
  "discount_value": 25,
  "maximum_discount": 500,
  "applicable_products": [10, 11, 12, 13],
  "start_date": "2025-11-18T00:00:00Z",
  "end_date": "2025-12-31T23:59:59Z",
  "is_active": true,
  "is_featured": true,
  "usage_limit": 500
}
```

### Example 2: Create a Buy X Get Y Deal

```bash
POST /api/deals
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "title": "Buy 2 Get 1 Free",
  "type": "buy_x_get_y",
  "buy_quantity": 2,
  "get_quantity": 1,
  "get_product_id": 5,
  "applicable_products": [1, 2, 3],
  "start_date": "2025-11-18T00:00:00Z",
  "end_date": "2025-12-31T23:59:59Z",
  "is_active": true
}
```

### Example 3: Validate Deal for Cart

```bash
POST /api/deals/validate
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "deal_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ]
}
```

### Example 4: Get Featured Deals

```bash
GET /api/deals/featured?limit=6
```

### Example 5: Get Deals for a Product

```bash
GET /api/deals/product/10
```

---

## Notes

1. **Deal Validation**: Deals are automatically validated based on:
   - Active status
   - Date range (start_date and end_date)
   - Usage limits (total and per customer)

2. **Usage Tracking**: When a deal is used in an order, the usage count is automatically incremented and a record is created in the `deal_usages` table.

3. **Slug Generation**: If a slug is not provided when creating a deal, it will be auto-generated from the title.

4. **Priority**: Deals with higher priority values appear first in listings.

5. **Featured Deals**: Featured deals are typically displayed prominently on the homepage or deal pages.

6. **Time Remaining**: The `time_remaining` attribute provides real-time countdown information for time-limited deals.

---

## Support

For additional support or questions about the Deals API, please contact the development team or refer to the main API documentation.

