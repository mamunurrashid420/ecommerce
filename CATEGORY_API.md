# Category API Documentation

## Overview
Complete Category management API for e-commerce with hierarchical categories, SEO features, featured categories, and full CRUD operations. Supports parent-child relationships, image uploads, and advanced filtering.

## Base URL
```
http://localhost:8000/api
```

## Authentication
- **Public endpoints:** No authentication required
- **Admin endpoints:** Require `Authorization: Bearer {admin_token}` header
- **Content-Type:** `application/json` or `multipart/form-data` (for image uploads)
- **Accept:** `application/json` (always required)

---

## 📋 Complete API Endpoints

### 🔓 Public Endpoints

#### 1. Get All Categories
**GET** `/api/categories`

**Description:** Retrieve categories with filtering, sorting, and optional pagination.

**Query Parameters:**
- `featured` (boolean) - Filter featured categories only (`true`/`false`)
- `parent_only` (boolean) - Get only parent categories (`true`/`false`)
- `active` (boolean) - Filter active categories only (`true`/`false`)
- `with_children` (boolean) - Include child categories (`true`/`false`)
- `paginate` (boolean) - Enable pagination (`true`/`false`)
- `per_page` (integer) - Items per page (default: 15, only if paginate=true)
- `sort_by` (string) - Sort field (default: `sort_order`)
- `sort_order` (string) - Sort direction (`asc`/`desc`, default: `asc`)

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic products and gadgets",
            "slug": "electronics",
            "parent_id": null,
            "image_url": "http://localhost:8000/storage/categories/electronics.jpg",
            "icon": "fas fa-laptop",
            "sort_order": 1,
            "is_active": true,
            "is_featured": true,
            "meta_title": "Electronics - Best Deals Online",
            "meta_description": "Shop the latest electronics with great deals and fast shipping",
            "meta_keywords": "electronics, gadgets, technology, deals",
            "created_by": 1,
            "updated_by": 1,
            "created_at": "2025-11-05T05:00:00.000000Z",
            "updated_at": "2025-11-05T05:00:00.000000Z",
            "full_image_url": "http://localhost:8000/storage/categories/electronics.jpg",
            "active_products_count": 25,
            "hierarchy_path": "Electronics",
            "parent": null,
            "children": [
                {
                    "id": 2,
                    "name": "Smartphones",
                    "description": "Latest smartphones and accessories",
                    "slug": "smartphones",
                    "parent_id": 1,
                    "image_url": "http://localhost:8000/storage/categories/smartphones.jpg",
                    "icon": "fas fa-mobile-alt",
                    "sort_order": 1,
                    "is_active": true,
                    "is_featured": false,
                    "active_products_count": 12,
                    "hierarchy_path": "Electronics > Smartphones"
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
        }
    ]
}
```

#### 2. Get Categories Dropdown
**GET** `/api/categories/dropdown`

**Description:** Get simplified category list for dropdown/select components in product creation forms.

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "parent_id": null
        },
        {
            "id": 2,
            "name": "Electronics > Smartphones",
            "parent_id": 1
        },
        {
            "id": 3,
            "name": "Electronics > Laptops",
            "parent_id": 1
        },
        {
            "id": 4,
            "name": "Clothing",
            "parent_id": null
        },
        {
            "id": 5,
            "name": "Clothing > Men's Wear",
            "parent_id": 4
        }
    ]
}
```

#### 3. Get Featured Categories
**GET** `/api/categories/featured`

**Description:** Get only featured categories for homepage/promotional displays.

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "description": "Electronic products and gadgets",
            "slug": "electronics",
            "parent_id": null,
            "image_url": "http://localhost:8000/storage/categories/electronics.jpg",
            "icon": "fas fa-laptop",
            "sort_order": 1,
            "is_active": true,
            "is_featured": true,
            "meta_title": "Electronics - Best Deals Online",
            "meta_description": "Shop the latest electronics with great deals and fast shipping",
            "meta_keywords": "electronics, gadgets, technology, deals",
            "active_products_count": 25,
            "hierarchy_path": "Electronics",
            "children": [
                {
                    "id": 2,
                    "name": "Smartphones",
                    "slug": "smartphones",
                    "active_products_count": 12
                }
            ]
        }
    ]
}
```

#### 4. Get Category Tree
**GET** `/api/categories/tree`

**Description:** Get hierarchical category tree structure for navigation menus.

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics",
            "icon": "fas fa-laptop",
            "sort_order": 1,
            "is_active": true,
            "is_featured": true,
            "active_products_count": 25,
            "children": [
                {
                    "id": 2,
                    "name": "Smartphones",
                    "slug": "smartphones",
                    "icon": "fas fa-mobile-alt",
                    "sort_order": 1,
                    "is_active": true,
                    "is_featured": false,
                    "active_products_count": 12,
                    "children": []
                },
                {
                    "id": 3,
                    "name": "Laptops",
                    "slug": "laptops",
                    "icon": "fas fa-laptop",
                    "sort_order": 2,
                    "is_active": true,
                    "is_featured": false,
                    "active_products_count": 8,
                    "children": []
                }
            ]
        }
    ]
}
```

