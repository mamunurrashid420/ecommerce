# Product API Documentation

## Overview
Complete CRUD API for managing products in the ecommerce system. All admin endpoints require authentication and admin privileges.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Admin endpoints require:
- **Header:** `Authorization: Bearer {admin_token}`
- **Header:** `Accept: application/json`

---

## Endpoints

### 1. Create Product
**POST** `/api/products`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json` or `multipart/form-data` (for image uploads)

**Description:** Creates a new product with comprehensive validation, automatic slug generation, and support for multiple image uploads.

**JSON Request Body:**
```json
{
    "name": "Conion Seiling Fan",
    "slug": "conion-seiling-fan",
    "description": "Lorem ipsum dolor sit amet consectetur, adipiscing elit ullamcorper fringilla sociosqu class, lobortis pellentesque metus scelerisque.",
    "long_description": "Lorem ipsum dolor sit amet consectetur, adipiscing elit ullamcorper fringilla sociosqu class, lobortis pellentesque metus scelerisque. Habitant egestas non ultrices aliquam mi vehicula ligula a senectus, auctor praesent mollis orci pharetra pulvinar dictum faucibus et iaculis, conubia augue tellus tempus penatibus rutrum per class.",
    "price": "5000",
    "stock_quantity": "10",
    "sku": "PROD-001",
    "weight": "5",
    "dimensions": "20X52X10",
    "brand": "Conion",
    "model": "RX00079",
    "category_id": "1",
    "tags": "supply chain,business,logistics,management",
    "is_active": true,
    "meta_title": "Hello This is first Blog post",
    "meta_description": "sociosqu class, lobortis pellentesque metus sceler sociosqu class, lobortis pellentesque metus sceler",
    "meta_keywords": "Hello, This ,is first, Blog post",
    "image_url": "https://example.com/product-image.jpg"
}
```

**Multipart Form Data (with images):**
```
name: "Conion Seiling Fan"
description: "Premium ceiling fan with modern design"
price: "5000"
stock_quantity: "10"
sku: "PROD-001"
category_id: "1"
brand: "Conion"
model: "RX00079"
tags: "ceiling fan,home appliance,cooling"
is_active: true
images[]: [File] // Up to 10 image files
images[]: [File]
images[]: [File]
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Product created successfully",
    "data": {
        "id": 1,
        "name": "Conion Seiling Fan",
        "slug": "conion-seiling-fan",
        "description": "Lorem ipsum dolor sit amet consectetur, adipiscing elit ullamcorper fringilla sociosqu class, lobortis pellentesque metus scelerisque.",
        "long_description": "Lorem ipsum dolor sit amet consectetur, adipiscing elit ullamcorper fringilla sociosqu class, lobortis pellentesque metus scelerisque. Habitant egestas non ultrices aliquam mi vehicula ligula a senectus, auctor praesent mollis orci pharetra pulvinar dictum faucibus et iaculis, conubia augue tellus tempus penatibus rutrum per class.",
        "price": "5000.00",
        "stock_quantity": 10,
        "sku": "PROD-001",
        "weight": "5.00",
        "dimensions": "20X52X10",
        "brand": "Conion",
        "model": "RX00079",
        "category_id": 1,
        "tags": ["supply chain", "business", "logistics", "management"],
        "is_active": true,
        "meta_title": "Hello This is first Blog post",
        "meta_description": "sociosqu class, lobortis pellentesque metus sceler sociosqu class, lobortis pellentesque metus sceler",
        "meta_keywords": "Hello, This ,is first, Blog post",
        "image_url": "https://example.com/product-image.jpg",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-04T16:30:00.000000Z",
        "updated_at": "2025-11-04T16:30:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics",
            "description": "Electronic products and gadgets",
            "created_at": "2025-11-04T10:00:00.000000Z",
            "updated_at": "2025-11-04T10:00:00.000000Z"
        },
        "media": [],
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

