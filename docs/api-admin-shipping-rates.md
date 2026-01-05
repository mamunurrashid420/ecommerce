# API Documentation: Admin Shipping Rate Management

## Overview
This document describes the Admin Shipping Rate Management API endpoints. These endpoints allow administrators to manage shipping rates for different product categories and subcategories.

**Base URL**: `/api/admin/shipping-rates`

**Authentication**: All endpoints require admin authentication via Bearer token.

---

## Endpoints

### 1. List All Shipping Rates

**Endpoint**: `GET /api/admin/shipping-rates`

**Description**: Retrieves a paginated list of all shipping rates with optional filtering and sorting.

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**Query Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `category` | string | No | Filter by category (A, B, or C) |
| `subcategory` | string | No | Filter by subcategory |
| `is_active` | boolean | No | Filter by active status (true/false) |
| `search` | string | No | Search in description (Bengali or English) |
| `sort_by` | string | No | Field to sort by (default: `sort_order`) |
| `sort_order` | string | No | Sort direction: `asc` or `desc` (default: `asc`) |
| `per_page` | integer | No | Number of items per page (default: 15) |

**Example Request**:
```bash
GET /api/admin/shipping-rates?category=A&is_active=true&per_page=20&sort_by=sort_order&sort_order=asc
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "category": "A",
      "subcategory": null,
      "description_bn": "বর্ণনা বাংলা",
      "description_en": "Description English",
      "rate_air": 150.00,
      "rate_ship": 100.00,
      "is_active": true,
      "sort_order": 1,
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

**Error Response (500 Internal Server Error)**:
```json
{
  "success": false,
  "message": "Failed to retrieve shipping rates",
  "error": "Error message details"
}
```

---

### 2. Create Shipping Rate

**Endpoint**: `POST /api/admin/shipping-rates`

**Description**: Creates a new shipping rate.

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**Request Body**:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `category` | string | Yes | Category: `A`, `B`, or `C` |
| `subcategory` | string | Conditional | Required for category `C`. Must be one of: `mold_tape_garments`, `liquid_cosmetics`, `battery_powerbank`, `sunglasses`. Must be `null` for categories A and B. |
| `description_bn` | string | Yes | Description in Bengali |
| `description_en` | string | Yes | Description in English |
| `rate_air` | number | Yes | Air shipping rate (must be >= 0) |
| `rate_ship` | number | Yes | Ship shipping rate (must be >= 0) |
| `is_active` | boolean | No | Active status (default: true) |
| `sort_order` | integer | No | Sort order (must be >= 0) |

**Category Rules**:
- **Category A & B**: `subcategory` must be `null` or omitted
- **Category C**: `subcategory` is required and must be one of:
  - `mold_tape_garments`
  - `liquid_cosmetics`
  - `battery_powerbank`
  - `sunglasses`

**Example Request**:
```json
{
  "category": "A",
  "description_bn": "সাধারণ পণ্য",
  "description_en": "General Products",
  "rate_air": 150.00,
  "rate_ship": 100.00,
  "is_active": true,
  "sort_order": 1
}
```

**Example Request (Category C)**:
```json
{
  "category": "C",
  "subcategory": "liquid_cosmetics",
  "description_bn": "তরল প্রসাধনী",
  "description_en": "Liquid Cosmetics",
  "rate_air": 200.00,
  "rate_ship": 150.00,
  "is_active": true,
  "sort_order": 5
}
```

**Success Response (201 Created)**:
```json
{
  "success": true,
  "message": "Shipping rate created successfully",
  "data": {
    "id": 10,
    "category": "A",
    "subcategory": null,
    "description_bn": "সাধারণ পণ্য",
    "description_en": "General Products",
    "rate_air": 150.00,
    "rate_ship": 100.00,
    "is_active": true,
    "sort_order": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses**:

**422 Unprocessable Entity - Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "category": ["The category field is required."],
    "rate_air": ["The rate air must be a number."]
  }
}
```

**422 Unprocessable Entity - Subcategory Validation**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subcategory": ["Subcategory is required for category C."]
  }
}
```

