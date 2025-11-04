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
**Content-Type:** `application/json`

**Description:** Creates a new product with comprehensive validation and automatic slug generation.

**Request Body:**
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

### 3. Delete Product
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