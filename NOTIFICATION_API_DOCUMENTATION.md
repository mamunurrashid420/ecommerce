# Notification API Documentation

Complete documentation for the Notification Management API endpoints.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Endpoints](#endpoints)
   - [List All Notifications](#1-list-all-notifications)
   - [Get Unread Notifications](#2-get-unread-notifications)
   - [Get Unread Count](#3-get-unread-count)
   - [Get Notification Details](#4-get-notification-details)
   - [Mark Notification as Read](#5-mark-notification-as-read)
   - [Mark All Notifications as Read](#6-mark-all-notifications-as-read)
   - [Delete Notification](#7-delete-notification)
   - [Delete All Notifications](#8-delete-all-notifications)
   - [Get Notification Statistics](#9-get-notification-statistics)
4. [Notification Structure](#notification-structure)
5. [New Order Notification](#new-order-notification)
6. [Error Responses](#error-responses)
7. [Example Use Cases](#example-use-cases)

---

## Overview

The Notification API allows authenticated users (primarily admin users) to manage their notifications. The system automatically creates notifications when certain events occur (e.g., when a customer creates an order). Notifications are stored in the database and can be retrieved, marked as read, or deleted through the API.

**Base URL**: `http://your-domain.com/api`

**Key Features**:
- Real-time notifications for admin users
- Automatic notification creation on order creation
- Read/unread status tracking
- Notification statistics and filtering
- Bulk operations (mark all as read, delete all)

---

## Authentication

**Required**: User authentication (Admin/User)
- **Authentication Method**: Bearer token via `Authorization: Bearer {token}` header
- **Middleware**: `auth:sanctum`
- **Access**: All authenticated users can access their own notifications

---

## Endpoints

### 1. List All Notifications

Retrieve a paginated list of all notifications for the authenticated user.

#### Endpoint
```
GET /api/notifications
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### Query Parameters

| Parameter | Type | Description | Required | Default |
|-----------|------|-------------|----------|---------|
| `read` | string | Filter by read status (`true`, `false`, `1`, `0`) | No | - |
| `type` | string | Filter by notification type (e.g., `App\Notifications\NewOrderNotification`) | No | - |
| `search` | string | Search in notification data | No | - |
| `date_from` | date | Filter notifications from this date (YYYY-MM-DD) | No | - |
| `date_to` | date | Filter notifications to this date (YYYY-MM-DD) | No | - |
| `sort_by` | string | Sort field (`created_at`, `read_at`) | No | `created_at` |
| `sort_order` | string | Sort direction (`asc`, `desc`) | No | `desc` |
| `per_page` | integer | Items per page | No | 15 |
| `page` | integer | Page number | No | 1 |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "App\\Notifications\\NewOrderNotification",
      "notifiable_type": "App\\Models\\User",
      "notifiable_id": 1,
      "data": {
        "order_id": 5,
        "order_number": "ORD-20250115-ABC123",
        "customer_id": 3,
        "customer_name": "John Doe",
        "total_amount": "149.99",
        "status": "pending",
        "message": "New order #ORD-20250115-ABC123 from John Doe",
        "created_at": "2025-01-15 10:30:00"
      },
      "read_at": null,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  },
  "unread_count": 12
}
```

#### Example Requests

```bash
# Get all notifications
curl -X GET "http://your-domain.com/api/notifications" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Get only unread notifications
curl -X GET "http://your-domain.com/api/notifications?read=false" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Get notifications from a specific date
curl -X GET "http://your-domain.com/api/notifications?date_from=2025-01-15" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Search notifications
curl -X GET "http://your-domain.com/api/notifications?search=John%20Doe" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 2. Get Unread Notifications

Retrieve a paginated list of unread notifications for the authenticated user.

#### Endpoint
```
GET /api/notifications/unread
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### Query Parameters

| Parameter | Type | Description | Required | Default |
|-----------|------|-------------|----------|---------|
| `type` | string | Filter by notification type | No | - |
| `sort_by` | string | Sort field | No | `created_at` |
| `sort_order` | string | Sort direction | No | `desc` |
| `per_page` | integer | Items per page | No | 15 |
| `page` | integer | Page number | No | 1 |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "type": "App\\Notifications\\NewOrderNotification",
      "notifiable_type": "App\\Models\\User",
      "notifiable_id": 1,
      "data": {
        "order_id": 5,
        "order_number": "ORD-20250115-ABC123",
        "customer_id": 3,
        "customer_name": "John Doe",
        "total_amount": "149.99",
        "status": "pending",
        "message": "New order #ORD-20250115-ABC123 from John Doe",
        "created_at": "2025-01-15 10:30:00"
      },
      "read_at": null,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 12
  }
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/notifications/unread" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 3. Get Unread Count

Get the count of unread notifications for the authenticated user.

#### Endpoint
```
GET /api/notifications/unread-count
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "unread_count": 12
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/notifications/unread-count" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 4. Get Notification Details

Retrieve detailed information about a specific notification.

#### Endpoint
```
GET /api/notifications/{id}
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `id` | string (UUID) | Notification ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "type": "App\\Notifications\\NewOrderNotification",
    "notifiable_type": "App\\Models\\User",
    "notifiable_id": 1,
    "data": {
      "order_id": 5,
      "order_number": "ORD-20250115-ABC123",
      "customer_id": 3,
      "customer_name": "John Doe",
      "total_amount": "149.99",
      "status": "pending",
      "message": "New order #ORD-20250115-ABC123 from John Doe",
      "created_at": "2025-01-15 10:30:00"
    },
    "read_at": null,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

#### Error Response (404 Not Found)

```json
{
  "success": false,
  "message": "Notification not found",
  "error": "No query results for model [Illuminate\\Notifications\\DatabaseNotification] 550e8400-e29b-41d4-a716-446655440000"
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/notifications/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 5. Mark Notification as Read

Mark a specific notification as read.

#### Endpoint
```
PUT /api/notifications/{id}/read
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `id` | string (UUID) | Notification ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Notification marked as read",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "type": "App\\Notifications\\NewOrderNotification",
    "notifiable_type": "App\\Models\\User",
    "notifiable_id": 1,
    "data": {
      "order_id": 5,
      "order_number": "ORD-20250115-ABC123",
      "customer_id": 3,
      "customer_name": "John Doe",
      "total_amount": "149.99",
      "status": "pending",
      "message": "New order #ORD-20250115-ABC123 from John Doe",
      "created_at": "2025-01-15 10:30:00"
    },
    "read_at": "2025-01-15T11:00:00.000000Z",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T11:00:00.000000Z"
  }
}
```

#### Example Request

```bash
curl -X PUT "http://your-domain.com/api/notifications/550e8400-e29b-41d4-a716-446655440000/read" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

---

### 6. Mark All Notifications as Read

Mark all unread notifications as read for the authenticated user.

#### Endpoint
```
PUT /api/notifications/read-all
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "All notifications marked as read",
  "marked_count": 12
}
```

#### Example Request

```bash
curl -X PUT "http://your-domain.com/api/notifications/read-all" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json"
```

---

### 7. Delete Notification

Delete a specific notification.

#### Endpoint
```
DELETE /api/notifications/{id}
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### URL Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `id` | string (UUID) | Notification ID | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Notification deleted successfully"
}
```

#### Example Request

```bash
curl -X DELETE "http://your-domain.com/api/notifications/550e8400-e29b-41d4-a716-446655440000" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 8. Delete All Notifications

Delete all notifications for the authenticated user, with optional filtering.

#### Endpoint
```
DELETE /api/notifications
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### Query Parameters

| Parameter | Type | Description | Required |
|-----------|------|-------------|----------|
| `read` | string | Filter by read status (`true`, `false`, `1`, `0`) | No |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Notifications deleted successfully",
  "deleted_count": 72
}
```

#### Example Requests

```bash
# Delete all notifications
curl -X DELETE "http://your-domain.com/api/notifications" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Delete only read notifications
curl -X DELETE "http://your-domain.com/api/notifications?read=true" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Delete only unread notifications
curl -X DELETE "http://your-domain.com/api/notifications?read=false" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

### 9. Get Notification Statistics

Get statistics about notifications for the authenticated user.

#### Endpoint
```
GET /api/notifications/stats
```

#### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "stats": {
    "total": 72,
    "unread": 12,
    "read": 60,
    "recent_7_days": 25,
    "by_type": {
      "App\\Notifications\\NewOrderNotification": 72
    }
  }
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/notifications/stats" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## Notification Structure

### Database Notification Object

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "type": "App\\Notifications\\NewOrderNotification",
  "notifiable_type": "App\\Models\\User",
  "notifiable_id": 1,
  "data": {
    // Notification-specific data (see below)
  },
  "read_at": "2025-01-15T11:00:00.000000Z" | null,
  "created_at": "2025-01-15T10:30:00.000000Z",
  "updated_at": "2025-01-15T10:30:00.000000Z"
}
```

### Notification Data Structure

The `data` field contains notification-specific information. The structure varies by notification type.

---

## New Order Notification

When a customer creates an order, all admin users automatically receive a `NewOrderNotification`. The notification data structure is as follows:

### Notification Data

```json
{
  "order_id": 5,
  "order_number": "ORD-20250115-ABC123",
  "customer_id": 3,
  "customer_name": "John Doe",
  "total_amount": "149.99",
  "status": "pending",
  "message": "New order #ORD-20250115-ABC123 from John Doe",
  "created_at": "2025-01-15 10:30:00"
}
```

### Fields

| Field | Type | Description |
|-------|------|-------------|
| `order_id` | integer | The ID of the order |
| `order_number` | string | Unique order number (e.g., "ORD-20250115-ABC123") |
| `customer_id` | integer | The ID of the customer who placed the order |
| `customer_name` | string | Name of the customer |
| `total_amount` | string | Total order amount (formatted as decimal string) |
| `status` | string | Order status (`pending`, `processing`, `shipped`, `delivered`, `cancelled`) |
| `message` | string | Human-readable notification message |
| `created_at` | string | Order creation timestamp |

### Automatic Creation

Notifications are automatically created when:
- A customer successfully creates an order
- The order is committed to the database
- All admin users receive the notification

The notification creation happens in the `OrderService::createOrder()` method after the order is successfully created.

---

## Error Responses

### 401 Unauthorized

```json
{
  "success": false,
  "message": "Unauthorized"
}
```

**Cause**: Missing or invalid authentication token.

### 404 Not Found

```json
{
  "success": false,
  "message": "Notification not found",
  "error": "No query results for model [Illuminate\\Notifications\\DatabaseNotification] {id}"
}
```

**Cause**: The notification ID does not exist or does not belong to the authenticated user.

### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Failed to retrieve notifications",
  "error": "Error message details"
}
```

**Cause**: Server error occurred while processing the request.

---

## Example Use Cases

### Use Case 1: Display Notification Badge

Display an unread notification count badge in the admin dashboard.

```javascript
// Fetch unread count
fetch('/api/notifications/unread-count', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    document.getElementById('notification-badge').textContent = data.unread_count;
  }
});
```

### Use Case 2: Display Notification List

Show a list of unread notifications in a dropdown menu.

```javascript
// Fetch unread notifications
fetch('/api/notifications/unread?per_page=10', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    data.data.forEach(notification => {
      const notificationData = notification.data;
      console.log(notificationData.message);
      // Display notification in UI
    });
  }
});
```

### Use Case 3: Mark Notification as Read on Click

When a user clicks on a notification, mark it as read and navigate to the order.

```javascript
function handleNotificationClick(notificationId, orderId) {
  // Mark as read
  fetch(`/api/notifications/${notificationId}/read`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  })
  .then(() => {
    // Navigate to order details
    window.location.href = `/admin/orders/${orderId}`;
  });
}
```

### Use Case 4: Mark All as Read

Provide a "Mark all as read" button in the notification center.

```javascript
function markAllAsRead() {
  fetch('/api/notifications/read-all', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log(`Marked ${data.marked_count} notifications as read`);
      // Refresh notification list
      loadNotifications();
    }
  });
}
```

### Use Case 5: Real-time Notification Polling

Poll for new notifications periodically.

```javascript
function pollNotifications() {
  setInterval(() => {
    fetch('/api/notifications/unread-count', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success && data.unread_count > 0) {
        // Show notification indicator
        showNotificationIndicator(data.unread_count);
      }
    });
  }, 30000); // Poll every 30 seconds
}
```

### Use Case 6: Notification Statistics Dashboard

Display notification statistics in an admin dashboard.

```javascript
fetch('/api/notifications/stats', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    const stats = data.stats;
    console.log(`Total: ${stats.total}`);
    console.log(`Unread: ${stats.unread}`);
    console.log(`Read: ${stats.read}`);
    console.log(`Recent (7 days): ${stats.recent_7_days}`);
    // Display in dashboard
  }
});
```

---

## Integration Notes

### Frontend Integration

1. **Store authentication token**: Ensure the Bearer token is stored securely (e.g., in localStorage or a secure cookie).

2. **Handle token expiration**: Implement token refresh logic if tokens expire.

3. **Error handling**: Always check the `success` field in responses and handle errors appropriately.

4. **Pagination**: Use the `pagination` object to implement pagination controls in the UI.

5. **Real-time updates**: Consider implementing WebSocket connections or polling for real-time notification updates.

### Backend Integration

1. **Notification creation**: Notifications are automatically created when orders are placed. No manual intervention needed.

2. **Extending notifications**: To add new notification types, create new notification classes following the same pattern as `NewOrderNotification`.

3. **Email notifications**: The notification system supports email notifications. To enable, modify the `via()` method in the notification class to include `'mail'`.

4. **Queue support**: For better performance, consider implementing queue support for notifications by implementing `ShouldQueue` in notification classes.

---

## Database Schema

### notifications Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid | Primary key (UUID) |
| `type` | string | Notification class name |
| `notifiable_type` | string | Model class name (e.g., "App\Models\User") |
| `notifiable_id` | bigint | ID of the notifiable model |
| `data` | text | JSON data containing notification details |
| `read_at` | timestamp | Timestamp when notification was read (nullable) |
| `created_at` | timestamp | Notification creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

### Indexes

- Primary key on `id`
- Index on `notifiable_type` and `notifiable_id` (polymorphic relationship)
- Index on `read_at` for filtering unread notifications

---

## Best Practices

1. **Polling frequency**: Don't poll too frequently. A 30-60 second interval is recommended for unread count checks.

2. **Batch operations**: Use "mark all as read" and "delete all" endpoints for better performance when dealing with multiple notifications.

3. **Cleanup**: Periodically delete old read notifications to keep the database size manageable.

4. **Error handling**: Always implement proper error handling in frontend applications.

5. **Loading states**: Show loading indicators when fetching notifications.

6. **Notification limits**: Consider implementing limits on the number of notifications stored per user.

---

## Support

For issues or questions regarding the Notification API, please contact the development team or refer to the main API documentation.