### 2. Update Product
**PUT** `/api/products/{id}`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json`

**Description:** Updates an existing product. All fields are optional - you can update individual fields without affecting others.

**URL Parameters:**
- `id` (integer) - Product ID

**Request Body (Partial Update Example):**
```json
{
    "name": "Updated Conion Ceiling Fan",
    "price": "5500",
    "stock_quantity": "15",
    "is_active": false,
    "tags": "ceiling fan,home appliance,cooling,energy efficient"
}
```

**Complete Update Example:**
```json
{
    "name": "Conion Premium Ceiling Fan",
    "slug": "conion-premium-ceiling-fan",
    "description": "Updated premium ceiling fan with advanced features",
    "long_description": "This premium ceiling fan offers superior performance with energy-efficient motor and modern design. Perfect for any room in your home.",
    "price": "6000",
    "stock_quantity": "20",
    "sku": "PROD-001-UPDATED",
    "weight": "5.5",
    "dimensions": "22X54X12",
    "brand": "Conion",
    "model": "RX00079-PRO",
    "category_id": "1",
    "tags": "premium,ceiling fan,energy efficient,modern design",
    "is_active": true,
    "meta_title": "Conion Premium Ceiling Fan - Best Quality",
    "meta_description": "Premium ceiling fan with advanced features and energy efficiency",
    "meta_keywords": "ceiling fan, premium, energy efficient, Conion",
    "image_url": "https://example.com/updated-product-image.jpg"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product updated successfully",
    "data": {
        "id": 1,
        "name": "Conion Premium Ceiling Fan",
        "slug": "conion-premium-ceiling-fan",
        "description": "Updated premium ceiling fan with advanced features",
        "long_description": "This premium ceiling fan offers superior performance with energy-efficient motor and modern design. Perfect for any room in your home.",
        "price": "6000.00",
        "stock_quantity": 20,
        "sku": "PROD-001-UPDATED",
        "weight": "5.50",
        "dimensions": "22X54X12",
        "brand": "Conion",
        "model": "RX00079-PRO",
        "category_id": 1,
        "tags": ["premium", "ceiling fan", "energy efficient", "modern design"],
        "is_active": true,
        "meta_title": "Conion Premium Ceiling Fan - Best Quality",
        "meta_description": "Premium ceiling fan with advanced features and energy efficiency",
        "meta_keywords": "ceiling fan, premium, energy efficient, Conion",
        "image_url": "https://example.com/updated-product-image.jpg",
        "created_by": 1,
        "updated_by": 1,
        "created_at": "2025-11-04T16:30:00.000000Z",
        "updated_at": "2025-11-04T17:15:00.000000Z",
        "category": {
            "id": 1,
            "name": "Electronics",
            "slug": "electronics",
            "description": "Electronic products and gadgets",
            "created_at": "2025-11-04T10:00:00.000000Z",
            "updated_at": "2025-11-04T10:00:00.000000Z"
        },
        "media": [],
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

### 3. Upload Product Images
**POST** `/api/products/{id}/images`

**Authentication:** Required (Admin only)
**Content-Type:** `multipart/form-data`

**Description:** Upload multiple images for an existing product. Supports up to 10 images per request.

**URL Parameters:**
- `id` (integer) - Product ID

**Form Data:**
```
images[]: [File] // Required, up to 10 image files
images[]: [File]
alt_texts[]: "Front view of the ceiling fan" // Optional
alt_texts[]: "Side view showing the blades"
titles[]: "Conion Fan - Front View" // Optional
titles[]: "Conion Fan - Side View"
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Images uploaded successfully",
    "data": {
        "product_id": 1,
        "uploaded_images": [
            {
                "id": 1,
                "product_id": 1,
                "type": "image",
                "url": "/storage/products/1/1699123456_0_fan-front.jpg",
                "alt_text": "Front view of the ceiling fan",
                "title": "Conion Fan - Front View",
                "is_thumbnail": true,
                "sort_order": 0,
                "file_size": 245760,
                "mime_type": "image/jpeg",
                "created_at": "2025-11-04T16:30:00.000000Z",
                "updated_at": "2025-11-04T16:30:00.000000Z"
            },
            {
                "id": 2,
                "product_id": 1,
                "type": "image",
                "url": "/storage/products/1/1699123456_1_fan-side.jpg",
                "alt_text": "Side view showing the blades",
                "title": "Conion Fan - Side View",
                "is_thumbnail": false,
                "sort_order": 1,
                "file_size": 198432,
                "mime_type": "image/jpeg",
                "created_at": "2025-11-04T16:30:00.000000Z",
                "updated_at": "2025-11-04T16:30:00.000000Z"
            }
        ],
        "total_images": 2
    }
}
```

---

### 4. Remove Product Image
**DELETE** `/api/products/{id}/images/{media_id}`

**Authentication:** Required (Admin only)

**Description:** Remove a specific image from a product. Automatically sets a new thumbnail if the removed image was the current thumbnail.

**URL Parameters:**
- `id` (integer) - Product ID
- `media_id` (integer) - Media/Image ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Image removed successfully",
    "data": {
        "removed_image": {
            "id": 2,
            "url": "/storage/products/1/1699123456_1_fan-side.jpg",
            "alt_text": "Side view showing the blades"
        },
        "remaining_images": 1
    }
}
```

---

### 5. Set Product Thumbnail
**PUT** `/api/products/{id}/images/{media_id}/thumbnail`

**Authentication:** Required (Admin only)

**Description:** Set a specific image as the product thumbnail. Removes thumbnail flag from all other images.

**URL Parameters:**
- `id` (integer) - Product ID
- `media_id` (integer) - Media/Image ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Thumbnail set successfully",
    "data": {
        "thumbnail_id": 1,
        "thumbnail_url": "/storage/products/1/1699123456_0_fan-front.jpg"
    }
}
```

---

### 6. Update Image Details
**PUT** `/api/products/{id}/images/{media_id}`

**Authentication:** Required (Admin only)
**Content-Type:** `application/json`

**Description:** Update image metadata like alt text, title, and sort order.

**URL Parameters:**
- `id` (integer) - Product ID
- `media_id` (integer) - Media/Image ID

**Request Body:**
```json
{
    "alt_text": "Updated alt text for the image",
    "title": "Updated title for the image",
    "sort_order": 5
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Image updated successfully",
    "data": {
        "id": 1,
        "product_id": 1,
        "type": "image",
        "url": "/storage/products/1/1699123456_0_fan-front.jpg",
        "alt_text": "Updated alt text for the image",
        "title": "Updated title for the image",
        "is_thumbnail": true,
        "sort_order": 5,
        "file_size": 245760,
        "mime_type": "image/jpeg",
        "created_at": "2025-11-04T16:30:00.000000Z",
        "updated_at": "2025-11-04T17:45:00.000000Z"
    }
}
```

---

### 7. Delete Product
**DELETE** `/api/products/{id}`

**Authentication:** Required (Admin only)

**Description:** Permanently deletes a product and all associated media files. Products with existing orders cannot be deleted for data integrity.

**URL Parameters:**
- `id` (integer) - Product ID

**Success Response (200):**
```json
{
    "success": true,
    "message": "Product deleted successfully",
    "data": {
        "id": 1,
        "name": "Conion Premium Ceiling Fan",
        "sku": "PROD-001-UPDATED",
        "slug": "conion-premium-ceiling-fan"
    }
}
```

**Conflict Response (409) - Product has orders:**
```json
{
    "success": false,
    "message": "Cannot delete product with existing orders. Consider deactivating instead."
}
```

---

## Field Validation Rules

### Required Fields (Create)
- `name`: string, max 255 characters
- `price`: numeric, minimum 0
- `stock_quantity`: integer, minimum 0
- `sku`: string, max 100 characters, must be unique
- `category_id`: must exist in categories table

### Optional Fields
- `slug`: string, max 255 characters, auto-generated from name if not provided
- `description`: string, short product description
- `long_description`: string, detailed product description
- `weight`: numeric, minimum 0
- `dimensions`: string, max 100 characters (format: LxWxH)
- `brand`: string, max 100 characters
- `model`: string, max 100 characters
- `tags`: string (comma-separated), converted to array
- `is_active`: boolean, default true
- `meta_title`: string, max 255 characters
- `meta_description`: string, max 500 characters
- `meta_keywords`: string, max 500 characters
- `image_url`: valid URL format

### Update Validation
- All fields are optional for updates
- Uses `sometimes|required` for key fields
- Unique validation excludes current product ID

---

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "name": ["The name field is required."],
        "sku": ["The sku has already been taken."],
        "price": ["The price must be a number."],
        "category_id": ["The selected category id is invalid."]
    }
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
    "message": "Product not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Failed to create product",
    "error": "Database connection failed"
}
```

---

## Usage Examples

### JavaScript/Fetch
```javascript
// Create Product
const createProduct = async (productData, token) => {
    const response = await fetch('/api/products', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(productData)
    });
    return await response.json();
};