#### 5. Get Single Category
**GET** `/api/categories/{identifier}`

**Description:** Get detailed information about a specific category by ID or slug.

**URL Parameters:**
- `identifier` - Category ID (integer) or slug (string)

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Electronics",
        "description": "Electronic products and gadgets including smartphones, laptops, and accessories",
        "slug": "electronics",
        "parent_id": null,
        "image_url": "http://localhost:8000/storage/categories/electronics.jpg",
        "icon": "fas fa-laptop",
        "sort_order": 1,
        "is_active": true,
        "is_featured": true,
        "meta_title": "Electronics - Best Deals Online",
        "meta_description": "Shop the latest electronics with great deals and fast shipping",
        "meta_keywords": "electronics, gadgets, technology, deals",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-05T05:00:00.000000Z",
        "updated_at": "2025-11-05T05:00:00.000000Z",
        "full_image_url": "http://localhost:8000/storage/categories/electronics.jpg",
        "active_products_count": 25,
        "hierarchy_path": "Electronics",
        "parent": null,
        "children": [
            {
                "id": 2,
                "name": "Smartphones",
                "slug": "smartphones",
                "sort_order": 1,
                "is_active": true,
                "active_products_count": 12
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
    }
}
```

---

### 🔒 Admin Endpoints (Authentication Required)

#### 6. Create Category
**POST** `/api/categories`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json` or `multipart/form-data`

**Description:** Create a new category with optional image upload and full SEO support.

**JSON Request Body:**
```json
{
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic products and gadgets including smartphones, laptops, and accessories",
    "parent_id": null,
    "image_url": "https://example.com/electronics.jpg",
    "icon": "fas fa-laptop",
    "sort_order": 1,
    "is_active": true,
    "is_featured": true,
    "meta_title": "Electronics - Best Deals Online",
    "meta_description": "Shop the latest electronics with great deals and fast shipping. Wide selection of smartphones, laptops, and tech accessories.",
    "meta_keywords": "electronics, gadgets, technology, deals, smartphones, laptops"
}
```

**Multipart Form Data (with image):**
```
name: "Electronics"
description: "Electronic products and gadgets"
parent_id: null
icon: "fas fa-laptop"
sort_order: 1
is_active: true
is_featured: true
meta_title: "Electronics - Best Deals Online"
meta_description: "Shop the latest electronics with great deals"
meta_keywords: "electronics, gadgets, technology"
image: [File] // Image file (JPEG, PNG, JPG, GIF, WebP, max 2MB)
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Category created successfully",
    "data": {
        "id": 1,
        "name": "Electronics",
        "description": "Electronic products and gadgets",
        "slug": "electronics",
        "parent_id": null,
        "image_url": "http://localhost:8000/storage/categories/1699123456_electronics.jpg",
        "icon": "fas fa-laptop",
        "sort_order": 1,
        "is_active": true,
        "is_featured": true,
        "meta_title": "Electronics - Best Deals Online",
        "meta_description": "Shop the latest electronics with great deals and fast shipping",
        "meta_keywords": "electronics, gadgets, technology, deals",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-05T05:00:00.000000Z",
        "updated_at": "2025-11-05T05:00:00.000000Z",
        "full_image_url": "http://localhost:8000/storage/categories/1699123456_electronics.jpg",
        "parent": null,
        "children": [],
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
    }
}
```