or

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subcategory": ["Invalid subcategory for category C. Must be one of: mold_tape_garments, liquid_cosmetics, battery_powerbank, sunglasses"]
  }
}
```

**400 Bad Request**:
```json
{
  "success": false,
  "message": "Failed to create shipping rate",
  "error": "Error message details"
}
```

---

### 3. Get Single Shipping Rate

**Endpoint**: `GET /api/admin/shipping-rates/{shippingRate}`

**Description**: Retrieves details of a specific shipping rate by ID.

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**URL Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `shippingRate` | integer | Yes | The ID of the shipping rate |

**Example Request**:
```bash
GET /api/admin/shipping-rates/1
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "category": "A",
    "subcategory": null,
    "description_bn": "সাধারণ পণ্য",
    "description_en": "General Products",
    "rate_air": 150.00,
    "rate_ship": 100.00,
    "is_active": true,
    "sort_order": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Response (404 Not Found)**:
```json
{
  "message": "No query results for model [App\\Models\\ShippingRate] {id}"
}
```

**Error Response (500 Internal Server Error)**:
```json
{
  "success": false,
  "message": "Failed to retrieve shipping rate",
  "error": "Error message details"
}
```

---

### 4. Update Shipping Rate

**Endpoint**: `PUT /api/admin/shipping-rates/{shippingRate}`

**Description**: Updates an existing shipping rate. All fields are optional (partial update).

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**URL Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `shippingRate` | integer | Yes | The ID of the shipping rate to update |

**Request Body** (All fields are optional):

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `category` | string | No | Category: `A`, `B`, or `C` |
| `subcategory` | string | Conditional | Required if category is `C`. Must be one of: `mold_tape_garments`, `liquid_cosmetics`, `battery_powerbank`, `sunglasses`. Will be set to `null` for categories A and B. |
| `description_bn` | string | No | Description in Bengali |
| `description_en` | string | No | Description in English |
| `rate_air` | number | No | Air shipping rate (must be >= 0) |
| `rate_ship` | number | No | Ship shipping rate (must be >= 0) |
| `is_active` | boolean | No | Active status |
| `sort_order` | integer | No | Sort order (must be >= 0) |

**Example Request**:
```json
{
  "rate_air": 175.00,
  "rate_ship": 120.00,
  "is_active": false
}
```

**Example Request (Change to Category C)**:
```json
{
  "category": "C",
  "subcategory": "battery_powerbank",
  "description_bn": "ব্যাটারি পাওয়ারব্যাঙ্ক",
  "description_en": "Battery Powerbank"
}
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "message": "Shipping rate updated successfully",
  "data": {
    "id": 1,
    "category": "A",
    "subcategory": null,
    "description_bn": "সাধারণ পণ্য",
    "description_en": "General Products",
    "rate_air": 175.00,
    "rate_ship": 120.00,
    "is_active": false,
    "sort_order": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T11:45:00.000000Z"
  }
}
```

**Error Responses**:

**422 Unprocessable Entity - Validation Error**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "rate_air": ["The rate air must be a number."],
    "category": ["The selected category is invalid."]
  }
}
```

**422 Unprocessable Entity - Subcategory Validation**:
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subcategory": ["Subcategory is required for category C."]
  }
}
```

**400 Bad Request**:
```json
{
  "success": false,
  "message": "Failed to update shipping rate",
  "error": "Error message details"
}
```

---

### 5. Delete Shipping Rate

**Endpoint**: `DELETE /api/admin/shipping-rates/{shippingRate}`

**Description**: Deletes a shipping rate permanently.

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**URL Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `shippingRate` | integer | Yes | The ID of the shipping rate to delete |

**Example Request**:
```bash
DELETE /api/admin/shipping-rates/1
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "message": "Shipping rate deleted successfully"
}
```

**Error Response (404 Not Found)**:
```json
{
  "message": "No query results for model [App\\Models\\ShippingRate] {id}"
}
```

**Error Response (500 Internal Server Error)**:
```json
{
  "success": false,
  "message": "Failed to delete shipping rate",
  "error": "Error message details"
}
```

---

### 6. Toggle Active Status

**Endpoint**: `POST /api/admin/shipping-rates/{shippingRate}/toggle-active`

**Description**: Toggles the active status of a shipping rate (activates if inactive, deactivates if active).

**Authentication**: Required (Admin)

**Request Headers**:
```
Content-Type: application/json
Authorization: Bearer {admin_token}
Accept: application/json
```

