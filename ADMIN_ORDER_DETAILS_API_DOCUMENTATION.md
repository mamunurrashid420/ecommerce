# Admin Order Details API Documentation

Complete documentation for the Admin Order Details API endpoint.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Get Order Details (Admin)](#get-order-details-admin)
4. [Response Structure](#response-structure)
5. [Error Responses](#error-responses)
6. [Example Use Cases](#example-use-cases)

---

## Overview

The Admin Order Details API allows administrators to retrieve comprehensive information about any order in the system. This endpoint provides detailed order information including customer details, order items, product information, and order status.

**Base URL**: `http://your-domain.com/api`

---

## Authentication

**Required**: Admin authentication
- **Authentication Method**: Bearer token via `Authorization: Bearer {admin_token}` header
- **Role**: Admin role (`admin`)
- **Middleware**: `auth:sanctum` and `admin`

---

## Get Order Details (Admin)

Retrieve detailed information about a specific order. Admins can access any order in the system.

### Endpoint
```
GET /api/orders/{order}
```

### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `order` | integer | Order ID | Yes |

### Success Response (200 OK)

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
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "created_at": "2025-01-10T08:00:00.000000Z",
      "updated_at": "2025-01-10T08:00:00.000000Z"
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
          "description": "High-quality wireless headphones with noise cancellation",
          "stock_quantity": 48,
          "is_active": true,
          "created_at": "2025-01-01T00:00:00.000000Z",
          "updated_at": "2025-01-01T00:00:00.000000Z"
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
          "description": "Fast charging USB-C cable",
          "stock_quantity": 99,
          "is_active": true,
          "created_at": "2025-01-01T00:00:00.000000Z",
          "updated_at": "2025-01-01T00:00:00.000000Z"
        }
      }
    ]
  }
}
```

### Response Fields

#### Order Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique order identifier |
| `order_number` | string | Unique order number (format: ORD-YYYYMMDD-XXXXXX) |
| `customer_id` | integer | ID of the customer who placed the order |
| `total_amount` | string | Total order amount (decimal with 2 places) |
| `status` | string | Current order status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) |
| `shipping_address` | string | Delivery address for the order |
| `notes` | string\|null | Additional notes provided by the customer |
| `created_at` | string | Order creation timestamp (ISO 8601) |
| `updated_at` | string | Last update timestamp (ISO 8601) |

#### Customer Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Customer ID |
| `name` | string | Customer full name |
| `email` | string\|null | Customer email address |
| `phone` | string | Customer phone number |
| `created_at` | string | Customer account creation timestamp |
| `updated_at` | string | Last update timestamp |

#### Order Item Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Order item ID |
| `order_id` | integer | Associated order ID |
| `product_id` | integer | Product ID |
| `quantity` | integer | Quantity ordered |
| `price` | string | Unit price at time of order (decimal) |
| `total` | string | Total price for this item (quantity × price) |

#### Product Object (within Order Item)

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product ID |
| `name` | string | Product name |
| `sku` | string | Product SKU |
| `price` | string | Current product price (may differ from order price) |
| `description` | string\|null | Product description |
| `stock_quantity` | integer | Current stock quantity |
| `is_active` | boolean | Whether product is currently active |
| `created_at` | string | Product creation timestamp |
| `updated_at` | string | Last update timestamp |

---

## Error Responses

### Unauthenticated (401 Unauthorized)

Occurs when no authentication token is provided or token is invalid.

```json
{
  "message": "Unauthenticated."
}
```

### Unauthorized Access (403 Forbidden)

Occurs when the authenticated user is not an admin.

```json
{
  "message": "Unauthorized. Admin access required."
}
```

### Order Not Found (404 Not Found)

Occurs when the specified order ID does not exist.

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

### Server Error (500 Internal Server Error)

Occurs when an unexpected server error happens.

```json
{
  "success": false,
  "message": "Failed to retrieve order",
  "error": "Database connection error"
}
```

---

## Example Requests

### cURL Example

```bash
# Get order details
curl -X GET "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

### JavaScript (Fetch) Example

```javascript
const orderId = 1;
const adminToken = 'your-admin-token';

fetch(`http://your-domain.com/api/orders/${orderId}`, {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${adminToken}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log('Order Details:', data);
})
.catch(error => {
  console.error('Error:', error);
});
```

### PHP (Guzzle) Example

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://your-domain.com/api/',
    'headers' => [
        'Authorization' => 'Bearer {admin_token}',
        'Accept' => 'application/json',
    ]
]);

$response = $client->get('orders/1');
$order = json_decode($response->getBody(), true);
```

### Python (Requests) Example

```python
import requests

url = "http://your-domain.com/api/orders/1"
headers = {
    "Authorization": "Bearer {admin_token}",
    "Accept": "application/json"
}

response = requests.get(url, headers=headers)
order_data = response.json()
print(order_data)
```

---

## Example Use Cases

### 1. View Order for Customer Support

An admin needs to view order details to assist a customer with their inquiry.

```bash
curl -X GET "http://your-domain.com/api/orders/123" \
  -H "Authorization: Bearer {admin_token}"
```

### 2. Process Order Fulfillment

Before shipping an order, admin retrieves order details to prepare the shipment.

```bash
curl -X GET "http://your-domain.com/api/orders/456" \
  -H "Authorization: Bearer {admin_token}"
```

### 3. Generate Order Report

Admin retrieves order details to generate a detailed order report for accounting.

```bash
curl -X GET "http://your-domain.com/api/orders/789" \
  -H "Authorization: Bearer {admin_token}"
```

---

## Notes

1. **Order Number Format**: Order numbers are unique and follow the format `ORD-YYYYMMDD-XXXXXX` where:
   - `YYYYMMDD` is the date the order was created
   - `XXXXXX` is a unique 6-character alphanumeric identifier

2. **Price Consistency**: The `price` field in order items reflects the price at the time of order creation. The product's current price may differ.

3. **Stock Information**: The `stock_quantity` in the product object shows the current stock level, which may differ from the stock level at the time of order creation.

4. **Status Values**: Valid order statuses are:
   - `pending`: Order created, awaiting processing
   - `processing`: Order being prepared
   - `shipped`: Order has been shipped
   - `delivered`: Order delivered to customer
   - `cancelled`: Order cancelled

5. **Timestamps**: All timestamps are in ISO 8601 format (UTC timezone).

6. **Monetary Values**: All monetary values are returned as strings with 2 decimal places to avoid floating-point precision issues.

---

## Related Endpoints

- **List All Orders**: `GET /api/orders` - Get paginated list of all orders
- **Update Order Status**: `PUT /api/orders/{order}` - Update order status
- **Delete Order**: `DELETE /api/orders/{order}` - Delete an order
- **Order Statistics**: `GET /api/orders/stats` - Get order statistics

---

## Best Practices

1. **Error Handling**: Always handle potential errors (404, 403, 500) in your application
2. **Caching**: Consider caching order details for frequently accessed orders
3. **Rate Limiting**: Be mindful of API rate limits when making multiple requests
4. **Data Validation**: Validate order IDs before making API calls
5. **Security**: Never expose admin tokens in client-side code or logs

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main [Order API Documentation](./ORDER_API_DOCUMENTATION.md).