#### 7. Update Category
**PUT** `/api/categories/{id}`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json` or `multipart/form-data`

**Description:** Update an existing category. All fields are optional for partial updates.

**URL Parameters:**
- `id` (integer) - Category ID

**Request Body (Partial Update Example):**
```json
{
    "name": "Updated Electronics",
    "is_featured": false,
    "meta_title": "Updated Electronics - Best Deals"
}
```

**Request Body (Complete Update Example):**
```json
{
    "name": "Premium Electronics",
    "slug": "premium-electronics",
    "description": "Premium electronic products and high-end gadgets",
    "parent_id": null,
    "image_url": "https://example.com/premium-electronics.jpg",
    "icon": "fas fa-microchip",
    "sort_order": 1,
    "is_active": true,
    "is_featured": true,
    "meta_title": "Premium Electronics - Luxury Tech Store",
    "meta_description": "Discover premium electronics and luxury tech products with exclusive deals",
    "meta_keywords": "premium electronics, luxury tech, high-end gadgets"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Category updated successfully",
    "data": {
        "id": 1,
        "name": "Premium Electronics",
        "slug": "premium-electronics",
        "description": "Premium electronic products and high-end gadgets",
        "parent_id": null,
        "image_url": "http://localhost:8000/storage/categories/premium-electronics.jpg",
        "icon": "fas fa-microchip",
        "sort_order": 1,
        "is_active": true,
        "is_featured": true,
        "meta_title": "Premium Electronics - Luxury Tech Store",
        "meta_description": "Discover premium electronics and luxury tech products with exclusive deals",
        "meta_keywords": "premium electronics, luxury tech, high-end gadgets",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-05T05:00:00.000000Z",
        "updated_at": "2025-11-05T06:30:00.000000Z",
        "full_image_url": "http://localhost:8000/storage/categories/premium-electronics.jpg"
    }
}
```

#### 8. Delete Category
**DELETE** `/api/categories/{id}`

**Authentication:** Required (Admin only)

**Description:** Delete a category. Cannot delete categories with products or child categories.

**URL Parameters:**
- `id` (integer) - Category ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Category deleted successfully",
    "data": {
        "id": 1,
        "name": "Premium Electronics",
        "slug": "premium-electronics"
    }
}
```

**Conflict Response (409) - Has Products:**
```json
{
    "success": false,
    "message": "Cannot delete category with existing products. Move products to another category first."
}
```

**Conflict Response (409) - Has Child Categories:**
```json
{
    "success": false,
    "message": "Cannot delete category with child categories. Delete or move child categories first."
}
```

#### 9. Update Sort Order (Bulk)
**POST** `/api/categories/sort-order`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json`

**Description:** Update sort order for multiple categories in bulk.

**Request Body:**
```json
{
    "categories": [
        {
            "id": 1,
            "sort_order": 1
        },
        {
            "id": 2,
            "sort_order": 2
        },
        {
            "id": 3,
            "sort_order": 3
        }
    ]
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Sort order updated successfully"
}
```

#### 10. Toggle Featured Status
**PUT** `/api/categories/{id}/toggle-featured`

**Authentication:** Required (Admin only)

**Description:** Toggle the featured status of a category.

**URL Parameters:**
- `id` (integer) - Category ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Featured status updated successfully",
    "data": {
        "id": 1,
        "is_featured": true
    }
}
```

#### 11. Toggle Active Status
**PUT** `/api/categories/{id}/toggle-active`

**Authentication:** Required (Admin only)

**Description:** Toggle the active status of a category.

**URL Parameters:**
- `id` (integer) - Category ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Active status updated successfully",
    "data": {
        "id": 1,
        "is_active": false
    }
}
```

---

## 📝 Field Validation Rules

### Required Fields (Create Category)
- `name`: string, max 255 characters

### Optional Fields (Create Category)
- `slug`: string, max 255 characters (auto-generated from name if not provided)
- `description`: string, category description
- `parent_id`: integer, must exist in categories table (for subcategories)
- `image_url`: valid URL format
- `icon`: string, max 100 characters (CSS class or icon identifier)
- `sort_order`: integer, minimum 0 (auto-assigned if not provided)
- `is_active`: boolean, default true
- `is_featured`: boolean, default false
- `meta_title`: string, max 255 characters
- `meta_description`: string, max 500 characters
- `meta_keywords`: string, max 500 characters
- `image`: image file (JPEG, PNG, JPG, GIF, WebP), max 2MB

### Update Category Validation
- All fields are optional for updates
- Uses `sometimes|required` for key fields
- Unique validation excludes current category ID
- Prevents circular parent-child relationships

### Business Rules
- Category cannot be its own parent
- Category cannot have its child as parent (prevents circular references)
- Categories with products cannot be deleted
- Categories with child categories cannot be deleted
- Slug is automatically generated and ensured to be unique

---

## ⚠️ Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."],
        "slug": ["The slug has already been taken."],
        "parent_id": ["The selected parent id is invalid."],
        "image": ["The image must be an image file."]
    }
}
```

