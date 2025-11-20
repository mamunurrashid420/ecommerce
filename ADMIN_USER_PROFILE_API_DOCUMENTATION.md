# Admin/User Profile Management API Documentation

This document describes the admin/user profile management API endpoints. These endpoints are for authenticated admin users (User model) to manage their profile information.

## Base URL
All endpoints are prefixed with `/api`

## Authentication

All endpoints require authentication using Laravel Sanctum. Include the authentication token in the `Authorization` header:

```
Authorization: Bearer {token}
```

The token is obtained from the login endpoint (`POST /api/login`).

## Endpoints

### 1. Get Authenticated User Profile

Retrieve the authenticated admin/user's profile information.

**Endpoint:** `GET /api/profile`

**Authentication:** Required (Bearer token)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:** None

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "email_verified_at": "2025-11-20T05:00:00.000000Z",
    "phone": "+1234567890",
    "address": "123 Admin Street, City, Country",
    "role": "admin",
    "status": "active",
    "role_id": 1,
    "last_login_at": "2025-11-20T10:30:00.000000Z",
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T10:30:00.000000Z",
    "roleModel": {
      "id": 1,
      "name": "Administrator",
      "slug": "administrator"
    }
  }
}
```

**Response Fields:**
- `id`: User ID
- `name`: User's full name
- `email`: User's email address
- `email_verified_at`: Email verification timestamp (nullable)
- `phone`: User's phone number (nullable)
- `address`: User's address (nullable)
- `role`: User role (admin/customer)
- `status`: Account status (active/inactive)
- `role_id`: Associated role ID (nullable)
- `last_login_at`: Last login timestamp (nullable)
- `roleModel`: Role relationship object (if role_id exists)
- `created_at`: Account creation timestamp
- `updated_at`: Last update timestamp

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Error Response (500):**
```json
{
  "message": "Server error message"
}
```

---

### 2. Update Authenticated User Profile

Update the authenticated admin/user's profile information.

**Endpoint:** `PUT /api/profile`

**Authentication:** Required (Bearer token)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**
```json
{
  "name": "Updated Admin Name",
  "email": "updated@example.com",
  "phone": "+0987654321",
  "address": "456 New Address, City, Country"
}
```

**Request Fields:**
All fields are optional (use `sometimes` validation):
- `name` (string, optional, max: 255): User's full name
- `email` (string, optional, email, unique): User's email address (must be unique)
- `phone` (string, optional, nullable, max: 20): User's phone number
- `address` (string, optional, nullable): User's address

**Success Response (200):**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Admin Name",
    "email": "updated@example.com",
    "email_verified_at": "2025-11-20T05:00:00.000000Z",
    "phone": "+0987654321",
    "address": "456 New Address, City, Country",
    "role": "admin",
    "status": "active",
    "role_id": 1,
    "last_login_at": "2025-11-20T10:30:00.000000Z",
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T11:00:00.000000Z",
    "roleModel": {
      "id": 1,
      "name": "Administrator",
      "slug": "administrator"
    }
  }
}
```

**Partial Update Example:**
You can update only specific fields:
```json
{
  "name": "New Name Only"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Error Response (422) - Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "name": ["The name field must not be greater than 255 characters."]
  }
}
```

**Error Response (500):**
```json
{
  "message": "Server error message"
}
```

---

### 3. Update Authenticated User Password

Change the password for the authenticated admin/user.

**Endpoint:** `PUT /api/profile/password`

**Authentication:** Required (Bearer token)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body (JSON):**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Required Fields:**
- `current_password` (string, required): Current password for verification
- `password` (string, required, min: 8): New password
- `password_confirmation` (string, required): Password confirmation (must match password)

**Password Requirements:**
- Minimum length: 8 characters
- Must be confirmed (password_confirmation must match password)
- Passwords are hashed using bcrypt

**Success Response (200):**
```json
{
  "message": "Password updated successfully"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

**Error Response (422) - Incorrect Current Password:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": ["The current password is incorrect."]
  }
}
```

