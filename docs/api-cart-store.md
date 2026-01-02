# API Documentation: Add Product with Variations to Cart

## Endpoint
`POST /api/customer/cart`

## Description
Adds a product with variations to the customer's cart. This endpoint supports products that have multiple variations (e.g., different sizes, colors) by allowing you to specify quantities for each variation.

## Authentication
**Required**: Customer authentication via Bearer token

The request must include a valid Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

The token must belong to a Customer model (not an admin User).

## Request

### Headers
```
Content-Type: application/json
Authorization: Bearer {customer_token}
Accept: application/json
```

### Request Body

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `product` | integer | Yes | The ID of the product to add to cart. Must exist in the products table. |
| `quantity` | integer | Yes | Total quantity of the product to add. Must be at least 1. The sum of all variation quantities must equal this value. |
| `variations` | array | Yes | Array of variation objects. Must contain at least one variation. |
| `variations[].id` | string | Yes | The ID/identifier of the variation. |
| `variations[].quantity` | integer | Yes | Quantity for this specific variation. Must be at least 1. |

### Example Request

```json
{
  "product": 123,
  "quantity": 6,
  "variations": [
    {
      "id": "variation_id_1",
      "quantity": 3
    },
    {
      "id": "variation_id_2",
      "quantity": 3
    }
  ]
}
```

### Validation Rules

1. **Product**: Must exist in the `products` table
2. **Quantity**: Must be an integer >= 1
3. **Variations**: 
   - Must be an array
   - Must contain at least one variation
   - Each variation must have an `id` (string) and `quantity` (integer >= 1)
4. **Quantity Match**: The sum of all variation quantities must exactly equal the main `quantity` field

## Response

### Success Response (201 Created)

```json
{
  "success": true,
  "message": "Items added to cart successfully",
  "data": {
    "cart_id": 45,
    "items": [
      {
        "id": 101,
        "product_id": 123,
        "product_name": "Product Name",
        "product_price": 29.99,
        "product_image_url": "https://example.com/image.jpg",
        "quantity": 3,
        "subtotal": 89.97,
        "variations": {
          "id": "variation_id_1",
          "quantity": 3
        }
      },
      {
        "id": 102,
        "product_id": 123,
        "product_name": "Product Name",
        "product_price": 29.99,
        "product_image_url": "https://example.com/image.jpg",
        "quantity": 3,
        "subtotal": 89.97,
        "variations": {
          "id": "variation_id_2",
          "quantity": 3
        }
      }
    ],
    "total_items": 6,
    "subtotal": 179.94,
    "total": 179.94
  }
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | Indicates if the request was successful |
| `message` | string | Success message |
| `data.cart_id` | integer | The ID of the customer's cart |
| `data.items` | array | Array of cart items that were added/updated |
| `data.items[].id` | integer | Cart item ID |
| `data.items[].product_id` | integer | Product ID |
| `data.items[].product_name` | string | Product name |
| `data.items[].product_price` | number | Unit price of the product |
| `data.items[].product_image_url` | string | URL to the product image |
| `data.items[].quantity` | integer | Quantity of this cart item |
| `data.items[].subtotal` | number | Subtotal for this item (price × quantity) |
| `data.items[].variations` | object | Variation information (id and quantity) |
| `data.total_items` | integer | Total number of items in the cart |
| `data.subtotal` | number | Subtotal of all items in the cart |
| `data.total` | number | Total amount of the cart |

### Error Responses

#### 400 Bad Request - Product Not Available

```json
{
  "success": false,
  "message": "Product is not available"
}
```

**Cause**: The product exists but is not active (`is_active = false`).

#### 400 Bad Request - Insufficient Stock

```json
{
  "success": false,
  "message": "Insufficient stock. Available: 5"
}
```

**Cause**: The requested quantity exceeds available stock.

#### 400 Bad Request - Cannot Add More Items

```json
{
  "success": false,
  "message": "Cannot add more items. Maximum available: 10"
}
```

**Cause**: Adding the requested quantity to existing cart items would exceed available stock.

#### 401 Unauthorized

```json
{
  "message": "Unauthenticated. No token provided."
}
```

or

```json
{
  "message": "Unauthenticated. Invalid token."
}
```

or

```json
{
  "message": "Unauthenticated. Customer authentication required.",
  "error": "Token belongs to App\\Models\\User, but Customer is required."
}
```

**Cause**: 
- No Bearer token provided
- Invalid token
- Token belongs to a User (admin) instead of a Customer

#### 422 Unprocessable Entity - Validation Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "product": ["The product field is required."],
    "quantity": ["The quantity must be an integer."],
    "variations": ["The variations field is required."]
  }
}
```

**Common validation errors**:
- Missing required fields
- Invalid product ID (doesn't exist)
- Quantity not an integer or less than 1
- Variations array empty or invalid structure
- Variation quantities don't match main quantity

Example: Quantity mismatch
```json
{
  "success": false,
  "message": "Total variation quantities must match the main quantity",
  "errors": {
    "quantity": ["Total variation quantities (5) does not match main quantity (6)"]
  }
}
```

#### 500 Internal Server Error

```json
{
  "success": false,
  "message": "Failed to add items to cart",
  "error": "Error message details"
}
```

**Cause**: An unexpected server error occurred.

## Business Logic

1. **Validation**: Validates the request data and ensures variation quantities match the main quantity
2. **Product Check**: Verifies the product exists and is active
3. **Stock Check**: Validates that sufficient stock is available for the requested quantity
4. **Cart Creation**: Creates a cart for the customer if one doesn't exist
5. **Item Handling**: 
   - If a cart item with the same product and variation ID already exists, it updates the quantity
   - If no matching item exists, it creates a new cart item
6. **Stock Re-check**: When updating existing items, verifies the new total quantity doesn't exceed stock
7. **Transaction**: All database operations are wrapped in a transaction to ensure data consistency

## Notes

- If the same product with the same variation ID already exists in the cart, the quantities are added together
- Each variation creates a separate cart item entry
- Stock validation is only performed for local products (products that exist in the database)
- The endpoint uses database transactions to ensure atomicity - if any part fails, all changes are rolled back
- The cart is automatically created if the customer doesn't have one

## Related Endpoints

- `GET /api/customer/cart` - Retrieve cart items
- `POST /api/customer/cart/add` - Add item to cart (simpler, single-item endpoint)
- `PUT /api/customer/cart/items/{cartItemId}` - Update cart item quantity
- `DELETE /api/customer/cart/items/{cartItemId}` - Remove item from cart
- `DELETE /api/customer/cart/clear` - Clear all items from cart

