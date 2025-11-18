# Product API Documentation

Complete documentation for all product management endpoints including CRUD operations and image management.

## Table of Contents
1. [Authentication](#authentication)
2. [List Products (Public)](#1-list-products-public)
3. [Get Product Details (Public)](#2-get-product-details-public)
4. [Create Product (Admin)](#3-create-product-admin)
5. [Update Product (Admin)](#4-update-product-admin)
6. [Delete Product (Admin)](#5-delete-product-admin)
7. [Upload Product Images (Admin)](#6-upload-product-images-admin)
8. [Remove Product Image (Admin)](#7-remove-product-image-admin)
9. [Set Product Thumbnail (Admin)](#8-set-product-thumbnail-admin)
10. [Update Image Details (Admin)](#9-update-image-details-admin)
11. [Error Responses](#error-responses)

---

## Authentication

**Public Endpoints** (No authentication required):
- `GET /api/products` - List products
- `GET /api/products/{product}` - Get product details

**Admin Endpoints** (Authentication required):
- **Authentication**: Bearer token via `Authorization: Bearer {token}` header
- **Role**: Admin role (`admin`)

**Base URL**: `http://your-domain.com/api`

---

## 1. List Products (Public)

Get a paginated list of active products.

### Endpoint
```
GET /api/products
```

### Query Parameters

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| `search` | string | Search term to search across name, description, SKU, brand, model, tags, and meta keywords | - |
| `category_id` | integer | Filter by category ID | - |
| `min_price` | decimal | Minimum price filter (min: 0) | - |
| `max_price` | decimal | Maximum price filter (must be >= min_price) | - |
| `brand` | string | Filter by brand name (partial match) | - |
| `in_stock` | boolean | Filter by stock availability (`true` for in stock, `false` for out of stock) | - |
| `sort_by` | string | Sort field (`name`, `price`, `created_at`, `stock_quantity`) | `created_at` |
| `sort_order` | string | Sort direction (`asc`/`desc`) | `desc` |
| `page` | integer | Page number for pagination | 1 |
| `per_page` | integer | Items per page (max: 100) | 12 |

### Success Response (200 OK)

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "MacBook Pro 16-inch",
      "slug": "macbook-pro-16-inch",
      "description": "Powerful laptop for professionals",
      "long_description": "The MacBook Pro 16-inch features the latest M3 chip...",
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
      "meta_description": "Buy MacBook Pro 16-inch with M3 chip at best price",
      "meta_keywords": "macbook, laptop, apple, m3",
      "image_url": null,
      "created_by": 1,
      "updated_by": 1,
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
          "url": "http://your-domain.com/storage/products/1/1234567890_macbook.jpg",
          "alt_text": "MacBook Pro front view",
          "title": "MacBook Pro",
          "is_thumbnail": true,
          "sort_order": 0
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
  ],
  "first_page_url": "http://your-domain.com/api/products?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://your-domain.com/api/products?page=5",
  "links": [...],
  "next_page_url": "http://your-domain.com/api/products?page=2",
  "path": "http://your-domain.com/api/products",
  "per_page": 12,
  "prev_page_url": null,
  "to": 12,
  "total": 58
}
```

### cURL Examples

**Basic pagination:**
```bash
curl -X GET "http://your-domain.com/api/products?page=1&per_page=12"
```

**Search products:**
```bash
curl -X GET "http://your-domain.com/api/products?search=laptop&page=1&per_page=12"
```

**Filter by category:**
```bash
curl -X GET "http://your-domain.com/api/products?category_id=1&page=1&per_page=12"
```

**Filter by price range:**
```bash
curl -X GET "http://your-domain.com/api/products?min_price=100&max_price=500&page=1&per_page=12"
```

**Filter by brand:**
```bash
curl -X GET "http://your-domain.com/api/products?brand=Apple&page=1&per_page=12"
```

**Filter in-stock products:**
```bash
curl -X GET "http://your-domain.com/api/products?in_stock=true&page=1&per_page=12"
```

**Sort by price (low to high):**
```bash
curl -X GET "http://your-domain.com/api/products?sort_by=price&sort_order=asc&page=1&per_page=12"
```

**Combined search and filters:**
```bash
curl -X GET "http://your-domain.com/api/products?search=laptop&category_id=1&min_price=500&max_price=2000&in_stock=true&sort_by=price&sort_order=asc&page=1&per_page=12"
```

### Search Functionality

The `search` parameter performs a comprehensive search across multiple product fields:
- **Product name** (`name`)
- **Short description** (`description`)
- **Long description** (`long_description`)
- **SKU** (`sku`)
- **Brand** (`brand`)
- **Model** (`model`)
- **Meta keywords** (`meta_keywords`)
- **Tags** (`tags` - JSON array search)

The search is case-insensitive and uses partial matching (LIKE queries).

### Filter Functionality

**Category Filter:**
- Filter products by a specific category ID
- Only returns products in the specified category

**Price Range Filter:**
- `min_price`: Minimum price (inclusive)
- `max_price`: Maximum price (inclusive)
- Both can be used together or independently
- `max_price` must be greater than or equal to `min_price` if both are provided

**Brand Filter:**
- Filter products by brand name
- Uses partial matching (case-insensitive)
- Example: `brand=Apple` will match "Apple", "Apple Inc", etc.

**Stock Filter:**
- `in_stock=true`: Only products with `stock_quantity > 0`
- `in_stock=false`: Only products with `stock_quantity <= 0`

**Sorting:**
- `sort_by`: Field to sort by
  - `name`: Product name (alphabetical)
  - `price`: Product price
  - `created_at`: Creation date (default)
  - `stock_quantity`: Available stock
- `sort_order`: Sort direction
  - `asc`: Ascending (A-Z, low to high, oldest first)
  - `desc`: Descending (Z-A, high to low, newest first)

### Notes

- Only returns active products (`is_active = true`)
- Results are paginated (12 items per page by default, max 100 per page)
- Includes category, media, creator, and updater relationships
- All filters can be combined for advanced product discovery
- Search and filters work together (AND logic between different filters)
- Search uses OR logic across multiple fields (matches if any field contains the search term)

---

## 2. Get Product Details (Public)

Get detailed information about a specific product by ID or slug.

### Endpoint
```
GET /api/products/{product}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer or string | Product ID or slug |

### Success Response (200 OK)

```json
{
  "id": 1,
  "name": "MacBook Pro 16-inch",
  "slug": "macbook-pro-16-inch",
  "description": "Powerful laptop for professionals",
  "long_description": "The MacBook Pro 16-inch features the latest M3 chip with 16-core CPU and 40-core GPU...",
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
  "meta_description": "Buy MacBook Pro 16-inch with M3 chip at best price",
  "meta_keywords": "macbook, laptop, apple, m3",
  "image_url": null,
  "created_by": 1,
  "updated_by": 1,
  "created_at": "2025-01-15T10:30:00.000000Z",
  "updated_at": "2025-01-15T10:30:00.000000Z",
  "category": {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic devices"
  },
  "media": [
    {
      "id": 1,
      "product_id": 1,
      "type": "image",
      "url": "http://your-domain.com/storage/products/1/1234567890_macbook.jpg",
      "file_path": "products/1/1234567890_macbook.jpg",
      "alt_text": "MacBook Pro front view",
      "title": "MacBook Pro",
      "is_thumbnail": true,
      "sort_order": 0,
      "file_size": 524288,
      "mime_type": "image/jpeg"
    }
  ],
  "customFields": [],
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
```

### Not Found Response (404)

```json
{
  "message": "Product not found"
}
```

### cURL Example

```bash
# Using ID
curl -X GET "http://your-domain.com/api/products/1"

# Using slug
curl -X GET "http://your-domain.com/api/products/macbook-pro-16-inch"
```

---

## 3. Create Product (Admin)

Create a new product in the system.

### Endpoint
```
POST /api/products
```

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Product name (max 255 characters) |
| `slug` | string | No | URL-friendly slug (auto-generated from name if not provided) |
| `description` | string | No | Short product description |
| `long_description` | string | No | Detailed product description |
| `price` | decimal | Yes | Product price (min: 0) |
| `stock_quantity` | integer | Yes | Available stock quantity (min: 0) |
| `sku` | string | Yes | Stock Keeping Unit (unique, max 100 characters) |
| `category_id` | integer | Yes | Category ID (must exist) |
| `weight` | decimal | No | Product weight (min: 0) |
| `dimensions` | string | No | Product dimensions (max 100 characters) |
| `brand` | string | No | Brand name (max 100 characters) |
| `model` | string | No | Model name (max 100 characters) |
| `tags` | string or array | No | Product tags (comma-separated string or array) |
| `is_active` | boolean | No | Active status (default: true) |
| `meta_title` | string | No | SEO meta title (max 255 characters) |
| `meta_description` | string | No | SEO meta description (max 500 characters) |
| `meta_keywords` | string | No | SEO meta keywords (max 500 characters) |
| `image_url` | string (URL) | No | External image URL |
| `images` | array | No | Image files to upload (max 10 files, max 5MB each) |

### Request Example (JSON)

```json
{
  "name": "MacBook Pro 16-inch",
  "description": "Powerful laptop for professionals",
  "long_description": "The MacBook Pro 16-inch features the latest M3 chip with 16-core CPU and 40-core GPU.",
  "price": 2499.00,
  "stock_quantity": 50,
  "sku": "MBP-16-M3-001",
  "category_id": 1,
  "weight": 2.15,
  "dimensions": "35.97 x 24.59 x 1.68 cm",
  "brand": "Apple",
  "model": "MacBook Pro",
  "tags": "laptop, apple, macbook, professional",
  "is_active": true,
  "meta_title": "MacBook Pro 16-inch - Best Price",
  "meta_description": "Buy MacBook Pro 16-inch with M3 chip at best price",
  "meta_keywords": "macbook, laptop, apple, m3"
}
```

### Request Example (Form Data with Images)

```
POST /api/products
Content-Type: multipart/form-data

name: MacBook Pro 16-inch
description: Powerful laptop for professionals
price: 2499.00
stock_quantity: 50
sku: MBP-16-M3-001
category_id: 1
brand: Apple
model: MacBook Pro
tags: laptop, apple, macbook
images[]: [binary file 1]
images[]: [binary file 2]
```

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16-inch",
    "slug": "macbook-pro-16-inch",
    "description": "Powerful laptop for professionals",
    "long_description": "The MacBook Pro 16-inch features the latest M3 chip...",
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
    "meta_description": "Buy MacBook Pro 16-inch with M3 chip at best price",
    "meta_keywords": "macbook, laptop, apple, m3",
    "image_url": null,
    "created_by": 1,
    "updated_by": 1,
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
        "url": "http://your-domain.com/storage/products/1/1234567890_macbook.jpg",
        "alt_text": null,
        "title": null,
        "is_thumbnail": true,
        "sort_order": 0
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

### Validation Error Response (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a number."],
    "sku": ["The sku has already been taken."],
    "category_id": ["The selected category id is invalid."],
    "images.0": ["The images.0 must be an image."]
  }
}
```

### cURL Example (JSON)

```bash
curl -X POST "http://your-domain.com/api/products" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MacBook Pro 16-inch",
    "description": "Powerful laptop for professionals",
    "price": 2499.00,
    "stock_quantity": 50,
    "sku": "MBP-16-M3-001",
    "category_id": 1,
    "brand": "Apple",
    "is_active": true
  }'
```

### cURL Example (with Images)

```bash
curl -X POST "http://your-domain.com/api/products" \
  -H "Authorization: Bearer {token}" \
  -F "name=MacBook Pro 16-inch" \
  -F "description=Powerful laptop for professionals" \
  -F "price=2499.00" \
  -F "stock_quantity=50" \
  -F "sku=MBP-16-M3-001" \
  -F "category_id=1" \
  -F "brand=Apple" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

### Notes

- Slug is automatically generated from name if not provided
- Tags can be provided as comma-separated string or array
- First uploaded image automatically becomes the thumbnail
- Multiple images can be uploaded during creation (max 10)
- All operations are wrapped in database transactions

---

## 4. Update Product (Admin)

Update an existing product.

### Endpoint
```
PUT /api/products/{product}
PATCH /api/products/{product}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (JSON)

All fields are optional. Only include fields you want to update.

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | No | Product name (max 255 characters) |
| `slug` | string | No | URL-friendly slug |
| `description` | string | No | Short product description |
| `long_description` | string | No | Detailed product description |
| `price` | decimal | No | Product price (min: 0) |
| `stock_quantity` | integer | No | Available stock quantity (min: 0) |
| `sku` | string | No | Stock Keeping Unit (unique, max 100 characters) |
| `category_id` | integer | No | Category ID (must exist) |
| `weight` | decimal | No | Product weight (min: 0) |
| `dimensions` | string | No | Product dimensions (max 100 characters) |
| `brand` | string | No | Brand name (max 100 characters) |
| `model` | string | No | Model name (max 100 characters) |
| `tags` | string or array | No | Product tags (comma-separated string or array) |
| `is_active` | boolean | No | Active status |
| `meta_title` | string | No | SEO meta title (max 255 characters) |
| `meta_description` | string | No | SEO meta description (max 500 characters) |
| `meta_keywords` | string | No | SEO meta keywords (max 500 characters) |
| `image_url` | string (URL) | No | External image URL |

### Request Example

```json
{
  "name": "MacBook Pro 16-inch (Updated)",
  "price": 2299.00,
  "stock_quantity": 45,
  "is_active": true
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16-inch (Updated)",
    "slug": "macbook-pro-16-inch-updated",
    "description": "Powerful laptop for professionals",
    "price": "2299.00",
    "stock_quantity": 45,
    "sku": "MBP-16-M3-001",
    "is_active": true,
    "updated_at": "2025-01-15T11:45:00.000000Z",
    "category": {
      "id": 1,
      "name": "Electronics"
    },
    "media": [...],
    "creator": {...},
    "updater": {...}
  }
}
```

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "sku": ["The sku has already been taken."],
    "price": ["The price must be at least 0."]
  }
}
```

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/products/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MacBook Pro 16-inch (Updated)",
    "price": 2299.00,
    "stock_quantity": 45
  }'
```

### Notes

- When updating `name`, the `slug` is automatically regenerated if not explicitly provided
- Tags can be updated as comma-separated string or array
- The `updated_by` field is automatically set to the authenticated user (if exists)

---

## 5. Delete Product (Admin)

Delete a product from the system.

### Endpoint
```
DELETE /api/products/{product}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Product deleted successfully",
  "data": {
    "id": 1,
    "name": "MacBook Pro 16-inch",
    "sku": "MBP-16-M3-001",
    "slug": "macbook-pro-16-inch"
  }
}
```

### Error Response (409 Conflict)

**Product has existing orders:**
```json
{
  "success": false,
  "message": "Cannot delete product with existing orders. Consider deactivating instead."
}
```

### cURL Example

```bash
curl -X DELETE "http://your-domain.com/api/products/1" \
  -H "Authorization: Bearer {token}"
```

### Notes

- Products with existing orders cannot be deleted (business logic)
- All associated media files are automatically deleted
- Custom fields are automatically deleted
- This operation cannot be undone
- Consider deactivating (`is_active: false`) instead of deleting

---

## 6. Upload Product Images (Admin)

Upload multiple images for an existing product.

### Endpoint
```
POST /api/products/{product}/images
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |

### Headers
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

### Request Body (Form Data)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `images` | array | Yes | Array of image files (max 10 files) |
| `images.*` | file | Yes | Image file (jpeg, png, jpg, gif, webp, max 5MB each) |
| `alt_texts` | array | No | Array of alt texts for each image |
| `alt_texts.*` | string | No | Alt text for corresponding image (max 255 characters) |
| `titles` | array | No | Array of titles for each image |
| `titles.*` | string | No | Title for corresponding image (max 255 characters) |

### Request Example

```
POST /api/products/1/images
Content-Type: multipart/form-data

images[]: [binary file 1]
images[]: [binary file 2]
alt_texts[]: MacBook Pro front view
alt_texts[]: MacBook Pro side view
titles[]: Front View
titles[]: Side View
```

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Images uploaded successfully",
  "data": {
    "product_id": 1,
    "uploaded_images": [
      {
        "id": 2,
        "product_id": 1,
        "type": "image",
        "url": "http://your-domain.com/storage/products/1/1234567890_image1.jpg",
        "alt_text": "MacBook Pro front view",
        "title": "Front View",
        "is_thumbnail": false,
        "sort_order": 1
      },
      {
        "id": 3,
        "product_id": 1,
        "type": "image",
        "url": "http://your-domain.com/storage/products/1/1234567890_image2.jpg",
        "alt_text": "MacBook Pro side view",
        "title": "Side View",
        "is_thumbnail": false,
        "sort_order": 2
      }
    ],
    "total_images": 3
  }
}
```

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "images": ["The images field is required."],
    "images.0": ["The images.0 must be an image."],
    "images.1": ["The images.1 may not be greater than 5120 kilobytes."]
  }
}
```

### cURL Example

```bash
curl -X POST "http://your-domain.com/api/products/1/images" \
  -H "Authorization: Bearer {token}" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "alt_texts[]=MacBook Pro front view" \
  -F "alt_texts[]=MacBook Pro side view" \
  -F "titles[]=Front View" \
  -F "titles[]=Side View"
```

### Notes

- Maximum 10 images can be uploaded at once
- Each image can be up to 5MB
- Supported formats: JPEG, PNG, JPG, GIF, WEBP
- Images are stored in `storage/app/public/products/{product_id}/`
- First image uploaded to a product without images becomes the thumbnail
- Sort order is automatically assigned based on upload sequence

---

## 7. Remove Product Image (Admin)

Remove a specific image from a product.

### Endpoint
```
DELETE /api/products/{product}/images/{media}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |
| `media` | integer | ProductMedia ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Image removed successfully",
  "data": {
    "removed_image": {
      "id": 2,
      "url": "http://your-domain.com/storage/products/1/1234567890_image1.jpg",
      "alt_text": "MacBook Pro front view"
    },
    "remaining_images": 2
  }
}
```

### Error Response (403 Forbidden)

**Image doesn't belong to product:**
```json
{
  "success": false,
  "message": "Image does not belong to this product"
}
```

### cURL Example

```bash
curl -X DELETE "http://your-domain.com/api/products/1/images/2" \
  -H "Authorization: Bearer {token}"
```

### Notes

- Physical image file is automatically deleted from storage
- If the removed image was the thumbnail, the first remaining image becomes the new thumbnail
- This operation cannot be undone

---

## 8. Set Product Thumbnail (Admin)

Set a specific image as the product thumbnail.

### Endpoint
```
PUT /api/products/{product}/images/{media}/thumbnail
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |
| `media` | integer | ProductMedia ID |

### Headers
```
Authorization: Bearer {token}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Thumbnail set successfully",
  "data": {
    "thumbnail_id": 2,
    "thumbnail_url": "http://your-domain.com/storage/products/1/1234567890_image1.jpg"
  }
}
```

### Error Response (403 Forbidden)

**Image doesn't belong to product:**
```json
{
  "success": false,
  "message": "Image does not belong to this product"
}
```

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/products/1/images/2/thumbnail" \
  -H "Authorization: Bearer {token}"
```

### Notes

- Only one image can be the thumbnail at a time
- Setting a new thumbnail automatically removes the thumbnail flag from all other images
- Operation is performed in a database transaction

---

## 9. Update Image Details (Admin)

Update metadata for a specific product image (alt text, title, sort order).

### Endpoint
```
PUT /api/products/{product}/images/{media}
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `product` | integer | Product ID |
| `media` | integer | ProductMedia ID |

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body (JSON)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `alt_text` | string | No | Alt text for the image (max 255 characters) |
| `title` | string | No | Title for the image (max 255 characters) |
| `sort_order` | integer | No | Display order (min: 0) |

### Request Example

```json
{
  "alt_text": "MacBook Pro front view - Updated",
  "title": "Front View - Updated",
  "sort_order": 0
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Image updated successfully",
  "data": {
    "id": 2,
    "product_id": 1,
    "type": "image",
    "url": "http://your-domain.com/storage/products/1/1234567890_image1.jpg",
    "alt_text": "MacBook Pro front view - Updated",
    "title": "Front View - Updated",
    "is_thumbnail": false,
    "sort_order": 0,
    "file_size": 524288,
    "mime_type": "image/jpeg"
  }
}
```

### Validation Error Response (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "sort_order": ["The sort order must be an integer."]
  }
}
```

### Error Response (403 Forbidden)

**Image doesn't belong to product:**
```json
{
  "success": false,
  "message": "Image does not belong to this product"
}
```

### cURL Example

```bash
curl -X PUT "http://your-domain.com/api/products/1/images/2" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "alt_text": "MacBook Pro front view - Updated",
    "title": "Front View - Updated",
    "sort_order": 0
  }'
```

### Notes

- All fields are optional - only include fields you want to update
- Sort order determines the display sequence of images
- Lower sort order values appear first

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
  "message": "Product not found"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a number."]
  }
}
```

**500 Server Error:**
```json
{
  "success": false,
  "message": "Failed to create product",
  "error": "Database connection error"
}
```

---

## Complete Workflow Examples

### Example 1: Create Product with Images

```bash
# 1. Create product
curl -X POST "http://your-domain.com/api/products" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "MacBook Pro 16-inch",
    "description": "Powerful laptop",
    "price": 2499.00,
    "stock_quantity": 50,
    "sku": "MBP-16-M3-001",
    "category_id": 1,
    "brand": "Apple"
  }'