**Error Response (422) - Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "password": [
      "The password must be at least 8 characters.",
      "The password confirmation does not match."
    ]
  }
}
```

**Error Response (500):**
```json
{
  "message": "Server error message"
}
```

---

## Usage Examples

### JavaScript (Fetch API)

**Get Profile:**
```javascript
fetch('http://your-domain.com/api/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
  .then(response => response.json())
  .then(data => {
    console.log('User Profile:', data.data);
  })
  .catch(error => console.error('Error:', error));
```

**Update Profile:**
```javascript
fetch('http://your-domain.com/api/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    name: 'Updated Name',
    email: 'updated@example.com',
    phone: '+1234567890',
    address: 'New Address'
  })
})
  .then(response => response.json())
  .then(data => {
    console.log('Profile Updated:', data.message);
    console.log('Updated Data:', data.data);
  })
  .catch(error => console.error('Error:', error));
```

**Update Password:**
```javascript
fetch('http://your-domain.com/api/profile/password', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    current_password: 'oldpassword123',
    password: 'newpassword123',
    password_confirmation: 'newpassword123'
  })
})
  .then(response => response.json())
  .then(data => {
    console.log('Password Updated:', data.message);
  })
  .catch(error => {
    if (error.response) {
      console.error('Validation Error:', error.response.data);
    } else {
      console.error('Error:', error);
    }
  });
```

### cURL

**Get Profile:**
```bash
curl -X GET http://your-domain.com/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

**Update Profile:**
```bash
curl -X PUT http://your-domain.com/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com",
    "phone": "+1234567890",
    "address": "New Address"
  }'
```

**Update Password:**
```bash
curl -X PUT http://your-domain.com/api/profile/password \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

### Axios (React/Vue)

**Get Profile:**
```javascript
import axios from 'axios';

const getProfile = async (token) => {
  try {
    const response = await axios.get('http://your-domain.com/api/profile', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return response.data.data;
  } catch (error) {
    console.error('Error fetching profile:', error.response?.data || error.message);
    throw error;
  }
};
```

**Update Profile:**
```javascript
const updateProfile = async (token, profileData) => {
  try {
    const response = await axios.put('http://your-domain.com/api/profile', profileData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error updating profile:', error.response?.data || error.message);
    throw error;
  }
};

// Usage
updateProfile(token, {
  name: 'Updated Name',
  email: 'updated@example.com'
});
```

**Update Password:**
```javascript
const updatePassword = async (token, passwordData) => {
  try {
    const response = await axios.put(
      'http://your-domain.com/api/profile/password',
      passwordData,
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        }
      }
    );
    return response.data;
  } catch (error) {
    console.error('Error updating password:', error.response?.data || error.message);
    throw error;
  }
};

// Usage
updatePassword(token, {
  current_password: 'oldpassword123',
  password: 'newpassword123',
  password_confirmation: 'newpassword123'
});
```

---

## Error Handling

All endpoints return appropriate HTTP status codes:
- `200` - Success
- `401` - Unauthenticated (missing or invalid token)
- `422` - Validation Error
- `500` - Server Error

Error responses follow this format:
```json
{
  "message": "Error message",
  "errors": {
    "field_name": ["Error message for this field"]
  }
}
```

---

## Security Notes

1. **Token Security**: Tokens should be stored securely (e.g., in httpOnly cookies or secure storage) and never exposed in client-side code or URLs.

2. **HTTPS**: Always use HTTPS in production to protect credentials and tokens in transit.

3. **Password Requirements**: 
   - Minimum 8 characters
   - Consider implementing additional password strength requirements (uppercase, lowercase, numbers, special characters) in the frontend

4. **Email Uniqueness**: When updating email, ensure it's unique across all users. The API will return a validation error if the email is already taken.

5. **Current Password Verification**: Password changes require verification of the current password to prevent unauthorized changes.

---

## Role Model Relationship

If the user has a `role_id`, the profile response includes the `roleModel` relationship with:
- `id`: Role ID
- `name`: Role name
- `slug`: Role slug
- Other role properties as defined in the Role model

---

## Account Status

Users can have the following account statuses:
- **active**: Normal account, can login and access the system
- **inactive**: Account is inactive, may have restricted access

Only active users can login. The profile endpoint will return the current status.

---

## Notes

- All endpoints require authentication via Laravel Sanctum
- Profile updates are partial - you can update only the fields you want to change
- Email updates must be unique across all users
- Password updates require current password verification
- The `roleModel` relationship is automatically loaded if `role_id` exists
- All timestamps are in ISO 8601 format (UTC)

---

## Support

For issues or questions regarding the admin/user profile management API, please contact the development team.

