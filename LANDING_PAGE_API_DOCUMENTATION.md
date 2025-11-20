# Landing Page API Documentation

This document describes the landing page API endpoints that provide aggregated data for the ecommerce store's landing/home page.

## Base URL
All endpoints are prefixed with `/api/landing`

## Endpoints

### 1. Get All Landing Page Data

Retrieves all data needed for the landing page in a single request.

**Endpoint:** `GET /api/landing`

**Authentication:** Not required (Public endpoint)

**Query Parameters:**
- None

**Success Response (200):**
```json
{
  "success": true,
  "message": "Landing page data retrieved successfully",
  "data": {
    "hero_section": {
      "title": "My Ecommerce Store",
      "tagline": "Your one-stop shop for everything",
      "description": "<p>Welcome to our amazing ecommerce store...</p>",
      "slider_images": [
        {
          "image": "http://example.com/storage/sliders/image1.jpg",
          "title": "Welcome to Our Store",
          "subtitle": "Shop the best products",
          "hyperlink": "https://example.com/products"
        }
      ]
    },
    "site_info": {
      "store_enabled": true,
      "store_mode": "live",
      "currency": "USD",
      "currency_symbol": "$",
      "formatted_currency": "$ ",
      "free_shipping_threshold": 50.00,
      "shipping_cost": 9.99
    },
    "featured_categories": [
      {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics",
        "description": "Electronic products",
        "image_url": "http://example.com/storage/categories/electronics.jpg",
        "is_featured": true,
        "is_active": true,
        "sort_order": 1,
        "active_products_count": 25,
        "children": [
          {
            "id": 2,
            "name": "Smartphones",
            "slug": "smartphones",
            "parent_id": 1
          }
        ]
      }
    ],
    "latest_products": [
      {
        "id": 1,
        "name": "Product Name",
        "slug": "product-name",
        "description": "Product description",
        "price": "99.99",
        "stock_quantity": 50,
        "sku": "PROD-001",
        "image_url": "http://example.com/storage/products/image.jpg",
        "in_stock": true,
        "category": {
          "id": 1,
          "name": "Electronics",
          "slug": "electronics"
        },
        "media": [
          {
            "id": 1,
            "url": "http://example.com/storage/products/image1.jpg",
            "type": "image",
            "is_thumbnail": true
          }
        ]
      }
    ],
    "top_selling_products": [
      {
        "id": 2,
        "name": "Best Seller Product",
        "slug": "best-seller-product",
        "description": "Product description",
        "price": "149.99",
        "stock_quantity": 30,
        "sku": "PROD-002",
        "image_url": "http://example.com/storage/products/image2.jpg",
        "total_sold": 150,
        "in_stock": true,
        "category": {
          "id": 1,
          "name": "Electronics",
          "slug": "electronics"
        },
        "media": [
          {
            "id": 2,
            "url": "http://example.com/storage/products/image2.jpg",
            "type": "image",
            "is_thumbnail": true
          }
        ]
      }
    ],
    "featured_deals": [
      {
        "id": 1,
        "title": "Summer Sale",
        "slug": "summer-sale",
        "short_description": "Get up to 50% off",
        "description": "Full description of the deal",
        "type": "product",
        "discount_type": "percentage",
        "discount_value": "50.00",
        "discount_percentage": 50,
        "original_price": "200.00",
        "deal_price": "100.00",
        "image_url": "http://example.com/storage/deals/summer-sale.jpg",
        "banner_image_url": "http://example.com/storage/deals/summer-sale-banner.jpg",
        "start_date": "2025-11-01T00:00:00.000000Z",
        "end_date": "2025-12-31T23:59:59.000000Z",
        "time_remaining": "45 days",
        "is_valid": true
      }
    ],
    "flash_deals": [
      {
        "id": 2,
        "title": "Flash Sale - Limited Time",
        "slug": "flash-sale-limited-time",
        "short_description": "24 hour flash sale",
        "discount_type": "percentage",
        "discount_value": "30.00",
        "discount_percentage": 30,
        "original_price": "100.00",
        "deal_price": "70.00",
        "image_url": "http://example.com/storage/deals/flash-sale.jpg",
        "banner_image_url": "http://example.com/storage/deals/flash-sale-banner.jpg",
        "end_date": "2025-11-21T23:59:59.000000Z",
        "time_remaining": "12 hours"
      }
    ]
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to retrieve landing page data",
  "error": "Error message details"
}
```

---

### 2. Get Hero Section Data Only

Retrieves only the hero section data (title, tagline, description, slider images).

**Endpoint:** `GET /api/landing/hero`

