# Search API Documentation

Complete documentation for the unified search endpoint that allows searching products and categories simultaneously.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Search Endpoint](#search-endpoint)
4. [Query Parameters](#query-parameters)
5. [Response Format](#response-format)
6. [Examples](#examples)
7. [Error Responses](#error-responses)
8. [Best Practices](#best-practices)

---

## Overview

The Search API provides a unified search functionality that allows clients to search for both products and categories in a single request. The endpoint is designed to be accessible to both authenticated and guest users, making it perfect for public-facing search features.

### Key Features

- **Unified Search**: Search products and categories simultaneously
- **Public Access**: No authentication required (works for both authenticated and guest users)
- **Pagination**: Built-in pagination support for large result sets
- **Filtering**: Advanced filtering options for products (price range, category, stock status)
- **Sorting**: Multiple sorting options including relevance-based sorting
- **Flexible Search**: Searches across multiple fields (name, description, SKU, tags, etc.)

---

## Authentication

**Public Endpoint** (No authentication required):
- `GET /api/search` - Search products and categories

**Base URL**: `http://your-domain.com/api`

**Note**: This endpoint works for both authenticated and guest users. Authentication is optional and does not affect search results.

---

## Search Endpoint

### Endpoint
```
GET /api/search
```

### Description
Performs a unified search across products and categories based on the provided query string. Results are paginated and can be filtered by type, category, price range, and other criteria.

---

## Query Parameters

| Parameter | Type | Required | Description | Default | Example |
|-----------|------|----------|-------------|---------|---------|
| `query` | string | **Yes** | Search query string (1-255 characters) | - | `"laptop"` |
| `type` | string | No | Type of results to return: `products`, `categories`, or `all` | `all` | `"products"` |
| `page` | integer | No | Page number for pagination (min: 1) | `1` | `2` |
| `per_page` | integer | No | Number of results per page (1-100) | `15` | `20` |
| `category_id` | integer | No | Filter products by category ID | - | `5` |
| `min_price` | decimal | No | Minimum price filter for products (min: 0) | - | `100.00` |
| `max_price` | decimal | No | Maximum price filter for products (min: 0, must be >= min_price) | - | `1000.00` |
| `in_stock` | boolean | No | Filter products that are in stock (`true`/`false`) | - | `true` |
| `sort_by` | string | No | Sort order: `relevance`, `name`, `price_asc`, `price_desc`, `created_at` | `relevance` | `"price_asc"` |
| `sort_order` | string | No | Sort direction: `asc` or `desc` | `desc` | `"asc"` |

### Parameter Details

#### `query` (Required)
- The search term to look for
- Searches across multiple fields:
  - **Products**: name, description, long_description, SKU, brand, model, slug, tags
  - **Categories**: name, description, slug, meta_keywords
- Minimum length: 1 character
- Maximum length: 255 characters
- Case-insensitive partial matching

#### `type` (Optional)
- `all`: Search both products and categories (default)
- `products`: Search only products
- `categories`: Search only categories

#### `sort_by` (Optional)
- `relevance`: Results sorted by relevance (exact matches first, then partial matches)
- `name`: Sort alphabetically by name
- `price_asc`: Sort products by price (lowest first) - only applies to products
- `price_desc`: Sort products by price (highest first) - only applies to products
- `created_at`: Sort by creation date

---

## Response Format

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "query": "laptop",
    "type": "all",
    "products": {
      "data": [
        {
          "id": 1,
          "name": "MacBook Pro 16-inch",
          "slug": "macbook-pro-16-inch",
          "description": "Powerful laptop for professionals",
          "long_description": "The MacBook Pro 16-inch features...",
          "price": "2499.00",
          "stock_quantity": 50,
          "sku": "MBP-16-M3-001",
          "weight": "2.15",
          "dimensions": "35.97 x 24.59 x 1.68 cm",
          "brand": "Apple",
          "model": "MacBook Pro",
          "category_id": 1,
          "tags": ["laptop", "apple", "macbook", "professional"],
          "is_active": true,
          "meta_title": "MacBook Pro 16-inch - Best Price",
          "meta_description": "Buy MacBook Pro 16-inch with M3 chip",
          "meta_keywords": "macbook, laptop, apple, m3",
          "image_url": null,
          "created_at": "2025-01-15T10:30:00.000000Z",
          "updated_at": "2025-01-15T10:30:00.000000Z",
          "category": {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics"
          },
          "media": [
            {
              "id": 1,
              "product_id": 1,
              "type": "image",
              "url": "http://your-domain.com/storage/products/1/image.jpg",
              "alt_text": "MacBook Pro front view",
              "title": "MacBook Pro",
              "is_thumbnail": true,
              "sort_order": 0
            }
          ]
        }
      ],
      "current_page": 1,
      "per_page": 15,
      "total": 25,
      "last_page": 2,
      "from": 1,
      "to": 15,
      "has_more_pages": true,
      "links": {
        "first": "http://your-domain.com/api/search?query=laptop&type=all&page=1",
        "last": "http://your-domain.com/api/search?query=laptop&type=all&page=2",
        "prev": null,
        "next": "http://your-domain.com/api/search?query=laptop&type=all&page=2"
      }
    },
    "categories": {
      "data": [
        {
          "id": 1,
          "name": "Laptops",
          "slug": "laptops",
          "description": "All laptop computers",
          "parent_id": null,
          "image_url": "http://your-domain.com/storage/categories/laptops.jpg",
          "icon": null,
          "sort_order": 1,
          "is_active": true,
          "is_featured": true,
          "meta_title": "Laptops - Best Deals",
          "meta_description": "Browse our collection of laptops",
          "meta_keywords": "laptops, computers, notebooks",
          "created_at": "2025-01-10T08:00:00.000000Z",
          "updated_at": "2025-01-10T08:00:00.000000Z",
          "parent": null,
          "children": []
        }
      ],
      "current_page": 1,
      "per_page": 15,
      "total": 3,
      "last_page": 1,
      "from": 1,
      "to": 3,
      "has_more_pages": false,
      "links": {
        "first": "http://your-domain.com/api/search?query=laptop&type=all&page=1",
        "last": "http://your-domain.com/api/search?query=laptop&type=all&page=1",
        "prev": null,
        "next": null
      }
    },
    "meta": {
      "total_results": 28,
      "products_count": 25,
      "categories_count": 3
    }
  }
}
```

### Response Structure

#### Top Level
- `success`: Boolean indicating request success
- `data`: Object containing search results and metadata

#### Data Object
- `query`: The original search query
- `type`: The search type used (`all`, `products`, or `categories`)
- `products`: Product search results (null if type is `categories`)
- `categories`: Category search results (null if type is `products`)
- `meta`: Summary metadata

#### Products/Categories Pagination Object
- `data`: Array of result items
- `current_page`: Current page number
- `per_page`: Items per page
- `total`: Total number of results
- `last_page`: Last page number
- `from`: First item number on current page
- `to`: Last item number on current page
- `has_more_pages`: Boolean indicating if more pages exist
- `links`: Navigation links object

#### Meta Object
- `total_results`: Total count of all results (products + categories)
- `products_count`: Total number of product results
- `categories_count`: Total number of category results

---

## Examples

### Example 1: Basic Search (All Types)

Search for "laptop" across both products and categories.

**Request:**
```bash
GET /api/search?query=laptop
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=laptop"
```

**Response:** Returns both products and categories matching "laptop"

---

### Example 2: Search Products Only

Search only for products matching "apple".

**Request:**
```bash
GET /api/search?query=apple&type=products
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=apple&type=products"
```

**Response:** Returns only product results

---

### Example 3: Search with Pagination

Search with custom pagination settings.

**Request:**
```bash
GET /api/search?query=phone&page=2&per_page=20
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=phone&page=2&per_page=20"
```

**Response:** Returns page 2 with 20 items per page

---

### Example 4: Search with Price Filter

Search products within a specific price range.

**Request:**
```bash
GET /api/search?query=laptop&type=products&min_price=500&max_price=2000
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=laptop&type=products&min_price=500&max_price=2000"
```

**Response:** Returns only products priced between $500 and $2000

---

### Example 5: Search with Category Filter

Search products in a specific category.

**Request:**
```bash
GET /api/search?query=phone&type=products&category_id=5
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=phone&type=products&category_id=5"
```

**Response:** Returns only products matching "phone" in category ID 5

---

### Example 6: Search In-Stock Products Only

Search only for products that are currently in stock.

**Request:**
```bash
GET /api/search?query=tablet&type=products&in_stock=true
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=tablet&type=products&in_stock=true"
```

**Response:** Returns only products with stock_quantity > 0

---

### Example 7: Search with Sorting

Search products sorted by price (lowest first).

**Request:**
```bash
GET /api/search?query=watch&type=products&sort_by=price_asc
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=watch&type=products&sort_by=price_asc"
```

**Response:** Returns products sorted by price ascending

---

### Example 8: Complex Search

Combine multiple filters and options.

**Request:**
```bash
GET /api/search?query=smartphone&type=products&category_id=3&min_price=200&max_price=800&in_stock=true&sort_by=price_asc&page=1&per_page=10
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=smartphone&type=products&category_id=3&min_price=200&max_price=800&in_stock=true&sort_by=price_asc&page=1&per_page=10"
```

**Response:** Returns in-stock smartphones in category 3, priced between $200-$800, sorted by price (lowest first), 10 per page

---

### Example 9: Search Categories Only

Search only for categories matching the query.

**Request:**
```bash
GET /api/search?query=electronics&type=categories
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=electronics&type=categories"
```

**Response:** Returns only category results

---

### Example 10: Search with Relevance Sorting

Use default relevance-based sorting (exact matches first).

**Request:**
```bash
GET /api/search?query=macbook&sort_by=relevance
```

**cURL:**
```bash
curl -X GET "http://your-domain.com/api/search?query=macbook&sort_by=relevance"
```

**Response:** Results sorted by relevance (exact name matches appear first)

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

**Missing Query Parameter:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "query": ["The query field is required."]
  }
}
```

**Invalid Type:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "type": ["The selected type is invalid."]
  }
}
```

**Invalid Price Range:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "max_price": ["The max price must be greater than or equal to min price."]
  }
}
```

**Invalid Pagination:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "page": ["The page must be at least 1."],
    "per_page": ["The per page may not be greater than 100."]
  }
}
```

**Invalid Category ID:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "category_id": ["The selected category id is invalid."]
  }
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Search failed",
  "error": "Database connection error"
}
```

