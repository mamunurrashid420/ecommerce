# Support Ticket API - Quick Reference

## Base URL
```
https://api.e3shopbd.com
```

## Authentication
All endpoints require Bearer token:
```
Authorization: Bearer {token}
```

---

## Endpoints Summary

### 1. Create Ticket (Customer Only)
```http
POST /api/support-tickets
```
**Body**: `subject`, `description`, `priority?`, `category?`

---

### 2. Get Tickets List (Customer/Admin)
```http
GET /api/support-tickets
```
**Query Params**: `status`, `priority`, `category`, `search`, `customer_id`, `assigned_to`, `date_from`, `date_to`, `sort_by`, `sort_order`, `per_page`

**Returns**: 
- Customer: Their own tickets
- Admin: All tickets

---

### 3. Get Single Ticket (Customer/Admin)
```http
GET /api/support-tickets/{ticket}
```
**Access**: Customers can only view their own tickets

---

### 4. Get Navbar Count (Customer/Admin)
```http
GET /api/support-tickets/navbar/count
```
**Returns**: Unread and unresolved ticket counts for navbar badge

---

### 5. Get Latest Tickets for Navbar (Customer/Admin)
```http
GET /api/support-tickets/navbar/latest?limit=5
```
**Returns**: Latest tickets with minimal data for dropdown

---

### 6. Get Ticket Messages (Customer/Admin)
```http
GET /api/support-tickets/{ticket}/messages
```
**Side Effect**: Marks ticket as read by viewer

---

### 7. Send Message (Customer/Admin)
```http
POST /api/support-tickets/{ticket}/messages
```
**Body**: `message`, `attachments?`

**Behavior**:
- Customer message: Marks ticket unread for admin
- Admin message: Changes status to "in_progress" if closed/resolved

---

### 8. Mark Message as Read (Customer/Admin)
```http
PUT /api/support-messages/{message}/read
```

---

### 9. Cancel Order (Customer/Admin)
```http
POST /api/orders/{order}/cancel
```
**Body**: `reason?`

---

## Enumerations

### Status
- `open` - New ticket
- `in_progress` - Being worked on
- `resolved` - Issue resolved
- `closed` - Ticket closed

### Priority
- `low`
- `medium` (default)
- `high`
- `urgent`

### Category
- `technical`
- `billing`
- `order`
- `product`
- `account`
- `other` (default)

---

## Quick Examples

### Create Ticket
```bash
curl -X POST https://api.e3shopbd.com/api/support-tickets \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Order issue",
    "description": "Wrong product received",
    "priority": "high",
    "category": "order"
  }'
```

### Get My Tickets
```bash
curl -X GET "https://api.e3shopbd.com/api/support-tickets?status=open" \
  -H "Authorization: Bearer {token}"
```

### Send Message
```bash
curl -X POST https://api.e3shopbd.com/api/support-tickets/1/messages \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"message": "Thank you for your help"}'
```

### Get Navbar Count
```bash
curl -X GET https://api.e3shopbd.com/api/support-tickets/navbar/count \
  -H "Authorization: Bearer {token}"
```

---

## Response Format

### Success
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error
```json
{
  "success": false,
  "message": "Error description"
}
```

### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["Error message"]
  }
}
```

---

## HTTP Status Codes
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

