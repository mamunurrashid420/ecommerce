# Order API Documentation

Complete documentation for all order management endpoints for both customers and administrators.

## Table of Contents
1. [Authentication](#authentication)
2. [Customer Order Endpoints](#customer-order-endpoints)
   - [List Customer Orders](#1-list-customer-orders)
   - [Create Order](#2-create-order)
   - [Get Order Details](#3-get-order-details)
3. [Admin Order Endpoints](#admin-order-endpoints)
   - [List All Orders](#4-list-all-orders-admin)
   - [Get Order Statistics](#5-get-order-statistics-admin)
   - [Update Order Status](#6-update-order-status-admin)
   - [Delete Order](#7-delete-order-admin)
4. [Order Status Flow](#order-status-flow)
5. [Service Layer Architecture](#service-layer-architecture)
6. [Error Responses](#error-responses)

---

## Authentication

**Customer Endpoints** (Customer authentication required):
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **User Type**: Customer (authenticated via phone/OTP)

**Admin Endpoints** (Admin authentication required):
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **Role**: Admin role (`admin`)

**Base URL**: `http://your-domain.com/api`

---

## Customer Order Endpoints

### 1. List Customer Orders

Get a paginated list of orders for the authenticated customer.

#### Endpoint
```
GET /api/orders
```

#### Headers
```
Authorization: Bearer {customer_token}
```

#### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `page` | integer | Page number for pagination | 1 |
| `per_page` | integer | Items per page | 15 |
| `status` | string | Filter by status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) | - |
| `search` | string | Search by order number | - |
| `sort_by` | string | Sort field (`created_at`, `total_amount`, `status`) | `created_at` |
| `sort_order` | string | Sort direction (`asc`, `desc`) | `desc` |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20250115-ABC123",
      "customer_id": 1,
      "total_amount": "149.99",
      "status": "pending",
      "shipping_address": "123 Main St, City, State 12345",
      "notes": "Please deliver before 5 PM",
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z",
      "order_items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 5,
          "quantity": 2,
          "price": "49.99",
          "total": "99.98",
          "product": {
            "id": 5,
            "name": "Wireless Headphones",
            "sku": "WH-001",
            "price": "49.99"
          }
        },
        {
          "id": 2,
          "order_id": 1,
          "product_id": 3,
          "quantity": 1,
          "price": "50.01",
          "total": "50.01",
          "product": {
            "id": 3,
            "name": "USB-C Cable",
            "sku": "USB-C-001",
            "price": "50.01"
          }
        }
      ]
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
curl -X GET "http://your-domain.com/api/orders?status=pending&page=1&per_page=10" \
  -H "Authorization: Bearer {customer_token}"
```

---

### 2. Create Order

Create a new order for the authenticated customer.

#### Endpoint
```
POST /api/orders
```

#### Headers
```
Authorization: Bearer {customer_token}
Content-Type: application/json
```

#### Request Body

```json
{
  "shipping_address": "123 Main St, City, State 12345",
  "notes": "Please deliver before 5 PM",
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
| `shipping_address` | string | Yes | Delivery address (max 500 characters) |
| `items` | array | Yes | Array of order items (minimum 1 item) |
| `items[].product_id` | integer | Yes | Product ID (must exist) |
| `items[].quantity` | integer | Yes | Quantity (minimum 1) |
| `notes` | string | No | Order notes (max 1000 characters) |

#### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20250115-ABC123",
    "customer_id": 1,
    "total_amount": "149.99",
    "status": "pending",
    "shipping_address": "123 Main St, City, State 12345",
    "notes": "Please deliver before 5 PM",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "order_items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 5,
        "quantity": 2,
        "price": "49.99",
        "total": "99.98",
        "product": {
          "id": 5,
          "name": "Wireless Headphones",
          "sku": "WH-001",
          "price": "49.99",
          "stock_quantity": 48
        }
      },
      {
        "id": 2,
        "order_id": 1,
        "product_id": 3,
        "quantity": 1,
        "price": "50.01",
        "total": "50.01",
        "product": {
          "id": 3,
          "name": "USB-C Cable",
          "sku": "USB-C-001",
          "price": "50.01",
          "stock_quantity": 99
        }
      }
    ]
  }
}
```

#### Validation Errors (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "shipping_address": [
      "The shipping address field is required."
    ],
    "items.0.product_id": [
      "The selected items.0.product_id is invalid."
    ],
    "items.0.quantity": [
      "The items.0.quantity must be at least 1."
    ]
  }
}
```

#### Business Logic Errors (400 Bad Request)

```json
{
  "success": false,
  "message": "Failed to create order",
  "error": "Insufficient stock for product 'Wireless Headphones'. Available: 1, Requested: 2"
}
```

```json
{
  "success": false,
  "message": "Failed to create order",
  "error": "Product 'USB-C Cable' is not available"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/orders" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "shipping_address": "123 Main St, City, State 12345",
    "notes": "Please deliver before 5 PM",
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
  }'
```

**Note**: 
- Stock is automatically reserved when an order is created
- If any product has insufficient stock, the entire order creation fails and no stock is reserved
- The customer ID is automatically taken from the authenticated customer

---

### 3. Get Order Details

Get detailed information about a specific order. Customers can only view their own orders.

#### Endpoint
```
GET /api/orders/{order}
```

#### Headers
```
Authorization: Bearer {customer_token}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `order` | integer | Order ID |

#### Success Response (200 OK)

```json
{
  "success": true,
  "order": {
    "id": 1,
    "order_number": "ORD-20250115-ABC123",
    "customer_id": 1,
    "total_amount": "149.99",
    "status": "pending",
    "shipping_address": "123 Main St, City, State 12345",
    "notes": "Please deliver before 5 PM",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "order_items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 5,
        "quantity": 2,
        "price": "49.99",
        "total": "99.98",
        "product": {
          "id": 5,
          "name": "Wireless Headphones",
          "sku": "WH-001",
          "price": "49.99",
          "description": "High-quality wireless headphones",
          "image_url": "http://your-domain.com/storage/products/5/image.jpg"
        }
      }
    ]
  }
}
```

#### Unauthorized Access (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized access to this order"
}
```

#### Not Found (404 Not Found)

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {customer_token}"
```

---

## Admin Order Endpoints

### 4. List All Orders (Admin)

Get a paginated list of all orders with advanced filtering options.

#### Endpoint
```
GET /api/orders
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
| `status` | string | Filter by status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) | - |
| `customer_id` | integer | Filter by customer ID | - |
| `search` | string | Search by order number, customer name, phone, or email | - |
| `date_from` | date | Filter orders from date (YYYY-MM-DD) | - |
| `date_to` | date | Filter orders to date (YYYY-MM-DD) | - |
| `sort_by` | string | Sort field (`created_at`, `total_amount`, `status`) | `created_at` |
| `sort_order` | string | Sort direction (`asc`, `desc`) | `desc` |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-20250115-ABC123",
      "customer_id": 1,
      "total_amount": "149.99",
      "status": "pending",
      "shipping_address": "123 Main St, City, State 12345",
      "notes": "Please deliver before 5 PM",
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z",
      "customer": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890"
      },
      "order_items": [
        {
          "id": 1,
          "order_id": 1,
          "product_id": 5,
          "quantity": 2,
          "price": "49.99",
          "total": "99.98",
          "product": {
            "id": 5,
            "name": "Wireless Headphones",
            "sku": "WH-001",
            "price": "49.99"
          }
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

#### Example Requests

```bash
# Get all pending orders
curl -X GET "http://your-domain.com/api/orders?status=pending" \
  -H "Authorization: Bearer {admin_token}"

# Search orders by customer
curl -X GET "http://your-domain.com/api/orders?search=john" \
  -H "Authorization: Bearer {admin_token}"

# Filter by date range
curl -X GET "http://your-domain.com/api/orders?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}"

# Filter by customer and status
curl -X GET "http://your-domain.com/api/orders?customer_id=1&status=delivered" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 5. Get Order Statistics (Admin)

Get order statistics including total orders, revenue, and status breakdown.

#### Endpoint
```
GET /api/orders/stats
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### Query Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `date_from` | date | Filter statistics from date (YYYY-MM-DD) |
| `date_to` | date | Filter statistics to date (YYYY-MM-DD) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "stats": {
    "total_orders": 150,
    "total_revenue": 22498.50,
    "status_breakdown": {
      "pending": 25,
      "processing": 30,
      "shipped": 40,
      "delivered": 50,
      "cancelled": 5
    }
  }
}
```

#### Example Request

```bash
# Get all-time statistics
curl -X GET "http://your-domain.com/api/orders/stats" \
  -H "Authorization: Bearer {admin_token}"

# Get statistics for a specific month
curl -X GET "http://your-domain.com/api/orders/stats?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}"
```

---

### 6. Update Order Status (Admin)

Update the status of an order. Status transitions are validated to ensure business logic compliance.

#### Endpoint
```
PUT /api/orders/{order}
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `order` | integer | Order ID |

#### Request Body

```json
{
  "status": "processing"
}
```

#### Request Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | Yes | New status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Order status updated successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-20250115-ABC123",
    "customer_id": 1,
    "total_amount": "149.99",
    "status": "processing",
    "shipping_address": "123 Main St, City, State 12345",
    "notes": "Please deliver before 5 PM",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:35:00.000000Z",
    "customer": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "order_items": [
      {
        "id": 1,
        "order_id": 1,
        "product_id": 5,
        "quantity": 2,
        "price": "49.99",
        "total": "99.98",
        "product": {
          "id": 5,
          "name": "Wireless Headphones",
          "sku": "WH-001"
        }
      }
    ]
  }
}
```

#### Validation Errors (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": [
      "The selected status is invalid."
    ]
  }
}
```

#### Invalid Status Transition (400 Bad Request)

```json
{
  "success": false,
  "message": "Cannot transition from 'delivered' to 'processing'"
}
```

#### Example Request

```bash
curl -X PUT "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "processing"
  }'
```

**Note**: 
- When an order is cancelled, stock is automatically released back to inventory
- Status transitions follow a strict flow (see [Order Status Flow](#order-status-flow))

---

### 7. Delete Order (Admin)

Delete an order. Delivered orders cannot be deleted.

#### Endpoint
```
DELETE /api/orders/{order}
```

#### Headers
```
Authorization: Bearer {admin_token}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `order` | integer | Order ID |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Order deleted successfully",
  "order": {
    "id": 1,
    "order_number": "ORD-20250115-ABC123",
    "total_amount": "149.99",
    "status": "pending"
  }
}
```

#### Cannot Delete Delivered Order (409 Conflict)

```json
{
  "success": false,
  "message": "Cannot delete delivered orders. Consider cancelling instead."
}
```

#### Not Found (404 Not Found)

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

#### Example Request

```bash
curl -X DELETE "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}"
```

**Note**: 
- Stock is automatically released when a non-cancelled order is deleted
- Delivered orders cannot be deleted for audit purposes

---

## Order Status Flow

Orders follow a strict status flow to ensure data integrity and proper business logic:

```
pending → processing → shipped → delivered
   ↓           ↓           ↓
cancelled   cancelled   cancelled
```

### Valid Status Transitions

| Current Status | Allowed Next Statuses |
|----------------|----------------------|
| `pending` | `processing`, `cancelled` |
| `processing` | `shipped`, `cancelled` |
| `shipped` | `delivered`, `cancelled` |
| `delivered` | *(final state - no transitions)* |
| `cancelled` | *(final state - no transitions)* |

### Status Descriptions

- **pending**: Order has been created and is awaiting processing
- **processing**: Order is being prepared for shipment
- **shipped**: Order has been shipped and is in transit
- **delivered**: Order has been successfully delivered to the customer
- **cancelled**: Order has been cancelled (stock is released)

### Stock Management

- **Order Creation**: Stock is reserved when an order is created
- **Order Cancellation**: Stock is released when an order is cancelled
- **Order Deletion**: Stock is released when a non-cancelled order is deleted
- **Delivered Orders**: Stock remains deducted (order is complete)

---

## Service Layer Architecture

The order system uses the **Service Design Pattern** for better code organization and maintainability:

### OrderService

Located at `app/Services/OrderService.php`

**Key Methods:**
- `createOrder()` - Create a new order with stock reservation
- `getOrders()` - Get all orders with filtering (Admin)
- `getCustomerOrders()` - Get orders for a specific customer
- `getOrder()` - Get a single order by ID
- `updateOrderStatus()` - Update order status with validation
- `deleteOrder()` - Delete an order with stock release
- `getOrderStats()` - Get order statistics

**Features:**
- Database transactions for data integrity
- Row-level locking to prevent race conditions
- Automatic stock management via InventoryService
- Status transition validation
- Comprehensive error handling
- Logging for debugging

### OrderController

Located at `app/Http/Controllers/OrderController.php`

The controller uses dependency injection to access the `OrderService`:

```php
public function __construct(OrderService $orderService)
{
    $this->orderService = $orderService;
}
```

**Authorization:**
- Customer endpoints automatically use the authenticated customer
- Admin endpoints require admin middleware
- Customers can only access their own orders

### Integration with InventoryService

The OrderService integrates with InventoryService for stock management:

- **Stock Reservation**: Uses `InventoryService::reserveStock()` when orders are created
- **Stock Release**: Uses `InventoryService::releaseStock()` when orders are cancelled or deleted
- **Stock Validation**: Uses `InventoryService::hasSufficientStock()` before order creation

---

## Error Responses

### Authentication Errors

#### Unauthenticated (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

#### Unauthorized Access (403 Forbidden)
```json
{
  "success": false,
  "message": "Unauthorized access to this order"
}
```

#### Admin Required (403 Forbidden)
```json
{
  "message": "Unauthorized. Admin access required."
}
```

### Validation Errors (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "shipping_address": [
      "The shipping address field is required."
    ],
    "items": [
      "The items field is required."
    ],
    "items.0.product_id": [
      "The selected items.0.product_id is invalid."
    ],
    "items.0.quantity": [
      "The items.0.quantity must be at least 1."
    ]
  }
}
```

### Business Logic Errors (400 Bad Request)

```json
{
  "success": false,
  "message": "Failed to create order",
  "error": "Insufficient stock for product 'Wireless Headphones'. Available: 1, Requested: 2"
}
```

```json
{
  "success": false,
  "message": "Cannot transition from 'delivered' to 'processing'"
}
```

### Not Found Errors (404 Not Found)

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

### Conflict Errors (409 Conflict)

```json
{
  "success": false,
  "message": "Cannot delete delivered orders. Consider cancelling instead."
}
```

### Server Errors (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to retrieve orders",
  "error": "Database connection error"
}
```

---

## Best Practices

1. **Always check stock before creating orders** - Use the inventory API to verify stock availability
2. **Use transactions** - The service automatically handles transactions for data integrity
3. **Handle errors gracefully** - All endpoints return consistent error responses
4. **Validate status transitions** - The system enforces valid status transitions automatically
5. **Monitor stock levels** - Stock is automatically managed, but monitor inventory regularly
6. **Use pagination** - Always use pagination for list endpoints to improve performance
7. **Filter orders** - Use query parameters to filter orders for better performance
8. **Log important actions** - All order operations are logged for audit purposes

---

## Example Workflows

### Customer Order Workflow

1. **Customer browses products** (Public API)
2. **Customer checks stock** (Inventory API - optional)
3. **Customer creates order** (`POST /api/orders`)
   - Stock is automatically reserved
   - Order number is generated
   - Order status is set to `pending`
4. **Customer views order** (`GET /api/orders/{order}`)
5. **Customer lists their orders** (`GET /api/orders`)

### Admin Order Management Workflow

1. **Admin views all orders** (`GET /api/orders`)
2. **Admin filters orders** (by status, customer, date, etc.)
3. **Admin views order statistics** (`GET /api/orders/stats`)
4. **Admin updates order status** (`PUT /api/orders/{order}`)
   - Status transitions are validated
   - Stock is released if order is cancelled
5. **Admin deletes order if needed** (`DELETE /api/orders/{order}`)
   - Stock is released (unless already cancelled)
   - Delivered orders cannot be deleted

---

## Notes

- All monetary values are returned as strings with 2 decimal places
- Order numbers are unique and auto-generated in format: `ORD-YYYYMMDD-XXXXXX`
- Stock management is fully automated through InventoryService
- All timestamps are in ISO 8601 format (UTC)
- Pagination defaults to 15 items per page
- Maximum pagination is typically 100 items per page (check server configuration)

