# Category Public API Documentation

Complete documentation for public category endpoints used in product listing pages.

## Table of Contents
1. [Authentication](#authentication)
2. [List Categories (Public)](#1-list-categories-public)
3. [Error Responses](#error-responses)

---

## Authentication

**Public Endpoints** (No authentication required):
- `GET /api/categories` - List categories

**Base URL**: `http://your-domain.com/api`

---

## 1. List Categories (Public)

Get a list of categories with optional filtering and sorting. This endpoint is designed for use in product listing pages to display category filters and navigation.

### Endpoint
```
GET /api/categories
```

### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `featured` | string | Filter by featured categories (`true`/`false`) | All categories |
| `parent_only` | string | Return only parent categories (`true`/`false`) | All categories |
| `active` | string | Filter by active status (`true`/`false`) | All categories |
| `with_children` | string | Include child categories in response (`true`/`false`) | false |
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
      "children": [
        {
          "id": 2,
          "name": "Laptops",
          "slug": "laptops",
          "parent_id": 1,
          "is_active": true,
          "sort_order": 1
        },
        {
          "id": 3,
          "name": "Smartphones",
          "slug": "smartphones",
          "parent_id": 1,
          "is_active": true,
          "sort_order": 2
        }
      ],
      "creator": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      },
      "updater": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com"
      }
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
      "children": [],
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
        ...
      }
    ],
    "first_page_url": "http://your-domain.com/api/categories?page=1",
    "from": 1,
    "last_page": 3,
    "last_page_url": "http://your-domain.com/api/categories?page=3",
    "links": [...],
    "next_page_url": "http://your-domain.com/api/categories?page=2",
    "path": "http://your-domain.com/api/categories",
    "per_page": 15,
    "prev_page_url": null,
    "to": 15,
    "total": 35
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Category ID |
| `name` | string | Category name |
| `slug` | string | URL-friendly identifier |
| `description` | string | Category description |
| `parent_id` | integer\|null | Parent category ID (null for top-level) |
| `image_url` | string\|null | Category image URL |
| `icon` | string\|null | Category icon identifier |
| `sort_order` | integer | Display order |
| `is_active` | boolean | Active status |
| `is_featured` | boolean | Featured status |
| `meta_title` | string\|null | SEO meta title |
| `meta_description` | string\|null | SEO meta description |
| `meta_keywords` | string\|null | SEO meta keywords |
| `full_image_url` | string\|null | Full image URL (with domain) |
| `active_products_count` | integer | Count of active products in category |
| `parent` | object\|null | Parent category object |
| `children` | array | Child categories array |
| `creator` | object\|null | User who created the category |
| `updater` | object\|null | User who last updated the category |

### cURL Examples

**Get all active categories:**
```bash
curl -X GET "http://your-domain.com/api/categories?active=true"
```

**Get only featured categories:**
```bash
curl -X GET "http://your-domain.com/api/categories?featured=true"
```

**Get only parent categories with children:**
```bash
curl -X GET "http://your-domain.com/api/categories?parent_only=true&with_children=true"
```

**Get categories with pagination:**
```bash
curl -X GET "http://your-domain.com/api/categories?paginate=true&per_page=20&page=1"
```

**Get categories sorted by name:**
```bash
curl -X GET "http://your-domain.com/api/categories?sort_by=name&sort_order=asc"
```

**Get active parent categories with children (for navigation menu):**
```bash
curl -X GET "http://your-domain.com/api/categories?active=true&parent_only=true&with_children=true&sort_by=sort_order&sort_order=asc"
```

### Use Cases

#### 1. Product List Page - Category Filter

Use this endpoint to populate a category filter dropdown on the product listing page:

```bash
# Get all active categories for filter dropdown
GET /api/categories?active=true&sort_by=name&sort_order=asc
```

#### 2. Navigation Menu

Get parent categories with their children for a hierarchical navigation menu:

```bash
# Get parent categories with active children
GET /api/categories?parent_only=true&active=true&with_children=true&sort_by=sort_order
```

#### 3. Featured Categories Section

Display featured categories on the homepage or category page:

```bash
# Get featured categories
GET /api/categories?featured=true&active=true&sort_by=sort_order
```

#### 4. Category Grid/List View

Get paginated categories for a category listing page:

```bash
# Get paginated categories
GET /api/categories?paginate=true&per_page=12&page=1&active=true
```

### Notes

- By default, returns all categories without pagination
- Only active child categories are included when `with_children=true`
- The `active_products_count` field shows the count of active products in each category
- Categories are sorted by `sort_order` by default
- The `full_image_url` field provides a complete URL for category images
- Parent-child relationships are included in the response
- All timestamps are in ISO 8601 format (UTC)

### Error Response (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to fetch categories",
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
  "message": "Failed to fetch categories",
  "error": "Database connection error"
}
```

---

## Integration Examples

### React/Next.js Example

```javascript
// Fetch categories for product list page
async function fetchCategories() {
  const response = await fetch('http://your-domain.com/api/categories?active=true&sort_by=name');
  const data = await response.json();
  
  if (data.success) {
    return data.data;
  }
  throw new Error(data.message);
}

// Usage in component
const CategoryFilter = () => {
  const [categories, setCategories] = useState([]);
  
  useEffect(() => {
    fetchCategories()
      .then(setCategories)
      .catch(console.error);
  }, []);
  
  return (
    <select>
      <option value="">All Categories</option>
      {categories.map(category => (
        <option key={category.id} value={category.id}>
          {category.name} ({category.active_products_count})
        </option>
      ))}
    </select>
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
      const response = await fetch('http://your-domain.com/api/categories?active=true');
      const data = await response.json();
      if (data.success) {
        this.categories = data.data;
      }
    } catch (error) {
      console.error('Failed to fetch categories:', error);
    }
  }
}
```

### jQuery Example

```javascript
// Fetch categories
$.ajax({
  url: 'http://your-domain.com/api/categories',
  method: 'GET',
  data: {
    active: true,
    sort_by: 'name',
    sort_order: 'asc'
  },
  success: function(response) {
    if (response.success) {
      // Populate category filter
      response.data.forEach(function(category) {
        $('#category-filter').append(
          $('<option>', {
            value: category.id,
            text: category.name + ' (' + category.active_products_count + ')'
          })
        );
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

1. **Caching**: Consider caching category data on the frontend since categories don't change frequently
2. **Filtering**: Use `active=true` to only show active categories to end users
3. **Performance**: Use `parent_only=true` when you only need top-level categories
4. **Pagination**: Enable pagination when displaying many categories
5. **Sorting**: Use `sort_by=sort_order` to respect the admin-defined display order
6. **Children**: Only request `with_children=true` when you need to display hierarchical navigation
7. **Featured**: Use `featured=true` for homepage or special category sections

---

## Additional Notes

- All timestamps are in ISO 8601 format (UTC)
- Category images are stored in `storage/app/public/categories/`
- The `active_products_count` is calculated dynamically and includes only active products
- Child categories are automatically filtered to show only active ones when `with_children=true`
- The `full_image_url` field automatically handles relative and absolute URLs
- Categories can have unlimited nesting levels (parent-child relationships)

---

## Support

For issues or questions, please refer to the main API documentation or contact the development team.