### Circular Reference Error (422)
```json
{
    "success": false,
    "message": "Category cannot be its own parent"
}
```

```json
{
    "success": false,
    "message": "Cannot set a child category as parent"
}
```

### Unauthorized (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
    "success": false,
    "message": "Unauthorized. Admin access required."
}
```

### Not Found (404)
```json
{
    "success": false,
    "message": "Category not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Failed to create category",
    "error": "Database connection failed"
}
```

---

## 💻 Complete Usage Examples

### JavaScript/Fetch Examples

```javascript
// 1. Get All Categories (Public)
const getAllCategories = async (filters = {}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`/api/categories?${params}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 2. Get Categories Dropdown (for product creation)
const getCategoriesDropdown = async () => {
    const response = await fetch('/api/categories/dropdown', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 3. Get Featured Categories (Public)
const getFeaturedCategories = async () => {
    const response = await fetch('/api/categories/featured', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 4. Get Category Tree (Public)
const getCategoryTree = async () => {
    const response = await fetch('/api/categories/tree', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 5. Get Single Category (Public)
const getCategory = async (identifier) => {
    const response = await fetch(`/api/categories/${identifier}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 6. Create Category (Admin)
const createCategory = async (categoryData, token) => {
    const response = await fetch('/api/categories', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(categoryData)
    });
    return await response.json();
};

// 7. Create Category with Image (Admin)
const createCategoryWithImage = async (categoryData, imageFile, token) => {
    const formData = new FormData();
    
    // Add category data
    Object.keys(categoryData).forEach(key => {
        formData.append(key, categoryData[key]);
    });
    
    // Add image
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    const response = await fetch('/api/categories', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: formData
    });
    return await response.json();
};

// 8. Update Category (Admin)
const updateCategory = async (categoryId, updateData, token) => {
    const response = await fetch(`/api/categories/${categoryId}`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(updateData)
    });
    return await response.json();
};

