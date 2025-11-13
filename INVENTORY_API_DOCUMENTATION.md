# Inventory Management API Documentation

This document describes the Inventory Management API endpoints for the ecommerce backend. All inventory endpoints require authentication and admin privileges.

## Base URL
All endpoints are prefixed with `/api/inventory`

## Authentication
All endpoints require:
- Authentication via Sanctum (`auth:sanctum` middleware)
- Admin role (`admin` middleware)

## Endpoints Overview

### Stock Level Queries
- [Get Stock Level](#get-stock-level)
- [Get Bulk Stock Levels](#get-bulk-stock-levels)
- [Check Stock Availability](#check-stock-availability)

### Stock Adjustments
- [Adjust Stock](#adjust-stock)
- [Set Stock](#set-stock)
- [Reserve Stock](#reserve-stock)
- [Release Stock](#release-stock)
- [Bulk Stock Adjustment](#bulk-stock-adjustment)

### Stock Alerts
- [Get Low Stock Products](#get-low-stock-products)
- [Get Out of Stock Products](#get-out-of-stock-products)

### Inventory History
- [Get Inventory History](#get-inventory-history)

---

## Stock Level Queries

### Get Stock Level
Get the current stock level for a specific product.

**Endpoint:** `GET /api/inventory/products/{product}`

**Parameters:**
- `product` (path) - Product ID

**Response:**
```json
{
  "success": true,
  "data": {
    "product_id": 1,
    "product_name": "Sample Product",
    "sku": "PROD-001",
    "stock_quantity": 50,
    "is_low_stock": false,
    "is_out_of_stock": false
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/inventory/products/1" \
  -H "Authorization: Bearer {token}"
```

---

### Get Bulk Stock Levels
Get stock levels for multiple products at once.

**Endpoint:** `POST /api/inventory/products/bulk`

**Request Body:**
```json
{
  "product_ids": [1, 2, 3, 4, 5]
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "product_id": 1,
      "product_name": "Product 1",
      "sku": "PROD-001",
      "stock_quantity": 50,
      "is_low_stock": false,
      "is_out_of_stock": false
    },
    {
      "product_id": 2,
      "product_name": "Product 2",
      "sku": "PROD-002",
      "stock_quantity": 5,
      "is_low_stock": true,
      "is_out_of_stock": false
    }
  ]
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/inventory/products/bulk" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "product_ids": [1, 2, 3]
  }'
```

---

### Check Stock Availability
Check if a product has sufficient stock for a requested quantity.

**Endpoint:** `GET /api/inventory/products/{product}/check?quantity={quantity}`

**Parameters:**
- `product` (path) - Product ID
- `quantity` (query) - Required quantity to check

**Response:**
```json
{
  "success": true,
  "data": {
    "has_sufficient_stock": true,
    "requested_quantity": 10,
    "available_quantity": 50,
    "product_id": 1
  }
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/inventory/products/1/check?quantity=10" \
  -H "Authorization: Bearer {token}"
```

---

## Stock Adjustments

### Adjust Stock
Increase or decrease stock quantity by a specific amount.

**Endpoint:** `POST /api/inventory/products/{product}/adjust`

**Request Body:**
```json
{
  "quantity": -5,
  "reason": "Damaged items returned",
  "reference_type": "return",
  "reference_id": 123
}
```

**Parameters:**
- `product` (path) - Product ID
- `quantity` (body, required) - Adjustment amount (positive to increase, negative to decrease)
- `reason` (body, optional) - Reason for adjustment
- `reference_type` (body, optional) - Type of reference (e.g., 'order', 'adjustment', 'return')
- `reference_id` (body, optional) - ID of the reference

**Response:**
```json
{
  "success": true,
  "message": "Stock adjusted successfully",
  "data": {
    "product_id": 1,
    "old_quantity": 50,
    "new_quantity": 45,
    "adjustment": -5
  }
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/inventory/products/1/adjust" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": -5,
    "reason": "Damaged items returned"
  }'
```

**Error Response (Insufficient Stock):**
```json
{
  "success": false,
  "message": "Insufficient stock. Available: 5, Requested: 10"
}
```

---

### Set Stock
Set stock to a specific quantity.

**Endpoint:** `PUT /api/inventory/products/{product}/set`

**Request Body:**
```json
{
  "quantity": 100,
  "reason": "Physical inventory count"
}
```

**Parameters:**
- `product` (path) - Product ID
- `quantity` (body, required) - New stock quantity (must be >= 0)
- `reason` (body, optional) - Reason for setting stock

**Response:**
```json
{
  "success": true,
  "message": "Stock set successfully",
  "data": {
    "product_id": 1,
    "old_quantity": 50,
    "new_quantity": 100,
    "adjustment": 50
  }
}
```

**Example:**
```bash
curl -X PUT "http://localhost:8000/api/inventory/products/1/set" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 100,
    "reason": "Physical inventory count"
  }'
```

---

### Reserve Stock
Reserve stock for an order (decreases available stock).

**Endpoint:** `POST /api/inventory/products/{product}/reserve`

**Request Body:**
```json
{
  "quantity": 5,
  "order_id": 123
}
```

**Parameters:**
- `product` (path) - Product ID
- `quantity` (body, required) - Quantity to reserve (must be >= 1)
- `order_id` (body, required) - Order ID

**Response:**
```json
{
  "success": true,
  "message": "Stock reserved successfully",
  "data": {
    "product_id": 1,
    "old_quantity": 50,
    "new_quantity": 45,
    "adjustment": -5
  }
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/inventory/products/1/reserve" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5,
    "order_id": 123
  }'
```

---

### Release Stock
Release previously reserved stock (increases available stock).

**Endpoint:** `POST /api/inventory/products/{product}/release`

**Request Body:**
```json
{
  "quantity": 5,
  "order_id": 123
}
```

**Parameters:**
- `product` (path) - Product ID
- `quantity` (body, required) - Quantity to release (must be >= 1)
- `order_id` (body, required) - Order ID

**Response:**
```json
{
  "success": true,
  "message": "Stock released successfully",
  "data": {
    "product_id": 1,
    "old_quantity": 45,
    "new_quantity": 50,
    "adjustment": 5
  }
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/inventory/products/1/release" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "quantity": 5,
    "order_id": 123
  }'
```

---

### Bulk Stock Adjustment
Adjust stock for multiple products in a single request.

**Endpoint:** `POST /api/inventory/products/bulk-adjust`

**Request Body:**
```json
{
  "adjustments": [
    {
      "product_id": 1,
      "quantity": -5,
      "reason": "Damaged items",
      "reference_type": "adjustment",
      "reference_id": null
    },
    {
      "product_id": 2,
      "quantity": 10,
      "reason": "New shipment received",
      "reference_type": "shipment",
      "reference_id": 456
    }
  ]
}
```

**Parameters:**
- `adjustments` (body, required) - Array of adjustment objects
  - `product_id` (required) - Product ID
  - `quantity` (required) - Adjustment amount
  - `reason` (optional) - Reason for adjustment
  - `reference_type` (optional) - Type of reference
  - `reference_id` (optional) - ID of the reference

**Response (Success):**
```json
{
  "success": true,
  "message": "All adjustments completed successfully",
  "results": [
    {
      "product_id": 1,
      "old_quantity": 50,
      "new_quantity": 45,
      "adjustment": -5
    },
    {
      "product_id": 2,
      "old_quantity": 20,
      "new_quantity": 30,
      "adjustment": 10
    }
  ]
}
```

**Response (Partial Failure):**
```json
{
  "success": false,
  "message": "Some adjustments failed",
  "results": [
    {
      "product_id": 1,
      "old_quantity": 50,
      "new_quantity": 45,
      "adjustment": -5
    }
  ],
  "errors": [
    {
      "index": 1,
      "product_id": 2,
      "error": "Insufficient stock. Available: 5, Requested: 10"
    }
  ]
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/inventory/products/bulk-adjust" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "adjustments": [
      {
        "product_id": 1,
        "quantity": -5,
        "reason": "Damaged items"
      },
      {
        "product_id": 2,
        "quantity": 10,
        "reason": "New shipment"
      }
    ]
  }'
```

---

## Stock Alerts

### Get Low Stock Products
Get all products with stock below a threshold.

**Endpoint:** `GET /api/inventory/low-stock?threshold={threshold}`

**Parameters:**
- `threshold` (query, optional) - Stock threshold (default: 10)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Product 2",
      "sku": "PROD-002",
      "stock_quantity": 5,
      "is_active": true
    }
  ],
  "threshold": 10
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/inventory/low-stock?threshold=10" \
  -H "Authorization: Bearer {token}"
```

---

### Get Out of Stock Products
Get all products with zero or negative stock.

**Endpoint:** `GET /api/inventory/out-of-stock`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3,
      "name": "Product 3",
      "sku": "PROD-003",
      "stock_quantity": 0,
      "is_active": true
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/inventory/out-of-stock" \
  -H "Authorization: Bearer {token}"
```

---

## Inventory History

### Get Inventory History
Get the inventory change history for a product.

**Endpoint:** `GET /api/inventory/products/{product}/history?limit={limit}`

**Parameters:**
- `product` (path) - Product ID
- `limit` (query, optional) - Maximum number of records to return (default: 50)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "old_quantity": 50,
      "new_quantity": 45,
      "adjustment": -5,
      "reason": "Stock reserved for order #123",
      "reference_type": "order",
      "reference_id": 123,
      "created_by": 1,
      "created_at": "2025-11-12T10:30:00.000000Z",
      "updated_at": "2025-11-12T10:30:00.000000Z"
    },
    {
      "id": 2,
      "product_id": 1,
      "old_quantity": 55,
      "new_quantity": 50,
      "adjustment": -5,
      "reason": "Manual adjustment",
      "reference_type": null,
      "reference_id": null,
      "created_by": 1,
      "created_at": "2025-11-12T09:15:00.000000Z",
      "updated_at": "2025-11-12T09:15:00.000000Z"
    }
  ]
}
```

**Example:**
```bash
curl -X GET "http://localhost:8000/api/inventory/products/1/history?limit=20" \
  -H "Authorization: Bearer {token}"
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "quantity": ["The quantity field is required."]
  }
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Product not found",
  "error": "No query results for model [App\\Models\\Product] 999"
}
```

### Insufficient Stock (400)
```json
{
  "success": false,
  "message": "Insufficient stock. Available: 5, Requested: 10"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to adjust stock",
  "error": "Database connection error"
}
```

---

## Service Layer Architecture

The inventory system uses the **Service Design Pattern**:

### InventoryService
Located at `app/Services/InventoryService.php`

**Key Methods:**
- `adjustStock()` - Adjust stock by a specific amount
- `setStock()` - Set stock to a specific quantity
- `reserveStock()` - Reserve stock for orders
- `releaseStock()` - Release reserved stock
- `getStock()` - Get current stock level
- `getBulkStock()` - Get stock for multiple products
- `getLowStockProducts()` - Get products with low stock
- `getOutOfStockProducts()` - Get out of stock products
- `bulkAdjustStock()` - Bulk stock adjustments
- `getHistory()` - Get inventory history
- `hasSufficientStock()` - Check stock availability

**Features:**
- Database transactions for data integrity
- Row-level locking to prevent race conditions
- Automatic history tracking
- Comprehensive error handling
- Logging for debugging

### InventoryController
Located at `app/Http/Controllers/Api/InventoryController.php`

The controller uses dependency injection to access the `InventoryService`:
```php
public function __construct(InventoryService $inventoryService)
{
    $this->inventoryService = $inventoryService;
}
```

### InventoryHistory Model
Located at `app/Models/InventoryHistory.php`

Tracks all inventory changes with:
- Product reference
- Old and new quantities
- Adjustment amount
- Reason for change
- Reference type and ID (for linking to orders, etc.)
- User who made the change
- Timestamp

---

## Best Practices

1. **Always use transactions** - The service automatically handles transactions for data integrity
2. **Check stock before reserving** - Use `checkStock` endpoint before creating orders
3. **Use bulk operations** - For multiple products, use bulk endpoints for better performance
4. **Provide reasons** - Always include a reason for stock adjustments for audit purposes
5. **Monitor low stock** - Regularly check low stock alerts to prevent stockouts
6. **Review history** - Use history endpoint to track inventory changes and identify issues

---

## Integration with Orders

When creating orders, you can use the inventory service to reserve stock:

```php
// In OrderController
$inventoryService->reserveStock($productId, $quantity, $orderId);
```

When cancelling orders, release the stock:

```php
// In OrderController
$inventoryService->releaseStock($productId, $quantity, $orderId);
```

---

## Database Migration

Run the migration to create the inventory_histories table:

```bash
php artisan migrate
```

The migration creates a table with the following structure:
- `id` - Primary key
- `product_id` - Foreign key to products
- `old_quantity` - Stock quantity before change
- `new_quantity` - Stock quantity after change
- `adjustment` - Amount of change (positive/negative)
- `reason` - Reason for the change
- `reference_type` - Type of reference (e.g., 'order')
- `reference_id` - ID of the reference
- `created_by` - User who made the change
- `created_at` / `updated_at` - Timestamps

---

## Notes

- All stock adjustments are logged in the `inventory_histories` table
- Stock cannot go below 0 (negative stock is prevented)
- The service uses row-level locking to prevent race conditions
- Low stock threshold defaults to 10 but can be customized
- All endpoints require admin authentication

