# Coupon Apply Documentation for Customers

## Overview

This documentation describes how customers can apply coupons to their orders in the ecommerce backend. The coupon system allows customers to validate and apply discount codes before placing an order.

## Table of Contents

1. [Available Coupons](#available-coupons)
2. [Validate Coupon](#validate-coupon)
3. [Coupon Types](#coupon-types)
4. [Validation Rules](#validation-rules)
5. [Error Handling](#error-handling)
6. [Examples](#examples)

---

## Available Coupons

Get a list of all available coupons that customers can use.

### Endpoint

```
GET /api/coupons/available
```

### Authentication

- **Public endpoint** - No authentication required
- If authenticated as a customer, the response filters out coupons that the customer has already used up

### Response

**Success Response (200 OK)**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off",
      "description": "Get 20% off on all products",
      "type": "percentage",
      "discount_value": 20,
      "minimum_purchase": 100.00,
      "maximum_discount": 50.00,
      "valid_until": "2024-12-31"
    },
    {
      "id": 2,
      "code": "FLAT50",
      "name": "Flat ₹50 Off",
      "description": "Get ₹50 off on orders above ₹200",
      "type": "fixed",
      "discount_value": 50,
      "minimum_purchase": 200.00,
      "maximum_discount": null,
      "valid_until": "2024-12-31"
    }
  ]
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Coupon ID |
| `code` | string | Coupon code to use |
| `name` | string | Coupon name |
| `description` | string | Coupon description |
| `type` | string | Discount type: `percentage` or `fixed` |
| `discount_value` | decimal | Discount value (percentage or fixed amount) |
| `minimum_purchase` | decimal | Minimum purchase amount required (null if none) |
| `maximum_discount` | decimal | Maximum discount cap for percentage coupons (null if none) |
| `valid_until` | date | Expiry date of the coupon |

---

## Validate Coupon

Validate a coupon code and calculate the discount for specific order items.

### Endpoint

```
POST /api/coupons/validate
```

### Authentication

- **Customer authentication required**
- Requires `customer` middleware
- Customer must be logged in

### Request Body

```json
{
  "code": "SAVE20",
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

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `code` | string | Yes | Coupon code (max 50 characters) |
| `items` | array | Yes | Array of order items (minimum 1 item) |
| `items[].product_id` | integer | Yes | Product ID (must exist in products table) |
| `items[].quantity` | integer | Yes | Quantity (minimum 1) |

### Response

**Success Response (200 OK)**

```json
{
  "success": true,
  "message": "Coupon is valid",
  "data": {
    "coupon": {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off",
      "description": "Get 20% off on all products",
      "type": "percentage",
      "discount_value": 20
    },
    "subtotal": 500.00,
    "discount_amount": 100.00,
    "total_after_discount": 400.00
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `coupon.id` | integer | Coupon ID |
| `coupon.code` | string | Coupon code |
| `coupon.name` | string | Coupon name |
| `coupon.description` | string | Coupon description |
| `coupon.type` | string | Discount type: `percentage` or `fixed` |
| `coupon.discount_value` | decimal | Discount value |
| `subtotal` | decimal | Total amount of applicable items before discount |
| `discount_amount` | decimal | Calculated discount amount |
| `total_after_discount` | decimal | Final amount after applying discount |

---

## Coupon Types

### Percentage Discount

- **Type**: `percentage`
- **Calculation**: `discount = (subtotal × discount_value) / 100`
- **Maximum Discount**: If `maximum_discount` is set, the discount is capped at that amount
- **Example**: 20% off with max ₹50 discount
  - Subtotal: ₹500
  - Discount: min(₹500 × 20%, ₹50) = ₹50

### Fixed Discount

- **Type**: `fixed`
- **Calculation**: `discount = discount_value`
- **Maximum Discount**: Not applicable for fixed discounts
- **Example**: Flat ₹50 off
  - Subtotal: ₹500
  - Discount: ₹50

---

## Validation Rules

The system validates coupons based on the following rules:

### 1. Coupon Existence
- Coupon code must exist in the database

### 2. Coupon Validity
- Coupon must be active (`is_active = true`)
- Current date must be within `valid_from` and `valid_until` range (if set)
- Total usage count must be less than `usage_limit` (if set)

### 3. Customer Eligibility
- Customer must not have exceeded `usage_limit_per_customer` (if set)
- If `first_order_only = true`, customer must not have any previous orders

### 4. Product/Category Eligibility
- Coupon must apply to at least one product in the cart
- If `applicable_products` is set, product must be in the list
- If `applicable_categories` is set, product's category must be in the list
- If both are null, coupon applies to all products

### 5. Minimum Purchase
- If `minimum_purchase` is set, subtotal of applicable items must meet or exceed this amount

### 6. Discount Calculation
- Discount cannot exceed the order subtotal
- For percentage discounts, maximum discount cap is applied if set

---

## Error Handling

### Validation Errors (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "code": ["The code field is required."],
    "items": ["The items field is required."],
    "items.0.product_id": ["The selected items.0.product_id is invalid."]
  }
}
```

### Business Logic Errors (400 Bad Request)

**Coupon Not Found**
```json
{
  "success": false,
  "message": "Coupon not found"
}
```

**Coupon Expired or Invalid**
```json
{
  "success": false,
  "message": "Coupon is not valid or has expired"
}
```

**Customer Not Eligible**
```json
{
  "success": false,
  "message": "Coupon cannot be used by this customer"
}
```

**Minimum Purchase Not Met**
```json
{
  "success": false,
  "message": "Minimum purchase amount of 100.00 required for this coupon"
}
```

### Authentication Errors (401 Unauthorized)

```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

---

## Examples

### Example 1: Validate Percentage Coupon

**Request:**
```bash
POST /api/coupons/validate
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "code": "SAVE20",
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
  "message": "Coupon is valid",
  "data": {
    "coupon": {
      "id": 1,
      "code": "SAVE20",
      "name": "20% Off",
      "description": "Get 20% off on all products",
      "type": "percentage",
      "discount_value": 20
    },
    "subtotal": 1000.00,
    "discount_amount": 200.00,
    "total_after_discount": 800.00
  }
}
```

### Example 2: Validate Fixed Discount Coupon

**Request:**
```bash
POST /api/coupons/validate
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "code": "FLAT50",
  "items": [
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Coupon is valid",
  "data": {
    "coupon": {
      "id": 2,
      "code": "FLAT50",
      "name": "Flat ₹50 Off",
      "description": "Get ₹50 off on orders above ₹200",
      "type": "fixed",
      "discount_value": 50
    },
    "subtotal": 300.00,
    "discount_amount": 50.00,
    "total_after_discount": 250.00
  }
}
```

### Example 3: Error - Minimum Purchase Not Met

**Request:**
```bash
POST /api/coupons/validate
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "code": "SAVE20",
  "items": [
    {
      "product_id": 1,
      "quantity": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": false,
  "message": "Minimum purchase amount of 100.00 required for this coupon"
}
```

### Example 4: Error - Coupon Already Used

**Request:**
```bash
POST /api/coupons/validate
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "code": "FIRSTORDER",
  "items": [
    {
      "product_id": 1,
      "quantity": 1
    }
  ]
}
```

**Response:**
```json
{
  "success": false,
  "message": "Coupon cannot be used by this customer"
}
```

---

## Integration Flow

### Step-by-Step Process

1. **Get Available Coupons** (Optional)
   - Call `GET /api/coupons/available` to show available coupons to the customer
   - Display coupon codes and descriptions in the UI

2. **Customer Enters Coupon Code**
   - Customer enters coupon code in the checkout form
   - Frontend collects current cart items

3. **Validate Coupon**
   - Call `POST /api/coupons/validate` with coupon code and cart items
   - Include customer authentication token

4. **Handle Response**
   - If successful, display discount amount and updated total
   - If error, show appropriate error message to customer

5. **Apply to Order**
   - When creating order, include the validated coupon code
   - The order service will apply the coupon and record usage

---

## Notes

- Coupon validation is performed **before** order creation
- The discount calculation only includes items that are eligible for the coupon
- If a coupon has product/category restrictions, only matching items are included in the subtotal
- The coupon usage is recorded when the order is successfully created
- Customers can validate multiple coupons before placing an order, but only one coupon can be applied per order

---

## Related Endpoints

- **Get Available Coupons**: `GET /api/coupons/available` (Public)
- **Create Order**: `POST /api/orders` (Customer - applies the validated coupon)
- **Get Customer Orders**: `GET /api/orders` (Customer/Admin)

---

## Support

For issues or questions regarding coupon functionality, please contact the development team or refer to the main API documentation.

