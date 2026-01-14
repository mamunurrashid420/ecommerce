# Districts and Upazillas API Documentation

## Overview
This API provides endpoints to manage and retrieve Bangladesh districts and upazillas (upazilas) data. The system includes both public endpoints (for frontend use) and admin-only endpoints (for CRUD operations).

## Database Schema

### Districts Table
- `id` - Primary key
- `name` - District name in English
- `name_bn` - District name in Bengali
- `division` - Division name in English (Dhaka, Chittagong, Rajshahi, Khulna, Barishal, Sylhet, Rangpur, Mymensingh)
- `division_bn` - Division name in Bengali
- `sort_order` - For custom sorting (default: 0)
- `is_active` - Active status (default: true)
- `created_at`, `updated_at` - Timestamps

### Upazillas Table
- `id` - Primary key
- `district_id` - Foreign key to districts table
- `name` - Upazilla name in English
- `name_bn` - Upazilla name in Bengali
- `sort_order` - For custom sorting (default: 0)
- `is_active` - Active status (default: true)
- `created_at`, `updated_at` - Timestamps

## Seeded Data
The database has been seeded with:
- **64 Districts** across 8 divisions of Bangladesh
- **544+ Upazillas** distributed across all districts
- All data includes both English and Bengali names

## Public API Endpoints (No Authentication Required)

### Districts

#### 1. Get All Districts
```http
GET /api/districts
```

**Query Parameters:**
- `active` (boolean) - Filter by active status
- `division` (string) - Filter by division name
- `with_upazillas` (boolean) - Include upazillas in response
- `active_only` (boolean) - When with_upazillas is true, only include active upazillas
- `search` (string) - Search in name, name_bn, division, division_bn
- `per_page` (integer, max: 100) - Enable pagination

**Example Requests:**
```bash
# Get all active districts
GET /api/districts?active=1

# Get districts with their upazillas
GET /api/districts?with_upazillas=1

# Get districts of Dhaka division
GET /api/districts?division=Dhaka

# Search districts
GET /api/districts?search=Dhaka

# Paginated results
GET /api/districts?per_page=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Dhaka",
      "name_bn": "ঢাকা",
      "division": "Dhaka",
      "division_bn": "ঢাকা",
      "sort_order": 0,
      "is_active": true,
      "created_at": "2026-01-14T...",
      "updated_at": "2026-01-14T...",
      "upazillas": [...]  // if with_upazillas=1
    }
  ]
}
```

#### 2. Get Single District
```http
GET /api/districts/{id}
```

**Query Parameters:**
- `with_upazillas` (boolean) - Include upazillas in response
- `active_only` (boolean) - When with_upazillas is true, only include active upazillas

**Example:**
```bash
GET /api/districts/1?with_upazillas=1
```

#### 3. Get All Divisions
```http
GET /api/districts/divisions
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "division": "Dhaka",
      "division_bn": "ঢাকা"
    },
    {
      "division": "Chittagong",
      "division_bn": "চট্টগ্রাম"
    }
  ]
}
```

### Upazillas

#### 1. Get All Upazillas
```http
GET /api/upazillas
```

**Query Parameters:**
- `active` (boolean) - Filter by active status
- `district_id` (integer) - Filter by district
- `with_district` (boolean) - Include district information
- `search` (string) - Search in name and name_bn
- `per_page` (integer, max: 100) - Enable pagination

**Example Requests:**
```bash
# Get all active upazillas
GET /api/upazillas?active=1

# Get upazillas of a specific district
GET /api/upazillas?district_id=1

# Get upazillas with district info
GET /api/upazillas?with_district=1

# Search upazillas
GET /api/upazillas?search=Mirpur
```

#### 2. Get Single Upazilla
```http
GET /api/upazillas/{id}
```

**Query Parameters:**
- `with_district` (boolean) - Include district information

**Example:**
```bash
GET /api/upazillas/1?with_district=1
```

#### 3. Get Upazillas by District
```http
GET /api/upazillas/district/{districtId}
```

**Description:** Returns only active upazillas for a specific district, ordered by sort_order and name.

**Example:**
```bash
GET /api/upazillas/district/1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "district_id": 1,
      "name": "Dhamrai",
      "name_bn": "ধামরাই",
      "sort_order": 0,
      "is_active": true,
      "created_at": "2026-01-14T...",
      "updated_at": "2026-01-14T..."
    }
  ]
}
```

## Admin API Endpoints (Authentication Required)

All admin endpoints require `auth:sanctum` middleware and admin role.

### Admin District Management

#### 1. Get All Districts (Admin)
```http
GET /api/admin/districts
```
Same query parameters as public endpoint.

#### 2. Create District
```http
POST /api/admin/districts
```

