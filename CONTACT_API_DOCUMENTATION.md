# Contact API Documentation

Complete documentation for the Contact API endpoints, including retrieving contact information and submitting contact forms.

## Table of Contents
1. [Overview](#overview)
2. [Get Contact Information](#get-contact-information)
3. [Submit Contact Form](#submit-contact-form)
4. [Error Responses](#error-responses)

---

## Overview

The Contact API provides endpoints for:
- Retrieving public contact information (address, email, phone) from site settings
- Submitting contact forms that save to the database and send email notifications

### Base URL
```
http://your-domain.com/api
```

### Authentication
- **Required**: No
- **Public Access**: Yes (all endpoints are publicly accessible)

### Email Configuration

The contact form submission sends emails using Laravel's mail system. Configure the following environment variables in your `.env` file:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Supported Mail Drivers:**
- `smtp` - SMTP server
- `mailgun` - Mailgun service
- `ses` - Amazon SES
- `postmark` - Postmark service
- `sendmail` - Sendmail
- `log` - Log emails (for testing)

---

## Get Contact Information

Retrieve public contact information including address, email, and contact number from site settings.

### Endpoint
```
GET /api/contact
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Request Example (cURL)

```bash
curl -X GET "http://localhost:8000/api/contact"
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "address": "123 Main Street, City, State 12345",
    "email": "info@example.com",
    "support_email": "support@example.com",
    "contact_number": "+1 (555) 123-4567",
    "business_name": "My Store"
  }
}
```

**Response when contact information is not set:**
```json
{
  "success": true,
  "data": {
    "address": null,
    "email": null,
    "support_email": null,
    "contact_number": null,
    "business_name": null
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `data` | object | Response data object |
| `data.address` | string\|null | Business address |
| `data.email` | string\|null | Primary contact email |
| `data.support_email` | string\|null | Support email address |
| `data.contact_number` | string\|null | Contact phone number |
| `data.business_name` | string\|null | Business name |

---

## Submit Contact Form

Submit a contact form. The submission will be saved to the database and an email notification will be sent to the email address configured in site settings.

### Endpoint
```
POST /api/contact
```

### Authentication
- **Required**: No
- **Public Access**: Yes

### Content-Type
```
application/json
```

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `name` | string | Yes | max:255 | Contact person's name |
| `email` | string | Yes | email, max:255 | Contact person's email |
| `phone` | string | No | max:20 | Contact person's phone number |
| `subject` | string | No | max:255 | Subject of the contact message |
| `message` | string | Yes | min:10, max:5000 | Contact message content |

### Request Example (cURL)

**Basic Request:**
```bash
curl -X POST "http://localhost:8000/api/contact" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "message": "I would like to inquire about your products."
  }'
```

**Full Request with All Fields:**
```bash
curl -X POST "http://localhost:8000/api/contact" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1 (555) 123-4567",
    "subject": "Product Inquiry",
    "message": "I would like to know more about your product catalog and pricing."
  }'
```

### Response Format

**Success Response (201 Created):**
```json
{
  "success": true,
  "message": "Thank you for contacting us! We will get back to you soon.",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "submitted_at": "2025-11-18T08:00:00.000000Z"
  }
}
```

### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `message` | string | Success message |
| `data` | object | Response data object |
| `data.id` | integer | Contact submission ID |
| `data.name` | string | Contact person's name |
| `data.email` | string | Contact person's email |
| `data.submitted_at` | string | ISO 8601 timestamp of submission |

### Email Notification

When a contact form is submitted:
1. The submission is saved to the `contacts` table in the database
2. An email notification is sent to the email address configured in site settings (`email` or `support_email`)
3. The email includes all contact form details in a formatted HTML template
4. If email sending fails, the submission is still saved (error is logged)

**Email Recipient Priority:**
1. `email` field from site settings (primary)
2. `support_email` field from site settings (fallback)
3. If neither is configured, no email is sent (submission is still saved)

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "email": [
      "The email must be a valid email address."
    ],
    "message": [
      "The message must be at least 10 characters.",
      "The message must not be greater than 5000 characters."
    ]
  }
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to submit contact form",
  "error": "Error message details"
}
```

**Example:**
```json
{
  "success": false,
  "message": "Failed to retrieve contact information",
  "error": "Database connection error"
}
```

---

## Important Notes

### Contact Form Submission

- **Database Storage**: All contact form submissions are saved to the `contacts` table
- **Status Tracking**: Submissions start with status `new` and can be managed in the admin panel
- **Email Delivery**: Email is sent asynchronously (queued) if queue is configured
- **Error Handling**: If email sending fails, the submission is still saved to the database

### Contact Status Values

The contact submissions have the following status values:
- `new` - New submission (default)
- `read` - Submission has been read
- `replied` - Response has been sent
- `archived` - Submission has been archived

### Email Configuration

**Required .env Variables:**
```env
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Your Store Name"
```

**Testing Email:**
For development/testing, you can use the `log` mailer which writes emails to log files:
```env
MAIL_MAILER=log
```

### Message Length

- **Minimum**: 10 characters
- **Maximum**: 5000 characters
- This helps prevent spam and ensures meaningful messages

### Rate Limiting

Consider implementing rate limiting for the contact form endpoint to prevent abuse:
- Limit submissions per IP address
- Limit submissions per email address
- Use Laravel's built-in rate limiting middleware

### Admin Panel Integration

Contact submissions are stored in the database and can be:
- Viewed in the admin panel
- Marked as read
- Marked as replied
- Archived
- Filtered by status

---

## API Endpoints Summary

| Endpoint | Method | Authentication | Description |
|----------|--------|----------------|-------------|
| `/api/contact` | GET | None | Get contact information |
| `/api/contact` | POST | None | Submit contact form |

---

## Database Schema

The `contacts` table structure:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Contact person's name |
| `email` | string | Contact person's email |
| `phone` | string\|null | Contact person's phone |
| `subject` | string\|null | Message subject |
| `message` | text | Message content |
| `status` | enum | Status: new, read, replied, archived |
| `read_at` | timestamp\|null | When the submission was read |
| `created_at` | timestamp | Submission timestamp |
| `updated_at` | timestamp | Last update timestamp |

---

## Support

For issues or questions about the Contact API, please refer to the Site Settings API documentation for managing contact information, or contact the development team.

