# API Documentation: Submit Contact Form

## Endpoint
`POST /api/contact`

## Description
Submits a contact form message from a user. This is a public endpoint that does not require authentication. The submitted contact form will be saved to the database and an email notification will be sent to the configured site email address.

## Authentication
**Not Required**: This is a public endpoint accessible without authentication.

## Request

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | The name of the person submitting the contact form. Maximum 255 characters. |
| `email` | string | Yes | The email address of the person submitting the contact form. Must be a valid email format. Maximum 255 characters. |
| `phone` | string | No | Phone number of the person submitting the contact form. Maximum 20 characters. |
| `subject` | string | No | Subject line for the contact message. Maximum 255 characters. |
| `message` | string | Yes | The main message content. Minimum 10 characters, maximum 5000 characters. |

### Example Request

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "+1234567890",
  "subject": "Product Inquiry",
  "message": "I would like to know more about your product availability and pricing."
}
```

### Minimal Request Example

```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "message": "I have a question about your services."
}
```

### Validation Rules

1. **name**: 
   - Required
   - Must be a string
   - Maximum 255 characters

2. **email**: 
   - Required
   - Must be a valid email format
   - Maximum 255 characters

3. **phone**: 
   - Optional
   - Must be a string if provided
   - Maximum 20 characters

4. **subject**: 
   - Optional
   - Must be a string if provided
   - Maximum 255 characters

5. **message**: 
   - Required
   - Must be a string
   - Minimum 10 characters
   - Maximum 5000 characters

## Response

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Thank you for contacting us! We will get back to you soon.",
  "data": {
    "id": 42,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "submitted_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful (always `true` for successful responses) |
| `message` | string | Success message confirming the form submission |
| `data.id` | integer | The unique ID of the created contact record |
| `data.name` | string | The name of the person who submitted the form |
| `data.email` | string | The email address of the person who submitted the form |
| `data.submitted_at` | string | ISO 8601 timestamp of when the contact form was submitted |

### Error Responses

#### 422 Unprocessable Entity - Validation Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."],
    "message": ["The message must be at least 10 characters."]
  }
}
```

**Common validation errors**:
- Missing required fields (`name`, `email`, `message`)
- Invalid email format
- Message too short (less than 10 characters)
- Message too long (more than 5000 characters)
- Field values exceeding maximum length limits

**Example: Missing Required Fields**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email field is required."],
    "message": ["The message field is required."]
  }
}
```

**Example: Invalid Email Format**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email must be a valid email address."]
  }
}
```

**Example: Message Too Short**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "message": ["The message must be at least 10 characters."]
  }
}
```

#### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Failed to submit contact form",
  "error": "Error message details"
}
```

**Cause**: An unexpected server error occurred during form submission or email sending.

**Note**: The contact form submission may still succeed even if the email notification fails. Email sending errors are logged but do not cause the request to fail.

## Business Logic

1. **Validation**: Validates all request fields according to the validation rules
2. **Contact Creation**: Creates a new contact record in the database with status set to `'new'`
3. **Email Notification**: 
   - Retrieves the site email address from site settings (uses `email` or falls back to `support_email`)
   - Sends an email notification to the configured recipient email address
   - Email sending failures are logged but do not cause the request to fail
4. **Response**: Returns the created contact information with a success message

## Notes

- This endpoint is publicly accessible and does not require any authentication
- The contact form submission is always saved to the database, even if email sending fails
- Email notifications are sent asynchronously and failures are logged but don't affect the response
- The contact record is created with a default status of `'new'`
- All timestamps are returned in ISO 8601 format
- The phone and subject fields are optional and can be omitted from the request

## Related Endpoints

- `GET /api/contact` - Get contact information (address, email, phone, etc.)
- Admin endpoints (require authentication):
  - `GET /api/admin/contacts` - List all contact submissions (with filtering and pagination)
  - `GET /api/admin/contacts/{id}` - Get a specific contact submission
  - `PUT /api/admin/contacts/{id}/status` - Update contact status (new, read, replied, archived)
  - `DELETE /api/admin/contacts/{id}` - Delete a contact submission
  - `GET /api/admin/contacts/stats` - Get contact statistics

