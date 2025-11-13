# Purchase Management API Documentation

Complete API documentation for the purchase management system with comprehensive product and inventory validation.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Public Endpoints](#public-endpoints)
   - [Check Product Availability](#1-check-product-availability)
   - [Get Purchase Summary](#2-get-purchase-summary)
4. [Customer Endpoints](#customer-endpoints)
   - [Validate Purchase Items](#3-validate-purchase-items)
   - [Get Purchase Summary (Authenticated)](#4-get-purchase-summary-authenticated)
5. [Purchase Validation Features](#purchase-validation-features)
6. [Error Responses](#error-responses)
7. [Service Architecture](#service-architecture)

---

## Overview

The Purchase Management System provides comprehensive validation of products and inventory before order creation. It ensures:

- **Product Validation**: Checks if products exist, are active, and have valid pricing
- **Inventory Validation**: Verifies stock availability and prevents overselling
- **Concurrent Request Handling**: Uses database locks to prevent race conditions
- **Detailed Error Reporting**: Provides specific error messages for each validation failure
- **Low Stock Warnings**: Alerts when products have low inventory levels

**Base URL**: `http://your-domain.com/api`

---

## Authentication

### Public Endpoints
- No authentication required
- Can be used by anyone to check product availability

### Customer Endpoints
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **User Type**: Customer (authenticated via phone/OTP)
- **Middleware**: `customer`

---

## Public Endpoints

### 1. Check Product Availability

Check if products are available and have sufficient stock before purchase.

#### Endpoint
```
POST /api/purchase/check-availability
```

#### Headers
```
Content-Type: application/json
```

#### Request Body

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Array of items to check |
| `items[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `items[].quantity` | integer | Yes | Quantity to check (must be positive integer, min: 1) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "available": true,
  "items": [
    {
      "product_id": 1,
      "product_name": "Wireless Headphones",
      "quantity": 2,
      "price": "49.99",
      "total": "99.98",
      "available_stock": 50
    },
    {
      "product_id": 3,
      "product_name": "Smart Watch",
      "quantity": 1,
      "price": "199.99",
      "total": "199.99",
      "available_stock": 25
    }
  ],
  "warnings": [
    {
      "index": 0,
      "product_id": 1,
      "product_name": "Wireless Headphones",
      "warning": "Product 'Wireless Headphones' has low stock (5 remaining)",
      "available_stock": 5
    }
  ],
  "total_amount": "299.97"
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Insufficient stock for 'Wireless Headphones'. Available: 1, Requested: 2; Product 'Smart Watch' is not available (inactive)"
}
```

#### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "items": [
      "The items field is required."
    ],
    "items.0.product_id": [
      "The items.0.product_id field is required."
    ],
    "items.0.quantity": [
      "The items.0.quantity must be at least 1."
    ]
  }
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/purchase/check-availability \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ]
  }'
```

---

### 2. Get Purchase Summary

Get a detailed summary of the purchase including itemized breakdown and total amount.

#### Endpoint
```
POST /api/purchase/summary
```

#### Headers
```
Content-Type: application/json
```

#### Request Body

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Array of items for purchase summary |
| `items[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `items[].quantity` | integer | Yes | Quantity (must be positive integer, min: 1) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "summary": {
    "total_items": 2,
    "total_quantity": 3,
    "total_amount": "299.97",
    "items": [
      {
        "product_id": 1,
        "product_name": "Wireless Headphones",
        "product_sku": "WH-001",
        "quantity": 2,
        "unit_price": "49.99",
        "item_total": "99.98",
        "available_stock": 50
      },
      {
        "product_id": 3,
        "product_name": "Smart Watch",
        "product_sku": "SW-003",
        "quantity": 1,
        "unit_price": "199.99",
        "item_total": "199.99",
        "available_stock": 25
      }
    ]
  },
  "warnings": [
    {
      "index": 0,
      "product_id": 1,
      "product_name": "Wireless Headphones",
      "warning": "Product 'Wireless Headphones' has low stock (5 remaining)",
      "available_stock": 5
    }
  ]
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "error": "Purchase validation failed: Product not found with ID: 999"
}
```

#### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "items": [
      "The items field is required."
    ]
  }
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/purchase/summary \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ]
  }'
```

---

## Customer Endpoints

### 3. Validate Purchase Items

Validate purchase items for authenticated customers. This endpoint performs comprehensive validation including customer verification.

#### Endpoint
```
POST /api/purchase/validate
```

#### Headers
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Array of items to validate |
| `items[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `items[].quantity` | integer | Yes | Quantity (must be positive integer, min: 1) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Purchase items are valid",
  "data": {
    "total_items": 2,
    "total_quantity": 3,
    "total_amount": "299.97",
    "items": [
      {
        "product_id": 1,
        "product_name": "Wireless Headphones",
        "product_sku": "WH-001",
        "quantity": 2,
        "unit_price": "49.99",
        "item_total": "99.98",
        "available_stock": 50
      },
      {
        "product_id": 3,
        "product_name": "Smart Watch",
        "product_sku": "SW-003",
        "quantity": 1,
        "unit_price": "199.99",
        "item_total": "199.99",
        "available_stock": 25
      }
    ]
  },
  "warnings": [
    {
      "index": 0,
      "product_id": 1,
      "product_name": "Wireless Headphones",
      "warning": "Product 'Wireless Headphones' has low stock (5 remaining)",
      "available_stock": 5
    }
  ]
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Purchase validation failed",
  "error": "Purchase validation failed: Insufficient stock for 'Wireless Headphones'. Available: 1, Requested: 2"
}
```

#### Unauthorized Response (401 Unauthorized)

```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

#### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "items": [
      "The items field is required."
    ]
  }
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/purchase/validate \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ]
  }'
```

---

### 4. Get Purchase Summary (Authenticated)

Get purchase summary for authenticated customers. This endpoint includes customer validation.

#### Endpoint
```
POST /api/purchase/summary
```

#### Headers
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Array of items for purchase summary |
| `items[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `items[].quantity` | integer | Yes | Quantity (must be positive integer, min: 1) |

#### Success Response (200 OK)

Same as public endpoint response, but includes customer validation.

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "error": "Customer not found with ID: 999"
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/purchase/summary \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {
        "product_id": 1,
        "quantity": 2
      },
      {
        "product_id": 3,
        "quantity": 1
      }
    ]
  }'
```

---

## Purchase Validation Features

### Product Validation

The system validates the following for each product:

1. **Product Existence**: Verifies product exists in database
2. **Product Status**: Checks if product is active (`is_active = true`)
3. **Price Validation**: Ensures product has valid price (> 0)
4. **Duplicate Detection**: Prevents duplicate products in the same purchase

### Inventory Validation

The system validates the following for inventory:

1. **Stock Availability**: Checks if sufficient stock is available
2. **Out of Stock Detection**: Identifies products with zero stock
3. **Low Stock Warnings**: Alerts when stock is ≤ 10 units
4. **Concurrent Request Protection**: Uses database locks to prevent race conditions

### Validation Rules

| Validation | Rule | Error Message |
|------------|------|---------------|
| Empty items | Items array cannot be empty | "Purchase items cannot be empty" |
| Duplicate products | No duplicate product IDs | "Duplicate products found in purchase items" |
| Missing fields | product_id and quantity required | "Missing required fields: product_id and quantity are required" |
| Invalid quantity | Must be positive integer | "Invalid quantity: must be a positive integer, got '{value}'" |
| Product not found | Product must exist | "Product not found with ID: {id}" |
| Product inactive | Product must be active | "Product '{name}' is not available (inactive)" |
| Insufficient stock | Stock must be >= quantity | "Insufficient stock for '{name}'. Available: {available}, Requested: {requested}" |
| Out of stock | Stock must be > 0 | "Product '{name}' is out of stock" |
| Invalid price | Price must be > 0 | "Product '{name}' has invalid price" |

### Low Stock Warnings

Products with stock ≤ 10 units will generate warnings (but won't fail validation):

```json
{
  "warnings": [
    {
      "index": 0,
      "product_id": 1,
      "product_name": "Wireless Headphones",
      "warning": "Product 'Wireless Headphones' has low stock (5 remaining)",
      "available_stock": 5
    }
  ]
}
```

---

## Error Responses

### Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 400 | Bad Request - Validation failed or business logic error |
| 401 | Unauthorized - Missing or invalid authentication token |
| 422 | Unprocessable Entity - Request validation failed |
| 500 | Internal Server Error - Server error |

### Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error description"
}
```

### Validation Error Format

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### Common Error Scenarios

#### 1. Product Not Found

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Product not found with ID: 999"
}
```

#### 2. Insufficient Stock

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Insufficient stock for 'Wireless Headphones'. Available: 1, Requested: 2"
}
```

#### 3. Product Inactive

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Product 'Smart Watch' is not available (inactive)"
}
```

#### 4. Out of Stock

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Product 'Wireless Headphones' is out of stock"
}
```

#### 5. Invalid Quantity

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "items.0.quantity": [
      "The items.0.quantity must be at least 1."
    ]
  }
}
```

#### 6. Duplicate Products

```json
{
  "success": false,
  "available": false,
  "error": "Purchase validation failed: Duplicate products found in purchase items"
}
```

---

## Service Architecture

### PurchaseService

The `PurchaseService` handles all purchase validation logic:

#### Methods

1. **validatePurchaseItems(array $items)**: Validates items without database locks
2. **validatePurchaseItemsWithLock(array $items)**: Validates items with database locks (prevents race conditions)
3. **checkAvailability(array $items)**: Checks product availability
4. **validateCustomer(int $customerId)**: Validates customer can make purchase
5. **getPurchaseSummary(array $items, ?int $customerId)**: Gets purchase summary

#### Integration with OrderService

The `OrderService` uses `PurchaseService` for validation:

```php
// OrderService automatically uses PurchaseService for validation
$validation = $this->purchaseService->validatePurchaseItemsWithLock($items);
```

### Database Transactions

- All purchase validations with locks use database transactions
- Transactions are rolled back on validation failure
- Prevents partial updates and race conditions

### Concurrent Request Handling

The system uses `lockForUpdate()` to prevent race conditions:

- Products are locked during validation
- Prevents multiple customers from purchasing the same limited stock simultaneously
- Ensures accurate inventory tracking

---

## Integration with Order Creation

When creating an order via `POST /api/orders`, the system automatically:

1. Validates customer using `PurchaseService::validateCustomer()`
2. Validates all items using `PurchaseService::validatePurchaseItemsWithLock()`
3. Creates order with validated data
4. Reserves inventory for each item
5. Returns order with warnings if any low stock items exist

### Order Creation Flow

```
1. Customer submits order request
   ↓
2. PurchaseService validates customer
   ↓
3. PurchaseService validates items with database locks
   ↓
4. OrderService creates order
   ↓
5. InventoryService reserves stock
   ↓
6. Order created successfully
```

---

## Best Practices

### 1. Pre-validate Before Order Creation

Always check availability before allowing customers to proceed to checkout:

```bash
# Step 1: Check availability
POST /api/purchase/check-availability

# Step 2: If available, proceed to order creation
POST /api/orders
```

### 2. Handle Warnings

Low stock warnings don't prevent purchase but should be displayed to customers:

```json
{
  "warnings": [
    {
      "product_name": "Wireless Headphones",
      "warning": "Product 'Wireless Headphones' has low stock (5 remaining)"
    }
  ]
}
```

### 3. Error Handling

Always handle validation errors gracefully:

- Display specific error messages to users
- Provide actionable feedback (e.g., "Only 2 items available")
- Allow users to adjust quantities based on available stock

### 4. Concurrent Purchases

The system handles concurrent purchases automatically using database locks. However:

- Keep validation requests quick
- Don't hold locks unnecessarily
- Use the public endpoints for initial checks (no locks)

---

## Example Workflow

### Complete Purchase Flow

```bash
# 1. Customer browses products and adds to cart
# 2. Before checkout, check availability
curl -X POST http://your-domain.com/api/purchase/check-availability \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ]
  }'

# Response: Items available, proceed to checkout

# 3. Get purchase summary
curl -X POST http://your-domain.com/api/purchase/summary \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ]
  }'

# Response: Summary with totals

# 4. Validate items (authenticated)
curl -X POST http://your-domain.com/api/purchase/validate \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ]
  }'

# Response: Items validated

# 5. Create order (automatically validates again with locks)
curl -X POST http://your-domain.com/api/orders \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 3, "quantity": 1}
    ],
    "shipping_address": "123 Main St, City, State 12345",
    "notes": "Please deliver before 5 PM"
  }'

# Response: Order created successfully
```

---

## Notes

- All endpoints validate product existence, status, and inventory
- Database locks prevent race conditions during order creation
- Low stock warnings are informational and don't block purchases
- All validation errors provide specific, actionable messages
- The system automatically handles inventory reservation during order creation

---

**Last Updated**: 2025-01-15
**API Version**: 1.0

