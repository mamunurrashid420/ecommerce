# Customer Dashboard API Documentation

This document describes the Customer Dashboard API endpoints that provide dashboard statistics and activity data for authenticated customers.

## Base URL
All endpoints are prefixed with `/api/customer/dashboard/`

## Authentication
All endpoints require customer authentication using the `customer` middleware. Include the customer's Bearer token in the Authorization header:

```
Authorization: Bearer {customer_token}
```

## Endpoints

### 1. Dashboard Statistics
**GET** `/api/customer/dashboard/stats`

Returns key dashboard statistics for the authenticated customer.

#### Response
```json
{
    "success": true,
    "data": {
        "total_orders": 15,
        "pending_orders": 3,
        "completed_orders": 10,
        "total_spent": "2,450.75"
    }
}
```

#### Response Fields
- `total_orders`: Total number of orders placed by the customer
- `pending_orders`: Number of orders with "pending" status
- `completed_orders`: Number of orders with "delivered" status
- `total_spent`: Total amount spent on delivered/processing/shipped orders (formatted)

---

### 2. Recent Activity
**GET** `/api/customer/dashboard/recent-activity`

Returns recent order activity for the customer.

#### Query Parameters
- `limit` (optional): Number of recent orders to return (default: 10)

#### Response
```json
{
    "success": true,
    "data": {
        "recent_orders": [
            {
                "id": 123,
                "order_number": "ORD-2024-001",
                "status": "processing",
                "total_amount": "299.99",
                "created_at": "Jan 15, 2026",
                "items_count": 2,
                "first_product": {
                    "name": "Wireless Headphones",
                    "thumbnail": "https://example.com/storage/products/headphones.jpg"
                }
            }
        ],
        "has_activity": true
    }
}
```

#### Response Fields
- `recent_orders`: Array of recent order objects
- `has_activity`: Boolean indicating if customer has any orders

---

### 3. Profile Summary
**GET** `/api/customer/dashboard/profile-summary`

Returns customer profile information for dashboard display.

#### Response
```json
{
    "success": true,
    "data": {
        "id": 456,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "profile_picture_url": "https://example.com/storage/profiles/john.jpg",
        "member_since": "Jan 2024",
        "total_orders": 15,
        "can_make_purchases": true
    }
}
```

#### Response Fields
- `member_since`: Formatted date when customer joined
- `can_make_purchases`: Boolean indicating if customer can place orders (not banned/suspended)

---

### 4. Order Status Breakdown
**GET** `/api/customer/dashboard/order-status-breakdown`

Returns count of orders by status for the customer.

#### Response
```json
{
    "success": true,
    "data": {
        "pending": 3,
        "processing": 2,
        "shipped": 1,
        "delivered": 10,
        "cancelled": 1
    }
}
```

#### Response Fields
Returns an object with order counts for each status:
- `pending`: Orders awaiting processing
- `processing`: Orders being prepared
- `shipped`: Orders in transit
- `delivered`: Completed orders
- `cancelled`: Cancelled orders

---

### 5. Spending Trend
**GET** `/api/customer/dashboard/spending-trend`

Returns monthly spending data for the last 6 months.

#### Response
```json
{
    "success": true,
    "data": {
        "months": ["Aug 2025", "Sep 2025", "Oct 2025", "Nov 2025", "Dec 2025", "Jan 2026"],
        "spending": [150.50, 299.99, 0, 450.25, 199.99, 350.00]
    }
}
```

#### Response Fields
- `months`: Array of month labels (last 6 months)
- `spending`: Array of spending amounts corresponding to each month

---

## Error Responses

### 401 Unauthorized
```json
{
    "error": "Unauthorized"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## Usage Examples

### JavaScript/Fetch
```javascript
// Get dashboard stats
const response = await fetch('/api/customer/dashboard/stats', {
    headers: {
        'Authorization': `Bearer ${customerToken}`,
        'Accept': 'application/json'
    }
});
const data = await response.json();
```

### cURL
```bash
# Get recent activity
curl -X GET "https://yourapi.com/api/customer/dashboard/recent-activity?limit=5" \
  -H "Authorization: Bearer YOUR_CUSTOMER_TOKEN" \
  -H "Accept: application/json"
```

## Notes

1. All monetary values are returned as strings with proper formatting
2. Dates are formatted for display (e.g., "Jan 15, 2026")
3. The spending trend only includes orders with status: delivered, processing, or shipped
4. Profile picture URLs are fully qualified URLs
5. All endpoints return consistent JSON structure with `success` and `data` fields