**Note**: In production mode, the `error` field may be omitted for security reasons.

---

## Best Practices

### 1. Query String Optimization
- Use specific, meaningful search terms
- Avoid overly long queries (max 255 characters)
- Consider implementing debouncing on the client side for real-time search

### 2. Pagination
- Use appropriate `per_page` values (recommended: 15-30 items)
- Implement proper pagination controls in the UI
- Consider caching frequently accessed pages

### 3. Filtering
- Combine filters to narrow down results effectively
- Use `category_id` filter when users are browsing within a category
- Use price filters to help users find products within their budget

### 4. Sorting
- Use `relevance` sorting for general searches (default)
- Use `price_asc` or `price_desc` for price-based browsing
- Use `name` sorting for alphabetical listings

### 5. Type Selection
- Use `type=products` when building product search features
- Use `type=categories` when building category navigation
- Use `type=all` for general search functionality

### 6. Performance
- Implement client-side caching for frequently searched terms
- Use debouncing for real-time search (wait 300-500ms after user stops typing)
- Consider implementing search result caching on the server side

### 7. User Experience
- Display both products and categories in search results when `type=all`
- Show total result counts to help users understand search scope
- Provide clear feedback when no results are found
- Highlight search terms in results

### 8. Error Handling
- Always handle validation errors gracefully
- Provide user-friendly error messages
- Implement retry logic for server errors

