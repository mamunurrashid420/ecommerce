# Dropship Admin Panel Integration Documentation

## Overview

This documentation covers the admin panel integration for the Dropship sourcing system, enabling administrators to search, import, and manage products from Chinese e-commerce platforms (1688, Taobao, Tmall).

---

## Authentication

All API endpoints require authentication using Laravel Sanctum Bearer Token.

```
Authorization: Bearer {your_admin_token}
Accept: application/json
```

---

## API Endpoints

### 1. Product Search

Search for products by keyword from 1688/Taobao.

**Endpoint:** `GET /api/dropship/products/search`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| q | string | Yes | Search keyword |
| platform | string | No | Platform: `1688`, `taobao`, `tmall` (default: `1688`) |
| page | integer | No | Page number (default: 1) |
| page_size | integer | No | Results per page (default: 40, max: 100) |
| min_price | number | No | Minimum price filter |
| max_price | number | No | Maximum price filter |
| sort | string | No | Sort by: `price_asc`, `price_desc`, `sales` |

**Example Request:**
```javascript
// Admin Panel - React/Vue
const searchProducts = async (keyword, filters = {}) => {
  const params = new URLSearchParams({
    q: keyword,
    platform: filters.platform || '1688',
    page: filters.page || 1,
    page_size: filters.pageSize || 20,
    ...(filters.minPrice && { min_price: filters.minPrice }),
    ...(filters.maxPrice && { max_price: filters.maxPrice }),
  });

  const response = await fetch(`/api/dropship/products/search?${params}`, {
    headers: {
      'Authorization': `Bearer ${adminToken}`,
      'Accept': 'application/json',
    },
  });
  return response.json();
};
```

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "item_id": "652702302959",
        "title": "Product Name",
        "price": "13.00",
        "image": "https://...",
        "sales": 115,
        "shop_name": "Shop Name"
      }
    ],
    "total": 1000,
    "page": 1,
    "page_size": 20
  }
}
```

---

### 2. Get Product Details

Get complete product information including variants, images, and pricing.

**Endpoint:** `GET /api/dropship/products/{itemId}`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| itemId | string | Yes | Product ID from 1688/Taobao |
| platform | string | No | Platform (default: `1688`) |

**Example Request:**
```javascript
const getProductDetails = async (itemId, platform = '1688') => {
  const response = await fetch(
    `/api/dropship/products/${itemId}?platform=${platform}`,
    {
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Accept': 'application/json',
      },
    }
  );
  return response.json();
};
```

**Response:**
```json
{
  "success": true,
  "data": { /* raw API response */ },
  "transformed": {
    "source_id": "652702302959",
    "platform": "1688",
    "name": "Product Name in Chinese",
    "description": "Product description",
    "price": 13.00,
    "original_price": 15.00,
    "price_min": 13.00,
    "price_max": 22.00,
    "currency": "CNY",
    "stock_quantity": 421,
    "total_sold": 115,
    "images": ["https://...", "https://..."],
    "video_url": "https://...",
    "seller_id": "seller123",
    "shop_name": "Shop Name",
    "source_url": "https://detail.1688.com/offer/652702302959.html",
    "skus": [
      {
        "sku_id": "5765876567676",
        "price": 22.00,
        "original_price": 25.00,
        "stock": 304,
        "properties": "Color: Green, Size: Large",
        "image": "https://..."
      }
    ],
    "properties": {
      "Material": "Ceramic",
      "Brand": "Brand Name"
    },
    "category_id": "123456",
    "fetched_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### 3. Import Product to Local Store

Import a product from 1688/Taobao into your local product catalog.

**Endpoint:** `POST /api/dropship/products/import`

**Request Body:**
```json
{
  "source_id": "652702302959",
  "platform": "1688",
  "name": "Ceramic Mug with Lid",
  "description": "High quality ceramic mug...",
  "price": 15.99,
  "cost_price": 8.50,
  "category_id": 5,
  "images": ["https://...", "https://..."],
  "variants": [
    {
      "sku_id": "5765876567676",
      "name": "Green - Large",
      "price": 15.99,
      "cost": 8.50,
      "stock": 100
    }
  ]
}
```

**Example Request:**
```javascript
const importProduct = async (productData) => {
  const response = await fetch('/api/dropship/products/import', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${adminToken}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify(productData),
  });
  return response.json();
};

// Usage with product from search
const handleImport = async (sourceProduct) => {
  const productDetails = await getProductDetails(sourceProduct.item_id);

  const importData = {
    source_id: productDetails.transformed.source_id,
    platform: productDetails.transformed.platform,
    name: translateToEnglish(productDetails.transformed.name),
    description: translateToEnglish(productDetails.transformed.description),
    price: calculateSellingPrice(productDetails.transformed.price),
    cost_price: productDetails.transformed.price,
    category_id: selectedCategory,
    images: productDetails.transformed.images,
    variants: productDetails.transformed.skus.map(sku => ({
      sku_id: sku.sku_id,
      name: sku.properties,
      price: calculateSellingPrice(sku.price),
      cost: sku.price,
      stock: sku.stock,
    })),
  };

  return importProduct(importData);
};
```

---

### 4. Search by Image

Search for similar products using an image URL.

**Endpoint:** `POST /api/dropship/products/search-by-image`

**Request Body:**
```json
{
  "image_url": "https://example.com/product-image.jpg",
  "platform": "1688",
  "page": 1,
  "page_size": 20
}
```

**Example Request:**
```javascript
const searchByImage = async (imageUrl, platform = '1688') => {
  const response = await fetch('/api/dropship/products/search-by-image', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${adminToken}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      image_url: imageUrl,
      platform: platform,
    }),
  });
  return response.json();
};
```

---

### 5. Get Product Description

Get detailed HTML description for a product.

**Endpoint:** `GET /api/dropship/products/{itemId}/description`

**Example Request:**
```javascript
const getProductDescription = async (itemId, platform = '1688') => {
  const response = await fetch(
    `/api/dropship/products/${itemId}/description?platform=${platform}`,
    {
      headers: {
        'Authorization': `Bearer ${adminToken}`,
        'Accept': 'application/json',
      },
    }
  );
  return response.json();
};
```

---

### 6. Get Shipping Fee

Calculate shipping cost for a product.

**Endpoint:** `GET /api/dropship/products/{itemId}/shipping`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| platform | string | No | Platform (default: `1688`) |
| area_id | string | No | Destination area ID |
| quantity | integer | No | Quantity (default: 1) |

---

## Order Sourcing

### 7. Get Order Sourcing Info

Get sourcing information for an existing order.

**Endpoint:** `GET /api/dropship/orders/{orderId}/source`

**Example Request:**
```javascript
const getOrderSourcing = async (orderId) => {
  const response = await fetch(`/api/dropship/orders/${orderId}/source`, {
    headers: {
      'Authorization': `Bearer ${adminToken}`,
      'Accept': 'application/json',
    },
  });
  return response.json();
};
```

**Response:**
```json
{
  "success": true,
  "data": {
    "order_id": 12345,
    "items": [
      {
        "product_id": 1,
        "product_name": "Ceramic Mug",
        "quantity": 2,
        "source_id": "652702302959",
        "source_platform": "1688",
        "source_url": "https://detail.1688.com/offer/652702302959.html",
        "source_price": 13.00,
        "source_currency": "CNY",
        "current_stock": 421,
        "is_available": true
      }
    ]
  }
}
```

---

### 8. Price Comparison

Compare prices across platforms for order items.

**Endpoint:** `GET /api/dropship/orders/{orderId}/price-comparison`

---

### 9. Bulk Source Check

Check sourcing availability for multiple orders.

**Endpoint:** `POST /api/dropship/orders/bulk-source-check`

**Request Body:**
```json
{
  "order_ids": [123, 124, 125, 126]
}
```

---

### 10. Mark Items as Sourced

Update order items as sourced from supplier.

**Endpoint:** `POST /api/dropship/orders/{orderId}/mark-sourced`

**Request Body:**
```json
{
  "items": [
    {
      "product_id": 1,
      "supplier_order_id": "TB123456789",
      "tracking_number": "YT1234567890"
    }
  ]
}
```

---

## Shop & Category Endpoints

### 11. Get Shop Info

**Endpoint:** `GET /api/dropship/shops/{sellerId}`

### 12. Get Shop Products

**Endpoint:** `GET /api/dropship/shops/{sellerId}/products`

### 13. Get Category Info

**Endpoint:** `GET /api/dropship/categories/{catId}`

### 14. Get Category Products

**Endpoint:** `GET /api/dropship/categories/{catId}/products`

---

## Admin Panel UI Components

### Product Search Component (React Example)

```jsx
import { useState } from 'react';

function DropshipProductSearch() {
  const [keyword, setKeyword] = useState('');
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [platform, setPlatform] = useState('1688');

  const handleSearch = async () => {
    setLoading(true);
    try {
      const result = await searchProducts(keyword, { platform });
      setProducts(result.data?.items || []);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="dropship-search">
      <div className="search-bar">
        <select value={platform} onChange={(e) => setPlatform(e.target.value)}>
          <option value="1688">1688</option>
          <option value="taobao">Taobao</option>
          <option value="tmall">Tmall</option>
        </select>
        <input
          type="text"
          value={keyword}
          onChange={(e) => setKeyword(e.target.value)}
          placeholder="Search products..."
        />
        <button onClick={handleSearch} disabled={loading}>
          {loading ? 'Searching...' : 'Search'}
        </button>
      </div>

      <div className="product-grid">
        {products.map((product) => (
          <ProductCard
            key={product.item_id}
            product={product}
            onImport={() => handleImportClick(product)}
          />
        ))}
      </div>
    </div>
  );
}
```

---

## Error Handling

All endpoints return consistent error responses:

```json
{
  "success": false,
  "error": {
    "code": "PRODUCT_NOT_FOUND",
    "message": "Product with ID 123456 not found on 1688"
  }
}
```

### Common Error Codes

| Code | Description |
|------|-------------|
| `UNAUTHORIZED` | Invalid or expired token |
| `INVALID_PLATFORM` | Invalid platform specified |
| `PRODUCT_NOT_FOUND` | Product not found on source |
| `API_ERROR` | External API error |
| `RATE_LIMITED` | Too many requests |
| `VALIDATION_ERROR` | Invalid request parameters |

---

## Environment Configuration

```env
DROPSHIP_API_URL=http://api.tmapi.top
DROPSHIP_API_TOKEN=your_jwt_token_here
DROPSHIP_CACHE_TIMEOUT=3600
DROPSHIP_DEFAULT_PLATFORM=1688
```
```