**URL Parameters**:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `shippingRate` | integer | Yes | The ID of the shipping rate |

**Example Request**:
```bash
POST /api/admin/shipping-rates/1/toggle-active
```

**Success Response (200 OK)**:
```json
{
  "success": true,
  "message": "Shipping rate status updated successfully",
  "data": {
    "id": 1,
    "category": "A",
    "subcategory": null,
    "description_bn": "সাধারণ পণ্য",
    "description_en": "General Products",
    "rate_air": 150.00,
    "rate_ship": 100.00,
    "is_active": false,
    "sort_order": 1,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T12:00:00.000000Z"
  }
}
```

**Error Response (404 Not Found)**:
```json
{
  "message": "No query results for model [App\\Models\\ShippingRate] {id}"
}
```

**Error Response (500 Internal Server Error)**:
```json
{
  "success": false,
  "message": "Failed to update shipping rate status",
  "error": "Error message details"
}
```

---

## Data Model

### ShippingRate Object

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Unique identifier |
| `category` | string | Category: `A`, `B`, or `C` |
| `subcategory` | string\|null | Subcategory (only for category C): `mold_tape_garments`, `liquid_cosmetics`, `battery_powerbank`, or `sunglasses` |
| `description_bn` | string | Description in Bengali |
| `description_en` | string | Description in English |
| `rate_air` | decimal(2) | Air shipping rate |
| `rate_ship` | decimal(2) | Ship shipping rate |
| `is_active` | boolean | Whether the rate is active |
| `sort_order` | integer | Sort order for display |
| `created_at` | datetime | Creation timestamp |
| `updated_at` | datetime | Last update timestamp |

---

## Business Rules

1. **Category A & B**: 
   - Do not have subcategories
   - `subcategory` field must be `null`

2. **Category C**: 
   - Must have a subcategory
   - Valid subcategories: `mold_tape_garments`, `liquid_cosmetics`, `battery_powerbank`, `sunglasses`

3. **Rates**: 
   - Both `rate_air` and `rate_ship` must be non-negative numbers
   - Stored with 2 decimal places

4. **Active Status**: 
   - Only active rates are typically used in calculations
   - Can be toggled using the toggle-active endpoint

5. **Sorting**: 
   - Default sort order is by `sort_order` (ascending), then by `category`, then by `subcategory`

---

## Common Use Cases

### Use Case 1: Create a General Product Shipping Rate (Category A)
```bash
POST /api/admin/shipping-rates
{
  "category": "A",
  "description_bn": "সাধারণ পণ্য",
  "description_en": "General Products",
  "rate_air": 150.00,
  "rate_ship": 100.00,
  "is_active": true,
  "sort_order": 1
}
```

### Use Case 2: Create a Special Category Shipping Rate (Category C)
```bash
POST /api/admin/shipping-rates
{
  "category": "C",
  "subcategory": "liquid_cosmetics",
  "description_bn": "তরল প্রসাধনী",
  "description_en": "Liquid Cosmetics",
  "rate_air": 200.00,
  "rate_ship": 150.00,
  "is_active": true,
  "sort_order": 5
}
```

### Use Case 3: List Active Shipping Rates for Category A
```bash
GET /api/admin/shipping-rates?category=A&is_active=true
```

### Use Case 4: Update Only the Rates
```bash
PUT /api/admin/shipping-rates/1
{
  "rate_air": 175.00,
  "rate_ship": 125.00
}
```

### Use Case 5: Deactivate a Shipping Rate
```bash
POST /api/admin/shipping-rates/1/toggle-active
```

---

## Related Endpoints

- `GET /api/shipping-rates` - Public endpoint to get active shipping rates
- `GET /api/shipping-rates/grouped` - Public endpoint to get rates grouped by category
- `GET /api/shipping-rates/category/{category}` - Public endpoint to get rates by category

---

## Notes

- All endpoints require admin authentication
- The `subcategory` field is automatically set to `null` for categories A and B during create/update operations
- When updating a shipping rate to category C, you must provide a valid subcategory
- When updating a shipping rate from category C to A or B, the subcategory will be automatically set to `null`
- Rates are stored with 2 decimal precision
- The toggle-active endpoint provides a convenient way to activate/deactivate rates without sending the full update payload