// Create Product with Images
const createProductWithImages = async (formData, token) => {
    const response = await fetch('/api/products', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: formData // FormData object with product data and images
    });
    return await response.json();
};

// Upload Images to Existing Product
const uploadProductImages = async (productId, formData, token) => {
    const response = await fetch(`/api/products/${productId}/images`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        },
        body: formData
    });
    return await response.json();
};

// Remove Product Image
const removeProductImage = async (productId, mediaId, token) => {
    const response = await fetch(`/api/products/${productId}/images/${mediaId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// Set Product Thumbnail
const setProductThumbnail = async (productId, mediaId, token) => {
    const response = await fetch(`/api/products/${productId}/images/${mediaId}/thumbnail`, {
        method: 'PUT',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// Update Image Details
const updateImageDetails = async (productId, mediaId, updateData, token) => {
    const response = await fetch(`/api/products/${productId}/images/${mediaId}`, {
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

// Update Product
const updateProduct = async (productId, updateData, token) => {
    const response = await fetch(`/api/products/${productId}`, {
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

// Delete Product
const deleteProduct = async (productId, token) => {
    const response = await fetch(`/api/products/${productId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json'
        }
    });
    return await response.json();
};

// Example: Create FormData for image upload
const createImageFormData = (images, altTexts = [], titles = []) => {
    const formData = new FormData();
    
    images.forEach((image, index) => {
        formData.append('images[]', image);
        if (altTexts[index]) formData.append('alt_texts[]', altTexts[index]);
        if (titles[index]) formData.append('titles[]', titles[index]);
    });
    
    return formData;
};
```

### cURL Examples

**Create Product:**
```bash
curl -X POST "http://localhost:8000/api/products" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Conion Seiling Fan",
    "description": "Premium ceiling fan with modern design",
    "price": "5000",
    "stock_quantity": "10",
    "sku": "PROD-001",
    "category_id": "1",
    "brand": "Conion",
    "model": "RX00079",
    "tags": "ceiling fan,home appliance,cooling",
    "is_active": true
  }'