// 9. Delete Category (Admin)
const deleteCategory = async (categoryId, token) => {
    const response = await fetch(`/api/categories/${categoryId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 10. Update Sort Order (Admin)
const updateSortOrder = async (categories, token) => {
    const response = await fetch('/api/categories/sort-order', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ categories })
    });
    return await response.json();
};

// 11. Toggle Featured Status (Admin)
const toggleFeatured = async (categoryId, token) => {
    const response = await fetch(`/api/categories/${categoryId}/toggle-featured`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// 12. Toggle Active Status (Admin)
const toggleActive = async (categoryId, token) => {
    const response = await fetch(`/api/categories/${categoryId}/toggle-active`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// Example usage:
const exampleUsage = async () => {
    const token = 'your-admin-token';
    
    // Get categories for dropdown in product creation
    const dropdownCategories = await getCategoriesDropdown();
    console.log('Dropdown categories:', dropdownCategories);
    
    // Create a new category
    const newCategory = {
        name: 'Electronics',
        description: 'Electronic products and gadgets',
        icon: 'fas fa-laptop',
        is_featured: true,
        meta_title: 'Electronics - Best Deals',
        meta_description: 'Shop electronics with great deals',
        meta_keywords: 'electronics, gadgets, technology'
    };
    
    const result = await createCategory(newCategory, token);
    console.log('Category created:', result);
    
    // Get featured categories for homepage
    const featured = await getFeaturedCategories();
    console.log('Featured categories:', featured);
};
```

### cURL Examples

```bash
# 1. Get All Categories (Public)
curl -X GET "http://localhost:8000/api/categories" \
  -H "Accept: application/json"

# 2. Get Categories with Filters
curl -X GET "http://localhost:8000/api/categories?featured=true&active=true&with_children=true" \
  -H "Accept: application/json"

# 3. Get Categories Dropdown
curl -X GET "http://localhost:8000/api/categories/dropdown" \
  -H "Accept: application/json"

# 4. Get Featured Categories
curl -X GET "http://localhost:8000/api/categories/featured" \
  -H "Accept: application/json"

# 5. Get Category Tree
curl -X GET "http://localhost:8000/api/categories/tree" \
  -H "Accept: application/json"

# 6. Get Single Category
curl -X GET "http://localhost:8000/api/categories/1" \
  -H "Accept: application/json"

# 7. Create Category (JSON)
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic products and gadgets",
    "icon": "fas fa-laptop",
    "is_featured": true,
    "meta_title": "Electronics - Best Deals Online",
    "meta_description": "Shop the latest electronics with great deals",
    "meta_keywords": "electronics, gadgets, technology"
  }'

# 8. Create Category with Image
curl -X POST "http://localhost:8000/api/categories" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -F "name=Electronics" \
  -F "description=Electronic products and gadgets" \
  -F "icon=fas fa-laptop" \
  -F "is_featured=true" \
  -F "meta_title=Electronics - Best Deals Online" \
  -F "meta_description=Shop the latest electronics" \
  -F "meta_keywords=electronics, gadgets, technology" \
  -F "image=@/path/to/electronics.jpg"

# 9. Update Category
curl -X PUT "http://localhost:8000/api/categories/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Electronics",
    "is_featured": false,
    "meta_title": "Updated Electronics - Best Deals"
  }'

# 10. Delete Category
curl -X DELETE "http://localhost:8000/api/categories/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"

# 11. Update Sort Order
curl -X POST "http://localhost:8000/api/categories/sort-order" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "categories": [
      {"id": 1, "sort_order": 1},
      {"id": 2, "sort_order": 2},
      {"id": 3, "sort_order": 3}
    ]
  }'

# 12. Toggle Featured Status
curl -X PUT "http://localhost:8000/api/categories/1/toggle-featured" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"

# 13. Toggle Active Status
curl -X PUT "http://localhost:8000/api/categories/1/toggle-active" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔧 Business Logic & Features

### Hierarchical Categories
- **Parent-child relationships**: Support for unlimited category depth
- **Circular reference prevention**: Cannot set child as parent
- **Hierarchy path**: Automatic generation of breadcrumb-style paths
- **Tree structure**: Efficient querying of category trees

### SEO Optimization
- **Meta title, description, keywords**: Full SEO support
- **Slug generation**: Automatic URL-friendly slug creation
- **Slug uniqueness**: Automatic handling of duplicate slugs

### Image Management
- **Image upload**: Support for category images
- **Multiple formats**: JPEG, PNG, JPG, GIF, WebP
- **File size limit**: 2MB maximum
- **Automatic cleanup**: Old images deleted when updated
- **Full URL generation**: Automatic conversion to complete URLs

### Featured Categories
- **Homepage display**: Mark categories for promotional display
- **Featured filtering**: Easy retrieval of featured categories
- **Toggle functionality**: Quick featured status changes

### Sort Order Management
- **Custom ordering**: Manual sort order control
- **Bulk updates**: Update multiple category orders at once
- **Automatic assignment**: Default sort order for new categories

### Performance Optimization
- **Database indexes**: Optimized queries for common filters
- **Eager loading**: Efficient relationship loading
- **Caching-ready**: Structure supports caching strategies

---

## 🚀 Performance & Security

### Database Optimization
- **Indexes**: Strategic indexes for common query patterns
- **Foreign keys**: Proper referential integrity
- **Soft constraints**: Cascade deletes for parent-child relationships

### Security Features
- **Admin-only access** for all CUD operations
- **Input validation and sanitization**
- **SQL injection protection** via Eloquent ORM
- **XSS protection** through proper JSON encoding
- **File upload validation** for images

### Caching Recommendations
- Cache category tree structure
- Cache featured categories
- Cache dropdown data
- Use cache tags for selective invalidation

---

## 📊 API Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/categories` | Public | Get all categories with filters |
| GET | `/api/categories/dropdown` | Public | Get categories for dropdowns |
| GET | `/api/categories/featured` | Public | Get featured categories |
| GET | `/api/categories/tree` | Public | Get category tree structure |
| GET | `/api/categories/{id}` | Public | Get single category |
| POST | `/api/categories` | Admin | Create new category |
| PUT | `/api/categories/{id}` | Admin | Update category |
| DELETE | `/api/categories/{id}` | Admin | Delete category |
| POST | `/api/categories/sort-order` | Admin | Update sort order (bulk) |
| PUT | `/api/categories/{id}/toggle-featured` | Admin | Toggle featured status |
| PUT | `/api/categories/{id}/toggle-active` | Admin | Toggle active status |

This comprehensive Category API provides everything needed for a modern e-commerce category management system with hierarchical structure, SEO optimization, and advanced admin features.