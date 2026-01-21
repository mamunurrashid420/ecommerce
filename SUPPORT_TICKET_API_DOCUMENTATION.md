# Support Ticket API Documentation

## Overview

The Support Ticket system allows customers to create and manage support tickets, and enables admins to respond and manage all tickets. The system includes real-time messaging, status tracking, priority management, and navbar notifications.

## Authentication

All endpoints require authentication using Laravel Sanctum:
- **Customer Authentication**: Bearer token from customer login
- **Admin Authentication**: Bearer token from admin login
- **Middleware**: `auth.any` (accepts both customer and admin tokens)

**Header Required**:
```
Authorization: Bearer {token}
```

---

## Table of Contents

1. [Create Support Ticket](#1-create-support-ticket)
2. [Get Support Tickets List](#2-get-support-tickets-list)
3. [Get Single Ticket Details](#3-get-single-ticket-details)
4. [Get Navbar Ticket Count](#4-get-navbar-ticket-count)
5. [Get Latest Tickets for Navbar](#5-get-latest-tickets-for-navbar)
6. [Get Ticket Messages](#6-get-ticket-messages)
7. [Send Message to Ticket](#7-send-message-to-ticket)
8. [Mark Message as Read](#8-mark-message-as-read)
9. [Cancel Order](#9-cancel-order)

---

## 1. Create Support Ticket

Create a new support ticket (Customer only).

### Endpoint
```
POST /api/support-tickets
```

### Authentication
- **Required**: Yes (Customer only)
- **Middleware**: None (public customer route)

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `subject` | string | Yes | max:255 | Ticket subject/title |
| `description` | string | Yes | min:10, max:5000 | Detailed description of the issue |
| `priority` | string | No | enum: low, medium, high, urgent | Default: medium |
| `category` | string | No | enum: technical, billing, order, product, account, other | Default: other |

### Request Example

```json
{
  "subject": "Issue with order #12345",
  "description": "I received the wrong product in my order. I ordered a blue shirt but received a red one.",
  "priority": "high",
  "category": "order"
}
```

### Response Success (201 Created)

```json
{
  "success": true,
  "message": "Support ticket created successfully",
  "data": {
    "id": 1,
    "ticket_number": "TKT-65A3F2B1C4D5E",
    "customer_id": 5,
    "assigned_to": null,
    "subject": "Issue with order #12345",
    "description": "I received the wrong product in my order...",
    "status": "open",
    "priority": "high",
    "category": "order",
    "message_count": 0,
    "is_customer_read": true,
    "is_admin_read": false,
    "resolved_at": null,
    "closed_at": null,
    "last_replied_at": null,
    "last_replied_by": null,
    "created_at": "2026-01-22T10:30:00.000000Z",
    "updated_at": "2026-01-22T10:30:00.000000Z",
    "customer": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

### Response Error (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subject": ["The subject field is required."],
    "description": ["The description must be at least 10 characters."]
  }
}
```

### Status Codes
- `201` - Ticket created successfully
- `422` - Validation error
- `401` - Unauthorized (not authenticated)
- `400` - Bad request

---

## 2. Get Support Tickets List

Get list of support tickets. Returns customer's tickets for customers, all tickets for admins.

### Endpoint
```
GET /api/support-tickets
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `status` | string | No | Filter by status: open, in_progress, resolved, closed |
| `priority` | string | No | Filter by priority: low, medium, high, urgent |
| `category` | string | No | Filter by category: technical, billing, order, product, account, other |
| `search` | string | No | Search in subject and description |
| `customer_id` | integer | No | Filter by customer (Admin only) |
| `assigned_to` | integer | No | Filter by assigned admin (Admin only) |
| `date_from` | date | No | Filter from date (Admin only) |
| `date_to` | date | No | Filter to date (Admin only) |
| `sort_by` | string | No | Sort field (default: created_at) |
| `sort_order` | string | No | Sort order: asc, desc (default: desc) |
| `per_page` | integer | No | Items per page (default: 15) |

### Request Example

```
GET /api/support-tickets?status=open&priority=high&per_page=10
```

### Response Success (200 OK)

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "ticket_number": "TKT-65A3F2B1C4D5E",
        "customer_id": 5,
        "assigned_to": 2,
        "subject": "Issue with order #12345",
        "description": "I received the wrong product...",
        "status": "open",
        "priority": "high",
        "category": "order",
        "message_count": 3,
        "is_customer_read": false,
        "is_admin_read": true,
        "resolved_at": null,
        "closed_at": null,
        "last_replied_at": "2026-01-22T11:00:00.000000Z",
        "last_replied_by": 2,
        "created_at": "2026-01-22T10:30:00.000000Z",
        "updated_at": "2026-01-22T11:00:00.000000Z",
        "customer": {
          "id": 5,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "assigned_admin": {
          "id": 2,
          "name": "Admin User",
          "email": "admin@example.com"
        }
      }
    ],
    "first_page_url": "http://api.example.com/api/support-tickets?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://api.example.com/api/support-tickets?page=1",
    "next_page_url": null,
    "path": "http://api.example.com/api/support-tickets",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### Status Codes
- `200` - Success
- `401` - Unauthorized
- `500` - Server error

---

## 3. Get Single Ticket Details

Get detailed information about a specific support ticket.

### Endpoint
```
GET /api/support-tickets/{ticket}
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`
- **Access Control**:
  - Customers can only access their own tickets
  - Admins can access any ticket

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `ticket` | integer | Yes | Ticket ID |

### Request Example

```
GET /api/support-tickets/1
```

### Response Success (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "ticket_number": "TKT-65A3F2B1C4D5E",
    "customer_id": 5,
    "assigned_to": 2,
    "subject": "Issue with order #12345",
    "description": "I received the wrong product in my order. I ordered a blue shirt but received a red one.",
    "status": "in_progress",
    "priority": "high",
    "category": "order",
    "message_count": 5,
    "is_customer_read": true,
    "is_admin_read": true,
    "resolved_at": null,
    "closed_at": null,
    "last_replied_at": "2026-01-22T14:30:00.000000Z",
    "last_replied_by": 2,
    "created_at": "2026-01-22T10:30:00.000000Z",
    "updated_at": "2026-01-22T14:30:00.000000Z",
    "customer": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890"
    },
    "assigned_admin": {
      "id": 2,
      "name": "Admin User",
      "email": "admin@example.com"
    },
    "last_replied_by_user": {
      "id": 2,
      "name": "Admin User"
    }
  }
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized access to this ticket"
}
```

### Response Error (404 Not Found)

```json
{
  "success": false,
  "message": "Ticket not found"
}
```

### Status Codes
- `200` - Success
- `403` - Forbidden (customer accessing another customer's ticket)
- `404` - Ticket not found
- `401` - Unauthorized

---

## 4. Get Navbar Ticket Count

Get ticket count for navbar badge display. Returns unread and unresolved ticket counts.

### Endpoint
```
GET /api/support-tickets/navbar/count
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`

### Request Example

```
GET /api/support-tickets/navbar/count
```

### Response Success - Customer (200 OK)

```json
{
  "success": true,
  "total_unresolved": 3,
  "unread_count": 2,
  "count": 2
}
```

**Note**: `count` shows unread tickets if any exist, otherwise shows total unresolved tickets.

### Response Success - Admin (200 OK)

```json
{
  "success": true,
  "total_tickets": 45,
  "open_tickets": 12,
  "in_progress_tickets": 8,
  "unread_count": 5,
  "count": 5
}
```

### Status Codes
- `200` - Success
- `401` - Unauthorized
- `500` - Server error

---

## 5. Get Latest Tickets for Navbar

Get latest tickets for navbar dropdown display (limited information).

### Endpoint
```
GET /api/support-tickets/navbar/latest
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`

### Query Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `limit` | integer | No | Number of tickets to return (default: 5) |

### Request Example

```
GET /api/support-tickets/navbar/latest?limit=5
```

### Response Success (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ticket_number": "TKT-65A3F2B1C4D5E",
      "subject": "Issue with order #12345",
      "status": "open",
      "priority": "high",
      "is_customer_read": false,
      "created_at": "2026-01-22T10:30:00.000000Z",
      "customer": {
        "id": 5,
        "name": "John Doe"
      }
    },
    {
      "id": 2,
      "ticket_number": "TKT-65A3F2B1C4D5F",
      "subject": "Payment not processed",
      "status": "in_progress",
      "priority": "urgent",
      "is_customer_read": true,
      "created_at": "2026-01-22T09:15:00.000000Z",
      "customer": {
        "id": 8,
        "name": "Jane Smith"
      }
    }
  ]
}
```

### Status Codes
- `200` - Success
- `401` - Unauthorized
- `500` - Server error

---

## 6. Get Ticket Messages

Get all messages for a specific ticket. Automatically marks ticket as read.

### Endpoint
```
GET /api/support-tickets/{ticket}/messages
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`
- **Access Control**:
  - Customers can only access messages from their own tickets
  - Admins can access messages from any ticket

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `ticket` | integer | Yes | Ticket ID |

### Request Example

```
GET /api/support-tickets/1/messages
```

### Response Success (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "ticket_id": 1,
      "customer_id": 5,
      "admin_id": null,
      "message": "I received the wrong product in my order.",
      "sender_type": "customer",
      "is_read": true,
      "read_at": "2026-01-22T10:35:00.000000Z",
      "attachments": null,
      "created_at": "2026-01-22T10:30:00.000000Z",
      "updated_at": "2026-01-22T10:35:00.000000Z",
      "customer": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "admin": null
    },
    {
      "id": 2,
      "ticket_id": 1,
      "customer_id": null,
      "admin_id": 2,
      "message": "I apologize for the inconvenience. We'll send you the correct product right away.",
      "sender_type": "admin",
      "is_read": true,
      "read_at": "2026-01-22T11:05:00.000000Z",
      "attachments": null,
      "created_at": "2026-01-22T11:00:00.000000Z",
      "updated_at": "2026-01-22T11:05:00.000000Z",
      "customer": null,
      "admin": {
        "id": 2,
        "name": "Admin User",
        "email": "admin@example.com"
      }
    }
  ]
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized access to this ticket"
}
```

### Status Codes
- `200` - Success
- `403` - Forbidden
- `404` - Ticket not found
- `401` - Unauthorized

**Side Effect**: Marks the ticket as read by the authenticated user (customer or admin).

---

## 7. Send Message to Ticket

Send a new message/reply to a support ticket.

### Endpoint
```
POST /api/support-tickets/{ticket}/messages
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`
- **Access Control**:
  - Customers can only send messages to their own tickets
  - Admins can send messages to any ticket

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `ticket` | integer | Yes | Ticket ID |

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `message` | string | Yes | min:1, max:5000 | Message content |
| `attachments` | array | No | - | Array of attachment URLs (future feature) |

### Request Example

```json
{
  "message": "Thank you for your help! When can I expect the replacement?"
}
```

### Response Success (201 Created)

```json
{
  "success": true,
  "message": "Message sent successfully",
  "data": {
    "id": 3,
    "ticket_id": 1,
    "customer_id": 5,
    "admin_id": null,
    "message": "Thank you for your help! When can I expect the replacement?",
    "sender_type": "customer",
    "is_read": false,
    "read_at": null,
    "attachments": null,
    "created_at": "2026-01-22T15:00:00.000000Z",
    "updated_at": "2026-01-22T15:00:00.000000Z",
    "customer": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "admin": null
  }
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized access to this ticket"
}
```

### Response Error (422 Validation Error)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "message": ["The message field is required."]
  }
}
```

### Status Codes
- `201` - Message created successfully
- `422` - Validation error
- `403` - Forbidden
- `404` - Ticket not found
- `401` - Unauthorized

### Behavior Notes

**When Customer sends a message:**
- Ticket status remains unchanged (unless it was closed/resolved, then it reopens to "open")
- `is_admin_read` is set to `false`
- `is_customer_read` is set to `true`
- Message count is incremented

**When Admin sends a message:**
- If ticket status is "closed" or "resolved", it changes to "in_progress"
- `is_customer_read` is set to `false`
- `is_admin_read` is set to `true`
- `last_replied_at` and `last_replied_by` are updated
- Message count is incremented

---

## 8. Mark Message as Read

Mark a specific message as read.

### Endpoint
```
PUT /api/support-messages/{message}/read
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`
- **Access Control**:
  - Customers can only mark messages in their own tickets
  - Admins can mark any message as read

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `message` | integer | Yes | Message ID |

### Request Example

```
PUT /api/support-messages/5/read
```

### Response Success (200 OK)

```json
{
  "success": true,
  "message": "Message marked as read"
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### Status Codes
- `200` - Success
- `403` - Forbidden
- `404` - Message not found
- `401` - Unauthorized

---

## 9. Cancel Order

Cancel an order (accessible to both customers and admins).

### Endpoint
```
POST /api/orders/{order}/cancel
```

### Authentication
- **Required**: Yes (Customer or Admin)
- **Middleware**: `auth.any`
- **Access Control**:
  - Customers can only cancel their own orders
  - Admins can cancel any order

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `order` | integer | Yes | Order ID |

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `reason` | string | No | max:500 | Reason for cancellation |

### Request Example

```json
{
  "reason": "Changed my mind about the purchase"
}
```

### Response Success (200 OK)

```json
{
  "success": true,
  "message": "Order cancelled successfully",
  "data": {
    "id": 123,
    "order_number": "ORD-2026-001234",
    "status": "cancelled",
    "cancellation_reason": "Changed my mind about the purchase",
    "cancelled_at": "2026-01-22T15:30:00.000000Z"
  }
}
```

### Response Error (403 Forbidden)

```json
{
  "success": false,
  "message": "You are not authorized to cancel this order"
}
```

### Response Error (400 Bad Request)

```json
{
  "success": false,
  "message": "Order cannot be cancelled. It has already been shipped."
}
```

### Status Codes
- `200` - Order cancelled successfully
- `400` - Order cannot be cancelled (already shipped/delivered/cancelled)
- `403` - Forbidden
- `404` - Order not found
- `401` - Unauthorized

---

## Data Models

### Support Ticket Model

```json
{
  "id": 1,
  "ticket_number": "TKT-65A3F2B1C4D5E",
  "customer_id": 5,
  "assigned_to": 2,
  "subject": "Issue with order #12345",
  "description": "Detailed description of the issue",
  "status": "open",
  "priority": "high",
  "category": "order",
  "message_count": 5,
  "is_customer_read": true,
  "is_admin_read": false,
  "resolved_at": null,
  "closed_at": null,
  "last_replied_at": "2026-01-22T14:30:00.000000Z",
  "last_replied_by": 2,
  "created_at": "2026-01-22T10:30:00.000000Z",
  "updated_at": "2026-01-22T14:30:00.000000Z"
}
```

### Support Message Model

```json
{
  "id": 1,
  "ticket_id": 1,
  "customer_id": 5,
  "admin_id": null,
  "message": "Message content",
  "sender_type": "customer",
  "is_read": true,
  "read_at": "2026-01-22T10:35:00.000000Z",
  "attachments": null,
  "created_at": "2026-01-22T10:30:00.000000Z",
  "updated_at": "2026-01-22T10:35:00.000000Z"
}
```

---

## Enumerations

### Ticket Status
- `open` - Ticket is newly created and awaiting response
- `in_progress` - Ticket is being worked on
- `resolved` - Issue has been resolved
- `closed` - Ticket is closed

### Ticket Priority
- `low` - Low priority
- `medium` - Medium priority (default)
- `high` - High priority
- `urgent` - Urgent priority

### Ticket Category
- `technical` - Technical issues
- `billing` - Billing and payment issues
- `order` - Order-related issues
- `product` - Product-related questions
- `account` - Account management
- `other` - Other issues (default)

### Message Sender Type
- `customer` - Message sent by customer
- `admin` - Message sent by admin

---

## Error Responses

### Standard Error Format

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message description"
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

---

## Common HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| `200` | OK | Request successful |
| `201` | Created | Resource created successfully |
| `400` | Bad Request | Invalid request or business logic error |
| `401` | Unauthorized | Not authenticated |
| `403` | Forbidden | Authenticated but not authorized |
| `404` | Not Found | Resource not found |
| `422` | Unprocessable Entity | Validation error |
| `500` | Internal Server Error | Server error |

---

## Usage Examples

### Example 1: Customer Creates a Ticket

```bash
curl -X POST https://api.e3shopbd.com/api/support-tickets \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Product not as described",
    "description": "The product I received does not match the description on the website.",
    "priority": "high",
    "category": "product"
  }'
```

### Example 2: Customer Views Their Tickets

```bash
curl -X GET "https://api.e3shopbd.com/api/support-tickets?status=open" \
  -H "Authorization: Bearer {customer_token}"
```

### Example 3: Customer Sends a Message

```bash
curl -X POST https://api.e3shopbd.com/api/support-tickets/1/messages \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Thank you for your response. I appreciate your help."
  }'
```

### Example 4: Admin Views All Tickets

```bash
curl -X GET "https://api.e3shopbd.com/api/support-tickets?status=open&priority=urgent" \
  -H "Authorization: Bearer {admin_token}"
```

### Example 5: Get Navbar Count

```bash
curl -X GET https://api.e3shopbd.com/api/support-tickets/navbar/count \
  -H "Authorization: Bearer {token}"
```

---

## Notes

1. **Authentication**: All endpoints require a valid Bearer token from either customer or admin login.

2. **Access Control**:
   - Customers can only access their own tickets and messages
   - Admins can access all tickets and messages

3. **Auto-Read Marking**: When viewing messages, the ticket is automatically marked as read by the viewer.

4. **Ticket Status Changes**:
   - When an admin replies to a closed/resolved ticket, it automatically changes to "in_progress"
   - When a customer replies to a closed/resolved ticket, it reopens to "open"

5. **Pagination**: List endpoints support pagination with `per_page` parameter (default: 15).

6. **Filtering**: Multiple filters can be combined in list endpoints.

7. **Sorting**: Use `sort_by` and `sort_order` parameters to customize result ordering.

8. **Navbar Endpoints**: Designed for real-time UI updates with minimal data transfer.

---

## Support

For API support or questions, please contact the development team or create a support ticket through the system.

**API Base URL**: `https://api.e3shopbd.com`

**Documentation Version**: 1.0
**Last Updated**: 2026-01-22
