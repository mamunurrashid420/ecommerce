# Admin Order Status Update API Documentation

Complete documentation for the Admin Order Status Update API endpoint.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Update Order Status](#update-order-status)
4. [Order Status Flow](#order-status-flow)
5. [Request & Response](#request--response)
6. [Error Responses](#error-responses)
7. [Stock Management](#stock-management)
8. [Example Use Cases](#example-use-cases)

---

## Overview

The Admin Order Status Update API allows administrators to update the status of any order in the system. This endpoint enforces business logic rules for status transitions and automatically manages inventory stock when orders are cancelled.

**Base URL**: `http://your-domain.com/api`

**Important**: Status transitions follow a strict flow to ensure data integrity and proper business logic compliance.

---

## Authentication

**Required**: Admin authentication
- **Authentication Method**: Bearer token via `Authorization: Bearer {admin_token}` header
- **Role**: Admin role (`admin`)
- **Middleware**: `auth:sanctum` and `admin`

---

## Update Order Status

Update the status of an order. The system validates status transitions to ensure they follow the proper order workflow.

### Endpoint
```
PUT /api/orders/{order}
```

### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
```

### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `order` | integer | Order ID | Yes |

### Request Body

```json
{
  "status": "processing"
}
```

### Request Body Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | Yes | New order status. Must be one of: `pending`, `processing`, `shipped`, `delivered`, `cancelled` |

### Valid Status Values

- `pending`: Order has been created and is awaiting processing
- `processing`: Order is being prepared for shipment
- `shipped`: Order has been shipped and is in transit
- `delivered`: Order has been successfully delivered to the customer
- `cancelled`: Order has been cancelled (stock is automatically released)

---

## Order Status Flow

Orders follow a strict status flow to ensure data integrity:

```
pending → processing → shipped → delivered
   ↓           ↓           ↓
cancelled   cancelled   cancelled
```

### Valid Status Transitions

| Current Status | Allowed Next Statuses | Notes |
|----------------|----------------------|-------|
| `pending` | `processing`, `cancelled` | Initial state |
| `processing` | `shipped`, `cancelled` | Order being prepared |
| `shipped` | `delivered`, `cancelled` | Order in transit |
| `delivered` | *(none)* | Final state - cannot be changed |
| `cancelled` | *(none)* | Final state - cannot be changed |

### Status Transition Rules

1. **Forward Progression**: Orders can only move forward in the workflow (pending → processing → shipped → delivered)
2. **Cancellation**: Orders can be cancelled from `pending`, `processing`, or `shipped` status
3. **Final States**: Once an order reaches `delivered` or `cancelled`, it cannot be changed
4. **No Backward Transitions**: Orders cannot move backward (e.g., from `delivered` to `processing`)

---

## Request & Response

### Success Response (200 OK)

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
      "email": "john.doe@example.com",
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
}
```

### Response Fields

The response includes the complete updated order object with:
- Order details (ID, order number, status, amounts, etc.)
- Customer information
- All order items with product details

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

Occurs when the request body is invalid or missing required fields.

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "status": [
      "The status field is required."
    ]
  }
}
```

**Common Validation Errors:**
- Missing `status` field
- Invalid status value (not one of the allowed values)

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

### Invalid Status Transition (400 Bad Request)

Occurs when attempting an invalid status transition.

```json
{
  "success": false,
  "message": "Cannot transition from 'delivered' to 'processing'"
}
```

**Common Invalid Transitions:**
- Trying to change a `delivered` order
- Trying to change a `cancelled` order
- Trying to move backward (e.g., `shipped` → `processing`)
- Trying to skip steps (e.g., `pending` → `shipped`)

### Order Not Found (404 Not Found)

Occurs when the specified order ID does not exist.

```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

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

### Server Error (500 Internal Server Error)

Occurs when an unexpected server error happens.

```json
{
  "success": false,
  "message": "Failed to update order status",
  "error": "Database connection error"
}
```

---

## Stock Management

### Automatic Stock Release on Cancellation

When an order is cancelled, the system automatically releases the reserved stock back to inventory:

1. **Stock Reservation**: When an order is created, stock is automatically reserved
2. **Stock Release**: When an order is cancelled, all reserved stock is released back to inventory
3. **Stock Tracking**: Stock release is tracked in the inventory history

### Cancellation Flow

```
Order Status: pending/processing/shipped
    ↓
Status Updated to: cancelled
    ↓
System automatically releases stock for all order items
    ↓
Stock returned to available inventory
```

**Important Notes:**
- Stock is only released when transitioning TO `cancelled` status
- If an order is already `cancelled`, no additional stock operations occur
- Stock release happens within a database transaction to ensure data integrity
- Delivered orders cannot be cancelled (they are in final state)

---

## Example Requests

### cURL Examples

#### Update Order to Processing

```bash
curl -X PUT "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "processing"
  }'
```

#### Update Order to Shipped

```bash
curl -X PUT "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "shipped"
  }'
```

#### Mark Order as Delivered

```bash
curl -X PUT "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "delivered"
  }'
```

#### Cancel an Order

```bash
curl -X PUT "http://your-domain.com/api/orders/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "cancelled"
  }'
```

### JavaScript (Fetch) Example

```javascript
const orderId = 1;
const newStatus = 'processing';
const adminToken = 'your-admin-token';

fetch(`http://your-domain.com/api/orders/${orderId}`, {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${adminToken}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    status: newStatus
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Order status updated:', data.data.status);
  } else {
    console.error('Error:', data.message);
  }
})
.catch(error => {
  console.error('Request failed:', error);
});
```

### PHP (Guzzle) Example

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://your-domain.com/api/',
    'headers' => [
        'Authorization' => 'Bearer {admin_token}',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ]
]);

$response = $client->put('orders/1', [
    'json' => [
        'status' => 'processing'
    ]
]);

$result = json_decode($response->getBody(), true);
if ($result['success']) {
    echo "Order status updated to: " . $result['data']['status'];
}
```

### Python (Requests) Example

```python
import requests

url = "http://your-domain.com/api/orders/1"
headers = {
    "Authorization": "Bearer {admin_token}",
    "Content-Type": "application/json",
    "Accept": "application/json"
}
data = {
    "status": "processing"
}

response = requests.put(url, json=data, headers=headers)
result = response.json()

if result.get('success'):
    print(f"Order status updated to: {result['data']['status']}")
else:
    print(f"Error: {result.get('message')}")
```

---

## Example Use Cases

### 1. Process a Pending Order

When an admin starts processing an order:

```bash
# Order is in 'pending' status
curl -X PUT "http://your-domain.com/api/orders/123" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "processing"}'
```

### 2. Mark Order as Shipped

After preparing and shipping the order:

```bash
# Order is in 'processing' status
curl -X PUT "http://your-domain.com/api/orders/123" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "shipped"}'
```

### 3. Confirm Delivery

When the order is delivered to the customer:

```bash
# Order is in 'shipped' status
curl -X PUT "http://your-domain.com/api/orders/123" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "delivered"}'
```

### 4. Cancel an Order

When a customer requests cancellation or order cannot be fulfilled:

```bash
# Order can be in 'pending', 'processing', or 'shipped' status
curl -X PUT "http://your-domain.com/api/orders/123" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"status": "cancelled"}'

# Stock is automatically released back to inventory
```

---

## Status Transition Examples

### Valid Transitions

✅ **Pending → Processing**
```json
{"status": "processing"}
```

✅ **Processing → Shipped**
```json
{"status": "shipped"}
```

✅ **Shipped → Delivered**
```json
{"status": "delivered"}
```

✅ **Pending → Cancelled**
```json
{"status": "cancelled"}
```

✅ **Processing → Cancelled**
```json
{"status": "cancelled"}
```

✅ **Shipped → Cancelled**
```json
{"status": "cancelled"}
```

### Invalid Transitions

❌ **Delivered → Processing**
```json
{
  "success": false,
  "message": "Cannot transition from 'delivered' to 'processing'"
}
```

❌ **Cancelled → Processing**
```json
{
  "success": false,
  "message": "Cannot transition from 'cancelled' to 'processing'"
}
```

❌ **Pending → Shipped** (skipping processing)
```json
{
  "success": false,
  "message": "Cannot transition from 'pending' to 'shipped'"
}
```

❌ **Shipped → Processing** (backward transition)
```json
{
  "success": false,
  "message": "Cannot transition from 'shipped' to 'processing'"
}
```

---

## Best Practices

1. **Validate Status Before Update**: Check the current order status before attempting to update
2. **Handle Errors Gracefully**: Always handle validation errors and invalid transitions
3. **Use Transactions**: The API uses database transactions internally for data integrity
4. **Monitor Stock**: Be aware that cancelling orders releases stock back to inventory
5. **Audit Trail**: All status changes are logged for audit purposes
6. **User Feedback**: Provide clear feedback to users about status changes
7. **Status Validation**: Validate status transitions on the frontend before making API calls

---

## Integration Notes

### Frontend Integration

When building a frontend interface for order management:

1. **Status Dropdown**: Only show valid next statuses based on current status
2. **Disabled States**: Disable status options that are not valid transitions
3. **Confirmation Dialogs**: Show confirmation for critical actions (cancellation, delivery)
4. **Real-time Updates**: Consider polling or WebSocket updates for order status changes
5. **Error Handling**: Display user-friendly error messages for invalid transitions

### Backend Integration

When integrating with other systems:

1. **Webhooks**: Consider implementing webhooks for status change notifications
2. **Event Logging**: Log all status changes for audit purposes
3. **Email Notifications**: Send email notifications to customers on status changes
4. **Inventory Sync**: Stock release happens automatically, but monitor inventory levels
5. **Reporting**: Track status change history for analytics

---

## Related Endpoints

- **Get Order Details**: `GET /api/orders/{order}` - View order details
- **List All Orders**: `GET /api/orders` - Get paginated list of all orders
- **Order Statistics**: `GET /api/orders/stats` - Get order statistics
- **Delete Order**: `DELETE /api/orders/{order}` - Delete an order

---

## Support

For issues or questions regarding this API, please contact the development team or refer to the main [Order API Documentation](./ORDER_API_DOCUMENTATION.md).

---

## Changelog

- **v1.0** (2025-01-15): Initial API documentation
  - Status transition validation
  - Automatic stock release on cancellation
  - Comprehensive error handling