---

## Search Behavior Details

### Product Search Fields

The search queries the following product fields:
- `name`: Product name
- `description`: Short description
- `long_description`: Detailed description
- `sku`: Stock Keeping Unit
- `brand`: Brand name
- `model`: Model name
- `slug`: URL-friendly identifier
- `tags`: JSON array of tags (exact match)

### Category Search Fields

The search queries the following category fields:
- `name`: Category name
- `description`: Category description
- `slug`: URL-friendly identifier
- `meta_keywords`: SEO keywords

### Relevance Sorting

When `sort_by=relevance` (default):
- **Products**: 
  1. Exact name match
  2. Name starts with query
  3. Exact SKU match
  4. Description contains query
  5. Other matches
- **Categories**:
  1. Exact name match
  2. Name starts with query
  3. Exact slug match
  4. Other matches

### Active Status Filtering

- Only active products (`is_active = true`) are returned
- Only active categories (`is_active = true`) are returned
- Inactive items are automatically excluded from search results

---

## Response Variations

### When Type is "products"

```json
{
  "success": true,
  "data": {
    "query": "laptop",
    "type": "products",
    "products": { /* pagination object */ },
    "categories": null,
    "meta": {
      "total_results": 25,
      "products_count": 25,
      "categories_count": 0
    }
  }
}
```

