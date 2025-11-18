# Category with Products API Documentation

Complete documentation for the categories with latest products endpoint, designed for the categories listing page.

## Table of Contents
1. [Authentication](#authentication)
2. [Get Categories with Latest Products](#1-get-categories-with-latest-products)
3. [Error Responses](#error-responses)

---

## Authentication

**Public Endpoint** (No authentication required):
- `GET /api/categories/with-products` - Get categories with their 8 latest products

**Base URL**: `http://your-domain.com/api`

---

## 1. Get Categories with Latest Products

Get a list of categories, each including their 8 latest active products. This endpoint is specifically designed for the categories listing page where you want to display categories along with a preview of their latest products.

### Endpoint
```
GET /api/categories/with-products
```

### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `featured` | string | Filter by featured categories (`true`/`false`) | All categories |
| `parent_only` | string | Return only parent categories (`true`/`false`) | All categories |
| `active` | string | Filter by active status (`true`/`false`) | All categories |
| `sort_by` | string | Sort field (`sort_order`, `name`, `created_at`) | `sort_order` |
| `sort_order` | string | Sort direction (`asc`/`desc`) | `asc` |
| `paginate` | string | Enable pagination (`true`/`false`) | false |
| `per_page` | integer | Items per page (when pagination enabled) | 15 |

### Success Response (200 OK)

**Without Pagination (Default):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "description": "Electronic devices and gadgets",
      "parent_id": null,
      "image_url": "http://your-domain.com/storage/categories/electronics.jpg",
      "icon": "electronics-icon",
      "sort_order": 1,
      "is_active": true,
      "is_featured": true,
      "meta_title": "Electronics - Best Deals",
      "meta_description": "Shop the latest electronics",
      "meta_keywords": "electronics, gadgets, devices",
      "created_by": 1,
      "updated_by": 1,
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z",
      "full_image_url": "http://your-domain.com/storage/categories/electronics.jpg",
      "active_products_count": 45,
      "parent": null,
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "updater": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "products": [
        {
          "id": 10,
          "name": "MacBook Pro 16-inch",
          "slug": "macbook-pro-16-inch",
          "description": "Powerful laptop for professionals",
          "price": "2499.00",
          "stock_quantity": 50,
          "sku": "MBP-16-M3-001",
          "brand": "Apple",
          "model": "MacBook Pro",
          "category_id": 1,
          "is_active": true,
          "created_at": "2025-01-20T10:30:00.000000Z",
          "updated_at": "2025-01-20T10:30:00.000000Z",
          "category": {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics"
          },
          "media": [
            {
              "id": 1,
              "product_id": 10,
              "type": "image",
              "url": "http://your-domain.com/storage/products/10/macbook.jpg",
              "alt_text": "MacBook Pro front view",
              "title": "MacBook Pro",
              "is_thumbnail": true,
              "sort_order": 0
            }
          ]
        },
        {
          "id": 9,
          "name": "iPhone 15 Pro",
          "slug": "iphone-15-pro",
          "description": "Latest iPhone with advanced features",
          "price": "999.00",
          "stock_quantity": 30,
          "sku": "IPH-15-PRO-001",
          "brand": "Apple",
          "model": "iPhone 15 Pro",
          "category_id": 1,
          "is_active": true,
          "created_at": "2025-01-19T10:30:00.000000Z",
          "updated_at": "2025-01-19T10:30:00.000000Z",
          "category": {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics"
          },
          "media": [
            {
              "id": 2,
              "product_id": 9,
              "type": "image",
              "url": "http://your-domain.com/storage/products/9/iphone.jpg",
              "alt_text": "iPhone 15 Pro",
              "title": "iPhone 15 Pro",
              "is_thumbnail": true,
              "sort_order": 0
            }
          ]
        }
      ]
    },
    {
      "id": 4,
      "name": "Clothing",
      "slug": "clothing",
      "description": "Fashion and apparel",
      "parent_id": null,
      "image_url": "http://your-domain.com/storage/categories/clothing.jpg",
      "icon": "clothing-icon",
      "sort_order": 2,
      "is_active": true,
      "is_featured": false,
      "active_products_count": 120,
      "parent": null,
      "products": [
        {
          "id": 8,
          "name": "Cotton T-Shirt",
          "slug": "cotton-t-shirt",
          "description": "Comfortable cotton t-shirt",
          "price": "29.99",
          "stock_quantity": 100,
          "sku": "TSH-COT-001",
          "brand": "Fashion Brand",
          "category_id": 4,
          "is_active": true,
          "created_at": "2025-01-18T10:30:00.000000Z",
          "updated_at": "2025-01-18T10:30:00.000000Z",
          "category": {
            "id": 4,
            "name": "Clothing",
            "slug": "clothing"
          },
          "media": []
        }
      ],
      "creator": {...},
      "updater": {...}
    }
  ]
}
```

**With Pagination:**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Electronics",
        "slug": "electronics",
        "active_products_count": 45,
        "products": [...],
        ...
      }
    ],
    "first_page_url": "http://your-domain.com/api/categories/with-products?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://your-domain.com/api/categories/with-products?page=3",
    "links": [...],
    "next_page_url": "http://your-domain.com/api/categories/with-products?page=2",
    "path": "http://your-domain.com/api/categories/with-products",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 35
  }
}
```

