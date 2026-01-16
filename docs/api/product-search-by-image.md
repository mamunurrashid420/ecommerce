# Product Search by Image API

## Endpoint
```
POST /api/product-search-by-image
```

## Description
Search for products by uploading an image file. The API will find similar products based on the uploaded image.

## Authentication
Not required (Public endpoint)

## Request Method
`POST` (multipart/form-data)

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `image` | File | Yes | Image file to search for (JPEG, PNG, JPG, GIF, WebP) |
| `platform` | String | No | Platform to search on. Default: `1688`. Options: `1688`, `taobao`, `tmall` |
| `page` | Integer | No | Page number for pagination. Default: `1` |
| `page_size` | Integer | No | Number of results per page. Default: `20`. Max: `100` |
| `lang` | String | No | Language for results. Default: `en`. Options: `en`, `zh` |
| `sort` | String | No | Sort order. Options: `default`, `sales`, `price_up`, `price_down` |
| `price_start` | Numeric | No | Minimum price filter (in CNY) |
| `price_end` | Numeric | No | Maximum price filter (in CNY) |
| `support_dropshipping` | Boolean | No | Filter for dropshipping support |
| `is_factory` | Boolean | No | Filter for factory direct products |
| `verified_supplier` | Boolean | No | Filter for verified suppliers |
| `free_shipping` | Boolean | No | Filter for free shipping products |
| `new_arrival` | Boolean | No | Filter for new arrival products |

## File Upload Requirements
- **Max file size**: 2MB (2048 KB)
- **Allowed formats**: jpeg, png, jpg, gif, webp
- **Field name**: `image`

## Example Request (cURL)

```bash
curl -X POST http://localhost:8000/api/product-search-by-image \
  -H "Accept: application/json" \
  -F "image=@/path/to/your/image.jpg" \
  -F "platform=1688" \
  -F "page=1" \
  -F "page_size=20" \
  -F "lang=en" \
  -F "sort=default"
```

## Example Request (JavaScript/Fetch)

```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);
formData.append('platform', '1688');
formData.append('page', '1');
formData.append('page_size', '20');
formData.append('lang', 'en');

fetch('http://localhost:8000/api/product-search-by-image', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data));
```

## Example Request (Postman)
1. Set method to `POST`
2. URL: `http://localhost:8000/api/product-search-by-image`
3. Go to **Body** tab
4. Select **form-data**
5. Add key `image` with type **File** and select your image
6. Add other parameters as **Text** type

## Success Response (200 OK)

```json
{
  "result": {
    "page": 1,
    "per_page": 20,
    "total_found": 150,
    "products": {
      "items": [
        {
          "item_id": "752859102818",
          "title": "Children's Watch Multifunctional Colorful Light Waterproof Student Electronic Watch",
          "img": "https://cbu01.alicdn.com/img/ibank/O1CN01WgT3GF1LjTeBmEBcN_!!2423381335-0-cib.jpg",
          "main_imgs": [
            "https://cbu01.alicdn.com/img/ibank/O1CN01WgT3GF1LjTeBmEBcN_!!2423381335-0-cib.jpg"
          ],
          "price": "389.00",
          "price_info": {
            "price": "389.00",
            "price_min": "350.00",
            "price_max": "389.00",
            "discount_percentage": 10,
            "discount_price": "350.00"
          },
          "discount_percentage": 10,
          "discount_price": "350.00",
          "currency": "BDT",
          "sale_info": {
            "sale_quantity_30days": "7339",
            "sale_quantity_90days": "32280"
          },
          "shop_info": {
            "shop_name": "Store Name",
            "seller_id": "b2b-2423381335"
          }
        }
      ]
    },
    "keywords": [],
    "time": "2026-01-16T15:30:00+00:00"
  },
  "image": "http://localhost:8000/storage/search_images/1737039000_abc123.jpg"
}
```

## Error Responses

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "image": [
      "The image field is required."
    ]
  }
}
```

### 422 Invalid File Type
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "image": [
      "The image must be a file of type: jpeg, png, jpg, gif, webp."
    ]
  }
}
```

### 422 File Too Large
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "image": [
      "The image must not be greater than 2048 kilobytes."
    ]
  }
}
```

### 400 API Error
```json
{
  "success": false,
  "message": "Failed to search products by image",
  "error_code": 404
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to process image search",
  "error": "Error details here"
}
```

## Response Fields

| Field | Type | Description |
|-------|------|-------------|
| `result.page` | Integer | Current page number |
| `result.per_page` | Integer | Number of items per page |
| `result.total_found` | Integer | Total number of products found |
| `result.products.items` | Array | Array of product objects |
| `result.time` | String | Timestamp of the search |
| `image` | String | URL of the uploaded search image |

## Product Object Fields

| Field | Type | Description |
|-------|------|-------------|
| `item_id` | String | Unique product ID |
| `title` | String | Product title (translated to English if lang=en) |
| `img` | String | Main product image URL |
| `main_imgs` | Array | Array of product image URLs |
| `price` | String | Converted price in site currency |
| `discount_percentage` | Number | Discount percentage (0-100) |
| `discount_price` | String | Price after discount |
| `currency` | String | Currency code (e.g., BDT, USD) |
| `sale_info` | Object | Sales statistics |
| `shop_info` | Object | Seller/shop information |

## Notes

1. **Image Upload**: The image is temporarily stored in `storage/app/public/search_images/` and can be accessed via the returned URL
2. **Price Conversion**: All prices are automatically converted from CNY to your site's currency based on settings
3. **Discount Calculation**: Discounts are applied based on active offers in site settings
4. **Language**: When `lang=en`, product titles are returned in English
5. **Pagination**: Use `page` and `page_size` to navigate through results
6. **Filters**: Additional filters help narrow down search results

## Use Cases

- **Visual Search**: Users can upload a product photo to find similar items
- **Reverse Image Search**: Find the source or alternatives for a product
- **Price Comparison**: Compare prices of visually similar products
- **Dropshipping**: Find suppliers for products based on images

## Important Notes

⚠️ **Localhost Limitation**: The TMAPI service cannot access images hosted on localhost. For testing:
- Use publicly accessible image URLs with the `image_url` parameter instead
- Deploy your application to a public server
- Use images from CDNs or public sources

## Alternative: Search by Image URL

If you have a publicly accessible image URL, you can use:

```bash
curl -X POST http://localhost:8000/api/product-search-by-image \
  -H "Content-Type: application/json" \
  -d '{
    "image_url": "https://example.com/product-image.jpg",
    "platform": "1688",
    "page": 1,
    "page_size": 20,
    "lang": "en"
  }'
```

