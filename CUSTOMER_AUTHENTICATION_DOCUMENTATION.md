# Customer Authentication API Documentation

This document describes the customer authentication system using email and password.

## Base URL
All endpoints are prefixed with `/api/customer`

## Authentication Flow

The customer authentication system uses email and password for authentication. After successful login or registration, a Sanctum token is returned that must be included in subsequent authenticated requests.

## Endpoints

### 1. Register Customer

Register a new customer account with email and password.

**Endpoint:** `POST /api/customer/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "address": "123 Main St, City, Country"
}
```

**Required Fields:**
- `name` (string, required): Customer's full name
- `email` (string, required, unique): Customer's email address
- `password` (string, required, min: 8): Customer's password
- `password_confirmation` (string, required): Password confirmation (must match password)

**Optional Fields:**
- `phone` (string, nullable): Customer's phone number
- `address` (string, nullable): Customer's address

**Success Response (201):**
```json
{
  "message": "Registration successful",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, Country",
    "role": "customer",
    "profile_picture_url": null,
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T05:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

---

### 2. Login Customer

Authenticate an existing customer with email and password.

**Endpoint:** `POST /api/customer/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Required Fields:**
- `email` (string, required): Customer's email address
- `password` (string, required): Customer's password

**Success Response (200):**
```json
{
  "message": "Login successful",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, Country",
    "role": "customer",
    "profile_picture_url": null,
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T05:00:00.000000Z"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

**Error Response (422) - Account Banned:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Your account has been banned."]
  }
}
```

**Error Response (422) - Account Suspended:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Your account has been suspended."]
  }
}
```

---

### 3. Forgot Password

Request a password reset OTP to be sent to the customer's email.

**Endpoint:** `POST /api/customer/forgot-password`

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Required Fields:**
- `email` (string, required): Customer's email address

**Success Response (200):**
```json
{
  "message": "If the email exists, a password reset OTP has been sent.",
  "otp": "123456"
}
```

**Note:** The OTP is returned in the response for development/testing purposes. In production, this should be removed and the OTP should only be sent via email.

**OTP Details:**
- OTP: `123456` (dummy OTP for development)
- OTP expires in: 10 minutes

---

### 4. Reset Password

Reset customer password using the OTP received via email.

**Endpoint:** `POST /api/customer/reset-password`

**Request Body:**
```json
{
  "email": "john@example.com",
  "otp": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Required Fields:**
- `email` (string, required): Customer's email address
- `otp` (string, required, size: 6): OTP received via email
- `password` (string, required, min: 8): New password
- `password_confirmation` (string, required): Password confirmation (must match password)

**Success Response (200):**
```json
{
  "message": "Password reset successful. You can now login with your new password."
}
```

**Error Response (422) - Invalid OTP:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": ["Invalid OTP."]
  }
}
```

**Error Response (422) - Expired OTP:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "otp": ["OTP has expired. Please request a new one."]
  }
}
```

---

### 5. Change Password (Authenticated)

Change password for authenticated customer (requires authentication token).

**Endpoint:** `POST /api/customer/change-password`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Required Fields:**
- `current_password` (string, required): Current password
- `password` (string, required, min: 8): New password
- `password_confirmation` (string, required): Password confirmation (must match password)

**Success Response (200):**
```json
{
  "message": "Password changed successfully"
}
```

**Error Response (401) - Unauthenticated:**
```json
{
  "message": "Unauthenticated. Customer authentication required."
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

---

### 6. Get Customer Profile (Authenticated)

Get the authenticated customer's profile information.

**Endpoint:** `GET /api/customer/profile`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main St, City, Country",
    "role": "customer",
    "profile_picture_url": "http://example.com/storage/profile_pictures/abc123.jpg",
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T05:00:00.000000Z"
  }
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated. Customer authentication required."
}
```

---

### 7. Update Customer Profile (Authenticated)

Update the authenticated customer's profile information.