**Authentication:** Not required (Public endpoint)

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "title": "My Ecommerce Store",
    "tagline": "Your one-stop shop for everything",
    "description": "<p>Welcome to our amazing ecommerce store...</p>",
    "slider_images": [
      {
        "image": "http://example.com/storage/sliders/image1.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Shop the best products",
        "hyperlink": "https://example.com/products"
      }
    ]
  }
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to retrieve hero section data",
  "error": "Error message details"
}
```

---

### 3. Get Featured Products

Retrieves the latest products for the landing page.

**Endpoint:** `GET /api/landing/featured-products`

**Authentication:** Not required (Public endpoint)

**Query Parameters:**
- `limit` (integer, optional): Number of products to return. Default: 12

**Example Request:**
```
GET /api/landing/featured-products?limit=8
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "slug": "product-name",
      "description": "Product description",
      "price": "99.99",
      "stock_quantity": 50,
      "sku": "PROD-001",
      "image_url": "http://example.com/storage/products/image.jpg",
      "in_stock": true,
      "category": {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics"
      },
      "media": [
        {
          "id": 1,
          "url": "http://example.com/storage/products/image1.jpg",
          "type": "image",
          "is_thumbnail": true
        }
      ]
    }
  ]
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to retrieve featured products",
  "error": "Error message details"
}
```

---

### 4. Get Top Selling Products

Retrieves the top selling products based on order history.

**Endpoint:** `GET /api/landing/top-selling-products`

**Authentication:** Not required (Public endpoint)

**Query Parameters:**
- `limit` (integer, optional): Number of products to return. Default: 8

**Example Request:**
```
GET /api/landing/top-selling-products?limit=10
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "name": "Best Seller Product",
      "slug": "best-seller-product",
      "description": "Product description",
      "price": "149.99",
      "stock_quantity": 30,
      "sku": "PROD-002",
      "image_url": "http://example.com/storage/products/image2.jpg",
      "total_sold": 150,
      "in_stock": true,
      "category": {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics"
      },
      "media": [
        {
          "id": 2,
          "url": "http://example.com/storage/products/image2.jpg",
          "type": "image",
          "is_thumbnail": true
        }
      ]
    }
  ]
}
```

**Error Response (500):**
```json
{
  "success": false,
  "message": "Failed to retrieve top selling products",
  "error": "Error message details"
}
```

---

## Data Structure Details

### Hero Section
- **title**: Store title from site settings
- **tagline**: Store tagline from site settings
- **description**: Store description (HTML content)
- **slider_images**: Array of slider images with title, subtitle, and hyperlink

### Site Info
- **store_enabled**: Whether the store is enabled
- **store_mode**: Store mode (live, maintenance, coming_soon)
- **currency**: Currency code (e.g., USD)
- **currency_symbol**: Currency symbol (e.g., $)
- **formatted_currency**: Formatted currency string
- **free_shipping_threshold**: Minimum order amount for free shipping
- **shipping_cost**: Default shipping cost

### Featured Categories
- Returns up to 8 featured categories
- Includes child categories (up to 5 per parent)
- Includes active products count
- Ordered by sort_order

### Latest Products
- Returns up to 12 latest products
- Only active products are included
- Includes category and media information
- Ordered by creation date (newest first)

### Top Selling Products
- Returns up to 8 top selling products
- Based on total quantity sold from order items
- Only active products are included
- Ordered by total sold (highest first)

### Featured Deals
- Returns up to 6 featured deals
- Only valid and active deals are included
- Ordered by priority and creation date
- Includes discount information and time remaining

### Flash Deals
- Returns up to 4 flash deals
- Only active flash deals with valid end dates are included
- Ordered by end date (ending soonest first)
- Includes time remaining information

---

## Usage Examples

### JavaScript (Fetch API)

**Get all landing page data:**
```javascript
fetch('http://your-domain.com/api/landing')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Hero Section:', data.data.hero_section);
      console.log('Featured Categories:', data.data.featured_categories);
      console.log('Latest Products:', data.data.latest_products);
      console.log('Featured Deals:', data.data.featured_deals);
    }
  })
  .catch(error => console.error('Error:', error));
```

**Get only hero section:**
```javascript
fetch('http://your-domain.com/api/landing/hero')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Hero Data:', data.data);
    }
  });
```

**Get featured products with custom limit:**
```javascript
fetch('http://your-domain.com/api/landing/featured-products?limit=8')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Featured Products:', data.data);
    }
  });
```

### cURL

**Get all landing page data:**
```bash
curl -X GET http://your-domain.com/api/landing \
  -H "Accept: application/json"
```

**Get hero section only:**
```bash
curl -X GET http://your-domain.com/api/landing/hero \
  -H "Accept: application/json"
```

**Get featured products:**
```bash
curl -X GET "http://your-domain.com/api/landing/featured-products?limit=8" \
  -H "Accept: application/json"
```

**Get top selling products:**
```bash
curl -X GET "http://your-domain.com/api/landing/top-selling-products?limit=10" \
  -H "Accept: application/json"
```

---

## Performance Considerations

1. **Caching**: Consider implementing caching for landing page data as it changes infrequently
2. **Pagination**: The main endpoint returns limited data (8-12 items per section) for performance
3. **Lazy Loading**: Use individual endpoints if you only need specific sections
4. **Image Optimization**: Ensure product and deal images are optimized for web

---

## Error Handling

All endpoints return appropriate HTTP status codes:
- `200` - Success
- `500` - Server Error

Error responses follow this format:
```json
{
  "success": false,
  "message": "Error message",
  "error": "Detailed error information (in development mode)"
}
```

---

## Notes

- All endpoints are public and do not require authentication
- Only active products and categories are returned
- Deals are filtered to show only valid deals (within date range and active)
- Product stock information is included to help with availability display
- Media URLs are full paths including the storage base URL

---

## Support

For issues or questions regarding the landing page API, please contact the development team.

