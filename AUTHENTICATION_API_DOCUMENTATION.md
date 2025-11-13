# Authentication API Documentation

Complete documentation for both Admin/User and Customer authentication systems.

## Table of Contents
1. [Overview](#overview)
2. [Admin/User Authentication (Email/Password)](#adminuser-authentication-emailpassword)
   - [Register Admin/User](#1-register-adminuser)
   - [Login Admin/User](#2-login-adminuser)
   - [Logout Admin/User](#3-logout-adminuser)
3. [Customer Authentication (Phone/OTP)](#customer-authentication-phoneotp)
   - [Send OTP](#1-send-otp)
   - [Register Customer](#2-register-customer)
   - [Login Customer](#3-login-customer)
   - [Update Profile](#4-update-profile)
   - [Logout Customer](#5-logout-customer)
4. [Error Responses](#error-responses)
5. [Complete Flow Examples](#complete-flow-examples)

---

## Overview

The system provides two separate authentication mechanisms:

1. **Admin/User Authentication**: Traditional email/password authentication for admin users and system users
2. **Customer Authentication**: Phone number and OTP (One-Time Password) authentication for customers

### Key Features
- **Admin/User**: Email/password authentication with role-based access
- **Customer**: Phone number-based authentication with OTP verification
- Token-based authentication using Laravel Sanctum
- Profile management for customers (name, address, profile picture)

### Base URL
```
http://your-domain.com/api
```

---

## Admin/User Authentication (Email/Password)

This authentication system is used for admin users and system users who need to access the admin panel and manage the ecommerce platform.

### Authentication Flow
1. User registers with email and password → `POST /api/register`
2. User logs in with email and password → `POST /api/login`
3. System returns authentication token
4. User uses token for authenticated requests

---

### 1. Register Admin/User

Register a new admin or user account with email and password.

#### Endpoint
```
POST /api/register
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | User's full name (max 255 characters) |
| `email` | string | Yes | User's email address (must be unique) |
| `password` | string | Yes | Password (min 8 characters) |
| `password_confirmation` | string | Yes | Password confirmation (must match password) |
| `phone` | string | No | User's phone number (max 20 characters) |
| `address` | string | No | User's address |
| `role` | string | No | User role: `admin` or `customer` (default: `customer`) |

#### Request Example
```json
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "address": "123 Admin Street",
  "role": "admin"
}
```

#### Success Response (201 Created)

```json
{
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "phone": "+1234567890",
    "address": "123 Admin Street",
    "status": "active",
    "last_login_at": null,
    "created_at": "2025-11-12T10:30:00.000000Z",
    "updated_at": "2025-11-12T10:30:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "admin"
  }'
```

#### JavaScript Example
```javascript
const response = await fetch('http://localhost:8000/api/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'Admin User',
    email: 'admin@example.com',
    password: 'password123',
    password_confirmation: 'password123',
    role: 'admin'
  })
});

const data = await response.json();
localStorage.setItem('auth_token', data.token);
```

#### Error Responses

##### Validation Error (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email has already been taken."
    ],
    "password": [
      "The password must be at least 8 characters.",
      "The password confirmation does not match."
    ]
  }
}
```

---

### 2. Login Admin/User

Login with email and password.

#### Endpoint
```
POST /api/login
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `email` | string | Yes | User's email address |
| `password` | string | Yes | User's password |

#### Request Example
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

#### Success Response (200 OK)

```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "phone": "+1234567890",
    "address": "123 Admin Street",
    "status": "active",
    "last_login_at": "2025-11-12T11:45:00.000000Z",
    "created_at": "2025-11-12T10:30:00.000000Z",
    "updated_at": "2025-11-12T11:45:00.000000Z"
  },
  "token": "2|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

#### JavaScript Example
```javascript
const response = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'password123'
  })
});

const data = await response.json();
localStorage.setItem('auth_token', data.token);
```

#### Error Responses

##### Invalid Credentials (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

##### Account Not Active (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Your account is not active."
    ]
  }
}
```

---

### 3. Logout Admin/User

Logout the authenticated admin/user and invalidate the current token.

#### Endpoint
```
POST /api/logout
```

#### Authentication
- **Required**: Yes
- **Header**: `Authorization: Bearer {token}`

#### Success Response (200 OK)

```json
{
  "message": "Logged out successfully"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/logout" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890"
```

#### JavaScript Example
```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/logout', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  }
});

localStorage.removeItem('auth_token');
```

---

## Customer Authentication (Phone/OTP)

This authentication system uses phone number and OTP (One-Time Password) for customer registration and login. This eliminates the need for traditional email/password authentication for customers.

### Authentication Flow

#### Registration Flow
1. Customer requests OTP by providing phone number → `POST /api/customer/send-otp`
2. System sends OTP (currently hardcoded as "654321" for testing)
3. Customer submits phone number + OTP → `POST /api/customer/register`
4. System verifies OTP and creates/updates customer record
5. System returns authentication token
6. Customer updates profile with name and address → `PUT /api/customer/profile`

#### Login Flow
1. Customer requests OTP by providing phone number → `POST /api/customer/send-otp`
2. System sends OTP
3. Customer submits phone number + OTP → `POST /api/customer/login`
4. System verifies OTP
5. System returns authentication token

---

### 1. Send OTP

Request an OTP to be sent to the provided phone number. This endpoint is used for both registration and login.

#### Endpoint
```
POST /api/customer/send-otp
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `phone` | string | Yes | Phone number (max 20 characters) |

#### Request Example
```json
{
  "phone": "+1234567890"
}
```

#### Success Response (200 OK)

```json
{
  "message": "OTP sent successfully",
  "otp": "654321"
}
```

**Note:** The `otp` field in the response is included for testing purposes only. In production, this should be removed and the OTP should only be sent via SMS.

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/customer/send-otp" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1234567890"
  }'
```

#### JavaScript Example
```javascript
const response = await fetch('http://localhost:8000/api/customer/send-otp', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    phone: '+1234567890'
  })
});