### Response Fields

**Category Fields:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Category ID |
| `name` | string | Category name |
| `slug` | string | URL-friendly identifier |
| `description` | string | Category description |
| `parent_id` | integer\|null | Parent category ID |
| `image_url` | string\|null | Category image URL |
| `icon` | string\|null | Category icon identifier |
| `sort_order` | integer | Display order |
| `is_active` | boolean | Active status |
| `is_featured` | boolean | Featured status |
| `active_products_count` | integer | Count of active products in category |
| `parent` | object\|null | Parent category object |
| `products` | array | Array of 8 latest products (max) |
| `creator` | object\|null | User who created the category |
| `updater` | object\|null | User who last updated the category |

**Product Fields (within products array):**
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Product ID |
| `name` | string | Product name |
| `slug` | string | URL-friendly identifier |
| `description` | string | Product description |
| `price` | decimal | Product price |
| `stock_quantity` | integer | Available stock |
| `sku` | string | Stock Keeping Unit |
| `brand` | string\|null | Brand name |
| `model` | string\|null | Model name |
| `category_id` | integer | Category ID |
| `is_active` | boolean | Active status |
| `created_at` | string | Creation timestamp |
| `updated_at` | string | Last update timestamp |
| `category` | object | Category object |
| `media` | array | Product media/images array |

### cURL Examples

**Get all categories with latest products:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products"
```

**Get only active categories with products:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products?active=true"
```

**Get only featured categories with products:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products?featured=true&active=true"
```

**Get only parent categories with products:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products?parent_only=true&active=true"
```

**Get categories with pagination:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products?paginate=true&per_page=10&page=1"
```

**Get categories sorted by name:**
```bash
curl -X GET "http://your-domain.com/api/categories/with-products?sort_by=name&sort_order=asc&active=true"
```

### Use Cases

#### 1. Categories Listing Page

Display all categories with a preview of their latest products:

```bash
# Get all active categories with their 8 latest products
GET /api/categories/with-products?active=true&sort_by=sort_order&sort_order=asc
```

#### 2. Featured Categories Section

Show featured categories with product previews on homepage:

```bash
# Get featured categories with products
GET /api/categories/with-products?featured=true&active=true
```

#### 3. Parent Categories Grid

Display parent categories only with their latest products:

```bash
# Get parent categories with products
GET /api/categories/with-products?parent_only=true&active=true&sort_by=sort_order
```

### Notes

- Each category includes a maximum of **8 latest products** (ordered by `created_at` descending)
- Only **active products** (`is_active = true`) are included
- Products are sorted by creation date (newest first)
- Each product includes its `media` (images) and `category` relationship
- The `active_products_count` field shows the total count of active products in the category
- Categories without products will have an empty `products` array
- By default, returns all categories without pagination
- Categories are sorted by `sort_order` by default
- All timestamps are in ISO 8601 format (UTC)

### Product Selection Logic

- Products are selected based on:
  1. Must be active (`is_active = true`)
  2. Must belong to the category
  3. Ordered by `created_at` descending (newest first)
  4. Limited to 8 products per category

### Performance Considerations

- This endpoint uses eager loading to minimize database queries
- Products are loaded with their media and category relationships
- Consider using pagination for large category lists
- The 8-product limit helps maintain response size and performance

### Error Response (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to fetch categories with products",
  "error": "Database connection error"
}
```

---

## Error Responses

### Standard Error Format

All error responses follow this structure:

```json
{
  "success": false,
  "message": "Error message description",
  "error": "Detailed error message (in development mode)"
}
```

### HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| `200` | Success |
| `500` | Server error |

### Common Error Scenarios

**500 Server Error:**
```json
{
  "success": false,
  "message": "Failed to fetch categories with products",
  "error": "Database connection error"
}
```

---

## Integration Examples

### React/Next.js Example

