# Order Creation API Update

## Summary of Changes

The order creation endpoint has been updated to support creating **separate orders for each item** with **70% payable price** calculation, using the `items` array instead of `cart_item_ids`.

## Endpoint

```
POST /api/customer/orders/create
```

## Key Changes

### 1. **Use Items Array Instead of Cart**
- Previously: Used `cart_item_ids` to fetch items from cart
- Now: Uses `items` array directly from request
- Benefit: More flexible, works with any item data

### 2. **Separate Order for Each Item**
- Previously: Created one order with multiple items
- Now: Creates **one order per item**
- Benefit: Better tracking and management per product

### 3. **70% Payable Price Calculation**
- Original price is extracted from item variations
- Payable price = Original price × 0.70 (70%)
- Order subtotal = Payable price × Quantity
- Total = Subtotal + Shipping + Tax (if not inclusive)

### 4. **Payment Method ID Support**
- Added `payment_method_id` field to orders table
- Links order to payment methods from the payment_methods table
- Maintains backward compatibility with `payment_method` string field

## Request Format

### Required Fields
```json
{
  "items": [
    {
      "id": 45,
      "quantity": 4,
      "product_id": null,
      "variations": {
        "id": "5970790707226",
        "sku_id": "5970790707226",
        "spec_id": "75be072e5d20708dcb74a45dc8116b6e",
        "price": 558,
        "original_price": 558,
        "stock": 9983,
        "props_names": "Color:735y black"
      }
    },
    {
      "id": 46,
      "quantity": 4,
      "product_id": null,
      "variations": {
        "id": "5970790707228",
        "sku_id": "5970790707228",
        "price": 558,
        "props_names": "Color:735y skin color"
      }
    }
  ],
  "shipping_address": {
    "full_name": "Mehedi Hasan",
    "phone": "01672164422",
    "emergency_phone": "",
    "address_line1": "Oioio oaisdfm aosdifaosdi",
    "address_line2": "",
    "city": "Puthia",
    "district": "Rajshahi",
    "state": "",
    "postal_code": "",
    "country": "Bangladesh"
  },
  "shipping_method": "ship",
  "payment_method": "manual",
  "payment_method_id": 1,
  "transaction_number": "56FSD087S",
  "payment_receipt": "[file upload]",
  "notes": "asdfkaopsdfka spdof apsdofk aspdofk a"
}
```

### Optional Fields (Backward Compatibility)
```json
{
  "cart_item_ids": [45, 46]
}
```
- If provided, these cart items will be deleted after order creation

## Response Format

```json
{
  "success": true,
  "message": "2 order(s) created successfully",
  "data": {
    "orders": [
      {
        "id": 1,
        "order_number": "ORD-ABC123-1234567890",
        "subtotal": 1561.20,
        "shipping_cost": 100.00,
        "shipping_method": "ship",
        "tax_amount": 0.00,
        "total_amount": 1661.20,
        "status": "pending",
        "payment_method": "manual",
        "payment_method_id": 1,
        "payment_status": "pending",
        "transaction_number": "56FSD087S",
        "payment_receipt_url": "http://domain/storage/payment_receipts/receipt_xxx.jpg",
        "shipping_address": {
          "full_name": "Mehedi Hasan",
          "phone": "01672164422",
          "address_line1": "...",
          "city": "Puthia",
          "district": "Rajshahi",
          "country": "Bangladesh"
        },
        "notes": "...",
        "items": [
          {
            "product_id": null,
            "product_code": "5970790707226",
            "product_name": "Product - Color:735y black (SKU: 5970790707226)",
            "product_image_url": null,
            "product_sku": "5970790707226",
            "quantity": 4,
            "price": 390.60,
            "total": 1562.40,
            "variations": {
              "id": "5970790707226",
              "sku_id": "5970790707226",
              "price": 558,
              "original_price": 558,
              "props_names": "Color:735y black"
            }
          }
        ],
        "status_history": [...],
        "created_at": "2026-01-14T..."
      },
      {
        "id": 2,
        "order_number": "ORD-XYZ789-1234567891",
        "subtotal": 1561.20,
        ...
      }
    ],
    "total_orders": 2
  }
}
```