```

**Update Product:**
```bash
curl -X PUT "http://localhost:8000/api/products/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Conion Ceiling Fan",
    "price": "5500",
    "stock_quantity": "15"
  }'
```

**Upload Product Images:**
```bash
curl -X POST "http://localhost:8000/api/products/1/images" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "alt_texts[]=Front view of the ceiling fan" \
  -F "alt_texts[]=Side view showing the blades" \
  -F "titles[]=Conion Fan - Front View" \
  -F "titles[]=Conion Fan - Side View"
```

**Remove Product Image:**
```bash
curl -X DELETE "http://localhost:8000/api/products/1/images/2" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"
```

**Set Product Thumbnail:**
```bash
curl -X PUT "http://localhost:8000/api/products/1/images/1/thumbnail" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"
```

**Update Image Details:**
```bash
curl -X PUT "http://localhost:8000/api/products/1/images/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "alt_text": "Updated alt text for the image",
    "title": "Updated title for the image",
    "sort_order": 5
  }'
```

**Delete Product:**
```bash
curl -X DELETE "http://localhost:8000/api/products/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Accept: application/json"
```

---

## Business Logic Notes

### Slug Generation
- Auto-generated from product name if not provided
- Ensures uniqueness by appending numbers (e.g., `product-name-1`, `product-name-2`)
- Updates automatically when name changes (unless explicitly provided)

### Tags Handling
- Accepts comma-separated string input
- Automatically converts to array format for storage
- Trims whitespace from individual tags

### Deletion Protection
- Products with existing orders cannot be deleted
- Returns 409 Conflict status with helpful message
- Suggests deactivating product instead

### Media Management
- Automatically deletes associated media files and records
- Cleans up physical files from storage
- Removes custom field associations

### Audit Trail
- Tracks `created_by` and `updated_by` fields
- Automatically sets user ID when authenticated
- Maintains creation and update timestamps

---

## Rate Limiting
- **Create/Update:** 30 requests per minute per user
- **Delete:** 10 requests per minute per user

## Security Features
- Admin-only access for all CUD operations
- Input validation and sanitization
- SQL injection protection via Eloquent ORM
- XSS protection through proper JSON encoding