const data = await response.json();
console.log('OTP:', data.otp); // "654321"
```

#### Error Responses

##### Validation Error (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": [
      "The phone field is required."
    ]
  }
}
```

---

### 2. Register Customer

Register a new customer using phone number and OTP verification.

#### Endpoint
```
POST /api/customer/register
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `phone` | string | Yes | Phone number (must match the one used in send-otp) |
| `otp` | string | Yes | 6-digit OTP code received from send-otp |

#### Request Example
```json
{
  "phone": "+1234567890",
  "otp": "654321"
}
```

#### Success Response (201 Created)

```json
{
  "message": "Registration successful",
  "customer": {
    "id": 1,
    "name": null,
    "email": null,
    "phone": "+1234567890",
    "address": null,
    "role": "customer",
    "profile_picture": null,
    "profile_picture_url": null,
    "created_at": "2025-11-12T10:30:00.000000Z",
    "updated_at": "2025-11-12T10:30:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/customer/register" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1234567890",
    "otp": "654321"
  }'
```

#### JavaScript Example
```javascript
const response = await fetch('http://localhost:8000/api/customer/register', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '654321'
  })
});

const data = await response.json();
localStorage.setItem('auth_token', data.token);
```

#### Error Responses

##### Invalid OTP (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": [
      "Invalid OTP."
    ]
  }
}
```

##### Expired OTP (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": [
      "OTP has expired. Please request a new one."
    ]
  }
}
```

##### OTP Not Requested (400 Bad Request)
```json
{
  "message": "Please request OTP first"
}
```

---

### 3. Login Customer

Login an existing customer using phone number and OTP verification.

#### Endpoint
```
POST /api/customer/login
```

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `phone` | string | Yes | Phone number (must match registered phone) |
| `otp` | string | Yes | 6-digit OTP code received from send-otp |

#### Request Example
```json
{
  "phone": "+1234567890",
  "otp": "654321"
}
```

#### Success Response (200 OK)

```json
{
  "message": "Login successful",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": null,
    "phone": "+1234567890",
    "address": "123 Main St, City, State 12345",
    "role": "customer",
    "profile_picture": "profile_pictures/abc123.jpg",
    "profile_picture_url": "http://localhost:8000/storage/profile_pictures/abc123.jpg",
    "created_at": "2025-11-12T10:30:00.000000Z",
    "updated_at": "2025-11-12T11:45:00.000000Z"
  },
  "token": "2|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/customer/login" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1234567890",
    "otp": "654321"
  }'
```

#### JavaScript Example
```javascript
const response = await fetch('http://localhost:8000/api/customer/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '654321'
  })
});

