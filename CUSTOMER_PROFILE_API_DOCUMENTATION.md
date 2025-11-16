# Customer Profile API Documentation

Complete documentation for the Customer Profile Management API endpoints.

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Endpoints](#endpoints)
   - [Get Customer Profile](#get-customer-profile)
   - [Update Customer Profile](#update-customer-profile)
4. [Response Structures](#response-structures)
5. [Error Responses](#error-responses)
6. [Profile Picture Upload](#profile-picture-upload)
7. [Example Use Cases](#example-use-cases)

---

## Overview

The Customer Profile API allows authenticated customers to view and update their own profile information, including name, email, address, and profile picture. These endpoints are protected and require customer authentication via Bearer token.

**Base URL**: `http://your-domain.com/api`

---

## Authentication

**Required**: Customer authentication
- **Authentication Method**: Bearer token via `Authorization: Bearer {customer_token}` header
- **Middleware**: `customer` (validates Customer model authentication)
- **Token Type**: Laravel Sanctum personal access token

**Note**: These endpoints are only accessible to authenticated customers. Admin/User tokens will not work with these endpoints.

---

## Endpoints

### Get Customer Profile

Retrieve the authenticated customer's profile information.

#### Endpoint
```
GET /api/customer/profile
```

#### Headers
```
Authorization: Bearer {customer_token}
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, State 12345",
    "role": "customer",
    "profile_picture": "profile_pictures/abc123.jpg",
    "profile_picture_url": "http://your-domain.com/storage/profile_pictures/abc123.jpg",
    "is_banned": false,
    "is_suspended": false,
    "banned_at": null,
    "suspended_at": null,
    "ban_reason": null,
    "suspend_reason": null,
    "created_at": "2025-01-10T08:00:00.000000Z",
    "updated_at": "2025-01-10T08:00:00.000000Z"
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | Customer unique identifier |
| `name` | string\|null | Customer's full name |
| `email` | string\|null | Customer's email address |
| `phone` | string | Customer's phone number (required, used for authentication) |
| `address` | string\|null | Customer's address |
| `role` | string | Customer role (typically "customer") |
| `profile_picture` | string\|null | Path to profile picture in storage |
| `profile_picture_url` | string\|null | Full URL to profile picture (computed attribute) |
| `is_banned` | boolean | Whether customer is banned |
| `is_suspended` | boolean | Whether customer is suspended |
| `banned_at` | datetime\|null | Date when customer was banned |
| `suspended_at` | datetime\|null | Date when customer was suspended |
| `ban_reason` | string\|null | Reason for ban |
| `suspend_reason` | string\|null | Reason for suspension |
| `created_at` | datetime | Account creation timestamp |
| `updated_at` | datetime | Last update timestamp |

#### Error Responses

**401 Unauthorized** - Invalid or missing authentication token
```json
{
  "message": "Unauthenticated."
}
```

#### Example Request

```bash
curl -X GET "http://your-domain.com/api/customer/profile" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Accept: application/json"
```

#### Example Response (JavaScript/Fetch)

```javascript
const response = await fetch('http://your-domain.com/api/customer/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${customerToken}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();
console.log(data.data); // Customer profile object
```

---

### Update Customer Profile

Update the authenticated customer's profile information. All fields are optional - only provided fields will be updated.

#### Endpoint
```
PUT /api/customer/profile
```

#### Headers
```
Authorization: Bearer {customer_token}
Accept: application/json
Content-Type: application/json
```

**OR** for profile picture upload:

```
Authorization: Bearer {customer_token}
Accept: application/json
Content-Type: multipart/form-data
```

#### Request Body (JSON)

| Field | Type | Description | Required | Validation |
|-------|------|-------------|----------|------------|
| `name` | string | Customer's full name | No | Max 255 characters |
| `email` | string | Customer's email address | No | Valid email format, unique |
| `address` | string | Customer's address | No | No specific limit |
| `profile_picture` | file | Profile picture image | No | Image file (jpeg, png, jpg, gif), max 2MB |

#### Request Body (Form Data - for profile picture)

When uploading a profile picture, use `multipart/form-data`:

| Field | Type | Description | Required |
|-------|------|-------------|----------|
| `name` | string | Customer's full name | No |
| `email` | string | Customer's email address | No |
| `address` | string | Customer's address | No |
| `profile_picture` | file | Profile picture image file | No |

#### Success Response (200 OK)

```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "+1234567890",
    "address": "456 New St, City, State 12345",
    "role": "customer",
    "profile_picture": "profile_pictures/xyz789.jpg",
    "profile_picture_url": "http://your-domain.com/storage/profile_pictures/xyz789.jpg",
    "is_banned": false,
    "is_suspended": false,
    "banned_at": null,
    "suspended_at": null,
    "ban_reason": null,
    "suspend_reason": null,
    "created_at": "2025-01-10T08:00:00.000000Z",
    "updated_at": "2025-01-10T10:30:00.000000Z"
  }
}
```

#### Error Responses

**401 Unauthorized** - Invalid or missing authentication token
```json
{
  "message": "Unauthenticated. Customer authentication required."
}
```

**422 Validation Error** - Invalid input data
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "profile_picture": [
      "The profile picture must be an image.",
      "The profile picture must not be greater than 2048 kilobytes."
    ]
  }
}
```

#### Validation Rules

- **name**: Optional, string, maximum 255 characters
- **email**: Optional, valid email format, must be unique if different from current email
- **address**: Optional, string
- **profile_picture**: Optional, must be an image file (jpeg, png, jpg, gif), maximum 2MB (2048 KB)

#### Profile Picture Behavior

- When a new profile picture is uploaded, the old profile picture (if exists) is automatically deleted from storage
- Profile pictures are stored in `storage/app/public/profile_pictures/`
- The `profile_picture_url` attribute provides the full accessible URL to the image
- Supported formats: JPEG, PNG, JPG, GIF
- Maximum file size: 2MB

#### Example Requests

**Update name and email (JSON)**

```bash
curl -X PUT "http://your-domain.com/api/customer/profile" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "email": "john.updated@example.com"
  }'
```

**Update address only (JSON)**

```bash
curl -X PUT "http://your-domain.com/api/customer/profile" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "address": "456 New Street, City, State 12345"
  }'
```

**Update profile picture (Form Data)**

```bash
curl -X PUT "http://your-domain.com/api/customer/profile" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Accept: application/json" \
  -F "profile_picture=@/path/to/image.jpg" \
  -F "name=John Doe"
```

**Update all fields including profile picture (Form Data)**

```bash
curl -X PUT "http://your-domain.com/api/customer/profile" \
  -H "Authorization: Bearer {customer_token}" \
  -H "Accept: application/json" \
  -F "name=John Doe" \
  -F "email=john.doe@example.com" \
  -F "address=123 Main St, City, State 12345" \
  -F "profile_picture=@/path/to/image.jpg"
```

#### Example Response (JavaScript/Fetch - JSON)

```javascript
const response = await fetch('http://your-domain.com/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${customerToken}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'John Doe Updated',
    email: 'john.updated@example.com',
    address: '456 New Street, City, State 12345'
  })
});

const data = await response.json();
console.log(data.message); // "Profile updated successfully"
console.log(data.data); // Updated customer profile object
```

#### Example Response (JavaScript/Fetch - Form Data with Image)

```javascript
const formData = new FormData();
formData.append('name', 'John Doe');
formData.append('email', 'john.doe@example.com');
formData.append('address', '123 Main St');
formData.append('profile_picture', fileInput.files[0]); // File from input element

const response = await fetch('http://your-domain.com/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${customerToken}`,
    'Accept': 'application/json'
    // Don't set Content-Type header - browser will set it with boundary
  },
  body: formData
});

const data = await response.json();
console.log(data.message); // "Profile updated successfully"
console.log(data.data.profile_picture_url); // Full URL to uploaded image
```

---

## Response Structures

### Customer Profile Object

The customer profile object contains all customer information. Sensitive fields like `password`, `otp`, and `otp_expires_at` are automatically hidden from responses.

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "address": "123 Main St, City, State 12345",
  "role": "customer",
  "profile_picture": "profile_pictures/abc123.jpg",
  "profile_picture_url": "http://your-domain.com/storage/profile_pictures/abc123.jpg",
  "is_banned": false,
  "is_suspended": false,
  "banned_at": null,
  "suspended_at": null,
  "ban_reason": null,
  "suspend_reason": null,
  "created_at": "2025-01-10T08:00:00.000000Z",
  "updated_at": "2025-01-10T08:00:00.000000Z"
}
```

---

## Error Responses

### 401 Unauthorized

Returned when:
- No authentication token provided
- Invalid authentication token
- Token belongs to a non-Customer user (e.g., Admin/User token)

```json
{
  "message": "Unauthenticated."
}
```

or

```json
{
  "message": "Unauthenticated. Customer authentication required."
}
```

### 422 Validation Error

Returned when request data fails validation:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

Common validation errors:
- **email**: "The email has already been taken."
- **email**: "The email must be a valid email address."
- **name**: "The name may not be greater than 255 characters."
- **profile_picture**: "The profile picture must be an image."
- **profile_picture**: "The profile picture must not be greater than 2048 kilobytes."
- **profile_picture**: "The profile picture must be a file of type: jpeg, png, jpg, gif."

### 500 Internal Server Error

Returned when an unexpected server error occurs:

```json
{
  "message": "Server Error"
}
```

---

## Profile Picture Upload

### Supported Formats
- JPEG (.jpg, .jpeg)
- PNG (.png)
- GIF (.gif)

### File Size Limit
- Maximum: 2MB (2048 KB)

### Storage Location
- Files are stored in: `storage/app/public/profile_pictures/`
- Accessible via: `http://your-domain.com/storage/profile_pictures/{filename}`

### Automatic Cleanup
- When a new profile picture is uploaded, the old one is automatically deleted
- This prevents storage bloat from unused images

### URL Generation
- The `profile_picture_url` attribute automatically provides the full accessible URL
- This URL is generated using Laravel's `asset()` helper with the storage path

---

## Example Use Cases

### Use Case 1: Customer Views Their Profile

A customer wants to view their current profile information after logging in.

**Request:**
```bash
GET /api/customer/profile
Authorization: Bearer {customer_token}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "profile_picture_url": "http://your-domain.com/storage/profile_pictures/abc123.jpg"
  }
}
```

### Use Case 2: Customer Updates Their Name and Email

A customer wants to update their name and email address.

**Request:**
```bash
PUT /api/customer/profile
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "name": "John Smith",
  "email": "john.smith@example.com"
}
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Smith",
    "email": "john.smith@example.com",
    "phone": "+1234567890",
    "address": "123 Main St"
  }
}
```

### Use Case 3: Customer Uploads Profile Picture

A customer wants to upload a new profile picture.

**Request:**
```bash
PUT /api/customer/profile
Authorization: Bearer {customer_token}
Content-Type: multipart/form-data

profile_picture: [binary file data]
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "profile_picture": "profile_pictures/xyz789.jpg",
    "profile_picture_url": "http://your-domain.com/storage/profile_pictures/xyz789.jpg"
  }
}
```

### Use Case 4: Customer Updates Only Address

A customer wants to update only their address, leaving other fields unchanged.

**Request:**
```bash
PUT /api/customer/profile
Authorization: Bearer {customer_token}
Content-Type: application/json

{
  "address": "456 New Street, City, State 12345"
}
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "address": "456 New Street, City, State 12345"
  }
}
```

### Use Case 5: Complete Profile Update

A customer wants to update all profile fields at once, including uploading a new profile picture.

**Request:**
```bash
PUT /api/customer/profile
Authorization: Bearer {customer_token}
Content-Type: multipart/form-data

name: "John Doe Updated"
email: "john.updated@example.com"
address: "789 Latest Ave, City, State 12345"
profile_picture: [binary file data]
```

**Response:**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "+1234567890",
    "address": "789 Latest Ave, City, State 12345",
    "profile_picture": "profile_pictures/new123.jpg",
    "profile_picture_url": "http://your-domain.com/storage/profile_pictures/new123.jpg"
  }
}
```

---

## Notes

1. **Phone Number**: The phone number cannot be updated through the profile API as it is used for authentication. To change phone number, customers would need to go through a separate process (contact support or use a dedicated phone update endpoint if available).

2. **Partial Updates**: All fields in the update request are optional. Only fields provided in the request will be updated. Fields not included in the request will remain unchanged.

3. **Email Uniqueness**: When updating email, the system checks for uniqueness. If the email is already taken by another customer, a validation error will be returned.

4. **Profile Picture Replacement**: When uploading a new profile picture, the old one is automatically deleted to save storage space.

5. **Authentication**: These endpoints use the `customer` middleware which specifically validates Customer model authentication. Admin/User tokens will not work with these endpoints.

6. **Response Format**: Both endpoints return data in a consistent format with a `data` key containing the customer object. The update endpoint also includes a `message` field indicating success.

---

## Integration Examples

### React/Next.js Example

```javascript
// Get customer profile
async function getCustomerProfile(token) {
  const response = await fetch('http://your-domain.com/api/customer/profile', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  
  if (!response.ok) {
    throw new Error('Failed to fetch profile');
  }
  
  const data = await response.json();
  return data.data;
}

// Update customer profile
async function updateCustomerProfile(token, profileData) {
  const formData = new FormData();
  
  if (profileData.name) formData.append('name', profileData.name);
  if (profileData.email) formData.append('email', profileData.email);
  if (profileData.address) formData.append('address', profileData.address);
  if (profileData.profile_picture) {
    formData.append('profile_picture', profileData.profile_picture);
  }
  
  const response = await fetch('http://your-domain.com/api/customer/profile', {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    },
    body: formData
  });
  
  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Failed to update profile');
  }
  
  const data = await response.json();
  return data.data;
}
```

### Vue.js Example

```javascript
// Using axios
import axios from 'axios';

// Get customer profile
async function getCustomerProfile() {
  try {
    const response = await axios.get('/api/customer/profile', {
      headers: {
        'Authorization': `Bearer ${this.$store.state.customerToken}`
      }
    });
    return response.data.data;
  } catch (error) {
    console.error('Failed to fetch profile:', error);
    throw error;
  }
}

// Update customer profile
async function updateCustomerProfile(profileData) {
  const formData = new FormData();
  
  Object.keys(profileData).forEach(key => {
    if (profileData[key] !== null && profileData[key] !== undefined) {
      formData.append(key, profileData[key]);
    }
  });
  
  try {
    const response = await axios.put('/api/customer/profile', formData, {
      headers: {
        'Authorization': `Bearer ${this.$store.state.customerToken}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    return response.data.data;
  } catch (error) {
    console.error('Failed to update profile:', error);
    throw error;
  }
}
```

---

**Last Updated**: 2025-01-16