**Endpoint:** `PUT /api/customer/profile`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body (JSON):**
```json
{
  "name": "Jane Doe",
  "email": "jane@example.com",
  "phone": "+0987654321",
  "address": "456 Oak Ave, City, Country"
}
```

**Or Form Data (multipart/form-data):**
```
name: Jane Doe
email: jane@example.com
phone: +0987654321
address: 456 Oak Ave, City, Country
profile_picture: [file]
```

**Optional Fields:**
- `name` (string, nullable): Customer's full name
- `email` (string, nullable, unique): Customer's email address
- `phone` (string, nullable): Customer's phone number
- `address` (string, nullable): Customer's address
- `profile_picture` (file, nullable): Profile picture image (jpeg, png, jpg, gif, max: 2MB)

**Success Response (200):**
```json
{
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "+0987654321",
    "address": "456 Oak Ave, City, Country",
    "role": "customer",
    "profile_picture_url": "http://example.com/storage/profile_pictures/xyz789.jpg",
    "created_at": "2025-11-20T05:00:00.000000Z",
    "updated_at": "2025-11-20T05:30:00.000000Z"
  }
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated. Customer authentication required."
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### 8. Logout Customer (Authenticated)

Logout the authenticated customer (revoke current token).

**Endpoint:** `POST /api/customer/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

## Authentication Token Usage

After successful login or registration, you will receive a token. Include this token in the `Authorization` header for all authenticated requests:

```
Authorization: Bearer {token}
```

**Example using cURL:**
```bash
curl -X GET http://your-domain.com/api/customer/profile \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890"
```

**Example using JavaScript (fetch):**
```javascript
fetch('http://your-domain.com/api/customer/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})
```

---

## Password Requirements

- Minimum length: 8 characters
- Must be confirmed (password_confirmation must match password)
- Passwords are hashed using bcrypt

---

## Account Status

Customers can have the following account statuses:
- **Active**: Normal account, can login and make purchases
- **Banned**: Account is banned, cannot login
- **Suspended**: Account is suspended, cannot login

Banned and suspended customers will receive appropriate error messages when attempting to login.

---

## Error Handling

All endpoints return appropriate HTTP status codes:
- `200` - Success
- `201` - Created (Registration)
- `401` - Unauthenticated
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

1. **OTP for Password Reset**: Currently uses a dummy OTP (`123456`) for development. In production, implement proper email sending functionality.

2. **Token Security**: Tokens should be stored securely (e.g., in httpOnly cookies or secure storage) and never exposed in client-side code or URLs.

3. **HTTPS**: Always use HTTPS in production to protect credentials and tokens in transit.

4. **Rate Limiting**: Consider implementing rate limiting on authentication endpoints to prevent brute force attacks.

5. **Password Strength**: Consider implementing additional password strength requirements (uppercase, lowercase, numbers, special characters).

---

## Example Workflows

### Registration Flow
1. Customer submits registration form with email and password
2. System creates customer account
3. System returns customer data and authentication token
4. Customer is automatically logged in

### Login Flow
1. Customer submits login form with email and password
2. System validates credentials
3. System checks account status (not banned/suspended)
4. System returns customer data and authentication token
5. Customer is logged in

### Password Reset Flow
1. Customer requests password reset with email
2. System generates OTP and sends to email (currently returns in response)
3. Customer receives OTP and submits reset form with OTP and new password
4. System validates OTP and updates password
5. Customer can now login with new password

### Change Password Flow
1. Authenticated customer submits change password form with current and new password
2. System validates current password
3. System updates password
4. Customer can continue using the application with new password

---

## Testing

### Test Credentials
For testing purposes, you can use the following:
- **Dummy OTP**: `123456` (for password reset)
- **OTP Expiry**: 10 minutes

### Example cURL Commands

**Register:**
```bash
curl -X POST http://your-domain.com/api/customer/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Login:**
```bash
curl -X POST http://your-domain.com/api/customer/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**Get Profile:**
```bash
curl -X GET http://your-domain.com/api/customer/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## Support

For issues or questions regarding the customer authentication API, please contact the development team.