## Price Calculation Example

### Item 1: Color - Black
- Original Price: 558 BDT
- Quantity: 4
- **Payable Price: 558 × 0.70 = 390.60 BDT** (70%)
- Subtotal: 390.60 × 4 = **1,562.40 BDT**
- Shipping: 100 BDT (per order)
- Tax: 0 BDT (if tax-inclusive)
- **Total Order Amount: 1,662.40 BDT**

### Item 2: Color - Skin Color
- Original Price: 558 BDT
- Quantity: 4
- **Payable Price: 558 × 0.70 = 390.60 BDT** (70%)
- Subtotal: 390.60 × 4 = **1,562.40 BDT**
- Shipping: 100 BDT (per order)
- Tax: 0 BDT (if tax-inclusive)
- **Total Order Amount: 1,662.40 BDT**

### Grand Total for Customer
- Order 1: 1,662.40 BDT
- Order 2: 1,662.40 BDT
- **Total: 3,324.80 BDT**

## Validation Rules

### Items Array
```php
'items' => 'required|array|min:1',
'items.*.id' => 'required|integer',
'items.*.quantity' => 'required|integer|min:1',
'items.*.product_id' => 'nullable|integer',
'items.*.variations' => 'nullable',
```

### Other Fields
```php
'shipping_address' => 'required',
'shipping_method' => 'nullable|in:air,ship',
'payment_method' => 'nullable|string|max:255',
'payment_method_id' => 'nullable|exists:payment_methods,id',
'notes' => 'nullable|string',
'transaction_number' => 'nullable|string|max:255',
'payment_receipt' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
'cart_item_ids' => 'nullable|array', // Optional, for cart cleanup
```

## Database Changes

### New Column: `payment_method_id`
Added to `orders` table:
```sql
ALTER TABLE orders ADD COLUMN payment_method_id BIGINT UNSIGNED NULL;
ALTER TABLE orders ADD CONSTRAINT orders_payment_method_id_foreign 
  FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE SET NULL;
```

### Order Model Updated
```php
protected $fillable = [
    // ... existing fields
    'payment_method_id', // NEW
];

public function paymentMethod()
{
    return $this->belongsTo(PaymentMethod::class);
}
```

## Features

### 1. **Multiple Orders Created**
- Each item in the array creates a separate order
- Each order has its own order number
- Each order can be tracked independently

### 2. **Shared Data Across Orders**
- Same shipping address
- Same payment method and transaction number
- Same payment receipt (if uploaded)
- Same customer notes

### 3. **Individual Calculations**
- Each order calculates its own:
  - Subtotal (based on 70% price)
  - Shipping cost
  - Tax amount
  - Total amount

### 4. **Cart Cleanup**
- If `cart_item_ids` is provided, those items are removed from cart
- Maintains backward compatibility

### 5. **Status Tracking**
- Each order gets its own status history
- Initial status: "pending"
- Payment status: "pending"

## Variations Data Structure

The system extracts price from variations in this order:
1. `variations.price` (primary)
2. `variations.original_price` (fallback)

Example variations object:
```json
{
  "id": "5970790707226",
  "quantity": 8,
  "sku_id": "5970790707226",
  "spec_id": "75be072e5d20708dcb74a45dc8116b6e",
  "price": 558,
  "original_price": 558,
  "stock": 9983,
  "props_names": "Color:735y black"
}
```

## Product Name Generation

The system generates product names dynamically:
```
Base: "Product"
+ Props: " - Color:735y black"  (from variations.props_names)
+ SKU: " (SKU: 5970790707226)"  (from variations.sku_id)

Result: "Product - Color:735y black (SKU: 5970790707226)"
```

## Error Handling

