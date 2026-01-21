# Support Ticket API - Complete Documentation Package

## 📚 Documentation Files

This package contains comprehensive documentation for the Support Ticket API system:

### 1. **SUPPORT_TICKET_API_DOCUMENTATION.md** (Main Documentation)
   - Complete API reference with detailed endpoint descriptions
   - Request/response examples for all endpoints
   - Authentication and authorization details
   - Data models and enumerations
   - Error handling and status codes
   - Usage examples with cURL commands
   - **975 lines** of comprehensive documentation

### 2. **SUPPORT_TICKET_API_QUICK_REFERENCE.md** (Quick Reference)
   - Condensed one-page reference guide
   - Quick endpoint lookup
   - Essential parameters and examples
   - Perfect for developers who need quick answers

### 3. **Support_Ticket_API.postman_collection.json** (Postman Collection)
   - Ready-to-import Postman collection
   - Pre-configured requests for all endpoints
   - Environment variables for easy testing
   - Separate folders for Customer, Admin, and Shared endpoints

---

## 🚀 Quick Start

### Step 1: Import Postman Collection

1. Open Postman
2. Click **Import** button
3. Select `Support_Ticket_API.postman_collection.json`
4. Collection will be imported with all endpoints

### Step 2: Configure Environment Variables

Set these variables in Postman:

| Variable | Description | Example |
|----------|-------------|---------|
| `base_url` | API base URL | `https://api.e3shopbd.com` |
| `customer_token` | Customer auth token | `1\|abc123...` |
| `admin_token` | Admin auth token | `2\|xyz789...` |
| `ticket_id` | Ticket ID for testing | `1` |
| `message_id` | Message ID for testing | `1` |

### Step 3: Test Endpoints

1. **Customer Flow**:
   - Create a ticket → Get my tickets → View ticket → Send message
   
2. **Admin Flow**:
   - View all tickets → View ticket details → Send reply → Check navbar count

---

## 📋 API Endpoints Overview

### Customer Endpoints (7)
- ✅ `POST /api/support-tickets` - Create ticket
- ✅ `GET /api/support-tickets` - Get my tickets
- ✅ `GET /api/support-tickets/{id}` - View ticket details
- ✅ `GET /api/support-tickets/{id}/messages` - Get messages
- ✅ `POST /api/support-tickets/{id}/messages` - Send message
- ✅ `GET /api/support-tickets/navbar/count` - Navbar count
- ✅ `GET /api/support-tickets/navbar/latest` - Navbar latest

### Admin Endpoints (6)
- ✅ `GET /api/support-tickets` - Get all tickets (with filters)
- ✅ `GET /api/support-tickets/{id}` - View any ticket
- ✅ `GET /api/support-tickets/{id}/messages` - Get messages
- ✅ `POST /api/support-tickets/{id}/messages` - Send reply
- ✅ `GET /api/support-tickets/navbar/count` - Navbar count
- ✅ `GET /api/support-tickets/navbar/latest` - Navbar latest

### Shared Endpoints (2)
- ✅ `PUT /api/support-messages/{id}/read` - Mark message as read
- ✅ `POST /api/orders/{id}/cancel` - Cancel order

**Total: 9 unique endpoints** (some shared between customer/admin)

---

## 🔐 Authentication

All endpoints require Bearer token authentication:

```bash
Authorization: Bearer {token}
```

### Get Customer Token
```bash
POST /api/customer/login
{
  "email": "customer@example.com",
  "password": "password"
}
```

### Get Admin Token
```bash
POST /api/login
{
  "email": "admin@example.com",
  "password": "password"
}
```

---

## 📊 Data Flow Diagrams

Two interactive Mermaid diagrams are included in the documentation:

1. **Support Ticket API Flow** - Shows the relationship between endpoints and database
2. **Support Ticket Lifecycle** - Shows ticket status transitions

---

## 🎯 Common Use Cases

### Use Case 1: Customer Creates and Tracks Ticket

```bash
# 1. Create ticket
POST /api/support-tickets
{
  "subject": "Order issue",
  "description": "Wrong product received",
  "priority": "high",
  "category": "order"
}

# 2. Check navbar for updates
GET /api/support-tickets/navbar/count

# 3. View ticket details
GET /api/support-tickets/1

# 4. Send follow-up message
POST /api/support-tickets/1/messages
{
  "message": "Any update on this?"
}
```

### Use Case 2: Admin Manages Tickets

```bash
# 1. View all urgent tickets
GET /api/support-tickets?priority=urgent&status=open

# 2. View specific ticket
GET /api/support-tickets/1

# 3. View all messages
GET /api/support-tickets/1/messages

# 4. Send reply
POST /api/support-tickets/1/messages
{
  "message": "We're working on this issue"
}

# 5. Check navbar count
GET /api/support-tickets/navbar/count
```

---

## 📖 Documentation Structure

```
SUPPORT_TICKET_API_DOCUMENTATION.md (975 lines)
├── Overview
├── Authentication
├── Table of Contents
├── Endpoint Details (9 endpoints)
│   ├── Create Support Ticket
│   ├── Get Support Tickets List
│   ├── Get Single Ticket Details
│   ├── Get Navbar Ticket Count
│   ├── Get Latest Tickets for Navbar
│   ├── Get Ticket Messages
│   ├── Send Message to Ticket
│   ├── Mark Message as Read
│   └── Cancel Order
├── Data Models
├── Enumerations
├── Error Responses
├── HTTP Status Codes
├── Usage Examples
└── Notes
```

---

## 🔍 Key Features

✅ **Dual Authentication** - Supports both customer and admin tokens  
✅ **Access Control** - Customers can only access their own tickets  
✅ **Auto-Read Marking** - Tickets marked as read when viewed  
✅ **Status Management** - Automatic status transitions based on actions  
✅ **Navbar Integration** - Real-time count and latest tickets for UI  
✅ **Pagination** - All list endpoints support pagination  
✅ **Filtering** - Advanced filtering by status, priority, category, etc.  
✅ **Search** - Full-text search in subject and description  
✅ **Sorting** - Customizable sorting options  

---

## 📝 Notes

- All timestamps are in ISO 8601 format (UTC)
- Pagination default: 15 items per page
- Ticket numbers are auto-generated (format: `TKT-{UNIQUE_ID}`)
- Messages are ordered chronologically (oldest first)
- Navbar endpoints return minimal data for performance

---

## 🛠️ Testing Checklist

- [ ] Customer can create ticket
- [ ] Customer can view only their tickets
- [ ] Customer can send messages
- [ ] Admin can view all tickets
- [ ] Admin can reply to tickets
- [ ] Navbar count updates correctly
- [ ] Navbar latest shows recent tickets
- [ ] Message read status updates
- [ ] Ticket status transitions work
- [ ] Access control prevents unauthorized access

---

## 📞 Support

For questions or issues with the API:
- Review the main documentation: `SUPPORT_TICKET_API_DOCUMENTATION.md`
- Check the quick reference: `SUPPORT_TICKET_API_QUICK_REFERENCE.md`
- Import Postman collection for testing: `Support_Ticket_API.postman_collection.json`

**API Base URL**: `https://api.e3shopbd.com`  
**Documentation Version**: 1.0  
**Last Updated**: 2026-01-22

