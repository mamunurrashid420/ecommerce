# Policies API Documentation

Complete documentation for accessing public policy documents (Terms of Service, Privacy Policy, Return Policy, and Shipping Policy).

## Table of Contents
1. [Overview](#overview)
2. [Terms of Service API](#terms-of-service-api)
3. [Privacy Policy API](#privacy-policy-api)
4. [Return Policy API](#return-policy-api)
5. [Shipping Policy API](#shipping-policy-api)
6. [Error Responses](#error-responses)

---

## Overview

The Policies API provides public access to legal and policy documents stored in Site Settings. These endpoints are accessible without authentication and are designed for frontend/client applications to display policy information to users.

### Base URL
```
http://your-domain.com/api
```

### Authentication
- **Required**: No
- **Public Access**: Yes (all endpoints are publicly accessible)

---

## Terms of Service API

Retrieve the Terms of Service document.

### Endpoint
```
GET /api/policies/terms-of-service
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Request Example (cURL)

```bash
curl -X GET "http://localhost:8000/api/policies/terms-of-service"
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "terms_of_service": "Your terms of service content here...",
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

**Response when Terms of Service is not set:**
```json
{
  "success": true,
  "data": {
    "terms_of_service": null,
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `data` | object | Response data object |
| `data.terms_of_service` | string\|null | The terms of service content (HTML/text) |
| `data.last_updated` | string | ISO 8601 timestamp of when the policy was last updated |

---

## Privacy Policy API

Retrieve the Privacy Policy document.

### Endpoint
```
GET /api/policies/privacy-policy
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Request Example (cURL)

```bash
curl -X GET "http://localhost:8000/api/policies/privacy-policy"
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "privacy_policy": "Your privacy policy content here...",
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

**Response when Privacy Policy is not set:**
```json
{
  "success": true,
  "data": {
    "privacy_policy": null,
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `data` | object | Response data object |
| `data.privacy_policy` | string\|null | The privacy policy content (HTML/text) |
| `data.last_updated` | string | ISO 8601 timestamp of when the policy was last updated |

---

## Return Policy API

Retrieve the Return Policy document.

### Endpoint
```
GET /api/policies/return-policy
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Request Example (cURL)

```bash
curl -X GET "http://localhost:8000/api/policies/return-policy"
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "return_policy": "Your return policy content here...",
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

**Response when Return Policy is not set:**
```json
{
  "success": true,
  "data": {
    "return_policy": null,
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `data` | object | Response data object |
| `data.return_policy` | string\|null | The return policy content (HTML/text) |
| `data.last_updated` | string | ISO 8601 timestamp of when the policy was last updated |

---

## Shipping Policy API

Retrieve the Shipping Policy document.

### Endpoint
```
GET /api/policies/shipping-policy
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Request Example (cURL)

```bash
curl -X GET "http://localhost:8000/api/policies/shipping-policy"
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "shipping_policy": "Your shipping policy content here...",
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

**Response when Shipping Policy is not set:**
```json
{
  "success": true,
  "data": {
    "shipping_policy": null,
    "last_updated": "2025-11-18T07:00:00.000000Z"
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `data` | object | Response data object |
| `data.shipping_policy` | string\|null | The shipping policy content (HTML/text) |
| `data.last_updated` | string | ISO 8601 timestamp of when the policy was last updated |

---

## Error Responses

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to retrieve [policy name]",
  "error": "Error message details"
}
```

**Example:**
```json
{
  "success": false,
  "message": "Failed to retrieve terms of service",
  "error": "Database connection error"
}
```

---

## Important Notes

### Content Format
- Policy content can be stored as plain text or HTML
- The API returns the content as-is from the database
- If a policy is not set, the field will be `null`

### Last Updated Timestamp
- The `last_updated` field shows when the Site Settings record was last modified
- This timestamp applies to all site settings, not just the specific policy
- Format: ISO 8601 (e.g., `2025-11-18T07:00:00.000000Z`)

### Caching Recommendations
- These endpoints are public and can be cached on the client side
- Consider implementing client-side caching to reduce API calls
- The `last_updated` timestamp can be used to check if content has changed

### Content Management
- Policies are managed through the Site Settings API (Admin only)
- To update policies, use: `POST /api/site-settings` with admin authentication
- See Site Settings API documentation for updating policies

### Use Cases
- Display policy links in website footer
- Show policy content in modal dialogs or dedicated pages
- Include policy acceptance checkboxes during registration/checkout
- Display policy updates notifications based on `last_updated` timestamp

---

## API Endpoints Summary

| Endpoint | Method | Authentication | Description |
|----------|--------|----------------|-------------|
| `/api/policies/terms-of-service` | GET | None | Get Terms of Service |
| `/api/policies/privacy-policy` | GET | None | Get Privacy Policy |
| `/api/policies/return-policy` | GET | None | Get Return Policy |
| `/api/policies/shipping-policy` | GET | None | Get Shipping Policy |

---

## Support

For issues or questions about policy APIs, please refer to the Site Settings API documentation for managing policy content, or contact the development team.