### Validation Errors (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "items": ["The items field is required."],
    "shipping_address": ["The shipping address field is required."]
  }
}
```

### Empty Items Array (400)
```json
{
  "success": false,
  "message": "Items array is empty"
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to create order",
  "error": "Error details..."
}
```

## Testing Examples

### Using cURL with Form Data
```bash
curl -X POST http://localhost:8000/api/customer/orders/create \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "items[0][id]=45" \
  -F "items[0][quantity]=4" \
  -F "items[0][variations][price]=558" \
  -F "items[0][variations][sku_id]=5970790707226" \
  -F "items[0][variations][props_names]=Color:735y black" \
  -F "items[1][id]=46" \
  -F "items[1][quantity]=4" \
  -F "items[1][variations][price]=558" \
  -F "items[1][variations][sku_id]=5970790707228" \
  -F "items[1][variations][props_names]=Color:735y skin color" \
  -F "shipping_address={\"full_name\":\"Mehedi Hasan\",\"phone\":\"01672164422\",\"city\":\"Puthia\",\"district\":\"Rajshahi\",\"country\":\"Bangladesh\"}" \
  -F "shipping_method=ship" \
  -F "payment_method=manual" \
  -F "payment_method_id=1" \
  -F "transaction_number=56FSD087S" \
  -F "payment_receipt=@/path/to/receipt.jpg" \
  -F "notes=Order notes here"
```

### Using JavaScript Fetch
```javascript
const formData = new FormData();

// Add items
const items = [
  {
    id: 45,
    quantity: 4,
    variations: {
      price: 558,
      sku_id: "5970790707226",
      props_names: "Color:735y black"
    }
  },
  {
    id: 46,
    quantity: 4,
    variations: {
      price: 558,
      sku_id: "5970790707228",
      props_names: "Color:735y skin color"
    }
  }
];

items.forEach((item, index) => {
  formData.append(`items[${index}][id]`, item.id);
  formData.append(`items[${index}][quantity]`, item.quantity);
  formData.append(`items[${index}][variations]`, JSON.stringify(item.variations));
});

// Add shipping address
formData.append('shipping_address', JSON.stringify({
  full_name: "Mehedi Hasan",
  phone: "01672164422",
  city: "Puthia",
  district: "Rajshahi",
  country: "Bangladesh"
}));

// Add other fields
formData.append('shipping_method', 'ship');
formData.append('payment_method', 'manual');
formData.append('payment_method_id', '1');
formData.append('transaction_number', '56FSD087S');
formData.append('payment_receipt', fileInput.files[0]);
formData.append('notes', 'Order notes');

// Send request
const response = await fetch('/api/customer/orders/create', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const result = await response.json();
console.log(result);
```

## Migration Files

1. **Payment Methods Table**
   - `2026_01_14_100000_create_payment_methods_table.php`

2. **Payment Method ID in Orders**
   - `2026_01_14_110000_add_payment_method_id_to_orders_table.php`

## Benefits of This Approach

1. **Separate Tracking**: Each item has its own order for better management
2. **Clear Pricing**: 70% calculation is transparent and consistent
3. **Flexible**: Works with any item data, not limited to cart
4. **Scalable**: Easy to add more items without changing structure
5. **Payment Integration**: Direct link to payment methods table
6. **Backward Compatible**: Old cart cleanup still works if needed

## Notes

1. **Shipping Cost**: Applied to each order separately
2. **Tax Calculation**: Respects site settings (inclusive/exclusive)
3. **Transaction Number**: Shared across all orders in a single request
4. **Payment Receipt**: Same receipt used for all orders
5. **Order Numbers**: Each order gets unique number with timestamp
6. **Status History**: Each order maintains its own history

## Files Modified

1. `/app/Http/Controllers/Api/OrderController.php`
   - Updated `createFromCart()` method
   - Changed to process items array
   - Added 70% price calculation
   - Create separate orders per item

2. `/app/Models/Order.php`
   - Added `payment_method_id` to fillable
   - Added `paymentMethod()` relationship

3. `/database/migrations/2026_01_14_110000_add_payment_method_id_to_orders_table.php`
   - New migration for payment_method_id column