```javascript
// Fetch categories with products for categories page
async function fetchCategoriesWithProducts() {
  const response = await fetch(
    'http://your-domain.com/api/categories/with-products?active=true&sort_by=sort_order'
  );
  const data = await response.json();
  
  if (data.success) {
    return data.data;
  }
  throw new Error(data.message);
}

// Usage in component
const CategoriesPage = () => {
  const [categories, setCategories] = useState([]);
  
  useEffect(() => {
    fetchCategoriesWithProducts()
      .then(setCategories)
      .catch(console.error);
  }, []);
  
  return (
    <div className="categories-grid">
      {categories.map(category => (
        <div key={category.id} className="category-card">
          <h2>{category.name}</h2>
          <p>{category.description}</p>
          <p>Total Products: {category.active_products_count}</p>
          
          <div className="products-preview">
            <h3>Latest Products</h3>
            <div className="products-grid">
              {category.products.map(product => (
                <div key={product.id} className="product-card">
                  <img 
                    src={product.media[0]?.url || '/placeholder.jpg'} 
                    alt={product.media[0]?.alt_text || product.name}
                  />
                  <h4>{product.name}</h4>
                  <p>${product.price}</p>
                  <a href={`/products/${product.slug}`}>View Product</a>
                </div>
              ))}
            </div>
          </div>
          
          <a href={`/categories/${category.slug}`}>
            View All {category.active_products_count} Products
          </a>
        </div>
      ))}
    </div>
  );
};
```

### Vue.js Example

```javascript
// In your Vue component
export default {
  data() {
    return {
      categories: []
    }
  },
  async mounted() {
    try {
      const response = await fetch(
        'http://your-domain.com/api/categories/with-products?active=true'
      );
      const data = await response.json();
      if (data.success) {
        this.categories = data.data;
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error);
    }
  },
  template: `
    <div class="categories-page">
      <div v-for="category in categories" :key="category.id" class="category-section">
        <h2>{{ category.name }}</h2>
        <p>{{ category.description }}</p>
        
        <div class="products-preview">
          <div v-for="product in category.products" :key="product.id" class="product-item">
            <img :src="product.media[0]?.url || '/placeholder.jpg'" :alt="product.name" />
            <h3>{{ product.name }}</h3>
            <p>${{ product.price }}</p>
          </div>
        </div>
        
        <router-link :to="'/categories/' + category.slug">
          View All {{ category.active_products_count }} Products
        </router-link>
      </div>
    </div>
  `
}
```

### jQuery Example

```javascript
// Fetch categories with products
$.ajax({
  url: 'http://your-domain.com/api/categories/with-products',
  method: 'GET',
  data: {
    active: true,
    sort_by: 'sort_order',
    sort_order: 'asc'
  },
  success: function(response) {
    if (response.success) {
      response.data.forEach(function(category) {
        var categoryHtml = '<div class="category-card">';
        categoryHtml += '<h2>' + category.name + '</h2>';
        categoryHtml += '<p>' + category.description + '</p>';
        categoryHtml += '<p>Total Products: ' + category.active_products_count + '</p>';
        
        categoryHtml += '<div class="products-preview">';
        category.products.forEach(function(product) {
          var productImage = product.media[0] ? product.media[0].url : '/placeholder.jpg';
          categoryHtml += '<div class="product-item">';
          categoryHtml += '<img src="' + productImage + '" alt="' + product.name + '">';
          categoryHtml += '<h3>' + product.name + '</h3>';
          categoryHtml += '<p>$' + product.price + '</p>';
          categoryHtml += '<a href="/products/' + product.slug + '">View Product</a>';
          categoryHtml += '</div>';
        });
        categoryHtml += '</div>';
        
        categoryHtml += '<a href="/categories/' + category.slug + '">View All Products</a>';
        categoryHtml += '</div>';
        
        $('#categories-container').append(categoryHtml);
      });
    }
  },
  error: function(xhr, status, error) {
    console.error('Failed to fetch categories:', error);
  }
});
```

---

## Best Practices

1. **Caching**: Consider caching this endpoint since categories and their latest products don't change frequently
2. **Filtering**: Use `active=true` to only show active categories to end users
3. **Performance**: Use `parent_only=true` when you only need top-level categories
4. **Pagination**: Enable pagination when displaying many categories
5. **Sorting**: Use `sort_by=sort_order` to respect the admin-defined display order
6. **Featured**: Use `featured=true` for homepage or special category sections
7. **Product Limit**: The 8-product limit is fixed per category - adjust your UI accordingly

---

## Comparison with Other Endpoints

| Endpoint | Products Included | Use Case |
|----------|------------------|----------|
| `GET /api/categories` | No products | Category list/navigation |
| `GET /api/categories/with-products` | 8 latest products per category | Categories listing page |
| `GET /api/categories/{category}` | No products | Single category details |
| `GET /api/products?category_id={id}` | All products in category | Category products page |

---

## Additional Notes

- All timestamps are in ISO 8601 format (UTC)
- Category images are stored in `storage/app/public/categories/`
- Product images are stored in `storage/app/public/products/{product_id}/`
- The `active_products_count` is calculated dynamically and includes only active products
- Products are ordered by creation date (newest first)
- Each category will have a maximum of 8 products, even if more exist
- Categories without any active products will have an empty `products` array

---

## Support

For issues or questions, please refer to the main API documentation or contact the development team.