# Response: { "data": { "id": 1, ... } }

# 2. Upload images
curl -X POST "http://your-domain.com/api/products/1/images" \
  -H "Authorization: Bearer {token}" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "alt_texts[]=Front view" \
  -F "alt_texts[]=Side view"
```

### Example 2: Update Product and Manage Images

```bash
# 1. Update product details
curl -X PUT "http://your-domain.com/api/products/1" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "price": 2299.00,
    "stock_quantity": 45
  }'

# 2. Update image details
curl -X PUT "http://your-domain.com/api/products/1/images/2" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "alt_text": "Updated alt text",
    "sort_order": 0
  }'

# 3. Set new thumbnail
curl -X PUT "http://your-domain.com/api/products/1/images/2/thumbnail" \
  -H "Authorization: Bearer {token}"
```

### Example 3: Delete Product Image

```bash
# Remove specific image
curl -X DELETE "http://your-domain.com/api/products/1/images/2" \
  -H "Authorization: Bearer {token}"
```

---

## Best Practices

1. **SKU Management**: Use unique, descriptive SKUs for inventory tracking
2. **Slug Generation**: Let the system auto-generate slugs from names unless you have specific requirements
3. **Image Uploads**: 
   - Upload images during product creation or separately
   - Use descriptive alt texts for SEO
   - Set appropriate sort orders for image display
4. **Stock Management**: Keep stock quantities updated to prevent overselling
5. **Product Deactivation**: Use `is_active: false` instead of deletion if products have orders
6. **SEO Optimization**: Fill in meta fields (title, description, keywords) for better search visibility
7. **Tags**: Use consistent tag naming conventions for better filtering and search
8. **Image Management**: 
   - Set the best image as thumbnail
   - Use appropriate sort orders for image galleries
   - Provide alt texts for accessibility

---

## Additional Notes

- All timestamps are in ISO 8601 format (UTC)
- Image files are stored in `storage/app/public/products/{product_id}/`
- Slug uniqueness is automatically enforced
- The `created_by` and `updated_by` fields track user actions (nullable if user doesn't exist)
- Tags are stored as JSON array in the database
- Price and weight are stored as decimal with 2 decimal places
- Products with existing orders cannot be deleted (business logic)
- First uploaded image automatically becomes the thumbnail
- Maximum 10 images per product upload operation

---

## Support

For issues or questions, please refer to the main API documentation or contact the development team.

