# Admin Dashboard & Export Report APIs Documentation

## Table of Contents
1. [Admin Dashboard APIs](#admin-dashboard-apis)
2. [Export Report APIs](#export-report-apis)
3. [Authentication](#authentication)
4. [Error Handling](#error-handling)
5. [Examples](#examples)

---

## Admin Dashboard APIs

All dashboard endpoints require admin authentication. Use the `auth:sanctum` middleware with admin role.

**Base URL:** `/api/dashboard`

### 1. Get Dashboard Statistics

Get comprehensive dashboard statistics including overview, orders, inventory, promotions, and support metrics.

**Endpoint:** `GET /api/dashboard/stats`

**Authentication:** Required (Admin)

**Query Parameters:**
- `date_from` (optional): Start date for statistics (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date for statistics (format: YYYY-MM-DD). Default: today

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_orders": 150,
      "total_revenue": 45000.50,
      "total_customers": 75,
      "total_products": 200,
      "active_products": 180,
      "average_order_value": 300.00
    },
    "orders": {
      "status_breakdown": {
        "pending": 10,
        "processing": 5,
        "shipped": 20,
        "delivered": 100,
        "cancelled": 15
      },
      "revenue_by_status": {
        "pending": 3000.00,
        "processing": 1500.00,
        "shipped": 6000.00,
        "delivered": 30000.00,
        "cancelled": 4500.00
      },
      "pending_cancellations": 3
    },
    "inventory": {
      "low_stock_products": 5,
      "out_of_stock_products": 2
    },
    "promotions": {
      "active_coupons": 8,
      "active_deals": 12
    },
    "support": {
      "open_tickets": 5
    },
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/stats?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer {token}"
```

---

### 2. Get Sales Trends

Get sales trends data grouped by day, week, or month.

**Endpoint:** `GET /api/dashboard/sales-trends`

**Authentication:** Required (Admin)

**Query Parameters:**
- `period` (optional): Grouping period - `daily`, `weekly`, or `monthly`. Default: `daily`
- `days` (optional): Number of days to analyze. Default: `30`

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "daily",
    "trends": [
      {
        "date": "2024-01-01",
        "orders": 10,
        "revenue": 3000.00
      },
      {
        "date": "2024-01-02",
        "orders": 15,
        "revenue": 4500.00
      }
    ],
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/sales-trends?period=monthly&days=90" \
  -H "Authorization: Bearer {token}"
```

---

### 3. Get Top Products

Get top-selling products by quantity sold.

**Endpoint:** `GET /api/dashboard/top-products`

**Authentication:** Required (Admin)

**Query Parameters:**
- `limit` (optional): Number of products to return. Default: `10`
- `date_from` (optional): Start date (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date (format: YYYY-MM-DD). Default: today

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "product_id": 1,
        "product_name": "Product A",
        "product_sku": "SKU-001",
        "total_quantity_sold": 150,
        "total_revenue": 15000.00
      },
      {
        "product_id": 2,
        "product_name": "Product B",
        "product_sku": "SKU-002",
        "total_quantity_sold": 120,
        "total_revenue": 12000.00
      }
    ],
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/top-products?limit=20&date_from=2024-01-01" \
  -H "Authorization: Bearer {token}"
```

---

### 4. Get Top Customers

Get top customers by revenue or number of orders.

**Endpoint:** `GET /api/dashboard/top-customers`

**Authentication:** Required (Admin)

**Query Parameters:**
- `limit` (optional): Number of customers to return. Default: `10`
- `sort_by` (optional): Sort by `revenue` or `orders`. Default: `revenue`
- `date_from` (optional): Start date (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date (format: YYYY-MM-DD). Default: today

**Response:**
```json
{
  "success": true,
  "data": {
    "customers": [
      {
        "customer_id": 1,
        "customer_name": "John Doe",
        "customer_email": "john@example.com",
        "customer_phone": "+1234567890",
        "total_orders": 25,
        "total_revenue": 7500.00
      },
      {
        "customer_id": 2,
        "customer_name": "Jane Smith",
        "customer_email": "jane@example.com",
        "customer_phone": "+1234567891",
        "total_orders": 20,
        "total_revenue": 6000.00
      }
    ],
    "sort_by": "revenue",
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/top-customers?limit=15&sort_by=orders" \
  -H "Authorization: Bearer {token}"
```

---

### 5. Get Recent Orders

Get the most recent orders.

**Endpoint:** `GET /api/dashboard/recent-orders`

**Authentication:** Required (Admin)

**Query Parameters:**
- `limit` (optional): Number of orders to return. Default: `10`

**Response:**
```json
{
  "success": true,
  "data": {
    "orders": [
      {
        "id": 1,
        "order_number": "ORD-2024-001",
        "customer_name": "John Doe",
        "customer_email": "john@example.com",
        "status": "delivered",
        "total_amount": 300.00,
        "items_count": 3,
        "created_at": "2024-01-31 10:30:00"
      }
    ]
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/recent-orders?limit=20" \
  -H "Authorization: Bearer {token}"
```

---

### 6. Get Low Stock Alerts

Get products with low stock levels.

**Endpoint:** `GET /api/dashboard/low-stock-alerts`

**Authentication:** Required (Admin)

**Query Parameters:**
- `threshold` (optional): Stock threshold for low stock alert. Default: `10`
- `limit` (optional): Maximum number of products to return. Default: `50`

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": 1,
        "name": "Product A",
        "sku": "SKU-001",
        "stock_quantity": 5,
        "price": 100.00,
        "category": "Electronics",
        "is_out_of_stock": false
      },
      {
        "id": 2,
        "name": "Product B",
        "sku": "SKU-002",
        "stock_quantity": 0,
        "price": 200.00,
        "category": "Clothing",
        "is_out_of_stock": true
      }
    ],
    "threshold": 10
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/low-stock-alerts?threshold=15&limit=100" \
  -H "Authorization: Bearer {token}"
```

---

### 7. Get Category Sales Breakdown

Get sales breakdown by category.

**Endpoint:** `GET /api/dashboard/category-sales`

**Authentication:** Required (Admin)

**Query Parameters:**
- `date_from` (optional): Start date (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date (format: YYYY-MM-DD). Default: today

**Response:**
```json
{
  "success": true,
  "data": {
    "categories": [
      {
        "category_id": 1,
        "category_name": "Electronics",
        "total_quantity_sold": 500,
        "total_revenue": 50000.00,
        "order_count": 100
      },
      {
        "category_id": 2,
        "category_name": "Clothing",
        "total_quantity_sold": 300,
        "total_revenue": 30000.00,
        "order_count": 75
      }
    ],
    "date_range": {
      "from": "2024-01-01",
      "to": "2024-01-31"
    }
  }
}
```

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/dashboard/category-sales?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer {token}"
```

---

## Export Report APIs

All export endpoints require admin authentication and return CSV files for download.

**Base URL:** `/api/exports`

### 1. Export Orders

Export orders to CSV format with filtering options.

**Endpoint:** `GET /api/exports/orders`

**Authentication:** Required (Admin)

**Query Parameters:**
- `format` (optional): Export format. Currently only `csv` is supported. Default: `csv`
- `date_from` (optional): Start date filter (format: YYYY-MM-DD)
- `date_to` (optional): End date filter (format: YYYY-MM-DD)
- `status` (optional): Filter by order status (pending, processing, shipped, delivered, cancelled)

**Response:** CSV file download

**CSV Columns:**
- Order Number
- Order Date
- Customer Name
- Customer Email
- Customer Phone
- Status
- Subtotal
- Discount Amount
- Shipping Cost
- Tax Amount
- Total Amount
- Shipping Address
- Items Count
- Items Details
- Coupon Code
- Notes

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/exports/orders?date_from=2024-01-01&date_to=2024-01-31&status=delivered" \
  -H "Authorization: Bearer {token}" \
  -o orders_export.csv
```

---

### 2. Export Products

Export products to CSV format with filtering options.

**Endpoint:** `GET /api/exports/products`

**Authentication:** Required (Admin)

**Query Parameters:**
- `format` (optional): Export format. Currently only `csv` is supported. Default: `csv`
- `category_id` (optional): Filter by category ID
- `is_active` (optional): Filter by active status (true/false)

**Response:** CSV file download

**CSV Columns:**
- ID
- Name
- SKU
- Category
- Price
- Stock Quantity
- Description
- Is Active
- Brand
- Model
- Weight
- Created At
- Updated At

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/exports/products?is_active=true&category_id=1" \
  -H "Authorization: Bearer {token}" \
  -o products_export.csv
```

---

### 3. Export Customers

Export customers to CSV format with filtering options.

**Endpoint:** `GET /api/exports/customers`

**Authentication:** Required (Admin)

**Query Parameters:**
- `format` (optional): Export format. Currently only `csv` is supported. Default: `csv`
- `date_from` (optional): Start date filter for customer registration (format: YYYY-MM-DD)
- `date_to` (optional): End date filter for customer registration (format: YYYY-MM-DD)
- `is_banned` (optional): Filter by banned status (true/false)
- `is_suspended` (optional): Filter by suspended status (true/false)

**Response:** CSV file download

**CSV Columns:**
- ID
- Name
- Email
- Phone
- Address
- Total Orders
- Is Banned
- Is Suspended
- Banned At
- Suspended At
- Created At
- Updated At

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/exports/customers?date_from=2024-01-01&is_banned=false" \
  -H "Authorization: Bearer {token}" \
  -o customers_export.csv
```

---

### 4. Export Sales Report

Export aggregated sales report to CSV format.

**Endpoint:** `GET /api/exports/sales-report`

**Authentication:** Required (Admin)

**Query Parameters:**
- `date_from` (optional): Start date (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date (format: YYYY-MM-DD). Default: today
- `group_by` (optional): Grouping period - `day`, `week`, or `month`. Default: `day`

**Response:** CSV file download

**CSV Columns:**
- Period
- Total Orders
- Total Revenue
- Average Order Value
- Total Items Sold
- Total Discount
- Total Shipping
- Total Tax

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/exports/sales-report?date_from=2024-01-01&date_to=2024-01-31&group_by=month" \
  -H "Authorization: Bearer {token}" \
  -o sales_report.csv
```

---

### 5. Export Product Sales Report

Export detailed product sales report to CSV format.

**Endpoint:** `GET /api/exports/product-sales-report`

**Authentication:** Required (Admin)

**Query Parameters:**
- `date_from` (optional): Start date (format: YYYY-MM-DD). Default: 30 days ago
- `date_to` (optional): End date (format: YYYY-MM-DD). Default: today

**Response:** CSV file download

**CSV Columns:**
- Product ID
- Product Name
- SKU
- Category
- Total Quantity Sold
- Total Revenue
- Order Count
- Average Price

**Example Request:**
```bash
curl -X GET "https://api.example.com/api/exports/product-sales-report?date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer {token}" \
  -o product_sales_report.csv
```

---

## Authentication

All endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your_token_here}
```

To obtain a token, use the login endpoint:
```
POST /api/login
```

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com"
  }
}
```

---

## Error Handling

All endpoints return consistent error responses:

**400 Bad Request:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**401 Unauthorized:**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**403 Forbidden:**
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

**500 Internal Server Error:**
```json
{
  "success": false,
  "message": "Failed to retrieve dashboard statistics",
  "error": "Error details"
}
```

---

## Examples

### JavaScript/Fetch Example

```javascript
// Get dashboard statistics
async function getDashboardStats(dateFrom, dateTo) {
  const response = await fetch(
    `/api/dashboard/stats?date_from=${dateFrom}&date_to=${dateTo}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    }
  );
  
  const data = await response.json();
  return data;
}

// Export orders
async function exportOrders(dateFrom, dateTo) {
  const response = await fetch(
    `/api/exports/orders?date_from=${dateFrom}&date_to=${dateTo}`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );
  
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'orders_export.csv';
  document.body.appendChild(a);
  a.click();
  window.URL.revokeObjectURL(url);
  document.body.removeChild(a);
}
```

### PHP cURL Example

```php
// Get dashboard statistics
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.example.com/api/dashboard/stats?date_from=2024-01-01&date_to=2024-01-31");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);

// Export orders
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.example.com/api/exports/orders?date_from=2024-01-01&date_to=2024-01-31");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$token}"
]);

$csv = curl_exec($ch);
file_put_contents('orders_export.csv', $csv);
curl_close($ch);
```

### Python Requests Example

```python
import requests

# Get dashboard statistics
headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

response = requests.get(
    'https://api.example.com/api/dashboard/stats',
    params={'date_from': '2024-01-01', 'date_to': '2024-01-31'},
    headers=headers
)

data = response.json()

# Export orders
response = requests.get(
    'https://api.example.com/api/exports/orders',
    params={'date_from': '2024-01-01', 'date_to': '2024-01-31'},
    headers={'Authorization': f'Bearer {token}'}
)

with open('orders_export.csv', 'wb') as f:
    f.write(response.content)
```

---

## Notes

1. **Date Formats:** All date parameters should be in `YYYY-MM-DD` format.

2. **Pagination:** Dashboard endpoints that return lists may support pagination in future updates. Currently, limits are applied where specified.

3. **Export Formats:** Currently, only CSV format is supported. Excel export may be added in the future if Laravel Excel package is installed.

4. **Rate Limiting:** API endpoints may be subject to rate limiting. Check response headers for rate limit information.

5. **Large Exports:** For large datasets, exports may take time to generate. Consider using date filters to limit the data range.

6. **Timezone:** All timestamps are in the server's configured timezone. Ensure your application handles timezone conversions appropriately.

---

## Support

For issues or questions regarding these APIs, please contact the development team or refer to the main project documentation.

