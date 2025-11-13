# Admin Product Purchase API Documentation

Complete API documentation for admin product purchase management system. This system allows administrators to record product purchases from suppliers, which automatically updates inventory and product records.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Endpoints](#endpoints)
   - [Record Product Purchase](#1-record-product-purchase)
   - [Record Bulk Product Purchases](#2-record-bulk-product-purchases)
   - [Get Product Purchase History](#3-get-product-purchase-history)
   - [Get All Purchase History](#4-get-all-purchase-history)
   - [Get Purchase Statistics](#5-get-purchase-statistics)
4. [How It Works](#how-it-works)
5. [Inventory Impact](#inventory-impact)
6. [Error Responses](#error-responses)

---

## Overview

The Admin Product Purchase System allows administrators to:

- **Record supplier purchases**: Record product purchases from suppliers
- **Update inventory automatically**: Increases product stock quantities
- **Track purchase history**: Maintain complete audit trail of all purchases
- **Bulk operations**: Record multiple product purchases in a single request
- **Purchase analytics**: Get statistics and insights on purchases

**Key Features:**
- Automatically increases product stock when purchases are recorded
- Records detailed purchase information (supplier, PO number, price)
- Maintains complete inventory history with purchase references
- Supports bulk purchase recording
- Provides purchase statistics and analytics

**Base URL**: `http://your-domain.com/api`

---

## Authentication

**Required**: Admin authentication via Sanctum

- **Authentication**: Bearer token via `Authorization: Bearer {admin_token}` header
- **Role**: Admin role (`admin`)
- **Middleware**: `auth:sanctum` and `admin`

---

## Endpoints

### 1. Record Product Purchase

Record a single product purchase from a supplier. This will automatically increase the product's inventory stock.

#### Endpoint
```
POST /api/admin-purchases
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "product_id": 1,
  "quantity": 100,
  "purchase_price": 25.50,
  "supplier_name": "ABC Suppliers Inc.",
  "purchase_order_number": "PO-2025-001",
  "reason": "Monthly restock order"
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `product_id` | integer | Yes | Product ID (must exist in products table) |
| `quantity` | integer | Yes | Quantity purchased (must be positive, min: 1) |
| `purchase_price` | numeric | No | Purchase price per unit (optional, for tracking) |
| `supplier_name` | string | No | Supplier name (max: 255 characters) |
| `purchase_order_number` | string | No | Purchase order number (max: 100 characters) |
| `reason` | string | No | Reason/notes for purchase (max: 500 characters) |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Product purchase recorded successfully",
  "purchase": {
    "product_id": 1,
    "product_name": "Wireless Headphones",
    "product_sku": "WH-001",
    "purchase_quantity": 100,
    "old_stock": 50,
    "new_stock": 150,
    "purchase_price": "25.50",
    "supplier_name": "ABC Suppliers Inc.",
    "purchase_order_number": "PO-2025-001"
  },
  "product": {
    "id": 1,
    "name": "Wireless Headphones",
    "sku": "WH-001",
    "stock_quantity": 150,
    "is_active": true
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Failed to record purchase",
  "error": "Product not found with ID: 999"
}
```

#### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product_id": [
      "The product id field is required."
    ],
    "quantity": [
      "The quantity must be at least 1."
    ]
  }
}
```

#### Unauthorized Response (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/admin-purchases \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 100,
    "purchase_price": 25.50,
    "supplier_name": "ABC Suppliers Inc.",
    "purchase_order_number": "PO-2025-001",
    "reason": "Monthly restock order"
  }'
```

---

### 2. Record Bulk Product Purchases

Record multiple product purchases in a single request. All purchases are processed in a transaction - if any purchase fails, all are rolled back.

#### Endpoint
```
POST /api/admin-purchases/bulk
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "purchases": [
    {
      "product_id": 1,
      "quantity": 100,
      "purchase_price": 25.50,
      "supplier_name": "ABC Suppliers Inc.",
      "purchase_order_number": "PO-2025-001",
      "reason": "Monthly restock order"
    },
    {
      "product_id": 3,
      "quantity": 50,
      "purchase_price": 150.00,
      "supplier_name": "XYZ Electronics",
      "purchase_order_number": "PO-2025-002",
      "reason": "New product stock"
    }
  ]
}
```

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `purchases` | array | Yes | Array of purchase objects (min: 1) |
| `purchases[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `purchases[].quantity` | integer | Yes | Quantity purchased (must be positive, min: 1) |
| `purchases[].purchase_price` | numeric | No | Purchase price per unit |
| `purchases[].supplier_name` | string | No | Supplier name (max: 255 characters) |
| `purchases[].purchase_order_number` | string | No | Purchase order number (max: 100 characters) |
| `purchases[].reason` | string | No | Reason/notes for purchase (max: 500 characters) |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "All purchases recorded successfully",
  "total_purchases": 2,
  "results": [
    {
      "index": 0,
      "success": true,
      "purchase": {
        "product_id": 1,
        "product_name": "Wireless Headphones",
        "product_sku": "WH-001",
        "purchase_quantity": 100,
        "old_stock": 50,
        "new_stock": 150,
        "purchase_price": "25.50",
        "supplier_name": "ABC Suppliers Inc.",
        "purchase_order_number": "PO-2025-001"
      }
    },
    {
      "index": 1,
      "success": true,
      "purchase": {
        "product_id": 3,
        "product_name": "Smart Watch",
        "product_sku": "SW-003",
        "purchase_quantity": 50,
        "old_stock": 25,
        "new_stock": 75,
        "purchase_price": "150.00",
        "supplier_name": "XYZ Electronics",
        "purchase_order_number": "PO-2025-002"
      }
    }
  ]
}
```

#### Partial Failure Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Some purchases failed",
  "results": [
    {
      "index": 0,
      "success": true,
      "purchase": {
        "product_id": 1,
        "product_name": "Wireless Headphones",
        "purchase_quantity": 100,
        "old_stock": 50,
        "new_stock": 150
      }
    }
  ],
  "errors": [
    {
      "index": 1,
      "product_id": 999,
      "error": "Product not found with ID: 999"
    }
  ]
}
```

#### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "purchases": [
      "The purchases field is required."
    ],
    "purchases.0.product_id": [
      "The purchases.0.product id field is required."
    ]
  }
}
```

#### cURL Example

```bash
curl -X POST http://your-domain.com/api/admin-purchases/bulk \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "purchases": [
      {
        "product_id": 1,
        "quantity": 100,
        "purchase_price": 25.50,
        "supplier_name": "ABC Suppliers Inc.",
        "purchase_order_number": "PO-2025-001"
      },
      {
        "product_id": 3,
        "quantity": 50,
        "purchase_price": 150.00,
        "supplier_name": "XYZ Electronics",
        "purchase_order_number": "PO-2025-002"
      }
    ]
  }'
```

---

### 3. Get Product Purchase History

Get purchase history for a specific product.

#### Endpoint
```
GET /api/admin-purchases/products/{product_id}/history
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Required | Description | Default |
|-----------|------|----------|-------------|---------|
| `limit` | integer | No | Maximum number of records to return | 50 |

#### Success Response (200 OK)

```json
{
  "success": true,
  "product_id": 1,
  "total_records": 5,
  "history": [
    {
      "id": 15,
      "product_id": 1,
      "old_quantity": 50,
      "new_quantity": 150,
      "adjustment": 100,
      "reason": "Product purchase from supplier - Supplier: ABC Suppliers Inc. - PO#: PO-2025-001",
      "reference_type": "admin_purchase",
      "reference_id": null,
      "created_by": 1,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z",
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      }
    },
    {
      "id": 10,
      "product_id": 1,
      "old_quantity": 0,
      "new_quantity": 50,
      "adjustment": 50,
      "reason": "Product purchase from supplier - Supplier: XYZ Corp - PO#: PO-2024-120",
      "reference_type": "admin_purchase",
      "reference_id": null,
      "created_by": 1,
      "created_at": "2024-12-20T14:15:00.000000Z",
      "updated_at": "2024-12-20T14:15:00.000000Z",
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      }
    }
  ]
}
```

#### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "Product not found",
  "error": "No query results for model [App\\Models\\Product] 999"
}
```

#### cURL Example

```bash
curl -X GET "http://your-domain.com/api/admin-purchases/products/1/history?limit=50" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 4. Get All Purchase History

Get all purchase history with optional filters and pagination.

#### Endpoint
```
GET /api/admin-purchases/history
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Required | Description | Default |
|-----------|------|----------|-------------|---------|
| `product_id` | integer | No | Filter by product ID | - |
| `date_from` | date | No | Filter purchases from date (YYYY-MM-DD) | - |
| `date_to` | date | No | Filter purchases to date (YYYY-MM-DD) | - |
| `admin_id` | integer | No | Filter by admin user who made purchase | - |
| `per_page` | integer | No | Items per page | 15 |
| `page` | integer | No | Page number | 1 |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "product_id": 1,
      "old_quantity": 50,
      "new_quantity": 150,
      "adjustment": 100,
      "reason": "Product purchase from supplier - Supplier: ABC Suppliers Inc. - PO#: PO-2025-001",
      "reference_type": "admin_purchase",
      "reference_id": null,
      "created_by": 1,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z",
      "product": {
        "id": 1,
        "name": "Wireless Headphones",
        "sku": "WH-001"
      },
      "creator": {
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
    "total": 72
  }
}
```

#### cURL Example

```bash
curl -X GET "http://your-domain.com/api/admin-purchases/history?product_id=1&date_from=2025-01-01&date_to=2025-01-31&per_page=20" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 5. Get Purchase Statistics

Get purchase statistics and analytics.

#### Endpoint
```
GET /api/admin-purchases/stats
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `date_from` | date | No | Filter statistics from date (YYYY-MM-DD) |
| `date_to` | date | No | Filter statistics to date (YYYY-MM-DD) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "stats": {
    "total_purchases": 72,
    "total_quantity_purchased": 8500,
    "top_purchased_products": [
      {
        "product_id": 1,
        "total_purchased": 2500,
        "product": {
          "id": 1,
          "name": "Wireless Headphones",
          "sku": "WH-001",
          "stock_quantity": 150
        }
      },
      {
        "product_id": 3,
        "total_purchased": 1800,
        "product": {
          "id": 3,
          "name": "Smart Watch",
          "sku": "SW-003",
          "stock_quantity": 75
        }
      }
    ]
  }
}
```

#### cURL Example

```bash
curl -X GET "http://your-domain.com/api/admin-purchases/stats?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}"
```

---

## How It Works

### Purchase Recording Process

1. **Validation**: System validates product exists and purchase data
2. **Stock Update**: Product stock quantity is increased by purchase quantity
3. **History Recording**: Purchase is recorded in inventory history with reference type `admin_purchase`
4. **Transaction Safety**: All operations are wrapped in database transactions

### Example Flow

```
1. Admin records purchase: 100 units of Product ID 1
   ↓
2. System validates product exists
   ↓
3. Current stock: 50 units
   ↓
4. New stock: 150 units (50 + 100)
   ↓
5. Product stock_quantity updated in database
   ↓
6. Inventory history record created with:
   - reference_type: "admin_purchase"
   - adjustment: +100
   - reason: "Product purchase from supplier - Supplier: ABC Inc. - PO#: PO-001"
   ↓
7. Purchase recorded successfully
```

---

## Inventory Impact

### Automatic Stock Updates

When a purchase is recorded:

- **Stock Quantity**: Automatically increased by purchase quantity
- **Product Record**: Updated in real-time
- **Inventory History**: Complete audit trail maintained
- **No Manual Adjustment Needed**: Stock is updated automatically

### Example

**Before Purchase:**
- Product ID: 1
- Stock Quantity: 50

**Purchase Recorded:**
- Quantity: 100
- Supplier: ABC Suppliers Inc.

**After Purchase:**
- Product ID: 1
- Stock Quantity: 150 (50 + 100)
- Inventory History: New record with adjustment +100

### Inventory History Record

Each purchase creates an inventory history record with:

- `product_id`: Product that was purchased
- `old_quantity`: Stock before purchase
- `new_quantity`: Stock after purchase
- `adjustment`: Positive quantity (purchase amount)
- `reason`: Detailed reason including supplier and PO number
- `reference_type`: "admin_purchase"
- `created_by`: Admin user ID who recorded purchase

---

## Error Responses

### Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 201 | Created - Purchase recorded successfully |
| 200 | Success - Request processed successfully |
| 400 | Bad Request - Validation failed or business logic error |
| 403 | Forbidden - Admin access required |
| 404 | Not Found - Product not found |
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

### Common Error Scenarios

#### 1. Product Not Found

```json
{
  "success": false,
  "message": "Failed to record purchase",
  "error": "Product not found with ID: 999"
}
```

#### 2. Invalid Quantity

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "quantity": [
      "The quantity must be at least 1."
    ]
  }
}
```

#### 3. Unauthorized Access

```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