const data = await response.json();
localStorage.setItem('auth_token', data.token);
```

#### Error Responses

##### Customer Not Found (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": [
      "Customer not found. Please register first."
    ]
  }
}
```

##### Invalid OTP (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": [
      "Invalid OTP."
    ]
  }
}
```

##### Expired OTP (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": [
      "OTP has expired. Please request a new one."
    ]
  }
}
```

---

### 4. Update Profile

Update customer profile information. Requires authentication.

#### Endpoint
```
PUT /api/customer/profile
```

#### Authentication
- **Required**: Yes
- **Header**: `Authorization: Bearer {token}`

#### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Customer's full name (max 255 characters) |
| `address` | string | Yes | Customer's address |
| `profile_picture` | file | No | Profile picture image (jpeg, png, jpg, gif, max 2MB) |

#### Request Example (JSON - without image)
```json
{
  "name": "John Doe",
  "address": "123 Main Street, City, State 12345"
}
```

#### Request Example (Form Data - with image)
```
name: John Doe
address: 123 Main Street, City, State 12345
profile_picture: [binary file data]
```

#### Success Response (200 OK)

```json
{
  "message": "Profile updated successfully",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": null,
    "phone": "+1234567890",
    "address": "123 Main Street, City, State 12345",
    "role": "customer",
    "profile_picture": "profile_pictures/abc123.jpg",
    "profile_picture_url": "http://localhost:8000/storage/profile_pictures/abc123.jpg",
    "created_at": "2025-11-12T10:30:00.000000Z",
    "updated_at": "2025-11-12T12:00:00.000000Z"
  }
}
```

#### cURL Example (without image)
```bash
curl -X PUT "http://localhost:8000/api/customer/profile" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "address": "123 Main Street, City, State 12345"
  }'
```

#### cURL Example (with image)
```bash
curl -X PUT "http://localhost:8000/api/customer/profile" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890" \
  -F "name=John Doe" \
  -F "address=123 Main Street, City, State 12345" \
  -F "profile_picture=@/path/to/image.jpg"
```

#### JavaScript Example (without image)
```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    address: '123 Main Street, City, State 12345'
  })
});

const data = await response.json();
console.log(data);
```

#### JavaScript Example (with image)
```javascript
const token = localStorage.getItem('auth_token');
const formData = new FormData();

formData.append('name', 'John Doe');
formData.append('address', '123 Main Street, City, State 12345');
formData.append('profile_picture', fileInput.files[0]);

const response = await fetch('http://localhost:8000/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
});

const data = await response.json();
console.log(data);
```

#### Error Responses

##### Unauthenticated (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

##### Validation Error (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "address": [
      "The address field is required."
    ],
    "profile_picture": [
      "The profile picture must be an image.",
      "The profile picture must not be greater than 2048 kilobytes."
    ]
  }
}
```

---

### 5. Logout Customer

Logout the authenticated customer and invalidate the current token.

#### Endpoint
```
POST /api/customer/logout
```

#### Authentication
- **Required**: Yes
- **Header**: `Authorization: Bearer {token}`

#### Success Response (200 OK)

```json
{
  "message": "Logged out successfully"
}
```

#### cURL Example
```bash
curl -X POST "http://localhost:8000/api/customer/logout" \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890"
```

#### JavaScript Example
```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/customer/logout', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  }
});

localStorage.removeItem('auth_token');
```

#### Error Responses

##### Unauthenticated (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```

---

## Error Responses

### Common Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (Authentication required) |
| 422 | Validation Error |
| 500 | Internal Server Error |

### Error Response Format

All error responses follow this format:

```json
{
  "message": "Error message",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

---

## Complete Flow Examples

### Example 1: Admin Registration and Login

```javascript
// Step 1: Register Admin
const registerResponse = await fetch('http://localhost:8000/api/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    name: 'Admin User',
    email: 'admin@example.com',
    password: 'password123',
    password_confirmation: 'password123',
    role: 'admin'
  })
});
const registerData = await registerResponse.json();
const adminToken = registerData.token;
localStorage.setItem('admin_token', adminToken);

// Step 2: Login Admin
const loginResponse = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'password123'
  })
});
const loginData = await loginResponse.json();
localStorage.setItem('admin_token', loginData.token);
```

### Example 2: Customer Registration and Profile Setup

```javascript
// Step 1: Request OTP
const otpResponse = await fetch('http://localhost:8000/api/customer/send-otp', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ phone: '+1234567890' })
});
const otpData = await otpResponse.json();
console.log('OTP:', otpData.otp); // "654321"