### When Type is "categories"

```json
{
  "success": true,
  "data": {
    "query": "electronics",
    "type": "categories",
    "products": null,
    "categories": { /* pagination object */ },
    "meta": {
      "total_results": 3,
      "products_count": 0,
      "categories_count": 3
    }
  }
}
```

### When No Results Found

```json
{
  "success": true,
  "data": {
    "query": "nonexistent",
    "type": "all",
    "products": {
      "data": [],
      "current_page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1,
      "from": null,
      "to": null,
      "has_more_pages": false,
      "links": { /* navigation links */ }
    },
    "categories": {
      "data": [],
      "current_page": 1,
      "per_page": 15,
      "total": 0,
      "last_page": 1,
      "from": null,
      "to": null,
      "has_more_pages": false,
      "links": { /* navigation links */ }
    },
    "meta": {
      "total_results": 0,
      "products_count": 0,
      "categories_count": 0
    }
  }
}
```

---

## Integration Examples

### JavaScript/Fetch Example

```javascript
async function searchProducts(query, filters = {}) {
  const params = new URLSearchParams({
    query: query,
    type: 'products',
    ...filters
  });

  try {
    const response = await fetch(`/api/search?${params}`);
    const data = await response.json();
    
    if (data.success) {
      return data.data.products;
    } else {
      console.error('Search failed:', data.message);
      return null;
    }
  } catch (error) {
    console.error('Search error:', error);
    return null;
  }
}

// Usage
const results = await searchProducts('laptop', {
  min_price: 500,
  max_price: 2000,
  in_stock: true,
  sort_by: 'price_asc',
  per_page: 20
});
```

### React Hook Example

```javascript
import { useState, useEffect } from 'react';

function useSearch(query, options = {}) {
  const [results, setResults] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (!query || query.length < 1) {
      setResults(null);
      return;
    }

    const search = async () => {
      setLoading(true);
      setError(null);

      const params = new URLSearchParams({
        query,
        ...options
      });

      try {
        const response = await fetch(`/api/search?${params}`);
        const data = await response.json();

        if (data.success) {
          setResults(data.data);
        } else {
          setError(data.message);
        }
      } catch (err) {
        setError('Search failed');
      } finally {
        setLoading(false);
      }
    };

    // Debounce search
    const timeoutId = setTimeout(search, 300);
    return () => clearTimeout(timeoutId);
  }, [query, JSON.stringify(options)]);

  return { results, loading, error };
}

// Usage
function SearchComponent() {
  const [query, setQuery] = useState('');
  const { results, loading, error } = useSearch(query, {
    type: 'all',
    per_page: 15
  });

  return (
    <div>
      <input
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search..."
      />
      {loading && <p>Searching...</p>}
      {error && <p>Error: {error}</p>}
      {results && (
        <div>
          <p>Found {results.meta.total_results} results</p>
          {/* Render results */}
        </div>
      )}
    </div>
  );
}
```

---

## Additional Notes

- **Case Insensitive**: All searches are case-insensitive
- **Partial Matching**: Uses `LIKE` queries for partial matching
- **Active Items Only**: Only returns active products and categories
- **Pagination**: Separate pagination for products and categories when `type=all`
- **Performance**: Consider implementing full-text search indexes for better performance with large datasets
- **Rate Limiting**: Consider implementing rate limiting for production use
- **Caching**: Search results can be cached to improve performance

---

## Support

For issues or questions, please refer to the main API documentation or contact the development team.