#### 4. Bulk Purchase Partial Failure

```json
{
  "success": false,
  "message": "Some purchases failed",
  "results": [
    {
      "index": 0,
      "success": true,
      "purchase": { ... }
    }
  ],
  "errors": [
    {
      "index": 1,
      "product_id": 999,
      "error": "Product not found with ID: 999"
    }
  ]
}
```

---

## Best Practices

### 1. Record Purchases Immediately

Record purchases as soon as they are received to maintain accurate inventory:

```bash
# Record purchase when goods arrive
POST /api/admin-purchases
```

### 2. Include Complete Information

Always include supplier name and PO number for better tracking:

```json
{
  "product_id": 1,
  "quantity": 100,
  "supplier_name": "ABC Suppliers Inc.",
  "purchase_order_number": "PO-2025-001",
  "purchase_price": 25.50,
  "reason": "Monthly restock order"
}
```

### 3. Use Bulk Purchases for Multiple Products

When receiving multiple products from the same supplier:

```bash
# Use bulk endpoint for efficiency
POST /api/admin-purchases/bulk
```

### 4. Review Purchase History Regularly

Monitor purchase history to track:

- Purchase frequency
- Supplier performance
- Stock replenishment patterns
- Purchase costs

### 5. Use Statistics for Planning

Use purchase statistics to:

- Identify top purchased products
- Plan future purchases
- Analyze purchase trends
- Optimize inventory levels

---

## Integration Notes

### Automatic Inventory Updates

- No need to manually adjust inventory after recording purchases
- Stock is updated automatically and immediately
- All changes are tracked in inventory history

### Purchase Tracking

- All purchases are linked to admin users who recorded them
- Complete audit trail maintained
- Can filter by date, product, or admin user

### Transaction Safety

- Bulk purchases use database transactions
- If any purchase fails, all are rolled back
- Ensures data consistency

---

## Example Workflows

### Single Product Purchase

```bash
# 1. Receive goods from supplier
# 2. Record purchase
curl -X POST http://your-domain.com/api/admin-purchases \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 1,
    "quantity": 100,
    "supplier_name": "ABC Suppliers Inc.",
    "purchase_order_number": "PO-2025-001"
  }'

# 3. Stock automatically updated
# 4. Purchase recorded in history
```

### Bulk Purchase from Supplier

```bash
# 1. Receive multiple products from supplier
# 2. Record all purchases at once
curl -X POST http://your-domain.com/api/admin-purchases/bulk \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "purchases": [
      {
        "product_id": 1,
        "quantity": 100,
        "supplier_name": "ABC Suppliers Inc.",
        "purchase_order_number": "PO-2025-001"
      },
      {
        "product_id": 3,
        "quantity": 50,
        "supplier_name": "ABC Suppliers Inc.",
        "purchase_order_number": "PO-2025-001"
      }
    ]
  }'

# 3. All stocks updated
# 4. All purchases recorded
```

### Review Purchase History

```bash
# 1. Get purchase history for a product
curl -X GET "http://your-domain.com/api/admin-purchases/products/1/history" \
  -H "Authorization: Bearer {admin_token}"

# 2. Get all purchases for a date range
curl -X GET "http://your-domain.com/api/admin-purchases/history?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}"

# 3. Get purchase statistics
curl -X GET "http://your-domain.com/api/admin-purchases/stats?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}"
```

---

**Last Updated**: 2025-01-15
**API Version**: 1.0