// Step 2: Register with OTP
const registerResponse = await fetch('http://localhost:8000/api/customer/register', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '654321'
  })
});
const registerData = await registerResponse.json();
const token = registerData.token;
localStorage.setItem('auth_token', token);

// Step 3: Update Profile
const profileResponse = await fetch('http://localhost:8000/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    name: 'John Doe',
    address: '123 Main Street, City, State 12345'
  })
});
const profileData = await profileResponse.json();
console.log('Profile updated:', profileData);
```

### Example 3: Customer Login

```javascript
// Step 1: Request OTP
const otpResponse = await fetch('http://localhost:8000/api/customer/send-otp', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ phone: '+1234567890' })
});
const otpData = await otpResponse.json();

// Step 2: Login with OTP
const loginResponse = await fetch('http://localhost:8000/api/customer/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    phone: '+1234567890',
    otp: '654321'
  })
});
const loginData = await loginResponse.json();
const token = loginData.token;
localStorage.setItem('auth_token', token);
console.log('Logged in:', loginData.customer);
```

### Example 4: Update Customer Profile with Image

```javascript
const token = localStorage.getItem('auth_token');
const formData = new FormData();

formData.append('name', 'John Doe');
formData.append('address', '123 Main Street, City, State 12345');

// Get file from input
const fileInput = document.querySelector('input[type="file"]');
if (fileInput.files[0]) {
  formData.append('profile_picture', fileInput.files[0]);
}

const response = await fetch('http://localhost:8000/api/customer/profile', {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const data = await response.json();
console.log('Profile updated:', data);
console.log('Profile picture URL:', data.customer.profile_picture_url);
```

---

## Important Notes

### Admin/User Authentication
- Uses traditional email/password authentication
- Supports role-based access control (admin/customer)
- Account status must be "active" to login
- Last login timestamp is automatically updated on login

### Customer OTP Configuration
- **Default OTP**: Currently hardcoded as "654321" for testing
- **OTP Expiry**: 10 minutes from generation
- **Production**: In production, integrate with an SMS service provider (Twilio, AWS SNS, etc.) to send real OTPs
- **Security**: Never expose OTP in production responses

### Profile Picture Storage
- Profile pictures are stored in `storage/app/public/profile_pictures/`
- Ensure the storage link is created: `php artisan storage:link`
- Access profile pictures via: `http://your-domain.com/storage/profile_pictures/{filename}`
- The API automatically returns `profile_picture_url` in customer responses

### Token Management
- Tokens are generated using Laravel Sanctum
- Tokens should be stored securely (localStorage, sessionStorage, or secure cookies)
- Include token in `Authorization: Bearer {token}` header for protected endpoints
- Tokens are invalidated on logout
- Admin and Customer tokens are separate and cannot be used interchangeably

### Phone Number Format
- Phone numbers should be stored in a consistent format
- Recommended: Include country code (e.g., "+1234567890")
- Maximum length: 20 characters

### Security Considerations
1. **OTP**: Never expose OTP in production responses
2. **Rate Limiting**: Implement rate limiting on OTP requests to prevent abuse
3. **Token Security**: Use HTTPS in production
4. **Profile Pictures**: Validate file types and sizes on both client and server
5. **Phone Verification**: Consider implementing phone number verification via SMS service
6. **Password Strength**: Enforce strong password policies for admin accounts
7. **Account Status**: Check account status before allowing login

---

## Migration Requirements

Before using this authentication system, run the migration:

```bash
# Install doctrine/dbal (required for column modifications)
composer require doctrine/dbal

# Run migrations
php artisan migrate

# Create storage link for profile pictures
php artisan storage:link
```

---

## API Endpoints Summary

### Admin/User Endpoints
- `POST /api/register` - Register admin/user
- `POST /api/login` - Login admin/user
- `POST /api/logout` - Logout admin/user (protected)

### Customer Endpoints
- `POST /api/customer/send-otp` - Send OTP to phone
- `POST /api/customer/register` - Register customer
- `POST /api/customer/login` - Login customer
- `PUT /api/customer/profile` - Update customer profile (protected)
- `POST /api/customer/logout` - Logout customer (protected)

---

## Support

For issues or questions, please refer to the main project documentation or contact the development team.
