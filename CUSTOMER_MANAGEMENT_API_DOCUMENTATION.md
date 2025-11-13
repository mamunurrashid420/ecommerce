# Customer Management API Documentation

Complete documentation for the Admin Customer Management API endpoints.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Endpoints](#endpoints)
   - [List Customers](#list-customers)
   - [Get Customer Details](#get-customer-details)
   - [Search Customers](#search-customers)
   - [Ban Customer](#ban-customer)
   - [Unban Customer](#unban-customer)
   - [Suspend Customer](#suspend-customer)
   - [Unsuspend Customer](#unsuspend-customer)
   - [Get Customer Order History](#get-customer-order-history)
4. [Response Structures](#response-structures)
5. [Error Responses](#error-responses)
6. [Customer Status Behavior](#customer-status-behavior)
7. [Example Use Cases](#example-use-cases)

---

## Overview

The Customer Management API allows administrators to manage customers, including viewing customer information, searching customers, banning/suspending customers, and viewing customer order history. Banned and suspended customers are automatically prevented from adding items to cart and placing orders.

**Base URL**: `http://your-domain.com/api`

---

## Authentication

**Required**: Admin authentication
- **Authentication Method**: Bearer token via `Authorization: Bearer {admin_token}` header
- **Role**: Admin role (`admin`)
- **Middleware**: `auth:sanctum` and `admin`

---

## Endpoints

### List Customers

Retrieve a paginated list of customers with optional search and filtering.

#### Endpoint
```
GET /api/customers
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### Query Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `search` | string | Search by name, email, or phone | No |
| `is_banned` | boolean | Filter by banned status (true/false) | No |
| `is_suspended` | boolean | Filter by suspended status (true/false) | No |
| `role` | string | Filter by role (admin/customer) | No |
| `sort_by` | string | Sort field (default: created_at) | No |
| `sort_order` | string | Sort direction (asc/desc, default: desc) | No |
| `per_page` | integer | Items per page (default: 15) | No |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "address": "123 Main St, City, State 12345",
      "role": "customer",
      "is_banned": false,
      "is_suspended": false,
      "banned_at": null,
      "suspended_at": null,
      "ban_reason": null,
      "suspend_reason": null,
      "orders_count": 5,
      "created_at": "2025-01-10T08:00:00.000000Z",
      "updated_at": "2025-01-10T08:00:00.000000Z"
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
# Get all customers
curl -X GET "http://your-domain.com/api/customers" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# Search customers
curl -X GET "http://your-domain.com/api/customers?search=john" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# Filter banned customers
curl -X GET "http://your-domain.com/api/customers?is_banned=true" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# Filter suspended customers
curl -X GET "http://your-domain.com/api/customers?is_suspended=true" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

### Get Customer Details

Retrieve detailed information about a specific customer.

#### Endpoint
```
GET /api/customers/{customer}
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, State 12345",
    "role": "customer",
    "is_banned": false,
    "is_suspended": false,
    "banned_at": null,
    "suspended_at": null,
    "ban_reason": null,
    "suspend_reason": null,
    "orders_count": 5,
    "created_at": "2025-01-10T08:00:00.000000Z",
    "updated_at": "2025-01-10T08:00:00.000000Z"
  }
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/customers/1" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

### Search Customers

Quick search for customers by name, email, or phone number. Returns up to 20 results.

#### Endpoint
```
GET /api/customers-search
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### Query Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `query` | string | Search term (min 1 character) | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "is_banned": false,
      "is_suspended": false,
      "orders_count": 5
    }
  ],
  "count": 1
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/customers-search?query=john" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

### Ban Customer

Ban a customer account. Banned customers cannot add items to cart or place orders.

#### Endpoint
```
POST /api/customers/{customer}/ban
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Content-Type: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Request Body

```json
{
  "reason": "Violation of terms of service"
}
```

| Field | Type | Description | Required |
|------|------|-------------|----------|
| `reason` | string | Reason for banning (max 1000 characters) | No |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Customer banned successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "is_banned": true,
    "banned_at": "2025-01-15T10:30:00.000000Z",
    "ban_reason": "Violation of terms of service"
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Customer is already banned"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/customers/1/ban" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Violation of terms of service"
  }'
```

---

### Unban Customer

Remove ban from a customer account.

#### Endpoint
```
POST /api/customers/{customer}/unban
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Customer unbanned successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "is_banned": false,
    "banned_at": null,
    "ban_reason": null
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Customer is not banned"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/customers/1/unban" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

### Suspend Customer

Suspend a customer account. Suspended customers cannot add items to cart or place orders.

#### Endpoint
```
POST /api/customers/{customer}/suspend
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Content-Type: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Request Body

```json
{
  "reason": "Temporary suspension pending investigation"
}
```

| Field | Type | Description | Required |
|------|------|-------------|----------|
| `reason` | string | Reason for suspension (max 1000 characters) | No |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Customer suspended successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "is_suspended": true,
    "suspended_at": "2025-01-15T10:30:00.000000Z",
    "suspend_reason": "Temporary suspension pending investigation"
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Customer is already suspended"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/customers/1/suspend" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Temporary suspension pending investigation"
  }'
```

---

### Unsuspend Customer

Remove suspension from a customer account.

#### Endpoint
```
POST /api/customers/{customer}/unsuspend
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Customer unsuspended successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "is_suspended": false,
    "suspended_at": null,
    "suspend_reason": null
  }
}
```

#### Error Response (400 Bad Request)

```json
{
  "success": false,
  "message": "Customer is not suspended"
}
```

#### Example Request

```bash
curl -X POST "http://your-domain.com/api/customers/1/unsuspend" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

### Get Customer Order History

Retrieve order history for a specific customer with optional filtering.

#### Endpoint
```
GET /api/customers/{customer}/orders
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `customer` | integer | Customer ID | Yes |

#### Query Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `status` | string | Filter by order status | No |
| `search` | string | Search by order number | No |
| `date_from` | date | Filter orders from date (YYYY-MM-DD) | No |
| `date_to` | date | Filter orders to date (YYYY-MM-DD) | No |
| `sort_by` | string | Sort field (default: created_at) | No |
| `sort_order` | string | Sort direction (asc/desc, default: desc) | No |
| `per_page` | integer | Items per page (default: 15) | No |

#### Success Response (200 OK)

```json
{
  "success": true,
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890"
  },
  "orders": [
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
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 5
  }
}
```

#### Example Requests

```bash
# Get all orders for customer
curl -X GET "http://your-domain.com/api/customers/1/orders" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# Filter by status
curl -X GET "http://your-domain.com/api/customers/1/orders?status=pending" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"

# Filter by date range
curl -X GET "http://your-domain.com/api/customers/1/orders?date_from=2025-01-01&date_to=2025-01-31" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json"
```

---

## Response Structures

### Customer Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Customer ID |
| `name` | string | Customer name |
| `email` | string | Customer email |
| `phone` | string | Customer phone number |
| `address` | string | Customer address |
| `role` | string | Customer role (admin/customer) |
| `is_banned` | boolean | Whether customer is banned |
| `is_suspended` | boolean | Whether customer is suspended |
| `banned_at` | datetime | Date/time when customer was banned (null if not banned) |
| `suspended_at` | datetime | Date/time when customer was suspended (null if not suspended) |
| `ban_reason` | string | Reason for ban (null if not banned) |
| `suspend_reason` | string | Reason for suspension (null if not suspended) |
| `orders_count` | integer | Number of orders (when using withCount) |
| `created_at` | datetime | Account creation date |
| `updated_at` | datetime | Last update date |

---

## Error Responses

### 400 Bad Request

```json
{
  "success": false,
  "message": "Error message here"
}
```

Common error messages:
- "Customer is already banned"
- "Customer is not banned"
- "Customer is already suspended"
- "Customer is not suspended"

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "Failed to retrieve customer",
  "error": "No query results for model [App\\Models\\Customer] {id}"
}
```

### 422 Validation Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "reason": [
      "The reason field must not be greater than 1000 characters."
    ]
  }
}
```

### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Failed to retrieve customers",
  "error": "Error details"
}
```

---

## Customer Status Behavior

### Banned Customers

When a customer is banned:
- `is_banned` is set to `true`
- `banned_at` is set to the current timestamp
- `ban_reason` stores the reason (if provided)
- Customer **cannot** add items to cart
- Customer **cannot** place orders
- Customer receives error message: "Your account has been banned. Reason: {reason}"

### Suspended Customers

When a customer is suspended:
- `is_suspended` is set to `true`
- `suspended_at` is set to the current timestamp
- `suspend_reason` stores the reason (if provided)
- Customer **cannot** add items to cart
- Customer **cannot** place orders
- Customer receives error message: "Your account has been suspended. Reason: {reason}"

### Unbanning/Unsuspending

When a customer is unbanned or unsuspended:
- The respective status flag is set to `false`
- The timestamp and reason fields are set to `null`
- Customer can resume normal operations (add to cart, place orders)

### Status Priority

- If a customer is both banned and suspended, the ban takes precedence in error messages
- Both statuses prevent purchases independently

---

## Example Use Cases

### Use Case 1: Ban a Customer for Violation

**Scenario**: Admin needs to ban a customer who violated terms of service.

```bash
# Step 1: Search for the customer
curl -X GET "http://your-domain.com/api/customers-search?query=john.doe@example.com" \
  -H "Authorization: Bearer {admin_token}"

# Step 2: Ban the customer
curl -X POST "http://your-domain.com/api/customers/1/ban" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Violation of terms of service - multiple fraudulent orders"
  }'
```

### Use Case 2: Suspend Customer Temporarily

**Scenario**: Admin needs to suspend a customer pending investigation.

```bash
# Suspend customer
curl -X POST "http://your-domain.com/api/customers/1/suspend" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Temporary suspension pending investigation of payment issues"
  }'

# Later, unsuspend after investigation
curl -X POST "http://your-domain.com/api/customers/1/unsuspend" \
  -H "Authorization: Bearer {admin_token}"
```

### Use Case 3: View Customer Order History

**Scenario**: Admin needs to review a customer's order history to make a decision.

```bash
# Get customer details
curl -X GET "http://your-domain.com/api/customers/1" \
  -H "Authorization: Bearer {admin_token}"

# Get customer order history
curl -X GET "http://your-domain.com/api/customers/1/orders?status=pending&sort_by=created_at&sort_order=desc" \
  -H "Authorization: Bearer {admin_token}"
```

### Use Case 4: List All Banned Customers

**Scenario**: Admin needs to review all banned customers.

```bash
curl -X GET "http://your-domain.com/api/customers?is_banned=true&sort_by=banned_at&sort_order=desc" \
  -H "Authorization: Bearer {admin_token}"
```

### Use Case 5: Search and Manage Customer

**Scenario**: Admin needs to find a customer by phone number and manage their account.

```bash
# Search for customer
curl -X GET "http://your-domain.com/api/customers-search?query=+1234567890" \
  -H "Authorization: Bearer {admin_token}"

# View customer details
curl -X GET "http://your-domain.com/api/customers/1" \
  -H "Authorization: Bearer {admin_token}"

# View order history
curl -X GET "http://your-domain.com/api/customers/1/orders" \
  -H "Authorization: Bearer {admin_token}"

# Take action (ban/suspend) if needed
curl -X POST "http://your-domain.com/api/customers/1/ban" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Fraudulent activity detected"}'
```

---

## Notes

1. **Authentication**: All endpoints require admin authentication via Bearer token.

2. **Pagination**: List endpoints support pagination with `per_page` parameter (default: 15).

3. **Search**: The search functionality searches across name, email, and phone fields.

4. **Status Checks**: The system automatically checks customer status (banned/suspended) when:
   - Customer attempts to validate purchase items
   - Customer attempts to create an order
   - Customer attempts to get purchase summary (authenticated)

5. **Error Messages**: Banned and suspended customers receive clear error messages explaining why they cannot make purchases.

6. **Audit Trail**: All ban and suspension actions record timestamps and reasons for future reference.

7. **Reversibility**: Both bans and suspensions can be reversed using the unban/unsuspend endpoints.

---

## Support

For issues or questions regarding the Customer Management API, please contact the development team.

