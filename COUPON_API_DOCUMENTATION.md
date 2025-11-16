# Coupon API Documentation

Complete documentation for all coupon management endpoints for both customers and administrators.

## Table of Contents
1. [Authentication](#authentication)
2. [Public Coupon Endpoints](#public-coupon-endpoints)
   - [Get Available Coupons](#1-get-available-coupons)
3. [Customer Coupon Endpoints](#customer-coupon-endpoints)
   - [Validate Coupon Code](#2-validate-coupon-code)
4. [Admin Coupon Endpoints](#admin-coupon-endpoints)
   - [List All Coupons](#3-list-all-coupons-admin)
   - [Create Coupon](#4-create-coupon-admin)
   - [Get Coupon Details](#5-get-coupon-details-admin)
   - [Update Coupon](#6-update-coupon-admin)
   - [Delete Coupon](#7-delete-coupon-admin)
   - [Toggle Coupon Active Status](#8-toggle-coupon-active-status-admin)
   - [Get Coupon Statistics](#9-get-coupon-statistics-admin)
5. [Coupon Types and Features](#coupon-types-and-features)
6. [Coupon Integration with Orders](#coupon-integration-with-orders)
7. [Error Responses](#error-responses)

---

## Authentication

**Public Endpoints** (No authentication required):
- Available coupons can be viewed by anyone

**Customer Endpoints** (Customer authentication required):
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **User Type**: Customer (authenticated via phone/OTP)

**Admin Endpoints** (Admin authentication required):
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **Role**: Admin role (`admin`)

**Base URL**: `http://your-domain.com/api`

---

## Public Coupon Endpoints

### 1. Get Available Coupons

Get a list of all active and valid coupons that are currently available for use.

#### Endpoint
```
GET /api/coupons/available
```

#### Headers
```
Content-Type: application/json
```

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| None | - | - | - |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off Summer Sale",
      "description": "Get 20% off on all summer products",
      "type": "percentage",
      "discount_value": "20.00",
      "minimum_purchase": "50.00",
      "maximum_discount": "100.00",
      "valid_until": "2025-12-31"
    },
    {
      "id": 2,
      "code": "FLAT10",
      "name": "$10 Off",
      "description": "Get $10 off on orders above $50",
      "type": "fixed",
      "discount_value": "10.00",
      "minimum_purchase": "50.00",
      "maximum_discount": null,
      "valid_until": "2025-12-31"
    }
  ]
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/coupons/available"
```

#### Notes
- Only returns coupons that are:
  - Active (`is_active = true`)
  - Within valid date range (if specified)
  - Not exceeded usage limit (if specified)
  - For authenticated customers: Not exceeded per-customer usage limit

---

## Customer Coupon Endpoints

### 2. Validate Coupon Code

Validate a coupon code and calculate the discount for a specific set of order items.

#### Endpoint
```
POST /api/coupons/validate
```

#### Headers
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "code": "SAVE20",
  "items": [
    {
      "product_id": 5,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

#### Request Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `code` | string | Yes | Coupon code (max 50 characters) |
| `items` | array | Yes | Array of order items (minimum 1 item) |
| `items[].product_id` | integer | Yes | Product ID (must exist) |
| `items[].quantity` | integer | Yes | Quantity (minimum 1) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Coupon is valid",
  "data": {
    "coupon": {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off Summer Sale",
      "description": "Get 20% off on all summer products",
      "type": "percentage",
      "discount_value": "20.00"
    },
    "subtotal": "149.99",
    "discount_amount": "29.99",
    "total_after_discount": "120.00"
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Coupon not found"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/coupons/validate" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SAVE20",
    "items": [
      {"product_id": 5, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ]
  }'
```

#### Validation Rules
- Coupon must exist and be active
- Coupon must be within valid date range
- Coupon must not have exceeded usage limit
- Customer must not have exceeded per-customer usage limit
- If `first_order_only` is true, customer must not have any previous orders
- Order items must meet minimum purchase requirement (if specified)
- Products must be in applicable categories/products (if specified)

---

## Admin Coupon Endpoints

### 3. List All Coupons (Admin)

Get a paginated list of all coupons with filtering and search capabilities.

#### Endpoint
```
GET /api/coupons
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `page` | integer | Page number for pagination | 1 |
| `per_page` | integer | Items per page | 15 |
| `is_active` | boolean | Filter by active status | - |
| `type` | string | Filter by type (`percentage`, `fixed`) | - |
| `search` | string | Search by code or name | - |
| `sort_by` | string | Sort field (`created_at`, `code`, `name`) | `created_at` |
| `sort_order` | string | Sort direction (`asc`, `desc`) | `desc` |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off Summer Sale",
      "description": "Get 20% off on all summer products",
      "type": "percentage",
      "discount_value": "20.00",
      "minimum_purchase": "50.00",
      "maximum_discount": "100.00",
      "usage_limit": 1000,
      "usage_count": 245,
      "usage_limit_per_customer": 1,
      "valid_from": "2025-01-01",
      "valid_until": "2025-12-31",
      "is_active": true,
      "applicable_categories": [1, 2, 3],
      "applicable_products": null,
      "first_order_only": false,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/coupons?is_active=true&type=percentage&page=1" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 4. Create Coupon (Admin)

Create a new coupon with specified discount rules and restrictions.

#### Endpoint
```
POST /api/coupons
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "code": "SAVE20",
  "name": "20% Off Summer Sale",
  "description": "Get 20% off on all summer products",
  "type": "percentage",
  "discount_value": 20,
  "minimum_purchase": 50,
  "maximum_discount": 100,
  "usage_limit": 1000,
  "usage_limit_per_customer": 1,
  "valid_from": "2025-01-01",
  "valid_until": "2025-12-31",
  "is_active": true,
  "applicable_categories": [1, 2, 3],
  "applicable_products": null,
  "first_order_only": false
}
```

#### Request Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `code` | string | Yes | Unique coupon code (max 50 characters) |
| `name` | string | Yes | Coupon name (max 255 characters) |
| `description` | string | No | Coupon description |
| `type` | string | Yes | Discount type: `percentage` or `fixed` |
| `discount_value` | number | Yes | Discount value (for percentage: 0-100, for fixed: any positive number) |
| `minimum_purchase` | number | No | Minimum order amount required |
| `maximum_discount` | number | No | Maximum discount amount (for percentage type) |
| `usage_limit` | integer | No | Total usage limit across all customers |
| `usage_limit_per_customer` | integer | No | Usage limit per individual customer |
| `valid_from` | date | No | Start date (YYYY-MM-DD) |
| `valid_until` | date | No | End date (YYYY-MM-DD, must be >= valid_from) |
| `is_active` | boolean | No | Whether coupon is active (default: true) |
| `applicable_categories` | array | No | Array of category IDs (null = all categories) |
| `applicable_products` | array | No | Array of product IDs (null = all products) |
| `first_order_only` | boolean | No | Restrict to first order only (default: false) |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Coupon created successfully",
  "data": {
    "id": 1,
    "code": "SAVE20",
    "name": "20% Off Summer Sale",
    "description": "Get 20% off on all summer products",
    "type": "percentage",
    "discount_value": "20.00",
    "minimum_purchase": "50.00",
    "maximum_discount": "100.00",
    "usage_limit": 1000,
    "usage_count": 0,
    "usage_limit_per_customer": 1,
    "valid_from": "2025-01-01",
    "valid_until": "2025-12-31",
    "is_active": true,
    "applicable_categories": [1, 2, 3],
    "applicable_products": null,
    "first_order_only": false,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

#### Error Response (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "code": ["The code has already been taken."],
    "discount_value": ["Percentage discount cannot exceed 100%."]
  }
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/coupons" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SAVE20",
    "name": "20% Off Summer Sale",
    "type": "percentage",
    "discount_value": 20,
    "minimum_purchase": 50,
    "maximum_discount": 100,
    "usage_limit": 1000,
    "is_active": true
  }'
```

#### Validation Rules
- `code` must be unique
- `type` must be either `percentage` or `fixed`
- For `percentage` type: `discount_value` cannot exceed 100
- `valid_until` must be after or equal to `valid_from`
- `applicable_categories` and `applicable_products` must contain valid IDs

---

### 5. Get Coupon Details (Admin)

Get detailed information about a specific coupon including usage history.

#### Endpoint
```
GET /api/coupons/{coupon}
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `coupon` | integer | Coupon ID |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "SAVE20",
    "name": "20% Off Summer Sale",
    "description": "Get 20% off on all summer products",
    "type": "percentage",
    "discount_value": "20.00",
    "minimum_purchase": "50.00",
    "maximum_discount": "100.00",
    "usage_limit": 1000,
    "usage_count": 245,
    "usage_limit_per_customer": 1,
    "valid_from": "2025-01-01",
    "valid_until": "2025-12-31",
    "is_active": true,
    "applicable_categories": [1, 2, 3],
    "applicable_products": null,
    "first_order_only": false,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "usages": [
      {
        "id": 1,
        "coupon_id": 1,
        "order_id": 123,
        "customer_id": 5,
        "discount_amount": "29.99",
        "order_total_before_discount": "149.99",
        "order_total_after_discount": "120.00",
        "created_at": "2025-01-10T10:30:00.000000Z",
        "order": {
          "id": 123,
          "order_number": "ORD-20250110-ABC123",
          "total_amount": "120.00"
        },
        "customer": {
          "id": 5,
          "name": "John Doe",
          "email": "john@example.com"
        }
      }
    ]
  }
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/coupons/1" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 6. Update Coupon (Admin)

Update an existing coupon. All fields are optional - only provided fields will be updated.

#### Endpoint
```
PUT /api/coupons/{coupon}
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `coupon` | integer | Coupon ID |

#### Request Body

```json
{
  "name": "Updated 20% Off Summer Sale",
  "is_active": false,
  "usage_limit": 2000
}
```

#### Request Body Parameters

All parameters are optional. See [Create Coupon](#4-create-coupon-admin) for parameter descriptions.

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Coupon updated successfully",
  "data": {
    "id": 1,
    "code": "SAVE20",
    "name": "Updated 20% Off Summer Sale",
    "is_active": false,
    "usage_limit": 2000,
    ...
  }
}
```

#### Example Request

```bash
curl -X PUT "http://your-domain.com/api/coupons/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "is_active": false,
    "usage_limit": 2000
  }'
```

---

### 7. Delete Coupon (Admin)

Delete a coupon. Coupons that have been used cannot be deleted.

#### Endpoint
```
DELETE /api/coupons/{coupon}
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `coupon` | integer | Coupon ID |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Coupon deleted successfully"
}
```

#### Error Response (409 Conflict)

```json
{
  "success": false,
  "message": "Cannot delete coupon that has been used. Consider deactivating it instead."
}
```

#### Example Request

```bash
curl -X DELETE "http://your-domain.com/api/coupons/1" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 8. Toggle Coupon Active Status (Admin)

Toggle the active status of a coupon (activate/deactivate).

#### Endpoint
```
POST /api/coupons/{coupon}/toggle-active
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `coupon` | integer | Coupon ID |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Coupon status updated successfully",
  "data": {
    "id": 1,
    "code": "SAVE20",
    "is_active": false,
    ...
  }
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/coupons/1/toggle-active" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 9. Get Coupon Statistics (Admin)

Get statistics about coupon usage. Can be filtered by specific coupon ID.

#### Endpoint
```
GET /api/coupons/stats
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `coupon_id` | integer | Filter by specific coupon ID | - |

#### Success Response (200 OK) - Overall Statistics

```json
{
  "success": true,
  "data": {
    "total_coupons": 25,
    "active_coupons": 18,
    "total_usages": 1245,
    "total_discount_given": "12500.50",
    "total_orders_with_coupons": 1245
  }
}
```

#### Success Response (200 OK) - Specific Coupon Statistics

```json
{
  "success": true,
  "data": {
    "coupon_id": 1,
    "coupon_code": "SAVE20",
    "coupon_name": "20% Off Summer Sale",
    "total_usages": 245,
    "total_discount_given": "2450.00",
    "total_orders": 245,
    "usage_limit": 1000,
    "usage_count": 245,
    "remaining_uses": 755
  }
}
```

#### Example Request

```bash
# Overall statistics
curl -X GET "http://your-domain.com/api/coupons/stats" \
  -H "Authorization: Bearer {admin_token}"

# Specific coupon statistics
curl -X GET "http://your-domain.com/api/coupons/stats?coupon_id=1" \
  -H "Authorization: Bearer {admin_token}"
```

---

## Coupon Types and Features

### Discount Types

1. **Percentage Discount**
   - Discount is calculated as a percentage of the order total
   - Example: 20% off means 20% of the order total is discounted
   - Can have a `maximum_discount` cap
   - `discount_value` must be between 0 and 100

2. **Fixed Discount**
   - Discount is a fixed amount
   - Example: $10 off means exactly $10 is discounted
   - Cannot exceed the order total
   - `discount_value` can be any positive number

### Coupon Restrictions

1. **Minimum Purchase**
   - Order subtotal must meet or exceed this amount
   - Applied before discount calculation

2. **Maximum Discount** (Percentage only)
   - Caps the discount amount for percentage coupons
   - Example: 50% off with max $100 means discount won't exceed $100

3. **Usage Limits**
   - **Total Usage Limit**: Maximum times coupon can be used across all customers
   - **Per-Customer Limit**: Maximum times a single customer can use the coupon

4. **Date Restrictions**
   - **Valid From**: Coupon becomes active on this date
   - **Valid Until**: Coupon expires on this date

5. **Product/Category Restrictions**
   - **Applicable Categories**: Coupon only applies to products in these categories
   - **Applicable Products**: Coupon only applies to these specific products
   - If both are null, coupon applies to all products

6. **First Order Only**
   - Restricts coupon to customers who have never placed an order before

---

## Coupon Integration with Orders

### Applying Coupon to Order

When creating an order, include the `coupon_code` in the request body:

```json
{
  "shipping_address": "123 Main St, City, State 12345",
  "items": [
    {
      "product_id": 5,
      "quantity": 2
    }
  ],
  "coupon_code": "SAVE20"
}
```

### Order Response with Coupon

When a coupon is applied, the order response includes coupon information:

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 123,
    "order_number": "ORD-20250115-ABC123",
    "customer_id": 1,
    "coupon_id": 1,
    "coupon_code": "SAVE20",
    "subtotal": "149.99",
    "discount_amount": "29.99",
    "total_amount": "120.00",
    "status": "pending",
    "coupon": {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off Summer Sale"
    },
    ...
  }
}
```

### Coupon Usage Tracking

When a coupon is successfully applied to an order:
1. Coupon usage count is incremented
2. A `CouponUsage` record is created linking the coupon, order, and customer
3. Discount amount and order totals are recorded for analytics

---

## Error Responses

### Common Error Codes

| Status Code | Description |
|------------|-------------|
| 200 | Success |
| 201 | Created successfully |
| 400 | Bad Request - Invalid coupon or validation failed |
| 401 | Unauthorized - Missing or invalid authentication token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Coupon not found |
| 409 | Conflict - Cannot delete used coupon |
| 422 | Validation Error - Invalid input data |
| 500 | Internal Server Error |

### Error Response Format

```json
{
  "success": false,
  "message": "Error message description",
  "error": "Detailed error information"
}
```

### Common Error Messages

- **"Coupon not found"** - The provided coupon code does not exist
- **"Coupon is not valid or has expired"** - Coupon is inactive, expired, or exceeded usage limit
- **"Coupon cannot be used by this customer"** - Customer has exceeded per-customer limit or doesn't meet first-order requirement
- **"Minimum purchase amount of {amount} required for this coupon"** - Order subtotal is below minimum purchase requirement
- **"The code has already been taken"** - Coupon code already exists (when creating)
- **"Percentage discount cannot exceed 100%"** - Invalid discount value for percentage type
- **"Cannot delete coupon that has been used"** - Attempting to delete a coupon with usage history

---

## Best Practices

1. **Coupon Code Naming**
   - Use uppercase letters and numbers
   - Keep codes short and memorable
   - Avoid special characters that might cause issues

2. **Usage Limits**
   - Set appropriate usage limits to prevent abuse
   - Use per-customer limits for exclusive offers
   - Monitor usage statistics regularly

3. **Date Management**
   - Set clear start and end dates for time-limited promotions
   - Deactivate expired coupons instead of deleting them (for historical records)

4. **Testing**
   - Always validate coupons before applying to orders
   - Test edge cases (minimum purchase, maximum discount, etc.)
   - Verify coupon restrictions work as expected

5. **Security**
   - Keep coupon codes unpredictable to prevent guessing
   - Monitor for unusual usage patterns
   - Regularly review and deactivate unused coupons

---

## Examples

### Example 1: Create a 20% Off Coupon

```bash
curl -X POST "http://your-domain.com/api/coupons" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SUMMER20",
    "name": "Summer Sale 20% Off",
    "description": "Get 20% off on all items",
    "type": "percentage",
    "discount_value": 20,
    "minimum_purchase": 50,
    "maximum_discount": 100,
    "usage_limit": 1000,
    "valid_until": "2025-12-31",
    "is_active": true
  }'
```

### Example 2: Create a $10 Fixed Discount

```bash
curl -X POST "http://your-domain.com/api/coupons" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "FLAT10",
    "name": "$10 Off",
    "type": "fixed",
    "discount_value": 10,
    "minimum_purchase": 50,
    "usage_limit_per_customer": 1,
    "is_active": true
  }'
```

### Example 3: Create Category-Specific Coupon

```bash
curl -X POST "http://your-domain.com/api/coupons" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "ELECTRONICS15",
    "name": "15% Off Electronics",
    "type": "percentage",
    "discount_value": 15,
    "applicable_categories": [1, 2, 3],
    "is_active": true
  }'
```

### Example 4: Validate and Apply Coupon

```bash
# Step 1: Validate coupon
curl -X POST "http://your-domain.com/api/coupons/validate" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "SAVE20",
    "items": [
      {"product_id": 5, "quantity": 2}
    ]
  }'

# Step 2: Create order with coupon
curl -X POST "http://your-domain.com/api/orders" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main St",
    "items": [
      {"product_id": 5, "quantity": 2}
    ],
    "coupon_code": "SAVE20"
  }'
```

---

## Support

For issues or questions regarding the Coupon API, please contact the development team or refer to the main API documentation.