**Request Body:**
```json
{
  "name": "New District",
  "name_bn": "নতুন জেলা",
  "division": "Dhaka",
  "division_bn": "ঢাকা",
  "sort_order": 0,
  "is_active": true
}
```

**Validation Rules:**
- `name` - required, string, max:255, unique
- `name_bn` - nullable, string, max:255
- `division` - nullable, string, max:255
- `division_bn` - nullable, string, max:255
- `sort_order` - nullable, integer, min:0
- `is_active` - nullable, boolean

#### 3. Get Single District (Admin)
```http
GET /api/admin/districts/{id}
```

#### 4. Update District
```http
PUT /api/admin/districts/{id}
```

**Request Body:** Same as create, all fields optional

#### 5. Delete District
```http
DELETE /api/admin/districts/{id}
```

**Note:** This will cascade delete all upazillas in this district.

#### 6. Toggle Active Status
```http
POST /api/admin/districts/{id}/toggle-active
```

### Admin Upazilla Management

#### 1. Get All Upazillas (Admin)
```http
GET /api/admin/upazillas
```
Same query parameters as public endpoint.

#### 2. Create Upazilla
```http
POST /api/admin/upazillas
```

**Request Body:**
```json
{
  "district_id": 1,
  "name": "New Upazilla",
  "name_bn": "নতুন উপজেলা",
  "sort_order": 0,
  "is_active": true
}
```

**Validation Rules:**
- `district_id` - required, exists in districts table
- `name` - required, string, max:255 (must be unique within the district)
- `name_bn` - nullable, string, max:255
- `sort_order` - nullable, integer, min:0
- `is_active` - nullable, boolean

#### 3. Get Single Upazilla (Admin)
```http
GET /api/admin/upazillas/{id}
```

#### 4. Update Upazilla
```http
PUT /api/admin/upazillas/{id}
```

**Request Body:** Same as create, all fields optional

#### 5. Delete Upazilla
```http
DELETE /api/admin/upazillas/{id}
```

#### 6. Toggle Active Status
```http
POST /api/admin/upazillas/{id}/toggle-active
```

## Error Responses

All endpoints return consistent error responses:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    // Validation errors if applicable
  }
}
```

**HTTP Status Codes:**
- `200` - Success
- `201` - Created
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Running Migrations and Seeders

### Run Migrations
```bash
php artisan migrate
```

### Run Seeder
```bash
php artisan db:seed --class=DistrictUpazilaSeeder
```

### Refresh Everything
```bash
php artisan migrate:fresh --seed
```

## Model Relationships

### District Model
- Has many `upazillas` (all upazillas)
- Has many `activeUpazillas` (only active upazillas)

### Upazila Model
- Belongs to `district`

## Scopes

### District Scopes
- `active()` - Only active districts
- `ordered()` - Order by sort_order and name
- `byDivision($division)` - Filter by division

### Upazila Scopes
- `active()` - Only active upazillas
- `ordered()` - Order by sort_order and name
- `byDistrict($districtId)` - Filter by district

## Example Usage in Frontend

### Get Districts with Upazillas for a Dropdown
```javascript
// Fetch districts
const response = await fetch('/api/districts?active=1&with_upazillas=1&active_only=1');
const { data: districts } = await response.json();

// Use in dropdown
districts.forEach(district => {
  console.log(`${district.name} (${district.name_bn})`);
  district.active_upazillas.forEach(upazilla => {
    console.log(`  - ${upazilla.name} (${upazilla.name_bn})`);
  });
});
```

### Get Upazillas when District is Selected
```javascript
// When user selects a district
const districtId = 1;
const response = await fetch(`/api/upazillas/district/${districtId}`);
const { data: upazillas } = await response.json();

// Populate upazilla dropdown
upazillas.forEach(upazilla => {
  console.log(`${upazilla.name} (${upazilla.name_bn})`);
});
```

## Notes

1. All endpoints return JSON responses with a `success` boolean field
2. Public endpoints filter by active status by default when using specific endpoints
3. Admin endpoints have full access to all records regardless of status
4. The seeder includes all 64 districts and 544+ upazillas of Bangladesh
5. Both English and Bengali names are provided for all locations
6. Cascade deletion is enabled: deleting a district will delete all its upazillas

## Files Created

1. **Migrations:**
   - `2026_01_14_000001_create_districts_table.php`
   - `2026_01_14_000002_create_upazillas_table.php`

2. **Models:**
   - `app/Models/District.php`
   - `app/Models/Upazila.php`

3. **Controllers:**
   - `app/Http/Controllers/Api/DistrictController.php`
   - `app/Http/Controllers/Api/UpazilaController.php`

4. **Seeders:**
   - `database/seeders/DistrictUpazilaSeeder.php`

5. **Routes:**
   - Updated `routes/api.php` with public and admin routes

