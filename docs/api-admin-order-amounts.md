# API Documentation: Admin Order Amount Management

## Overview
This document describes the Admin Order Amount Management API endpoint. This endpoint allows administrators to update order amounts (subtotal, discount, shipping, tax, etc.) for orders, particularly useful for manual payment orders where amounts need to be set by admin after order creation.

**Base URL**: `/api/orders/{order}/amounts`

**Authentication**: All endpoints require admin authentication via Bearer token.

---

## Endpoint

### Update Order Amounts

**Endpoint**: `PUT /api/orders/{order}/amounts`

**Description**: Updates the financial amounts of an order. This is particularly useful for manual payment orders where the customer provides a transaction number but the amounts need to be verified and set by the admin.

**Note**: When amounts are updated, if the order's `payment_status` is `pending`, it will automatically be updated to `paid` and the `paid_at` timestamp will be set to the current time.

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**URL Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer/string | Yes | Order ID or order number |

**Request Body**:

All fields are optional, but at least one field must be provided. The total amount will be automatically recalculated based on the provided values.

| Field | Type | Required | Description | Validation |
|-------|------|----------|-------------|------------|
| `subtotal` | decimal | No | Order subtotal (before discount) | Must be >= 0 |
| `discount_amount` | decimal | No | Discount amount applied | Must be >= 0 |
| `shipping_cost` | decimal | No | Shipping cost | Must be >= 0 |
| `tax_amount` | decimal | No | Tax amount | Must be >= 0 |
| `tax_rate` | decimal | No | Tax rate percentage | Must be >= 0 and <= 100 |
| `tax_inclusive` | boolean | No | Whether tax is included in prices | true/false |

**Calculation Logic**:
- Total Amount = (Subtotal - Discount Amount) + Shipping Cost + Tax Amount (if tax is not inclusive)
- If `tax_inclusive` is true, tax is already included in the subtotal and won't be added again

**Example Request**:
```bash
PUT /api/orders/123/amounts
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "subtotal": 5000.00,
  "discount_amount": 200.00,
  "shipping_cost": 150.00,
  "tax_amount": 480.00,
  "tax_rate": 10.00,
  "tax_inclusive": false
}
```

**Example Request (Partial Update)**:
```bash
PUT /api/orders/123/amounts
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "subtotal": 5000.00,
  "shipping_cost": 150.00
}
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "message": "Order amounts updated successfully",
  "order": {
    "id": 123,
    "order_number": "ORD-ABC123-1234567890",
    "customer_id": 45,
    "subtotal": 5000.00,
    "discount_amount": 200.00,
    "shipping_cost": 150.00,
    "shipping_method": "air",
    "tax_amount": 480.00,
    "tax_rate": 10.00,
    "tax_inclusive": false,
    "total_amount": 5430.00,
    "status": "pending",
    "payment_method": "manual",
    "payment_status": "paid",
    "paid_at": "2024-01-15T11:45:00.000000Z",
    "transaction_number": "AS123423",
    "payment_receipt_image": "/storage/payment_receipts/receipt_1234567890_abc123.jpg",
    "shipping_address": {
      "full_name": "Customer",
      "phone": "01672164422",
      "address_line1": "Hayen aosidj foasdijf asdf asoidjfa oi",
      "address_line2": "Hayen aosidj foasdijf asdf",
      "city": "Dhaka",
      "state": "Dhaka",
      "postal_code": "1212",
      "country": "Bangladesh"
    },
    "notes": null,
    "customer": {
      "id": 45,
      "name": "John Doe",
      "phone": "01672164422",
      "email": "john@example.com"
    },
    "orderItems": [
      {
        "id": 1,
        "product_id": 10,
        "product_name": "Product Name",
        "quantity": 2,
        "price": 2500.00,
        "total": 5000.00
      }
    ],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T11:45:00.000000Z"
  }
}
```

**Error Responses**:

**400 Bad Request - Validation Error**:
```json
{
  "success": false,
  "message": "Subtotal must be a non-negative number"
}
```

**400 Bad Request - Empty Request**:
```json
{
  "success": false,
  "message": "At least one amount field must be provided"
}
```

**422 Unprocessable Entity - Validation Failed**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subtotal": [
      "The subtotal must be a number."
    ],
    "tax_rate": [
      "The tax rate must not be greater than 100."
    ]
  }
}
```

**404 Not Found**:
```json
{
  "success": false,
  "message": "No query results for model [App\\Models\\Order] 999"
}
```

**401 Unauthorized**:
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden**:
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

---

## Use Cases

### Use Case 1: Update Amounts for Manual Payment Order

When a customer creates an order with manual payment and provides a transaction number, the order is created with zero amounts. Admin needs to verify the payment and update the amounts.

**Step 1: Customer Creates Order**
```bash
POST /api/customer/orders/create
{
  "payment_method": "manual",
  "transaction_number": "AS123423",
  "shipping_address": {...},
  "shipping_method": "air",
  "payment_receipt": (file)
}
```

**Step 2: Admin Updates Amounts**
```bash
PUT /api/orders/{order_id}/amounts
{
  "subtotal": 5000.00,
  "discount_amount": 0,
  "shipping_cost": 150.00,
  "tax_amount": 480.00,
  "tax_rate": 10.00,
  "tax_inclusive": false
}
```

### Use Case 2: Adjust Shipping Cost

Admin needs to update only the shipping cost for an existing order.

```bash
PUT /api/orders/123/amounts
{
  "shipping_cost": 200.00
}
```

The total amount will be automatically recalculated.

### Use Case 3: Apply Discount

Admin wants to apply a discount to an order.

```bash
PUT /api/orders/123/amounts
{
  "discount_amount": 500.00
}
```

---

## Notes

1. **Automatic Total Calculation**: The `total_amount` is automatically recalculated based on the formula:
   - `total_amount = (subtotal - discount_amount) + shipping_cost + tax_amount` (if tax is not inclusive)
   - `total_amount = (subtotal - discount_amount) + shipping_cost` (if tax is inclusive)

2. **Automatic Payment Status Update**: When amounts are updated, if the order's `payment_status` is `pending`, it will automatically be updated to `paid` and the `paid_at` timestamp will be set. This indicates that the admin has verified and confirmed the payment.

3. **Partial Updates**: You can update only specific fields. Fields not provided will retain their current values.

4. **Order Creation Behavior**: When a customer creates an order with `payment_method: "manual"` and provides a `transaction_number`, the order is created with all amounts set to 0, allowing admin to set the correct amounts later.

5. **Transaction Safety**: All updates are performed within a database transaction to ensure data consistency.

6. **Logging**: All amount updates are logged for audit purposes.

---

## Related Endpoints

- `GET /api/orders/{order}` - Get order details
- `PUT /api/orders/{order}` - Update order status
- `POST /api/customer/orders/create` - Create order from cart

---

## Changelog

- **2026-01-03**: Initial version - Added admin order amount update endpoint

