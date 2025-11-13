# Category Admin API Documentation

Complete documentation for all admin category management endpoints.

## Table of Contents
1. [Authentication](#authentication)
2. [Create Category](#1-create-category)
3. [Get Category Details](#2-get-category-details)
4. [Update Category](#3-update-category)
5. [Delete Category](#4-delete-category)
6. [Bulk Update Sort Order](#5-bulk-update-sort-order)
7. [Toggle Featured Status](#6-toggle-featured-status)
8. [Toggle Active Status](#7-toggle-active-status)
9. [Get Category Tree](#8-get-category-tree)
10. [Error Responses](#error-responses)

---

## Authentication

All admin category endpoints require:
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **Role**: Admin role (`admin`)

**Base URL**: `http://your-domain.com/api`

---

## 1. Create Category

Create a new category in the system.

### Endpoint
```
POST /api/categories
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Category name (max 255 characters) |
| `slug` | string | No | URL-friendly slug (auto-generated from name if not provided) |
| `description` | string | No | Category description |
| `parent_id` | integer | No | ID of parent category (for subcategories) |
| `image_url` | string (URL) | No | External image URL |
| `image` | file | No | Image file to upload (jpeg, png, jpg, gif, webp, max 2MB) |
| `icon` | string | No | Icon identifier (max 100 characters) |
| `sort_order` | integer | No | Display order (auto-incremented if not provided) |
| `is_active` | boolean | No | Active status (default: true) |
| `is_featured` | boolean | No | Featured status (default: false) |
| `meta_title` | string | No | SEO meta title (max 255 characters) |
| `meta_description` | string | No | SEO meta description (max 500 characters) |
| `meta_keywords` | string | No | SEO meta keywords (max 500 characters) |

### Request Example (JSON)

```json
{
  "name": "Electronics",
  "description": "Electronic devices and gadgets",
  "parent_id": null,
  "icon": "electronics-icon",
  "sort_order": 1,
  "is_active": true,
  "is_featured": true,
  "meta_title": "Electronics - Best Deals",
  "meta_description": "Shop the latest electronics at great prices",
  "meta_keywords": "electronics, gadgets, devices"
}
```

### Request Example (Form Data with Image Upload)

```
POST /api/categories
Content-Type: multipart/form-data

name: Electronics
description: Electronic devices and gadgets
parent_id: null
image: [binary file]
is_active: true
is_featured: true
```

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Category created successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and gadgets",
    "parent_id": null,
    "image_url": "http://your-domain.com/storage/categories/1234567890_electronics.jpg",
    "icon": "electronics-icon",
    "sort_order": 1,
    "is_active": true,
    "is_featured": true,
    "meta_title": "Electronics - Best Deals",
    "meta_description": "Shop the latest electronics at great prices",
    "meta_keywords": "electronics, gadgets, devices",
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
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

### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "slug": ["The slug has already been taken."],
    "parent_id": ["The selected parent id is invalid."],
    "image": ["The image must be a file of type: jpeg, png, jpg, gif, webp."]
  }
}
```

### cURL Example

```bash
curl -X POST "http://your-domain.com/api/categories" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic devices and gadgets",
    "is_active": true,
    "is_featured": true
  }'
```

### cURL Example (with Image Upload)

```bash
curl -X POST "http://your-domain.com/api/categories" \
  -H "Authorization: Bearer {token}" \
  -F "name=Electronics" \
  -F "description=Electronic devices and gadgets" \
  -F "image=@/path/to/image.jpg" \
  -F "is_active=true" \
  -F "is_featured=true"
```

---

## 2. Get Category Details

Get detailed information about a specific category. This endpoint is public but useful for admin operations.

### Endpoint
```
GET /api/categories/{category}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer or string | Category ID or slug |

### Headers
```
Authorization: Bearer {token} (Optional for public access)
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices and gadgets",
    "parent_id": null,
    "image_url": "http://your-domain.com/storage/categories/1234567890_electronics.jpg",
    "icon": "electronics-icon",
    "sort_order": 1,
    "is_active": true,
    "is_featured": true,
    "meta_title": "Electronics - Best Deals",
    "meta_description": "Shop the latest electronics at great prices",
    "meta_keywords": "electronics, gadgets, devices",
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z",
    "active_products_count": 25,
    "parent": null,
    "children": [
      {
        "id": 2,
        "name": "Smartphones",
        "slug": "smartphones",
        "sort_order": 1,
        "is_active": true
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

### Not Found Response (404)

```json
{
  "success": false,
  "message": "Category not found"
}
```

### cURL Example

```bash
# Using ID
curl -X GET "http://your-domain.com/api/categories/1" \
  -H "Authorization: Bearer {token}"

# Using slug
curl -X GET "http://your-domain.com/api/categories/electronics" \
  -H "Authorization: Bearer {token}"
```

---

## 3. Update Category

Update an existing category.

### Endpoint
```
PUT /api/categories/{category}
PATCH /api/categories/{category}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer | Category ID |

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (JSON)

All fields are optional. Only include fields you want to update.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | No | Category name (max 255 characters) |
| `slug` | string | No | URL-friendly slug |
| `description` | string | No | Category description |
| `parent_id` | integer | No | ID of parent category (null for root category) |
| `image_url` | string (URL) | No | External image URL |
| `image` | file | No | Image file to upload (replaces existing image) |
| `icon` | string | No | Icon identifier (max 100 characters) |
| `sort_order` | integer | No | Display order |
| `is_active` | boolean | No | Active status |
| `is_featured` | boolean | No | Featured status |
| `meta_title` | string | No | SEO meta title (max 255 characters) |
| `meta_description` | string | No | SEO meta description (max 500 characters) |
| `meta_keywords` | string | No | SEO meta keywords (max 500 characters) |

### Request Example

```json
{
  "name": "Electronics & Gadgets",
  "description": "Updated description",
  "is_featured": false,
  "sort_order": 2
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Category updated successfully",
  "data": {
    "id": 1,
    "name": "Electronics & Gadgets",
    "slug": "electronics-gadgets",
    "description": "Updated description",
    "parent_id": null,
    "image_url": "http://your-domain.com/storage/categories/1234567890_electronics.jpg",
    "icon": "electronics-icon",
    "sort_order": 2,
    "is_active": true,
    "is_featured": false,
    "meta_title": "Electronics - Best Deals",
    "meta_description": "Shop the latest electronics at great prices",
    "meta_keywords": "electronics, gadgets, devices",
    "created_by": 1,
    "updated_by": 1,
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T11:45:00.000000Z",
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

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "slug": ["The slug has already been taken."],
    "parent_id": ["Category cannot be its own parent."]
  }
}
```

### Business Logic Errors

**Cannot set category as its own parent:**
```json
{
  "success": false,
  "message": "Category cannot be its own parent"
}
```

**Cannot set child category as parent:**
```json
{
  "success": false,
  "message": "Cannot set a child category as parent"
}
```

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/categories/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics & Gadgets",
    "is_featured": false
  }'
```

### Notes

- When updating `name`, the `slug` is automatically regenerated if not explicitly provided
- Uploading a new `image` will automatically delete the old image file
- The `updated_by` field is automatically set to the authenticated user

---

## 4. Delete Category

Delete a category from the system.

### Endpoint
```
DELETE /api/categories/{category}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer | Category ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Category deleted successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics"
  }
}
```

### Error Response (409 Conflict)

**Category has products:**
```json
{
  "success": false,
  "message": "Cannot delete category with existing products. Move products to another category first."
}
```

**Category has child categories:**
```json
{
  "success": false,
  "message": "Cannot delete category with child categories. Delete or move child categories first."
}
```

### cURL Example

```bash
curl -X DELETE "http://your-domain.com/api/categories/1" \
  -H "Authorization: Bearer {token}"
```

### Notes

- Categories with associated products cannot be deleted
- Categories with child categories cannot be deleted
- Associated image files are automatically deleted
- This operation cannot be undone

---

## 5. Bulk Update Sort Order

Update the sort order for multiple categories at once.

### Endpoint
```
POST /api/categories/sort-order
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `categories` | array | Yes | Array of category objects with id and sort_order |

### Request Example

```json
{
  "categories": [
    {
      "id": 1,
      "sort_order": 3
    },
    {
      "id": 2,
      "sort_order": 1
    },
    {
      "id": 3,
      "sort_order": 2
    }
  ]
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Sort order updated successfully"
}
```

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "categories": ["The categories field is required."],
    "categories.0.id": ["The categories.0.id field is required."],
    "categories.0.sort_order": ["The categories.0.sort_order must be an integer."]
  }
}
```

### cURL Example

```bash
curl -X POST "http://your-domain.com/api/categories/sort-order" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "categories": [
      {"id": 1, "sort_order": 3},
      {"id": 2, "sort_order": 1},
      {"id": 3, "sort_order": 2}
    ]
  }'
```

### Notes

- All updates are performed in a database transaction
- If any update fails, all changes are rolled back
- You can update as many categories as needed in a single request

---

## 6. Toggle Featured Status

Toggle the featured status of a category.

### Endpoint
```
PUT /api/categories/{category}/toggle-featured
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer | Category ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

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

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/categories/1/toggle-featured" \
  -H "Authorization: Bearer {token}"
```

### Notes

- This endpoint toggles the current featured status (true ↔ false)
- The `updated_by` field is automatically set to the authenticated user

---

## 7. Toggle Active Status

Toggle the active status of a category.

### Endpoint
```
PUT /api/categories/{category}/toggle-active
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | integer | Category ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

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

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/categories/1/toggle-active" \
  -H "Authorization: Bearer {token}"
```

### Notes

- This endpoint toggles the current active status (true ↔ false)
- Inactive categories are hidden from public views
- The `updated_by` field is automatically set to the authenticated user

---

## 8. Get Category Tree

Get the hierarchical tree structure of categories (parent categories with their children).

### Endpoint
```
GET /api/categories/tree
```

### Headers
```
Authorization: Bearer {token} (Optional)
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "slug": "electronics",
      "parent_id": null,
      "sort_order": 1,
      "is_active": true,
      "is_featured": true,
      "active_products_count": 25,
      "children": [
        {
          "id": 2,
          "name": "Smartphones",
          "slug": "smartphones",
          "parent_id": 1,
          "sort_order": 1,
          "is_active": true,
          "is_featured": false,
          "active_products_count": 10
        },
        {
          "id": 3,
          "name": "Laptops",
          "slug": "laptops",
          "parent_id": 1,
          "sort_order": 2,
          "is_active": true,
          "is_featured": false,
          "active_products_count": 15
        }
      ]
    },
    {
      "id": 4,
      "name": "Clothing",
      "slug": "clothing",
      "parent_id": null,
      "sort_order": 2,
      "is_active": true,
      "is_featured": false,
      "active_products_count": 50,
      "children": []
    }
  ]
}
```

### cURL Example

```bash
curl -X GET "http://your-domain.com/api/categories/tree" \
  -H "Authorization: Bearer {token}"
```

### Notes

- Only returns parent categories (categories with `parent_id = null`)
- Children are nested within their parent's `children` array
- Results are ordered by `sort_order`
- Includes active products count for each category

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
| `201` | Created successfully |
| `404` | Resource not found |
| `409` | Conflict (business logic violation) |
| `422` | Validation error |
| `500` | Server error |
| `401` | Unauthorized (missing or invalid token) |
| `403` | Forbidden (insufficient permissions) |

### Common Error Scenarios

**401 Unauthorized:**
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden:**
```json
{
  "message": "This action is unauthorized."
}
```

**404 Not Found:**
```json
{
  "success": false,
  "message": "Category not found"
}
```

**500 Server Error:**
```json
{
  "success": false,
  "message": "Failed to create category",
  "error": "Database connection error"
}
```

---

## Complete Workflow Examples

### Example 1: Create a Category Hierarchy

```bash
# 1. Create parent category
curl -X POST "http://your-domain.com/api/categories" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic devices",
    "is_active": true,
    "is_featured": true,
    "sort_order": 1
  }'

# Response: { "data": { "id": 1, ... } }

# 2. Create child category
curl -X POST "http://your-domain.com/api/categories" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Smartphones",
    "description": "Mobile phones",
    "parent_id": 1,
    "is_active": true,
    "sort_order": 1
  }'
```

### Example 2: Update and Reorder Categories

```bash
# 1. Update category
curl -X PUT "http://your-domain.com/api/categories/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics & Gadgets",
    "is_featured": true
  }'

# 2. Bulk update sort order
curl -X POST "http://your-domain.com/api/categories/sort-order" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "categories": [
      {"id": 1, "sort_order": 2},
      {"id": 2, "sort_order": 1}
    ]
  }'
```

### Example 3: Toggle Status and Delete

```bash
# 1. Toggle featured status
curl -X PUT "http://your-domain.com/api/categories/1/toggle-featured" \
  -H "Authorization: Bearer {token}"

# 2. Toggle active status
curl -X PUT "http://your-domain.com/api/categories/1/toggle-active" \
  -H "Authorization: Bearer {token}"

# 3. Delete category (only if no products or children)
curl -X DELETE "http://your-domain.com/api/categories/1" \
  -H "Authorization: Bearer {token}"
```

---

## Best Practices

1. **Slug Management**: Let the system auto-generate slugs from names unless you have specific requirements
2. **Image Uploads**: Use the `image` field for file uploads or `image_url` for external URLs
3. **Parent-Child Relationships**: Ensure parent categories exist before creating child categories
4. **Sort Order**: Use bulk update for reordering multiple categories efficiently
5. **Deletion**: Always check for products and child categories before attempting deletion
6. **Status Management**: Use toggle endpoints for quick status changes
7. **Error Handling**: Always check the `success` field in responses before processing data

---

## Additional Notes

- All timestamps are in ISO 8601 format (UTC)
- Image files are stored in `storage/app/public/categories/`
- Slug uniqueness is automatically enforced
- The `created_by` and `updated_by` fields track user actions
- Categories support unlimited nesting levels (parent-child relationships)
- Active products count is calculated dynamically

---

## Support

For issues or questions, please refer to the main API documentation or contact the development team.

