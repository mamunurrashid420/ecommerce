# Admin User Management API Documentation

Complete documentation for Admin User Management APIs including profile management, password updates, and role assignment.

## Table of Contents
1. [Overview](#overview)
2. [Authenticated Admin Profile Management](#authenticated-admin-profile-management)
   - [Get Profile](#1-get-profile)
   - [Update Profile](#2-update-profile)
   - [Update Password](#3-update-password)
3. [Admin User Management](#admin-user-management)
   - [Get Admin User Details](#1-get-admin-user-details)
   - [Update Admin User Information](#2-update-admin-user-information)
   - [Update Admin User Password](#3-update-admin-user-password)
   - [Assign Role to User](#4-assign-role-to-user)
   - [Change User Role](#5-change-user-role)
4. [Error Responses](#error-responses)
5. [Complete Flow Examples](#complete-flow-examples)

---

## Overview

The Admin User Management API provides endpoints for:
- **Authenticated Admin Profile Management**: Get, update profile, and change password for the currently authenticated admin user
- **Admin User Management**: Manage other admin users including viewing details, updating information, changing passwords, and assigning roles

### Key Features
- Profile management for authenticated admin users
- Password update with current password verification
- Admin user information management
- Role assignment and change functionality
- Secure authentication using Laravel Sanctum

### Base URL
```
http://your-domain.com/api
```

### Authentication
All endpoints require authentication using Bearer token (Laravel Sanctum).

**Header Format:**
```
Authorization: Bearer {token}
```

---

## Authenticated Admin Profile Management

These endpoints allow the currently authenticated admin user to manage their own profile.

### 1. Get Profile

Get the profile information of the currently authenticated admin user.

#### Endpoint
```
GET /api/profile
```

#### Headers
```
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St",
    "role": "admin",
    "role_id": 1,
    "status": "active",
    "last_login_at": "2024-01-15T10:30:00.000000Z",
    "created_at": "2024-01-01T08:00:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z",
    "role_model": {
      "id": 1,
      "name": "Super Admin",
      "slug": "super-admin",
      "description": "Full system access",
      "is_active": true
    }
  }
}
```

---

### 2. Update Profile

Update the profile information of the currently authenticated admin user.

#### Endpoint
```
PUT /api/profile
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body (All fields are optional)
```json
{
  "name": "John Doe Updated",
  "email": "john.updated@example.com",
  "phone": "+1234567891",
  "address": "456 New St"
}
```

#### Response (200 OK)
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Doe Updated",
    "email": "john.updated@example.com",
    "phone": "+1234567891",
    "address": "456 New St",
    "role": "admin",
    "role_id": 1,
    "status": "active",
    "last_login_at": "2024-01-15T10:30:00.000000Z",
    "created_at": "2024-01-01T08:00:00.000000Z",
    "updated_at": "2024-01-15T11:00:00.000000Z",
    "role_model": {
      "id": 1,
      "name": "Super Admin",
      "slug": "super-admin",
      "description": "Full system access",
      "is_active": true
    }
  }
}
```

#### Validation Rules
- `name`: Optional, string, max 255 characters
- `email`: Optional, valid email format, must be unique (excluding current user)
- `phone`: Optional, string, max 20 characters
- `address`: Optional, string

---

### 3. Update Password

Update the password of the currently authenticated admin user. Requires current password verification.

#### Endpoint
```
PUT /api/profile/password
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Response (200 OK)
```json
{
  "message": "Password updated successfully"
}
```

#### Validation Rules
- `current_password`: Required, must match the user's current password
- `password`: Required, string, minimum 8 characters
- `password_confirmation`: Required, must match the password field

#### Error Response (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": [
      "The current password is incorrect."
    ]
  }
}
```

---

## Admin User Management

These endpoints allow admin users to manage other admin users. Requires admin authentication.

### 1. Get Admin User Details

Get detailed information about a specific admin user.

#### Endpoint
```
GET /api/users/{id}
```

#### Headers
```
Authorization: Bearer {token}
```

#### Response (200 OK)
```json
{
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567892",
    "address": "789 Oak Ave",
    "role": "admin",
    "role_id": 2,
    "status": "active",
    "last_login_at": "2024-01-14T15:20:00.000000Z",
    "created_at": "2024-01-05T09:00:00.000000Z",
    "updated_at": "2024-01-14T15:20:00.000000Z"
  }
}
```

#### Error Response (404 Not Found)
```json
{
  "message": "User not found"
}
```

---

### 2. Update Admin User Information

Update information for a specific admin user.

#### Endpoint
```
PUT /api/users/{id}
PATCH /api/users/{id}
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body (All fields are optional)
```json
{
  "name": "Jane Smith Updated",
  "email": "jane.updated@example.com",
  "phone": "+1234567893",
  "address": "321 Pine St",
  "status": "active"
}
```

#### Response (200 OK)
```json
{
  "message": "Admin user updated successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith Updated",
    "email": "jane.updated@example.com",
    "phone": "+1234567893",
    "address": "321 Pine St",
    "role": "admin",
    "role_id": 2,
    "status": "active",
    "last_login_at": "2024-01-14T15:20:00.000000Z",
    "created_at": "2024-01-05T09:00:00.000000Z",
    "updated_at": "2024-01-15T12:00:00.000000Z"
  }
}
```

#### Validation Rules
- `name`: Optional, string, max 255 characters
- `email`: Optional, valid email format, must be unique (excluding the user being updated)
- `phone`: Optional, string, max 20 characters
- `address`: Optional, string
- `status`: Optional, must be one of: `active`, `inactive`, `banned`

---

### 3. Update Admin User Password

Update the password for a specific admin user. Admin can reset any user's password without knowing the current password.

#### Endpoint
```
PUT /api/users/{id}/password
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

#### Response (200 OK)
```json
{
  "message": "Password updated successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com"
  }
}
```

#### Validation Rules
- `password`: Required, string, minimum 8 characters
- `password_confirmation`: Required, must match the password field

#### Error Response (404 Not Found)
```json
{
  "message": "User not found"
}
```

---

### 4. Assign Role to User

Assign a role to an admin user. This will set or update the user's role.

#### Endpoint
```
POST /api/users/{id}/assign-role
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
  "role_id": 2
}
```

#### Response (200 OK)
```json
{
  "message": "Role assigned successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567892",
    "address": "789 Oak Ave",
    "role": "admin",
    "role_id": 2,
    "status": "active",
    "last_login_at": "2024-01-14T15:20:00.000000Z",
    "created_at": "2024-01-05T09:00:00.000000Z",
    "updated_at": "2024-01-15T13:00:00.000000Z",
    "role_model": {
      "id": 2,
      "name": "Content Manager",
      "slug": "content-manager",
      "description": "Manages content and products",
      "is_active": true
    }
  }
}
```

#### Validation Rules
- `role_id`: Required, must exist in the `roles` table

#### Error Responses

**404 Not Found:**
```json
{
  "message": "User not found"
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "role_id": [
      "The selected role id is invalid."
    ]
  }
}
```

---

### 5. Change User Role

Change the role of an admin user. This is functionally the same as assign role but uses PUT method.

#### Endpoint
```
PUT /api/users/{id}/change-role
```

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
  "role_id": 3
}
```

#### Response (200 OK)
```json
{
  "message": "Role changed successfully",
  "data": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1234567892",
    "address": "789 Oak Ave",
    "role": "admin",
    "role_id": 3,
    "status": "active",
    "last_login_at": "2024-01-14T15:20:00.000000Z",
    "created_at": "2024-01-05T09:00:00.000000Z",
    "updated_at": "2024-01-15T14:00:00.000000Z",
    "role_model": {
      "id": 3,
      "name": "Sales Manager",
      "slug": "sales-manager",
      "description": "Manages sales and orders",
      "is_active": true
    }
  }
}
```

#### Validation Rules
- `role_id`: Required, must exist in the `roles` table

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "message": "Unauthorized. Admin access required."
}
```

### 404 Not Found
```json
{
  "message": "User not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "The field name error message."
    ]
  }
}
```

---

## Complete Flow Examples

### Example 1: Authenticated Admin Updates Own Profile

1. **Login to get token:**
```bash
POST /api/login
{
  "email": "admin@example.com",
  "password": "password123"
}
```

2. **Get own profile:**
```bash
GET /api/profile
Headers: Authorization: Bearer {token}
```

3. **Update own profile:**
```bash
PUT /api/profile
Headers: Authorization: Bearer {token}
{
  "name": "Updated Name",
  "email": "updated@example.com"
}
```

4. **Update own password:**
```bash
PUT /api/profile/password
Headers: Authorization: Bearer {token}
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

### Example 2: Admin Manages Other Users

1. **Get list of admin users:**
```bash
GET /api/users
Headers: Authorization: Bearer {token}
```

2. **Get specific user details:**
```bash
GET /api/users/2
Headers: Authorization: Bearer {token}
```

3. **Update user information:**
```bash
PUT /api/users/2
Headers: Authorization: Bearer {token}
{
  "name": "Updated User Name",
  "status": "active"
}
```

4. **Update user password:**
```bash
PUT /api/users/2/password
Headers: Authorization: Bearer {token}
{
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

5. **Assign role to user:**
```bash
POST /api/users/2/assign-role
Headers: Authorization: Bearer {token}
{
  "role_id": 2
}
```

6. **Change user role:**
```bash
PUT /api/users/2/change-role
Headers: Authorization: Bearer {token}
{
  "role_id": 3
}
```

---

## API Endpoints Summary

### Authenticated Admin Profile Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/profile` | Get authenticated admin profile | Yes |
| PUT | `/api/profile` | Update authenticated admin profile | Yes |
| PUT | `/api/profile/password` | Update authenticated admin password | Yes |

### Admin User Management Endpoints

| Method | Endpoint | Description | Auth Required | Admin Required |
|--------|----------|-------------|---------------|----------------|
| GET | `/api/users` | List all admin users | Yes | Yes |
| GET | `/api/users/{id}` | Get admin user details | Yes | Yes |
| POST | `/api/users` | Create admin user | Yes | Yes |
| PUT | `/api/users/{id}` | Update admin user information | Yes | Yes |
| DELETE | `/api/users/{id}` | Delete admin user | Yes | Yes |
| PUT | `/api/users/{id}/password` | Update admin user password | Yes | Yes |
| POST | `/api/users/{id}/assign-role` | Assign role to user | Yes | Yes |
| PUT | `/api/users/{id}/change-role` | Change user role | Yes | Yes |
| POST | `/api/users/{id}/ban` | Ban admin user | Yes | Yes |
| POST | `/api/users/{id}/unban` | Unban admin user | Yes | Yes |
| GET | `/api/users-stats` | Get admin user statistics | Yes | Yes |

---

## Notes

1. **Password Security**: 
   - When updating own password, current password verification is required
   - When admin updates another user's password, no current password is needed

2. **Role Management**:
   - `assign-role` and `change-role` are functionally the same
   - Both endpoints update the `role_id` field of the user
   - The role must exist in the `roles` table

3. **User Status**:
   - Valid status values: `active`, `inactive`, `banned`
   - Only active users can log in

4. **Email Uniqueness**:
   - Email must be unique across all users
   - When updating, the current user's email is excluded from uniqueness check

5. **Authentication**:
   - All endpoints require valid Sanctum token
   - Admin user management endpoints require admin role
   - Profile endpoints are available to all authenticated admin users

---

## Testing Examples

### cURL Examples

**Get Profile:**
```bash
curl -X GET "http://your-domain.com/api/profile" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

**Update Profile:**
```bash
curl -X PUT "http://your-domain.com/api/profile" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'
```

**Update Password:**
```bash
curl -X PUT "http://your-domain.com/api/profile/password" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "current_password": "oldpassword123",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

**Assign Role:**
```bash
curl -X POST "http://your-domain.com/api/users/2/assign-role" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "role_id": 2
  }'
```

---

**Documentation Version**: 1.0  
**Last Updated**: 2024-01-15

