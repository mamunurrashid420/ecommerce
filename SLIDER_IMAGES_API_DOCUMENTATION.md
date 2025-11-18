# Slider Images API Documentation

Complete documentation for managing slider images in Site Settings.

## Table of Contents
1. [Overview](#overview)
2. [Slider Images in Site Settings](#slider-images-in-site-settings)
3. [Upload Slider Images](#upload-slider-images)
4. [Update/Reorder Slider Images](#updatereorder-slider-images)
5. [Delete Slider Images](#delete-slider-images)
6. [Remove Slider Items](#remove-slider-items)
7. [Retrieve Slider Images](#retrieve-slider-images)
8. [Examples](#examples)
9. [Error Responses](#error-responses)

---

## Overview

The Slider Images feature allows administrators to manage multiple slider images for the website homepage or any slider component. Slider images are stored as part of the Site Settings and can be managed through the Site Settings API endpoints.

### Base URL
```
http://your-domain.com/api
```

### Authentication
- **Required**: Yes (for admin endpoints)
- **Role**: Admin only (for create/update)
- **Header**: `Authorization: Bearer {token}`
- **Public Access**: Slider images are available in the public site-settings endpoint (no authentication required)

---

## Slider Images in Site Settings

Slider images are managed as part of the Site Settings API. Each slider image can include an image, title, subtitle, and optional hyperlink. The `slider_images` field stores an array of objects that are automatically converted to full URLs in API responses.

### Field Details

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| `slider_images` | array | No | array of images or array of objects | Multiple slider images with metadata |
| `slider_titles` | array | No | array of strings, max:255 each | Titles for each slider image (when uploading files) |
| `slider_subtitles` | array | No | array of strings, max:500 each | Subtitles for each slider image (when uploading files) |
| `slider_hyperlinks` | array | No | array of URLs, max:500 each | Hyperlinks for each slider image (when uploading files) |

**Slider Image Object Structure:**
Each slider image object contains:
- `image`: Image path or full URL (required)
- `title`: Title text (optional, max 255 characters)
- `subtitle`: Subtitle text (optional, max 500 characters)
- `hyperlink`: Clickable URL (optional, must be valid URL, max 500 characters)

**Validation Rules:**
- When uploading files: Each image must be `jpeg`, `png`, `jpg`, `gif`, or `svg`
- Maximum file size: 2MB per image
- When sending as JSON: Must be an array of objects with `image`, `title`, `subtitle`, and `hyperlink` fields
- Hyperlink must be a valid URL format

---

## Upload Slider Images

Upload new slider images to add to existing slider images. New images will be appended to the existing collection.

### Endpoint
```
POST /api/site-settings
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

### Content-Type
```
multipart/form-data
```

### Request Format

Send slider images as an array of files using `multipart/form-data`. You can optionally include titles, subtitles, and hyperlinks for each image.

**Request Fields:**
- `slider_images[]`: Array of image files (required)
- `slider_titles[]`: Array of title strings (optional, must match image array length)
- `slider_subtitles[]`: Array of subtitle strings (optional, must match image array length)
- `slider_hyperlinks[]`: Array of URL strings (optional, must match image array length)

**Important Notes:**
- Uploading new slider images will **append them to existing slider images** (not replace)
- Existing images will be preserved
- You can upload multiple images in a single request
- Titles, subtitles, and hyperlinks are optional but must be arrays matching the image array length
- If you provide titles/subtitles/hyperlinks, they will be matched by array index with the images
- To remove specific images, use the Remove Slider Items API endpoint

### Example: Upload Slider Images (cURL)

**Basic Upload (Images Only):**
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "slider_images[]=@/path/to/slider1.jpg" \
  -F "slider_images[]=@/path/to/slider2.png" \
  -F "slider_images[]=@/path/to/slider3.jpg"
```

**Upload with Titles, Subtitles, and Hyperlinks:**
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "slider_images[]=@/path/to/slider1.jpg" \
  -F "slider_titles[]=Welcome to Our Store" \
  -F "slider_subtitles[]=Discover amazing products at great prices" \
  -F "slider_hyperlinks[]=https://example.com/products" \
  -F "slider_images[]=@/path/to/slider2.png" \
  -F "slider_titles[]=Summer Sale" \
  -F "slider_subtitles[]=Up to 50% off on selected items" \
  -F "slider_hyperlinks[]=https://example.com/sale" \
  -F "slider_images[]=@/path/to/slider3.jpg" \
  -F "slider_titles[]=New Arrivals" \
  -F "slider_subtitles[]=Check out our latest collection" \
  -F "slider_hyperlinks[]=https://example.com/new-arrivals"
```

### Example: Upload Slider Images (JavaScript)

**Basic Upload (Images Only):**
```javascript
const token = 'your_admin_token';
const formData = new FormData();

// Add multiple slider images
formData.append('slider_images[]', file1); // File object
formData.append('slider_images[]', file2);
formData.append('slider_images[]', file3);

const response = await fetch('http://localhost:8000/api/site-settings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
});

const data = await response.json();
console.log(data.data.slider_images); // Array of objects with image, title, subtitle, hyperlink
```

**Upload with Titles, Subtitles, and Hyperlinks:**
```javascript
const token = 'your_admin_token';
const formData = new FormData();

// Define slider data
const sliders = [
  {
    image: file1,
    title: 'Welcome to Our Store',
    subtitle: 'Discover amazing products at great prices',
    hyperlink: 'https://example.com/products'
  },
  {
    image: file2,
    title: 'Summer Sale',
    subtitle: 'Up to 50% off on selected items',
    hyperlink: 'https://example.com/sale'
  },
  {
    image: file3,
    title: 'New Arrivals',
    subtitle: 'Check out our latest collection',
    hyperlink: 'https://example.com/new-arrivals'
  }
];

// Add to form data
sliders.forEach((slider, index) => {
  formData.append('slider_images[]', slider.image);
  formData.append('slider_titles[]', slider.title);
  formData.append('slider_subtitles[]', slider.subtitle);
  formData.append('slider_hyperlinks[]', slider.hyperlink);
});

const response = await fetch('http://localhost:8000/api/site-settings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  body: formData
});

const data = await response.json();
console.log(data.data.slider_images);
```

### Example: Upload Slider Images (PHP)

```php
$token = 'your_admin_token';
$url = 'http://localhost:8000/api/site-settings';

$curl = curl_init($url);

$files = [
    new CURLFile('/path/to/slider1.jpg', 'image/jpeg', 'slider1.jpg'),
    new CURLFile('/path/to/slider2.png', 'image/png', 'slider2.png'),
    new CURLFile('/path/to/slider3.jpg', 'image/jpeg', 'slider3.jpg'),
];

$postData = [];
foreach ($files as $index => $file) {
    $postData["slider_images[$index]"] = $file;
}

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_POSTFIELDS => $postData,
]);

$response = curl_exec($curl);
$data = json_decode($response, true);
curl_close($curl);
```

### Response Format

```json
{
  "success": true,
  "message": "Site settings updated successfully",
  "data": {
    "id": 1,
    "slider_images": [
      {
        "image": "http://localhost:8000/storage/sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products at great prices",
        "hyperlink": "https://example.com/products"
      },
      {
        "image": "http://localhost:8000/storage/sliders/def456.png",
        "title": "Summer Sale",
        "subtitle": "Up to 50% off on selected items",
        "hyperlink": "https://example.com/sale"
      },
      {
        "image": "http://localhost:8000/storage/sliders/ghi789.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "hyperlink": "https://example.com/new-arrivals"
      }
    ],
    // ... other site settings fields
  }
}
```

**Note:** If title, subtitle, or hyperlink are not provided, they will be `null` in the response.

---

## Update/Reorder Slider Images

Update the order of slider images or replace specific images by sending an array of image paths.

### Endpoint
```
POST /api/site-settings
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

### Content-Type
```
application/json
```

### Request Format

Send `slider_images` as an array of objects. Each object should contain `image`, `title`, `subtitle`, and `hyperlink` fields. This allows you to:
- Reorder existing images
- Update titles, subtitles, and hyperlinks
- Remove specific images (by excluding them from the array)
- Keep existing images in a different order

**Object Structure:**
```json
{
  "image": "sliders/abc123.jpg",  // Required: image path
  "title": "Updated Title",        // Optional: title text
  "subtitle": "Updated Subtitle",  // Optional: subtitle text
  "hyperlink": "https://example.com" // Optional: clickable URL
}
```

**Important Notes:**
- Images not included in the array will be **deleted** from storage
- Only include images you want to keep
- Order matters - the array order determines the display order
- You can update titles, subtitles, and hyperlinks without re-uploading images
- If you omit title, subtitle, or hyperlink, they will be set to `null`

### Example: Reorder Slider Images (cURL)

**Reorder Only:**
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_images": [
      {
        "image": "sliders/ghi789.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "hyperlink": "https://example.com/new-arrivals"
      },
      {
        "image": "sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products",
        "hyperlink": "https://example.com/products"
      },
      {
        "image": "sliders/def456.png",
        "title": "Summer Sale",
        "subtitle": "Up to 50% off",
        "hyperlink": "https://example.com/sale"
      }
    ]
  }'
```

**Update Titles, Subtitles, and Hyperlinks:**
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_images": [
      {
        "image": "sliders/abc123.jpg",
        "title": "Updated Title",
        "subtitle": "Updated Subtitle",
        "hyperlink": "https://example.com/updated-link"
      }
    ]
  }'
```

### Example: Update Slider Images (JavaScript)

```javascript
const token = 'your_admin_token';

const response = await fetch('http://localhost:8000/api/site-settings', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    slider_images: [
      {
        image: 'sliders/image1.jpg',
        title: 'Welcome to Our Store',
        subtitle: 'Discover amazing products',
        hyperlink: 'https://example.com/products'
      },
      {
        image: 'sliders/image2.png',
        title: 'Summer Sale',
        subtitle: 'Up to 50% off',
        hyperlink: 'https://example.com/sale'
      },
      {
        image: 'sliders/image3.jpg',
        title: 'New Arrivals',
        subtitle: 'Check out our latest collection',
        hyperlink: 'https://example.com/new-arrivals'
      }
    ]
  })
});

const data = await response.json();
console.log(data.data.slider_images);
```

### Response Format

```json
{
  "success": true,
  "message": "Site settings updated successfully",
  "data": {
    "id": 1,
    "slider_images": [
      {
        "image": "http://localhost:8000/storage/sliders/ghi789.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "hyperlink": "https://example.com/new-arrivals"
      },
      {
        "image": "http://localhost:8000/storage/sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products",
        "hyperlink": "https://example.com/products"
      },
      {
        "image": "http://localhost:8000/storage/sliders/def456.png",
        "title": "Summer Sale",
        "subtitle": "Up to 50% off",
        "hyperlink": "https://example.com/sale"
      }
    ],
    // ... other site settings fields
  }
}
```

---

## Delete Slider Images

Delete all slider images by sending an empty array, or delete specific images by excluding them from the array.

### Endpoint
```
POST /api/site-settings
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

### Content-Type
```
application/json
```

### Example: Delete All Slider Images

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_images": []
  }'
```

### Example: Delete Specific Slider Images

To delete specific images, send an array containing only the images you want to keep:

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_images": [
      {
        "image": "sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products",
        "hyperlink": "https://example.com/products"
      }
    ]
  }'
```

This will keep only the specified slider image and delete all others.

### Response Format

```json
{
  "success": true,
  "message": "Site settings updated successfully",
  "data": {
    "id": 1,
    "slider_images": [],
    // ... other site settings fields
  }
}
```

---

## Remove Slider Items

Remove specific slider items by their indices or image paths. This is the recommended way to remove individual slider items without affecting others.

### Endpoint
```
DELETE /api/site-settings/slider-items
```

### Authentication
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

### Content-Type
```
application/json
```

### Request Format

You can remove slider items by either:
- **Indices**: Provide array indices (0-based) of items to remove
- **Paths**: Provide image paths or URLs of items to remove

**Request Fields:**
- `slider_indices`: Array of integers (optional, 0-based indices)
- `slider_paths`: Array of strings (optional, image paths or URLs)

**Important Notes:**
- You must provide either `slider_indices` or `slider_paths` (or both)
- Indices are 0-based (first item is index 0, second is index 1, etc.)
- Paths can be:
  - Storage paths: `sliders/abc123.jpg`
  - Full storage paths: `/storage/sliders/abc123.jpg` or `storage/sliders/abc123.jpg`
  - Full URLs: `http://localhost:8000/storage/sliders/abc123.jpg`
- Removed images will be deleted from storage
- You can remove multiple items in a single request

### Example: Remove by Indices (cURL)

**Remove Single Item:**
```bash
curl -X DELETE "http://localhost:8000/api/site-settings/slider-items" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_indices": [0]
  }'
```

**Remove Multiple Items:**
```bash
curl -X DELETE "http://localhost:8000/api/site-settings/slider-items" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_indices": [0, 2, 4]
  }'
```

### Example: Remove by Paths (cURL)

**Remove by Storage Path:**
```bash
curl -X DELETE "http://localhost:8000/api/site-settings/slider-items" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_paths": ["sliders/abc123.jpg"]
  }'
```

**Remove by Full URL:**
```bash
curl -X DELETE "http://localhost:8000/api/site-settings/slider-items" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_paths": [
      "http://localhost:8000/storage/sliders/abc123.jpg",
      "http://localhost:8000/storage/sliders/def456.png"
    ]
  }'
```

**Remove Multiple Items by Mixed Paths:**
```bash
curl -X DELETE "http://localhost:8000/api/site-settings/slider-items" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_paths": [
      "sliders/abc123.jpg",
      "/storage/sliders/def456.png",
      "http://localhost:8000/storage/sliders/ghi789.jpg"
    ]
  }'
```

### Example: Remove Slider Items (JavaScript)

**Remove by Indices:**
```javascript
const token = 'your_admin_token';

const response = await fetch('http://localhost:8000/api/site-settings/slider-items', {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    slider_indices: [0, 2] // Remove first and third items
  })
});

const data = await response.json();
console.log(data.message); // "Successfully removed 2 slider item(s)"
console.log(data.data.slider_images); // Remaining slider images
```

**Remove by Paths:**
```javascript
const token = 'your_admin_token';

const response = await fetch('http://localhost:8000/api/site-settings/slider-items', {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    slider_paths: [
      'sliders/image1.jpg',
      'http://localhost:8000/storage/sliders/image2.png'
    ]
  })
});

const data = await response.json();
console.log(data);
```

**Remove by Both Indices and Paths:**
```javascript
const token = 'your_admin_token';

const response = await fetch('http://localhost:8000/api/site-settings/slider-items', {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    slider_indices: [0],
    slider_paths: ['sliders/image2.png']
  })
});

const data = await response.json();
console.log(data);
```

### Response Format

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Successfully removed 2 slider item(s)",
  "data": {
    "removed_count": 2,
    "slider_images": [
      {
        "image": "http://localhost:8000/storage/sliders/remaining1.jpg",
        "title": "Remaining Title",
        "subtitle": "Remaining Subtitle",
        "hyperlink": "https://example.com"
      },
      {
        "image": "http://localhost:8000/storage/sliders/remaining2.png",
        "title": "Another Title",
        "subtitle": "Another Subtitle",
        "hyperlink": null
      }
    ]
  }
}
```

**Error Response - No Items Found (404 Not Found):**
```json
{
  "success": false,
  "message": "No slider images found to remove"
}
```

**Error Response - Missing Parameters (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Please provide either slider_indices or slider_paths to remove items"
}
```

**Error Response - Validation Error (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "slider_indices.0": [
      "The slider indices.0 must be an integer.",
      "The slider indices.0 must be at least 0."
    ]
  }
}
```

### Example: Complete Workflow (Upload, Remove)

```javascript
const token = 'your_admin_token';
const baseUrl = 'http://localhost:8000/api/site-settings';

// Step 1: Upload new slider images
const formData = new FormData();
formData.append('slider_images[]', file1);
formData.append('slider_images[]', file2);
formData.append('slider_images[]', file3);

const uploadResponse = await fetch(baseUrl, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData
});
const uploadData = await uploadResponse.json();
console.log('Uploaded:', uploadData.data.slider_images);

// Step 2: Remove specific items by index
const removeResponse = await fetch(`${baseUrl}/slider-items`, {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    slider_indices: [0] // Remove first item
  })
});
const removeData = await removeResponse.json();
console.log('Removed:', removeData.data.removed_count);
console.log('Remaining:', removeData.data.slider_images);
```

---

## Retrieve Slider Images

Retrieve slider images from both admin and public endpoints.

### Admin Endpoint (Details)

**Endpoint:**
```
GET /api/site-settings
```

**Authentication:**
- **Required**: Yes
- **Role**: Admin only
- **Header**: `Authorization: Bearer {admin_token}`

**Response:**
```json
{
  "success": true,
  "message": "Site settings retrieved successfully",
  "data": {
    "id": 1,
    "slider_images": [
      {
        "image": "http://localhost:8000/storage/sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products at great prices",
        "hyperlink": "https://example.com/products"
      },
      {
        "image": "http://localhost:8000/storage/sliders/def456.png",
        "title": "Summer Sale",
        "subtitle": "Up to 50% off on selected items",
        "hyperlink": "https://example.com/sale"
      },
      {
        "image": "http://localhost:8000/storage/sliders/ghi789.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "hyperlink": "https://example.com/new-arrivals"
      }
    ],
    // ... other site settings fields
  }
}
```

### Public Endpoint

**Endpoint:**
```
GET /api/site-settings/public
```

**Authentication:**
- **Required**: No
- **Public Access**: Yes

**Response:**
```json
{
  "success": true,
  "data": {
    "slider_images": [
      {
        "image": "http://localhost:8000/storage/sliders/abc123.jpg",
        "title": "Welcome to Our Store",
        "subtitle": "Discover amazing products at great prices",
        "hyperlink": "https://example.com/products"
      },
      {
        "image": "http://localhost:8000/storage/sliders/def456.png",
        "title": "Summer Sale",
        "subtitle": "Up to 50% off on selected items",
        "hyperlink": "https://example.com/sale"
      },
      {
        "image": "http://localhost:8000/storage/sliders/ghi789.jpg",
        "title": "New Arrivals",
        "subtitle": "Check out our latest collection",
        "hyperlink": "https://example.com/new-arrivals"
      }
    ],
    // ... other public site settings fields
  }
}
```

### Example: Retrieve Slider Images (JavaScript)

```javascript
// Public endpoint (no authentication required)
const publicResponse = await fetch('http://localhost:8000/api/site-settings/public');
const publicData = await publicResponse.json();
console.log(publicData.data.slider_images);

// Admin endpoint (authentication required)
const token = 'your_admin_token';
const adminResponse = await fetch('http://localhost:8000/api/site-settings', {
  headers: {
    'Authorization': `Bearer ${token}`,
  }
});
const adminData = await adminResponse.json();
console.log(adminData.data.slider_images);
```

---

## Examples

### Example 1: Upload Multiple Slider Images with Titles, Subtitles, and Hyperlinks

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "slider_images[]=@/path/to/banner1.jpg" \
  -F "slider_titles[]=Special Offer" \
  -F "slider_subtitles[]=Get 30% off on all items" \
  -F "slider_hyperlinks[]=https://example.com/special-offer" \
  -F "slider_images[]=@/path/to/banner2.png" \
  -F "slider_titles[]=New Collection" \
  -F "slider_subtitles[]=Explore our latest arrivals" \
  -F "slider_hyperlinks[]=https://example.com/new-collection" \
  -F "slider_images[]=@/path/to/banner3.jpg" \
  -F "slider_titles[]=Free Shipping" \
  -F "slider_subtitles[]=On orders over $50" \
  -F "slider_hyperlinks[]=https://example.com/shipping" \
  -F "slider_images[]=@/path/to/banner4.png" \
  -F "slider_titles[]=Flash Sale" \
  -F "slider_subtitles[]=Limited time only" \
  -F "slider_hyperlinks[]=https://example.com/flash-sale"
```

### Example 2: Upload Slider Images with Other Settings

```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -F "title=My Store" \
  -F "slider_images[]=@/path/to/slider1.jpg" \
  -F "slider_titles[]=Welcome" \
  -F "slider_subtitles[]=Shop now" \
  -F "slider_hyperlinks[]=https://example.com" \
  -F "slider_images[]=@/path/to/slider2.png" \
  -F "slider_titles[]=Sale" \
  -F "slider_subtitles[]=50% off" \
  -F "slider_hyperlinks[]=https://example.com/sale" \
  -F "header_logo=@/path/to/logo.jpg"
```

### Example 3: Reorder and Update Existing Slider Images

First, get current slider images:
```bash
curl -X GET "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}"
```

Then reorder and update them:
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "slider_images": [
      {
        "image": "sliders/third_image.jpg",
        "title": "Updated Third Title",
        "subtitle": "Updated third subtitle",
        "hyperlink": "https://example.com/third"
      },
      {
        "image": "sliders/first_image.jpg",
        "title": "Updated First Title",
        "subtitle": "Updated first subtitle",
        "hyperlink": "https://example.com/first"
      },
      {
        "image": "sliders/second_image.png",
        "title": "Updated Second Title",
        "subtitle": "Updated second subtitle",
        "hyperlink": "https://example.com/second"
      }
    ]
  }'
```

### Example 4: Complete Workflow (Upload, Reorder, Delete)

```javascript
const token = 'your_admin_token';
const baseUrl = 'http://localhost:8000/api/site-settings';

// Step 1: Upload new slider images
const formData1 = new FormData();
formData1.append('slider_images[]', file1);
formData1.append('slider_images[]', file2);
formData1.append('slider_images[]', file3);

const uploadResponse = await fetch(baseUrl, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData1
});
const uploadData = await uploadResponse.json();
console.log('Uploaded:', uploadData.data.slider_images);

// Step 2: Reorder images (reverse order)
const paths = uploadData.data.slider_images.map(url => {
  // Extract path from full URL
  return url.replace('http://localhost:8000/storage/', '');
}).reverse();

const reorderResponse = await fetch(baseUrl, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ slider_images: paths })
});
const reorderData = await reorderResponse.json();
console.log('Reordered:', reorderData.data.slider_images);

// Step 3: Delete all slider images
const deleteResponse = await fetch(baseUrl, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ slider_images: [] })
});
const deleteData = await deleteResponse.json();
console.log('Deleted:', deleteData.data.slider_images); // []
```

### Example 5: Frontend Integration (React)

```jsx
import React, { useState, useEffect } from 'react';

function SliderManager() {
  const [sliderImages, setSliderImages] = useState([]);
  const [loading, setLoading] = useState(false);

  // Fetch slider images from public endpoint
  useEffect(() => {
    fetch('http://localhost:8000/api/site-settings/public')
      .then(res => res.json())
      .then(data => {
        setSliderImages(data.data.slider_images || []);
      });
  }, []);

  // Upload new slider images with titles, subtitles, and hyperlinks
  const handleUpload = async (files, titles = [], subtitles = [], hyperlinks = []) => {
    setLoading(true);
    const formData = new FormData();
    
    Array.from(files).forEach((file, index) => {
      formData.append('slider_images[]', file);
      if (titles[index]) formData.append('slider_titles[]', titles[index]);
      if (subtitles[index]) formData.append('slider_subtitles[]', subtitles[index]);
      if (hyperlinks[index]) formData.append('slider_hyperlinks[]', hyperlinks[index]);
    });

    try {
      const response = await fetch('http://localhost:8000/api/site-settings', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('admin_token')}`,
        },
        body: formData
      });
      const data = await response.json();
      setSliderImages(data.data.slider_images);
    } catch (error) {
      console.error('Upload failed:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div>
      <h2>Slider Images</h2>
      <input
        type="file"
        multiple
        accept="image/*"
        onChange={(e) => handleUpload(e.target.files)}
        disabled={loading}
      />
      <div className="slider-preview">
        {sliderImages.map((slider, index) => (
          <div key={index} className="slider-item">
            <img src={slider.image} alt={slider.title || `Slider ${index + 1}`} />
            {slider.title && <h3>{slider.title}</h3>}
            {slider.subtitle && <p>{slider.subtitle}</p>}
            {slider.hyperlink && (
              <a href={slider.hyperlink} target="_blank" rel="noopener noreferrer">
                Learn More
              </a>
            )}
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## Error Responses

### Validation Error (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "slider_images.0": [
      "The slider images.0 must be an image.",
      "The slider images.0 must not be greater than 2048 kilobytes."
    ],
    "slider_images.1": [
      "The slider images.1 must be a file of type: jpeg, png, jpg, gif, svg."
    ]
  }
}
```

### Unauthenticated (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

### Unauthorized - Not Admin (403 Forbidden)

```json
{
  "message": "Unauthorized. Admin access required."
}
```

### Server Error (500 Internal Server Error)

```json
{
  "success": false,
  "message": "Failed to update site settings",
  "error": "Error message details"
}
```

---

## Important Notes

### File Storage
- **Storage Location**: `storage/app/public/sliders/`
- **Access URL**: `http://your-domain.com/storage/sliders/{filename}`
- **Automatic Cleanup**: Images are automatically deleted from storage when:
  - Removed using the Remove Slider Items API endpoint
  - Removed when updating via JSON array (excluding them from the array)

### Image Formats
- **Supported Formats**: JPEG, PNG, JPG, GIF, SVG
- **Maximum Size**: 2MB per image
- **Recommended**: Use optimized images for better performance

### Array Handling
- **Upload Mode**: When uploading files, new images are **appended** to existing images (not replaced). Titles, subtitles, and hyperlinks are matched by array index.
- **Update Mode**: When sending objects as array via JSON, only included images are kept. You can update titles, subtitles, and hyperlinks without re-uploading images. Images not included in the array will be deleted.
- **Remove Mode**: Use the dedicated Remove Slider Items API endpoint (`DELETE /api/site-settings/slider-items`) to remove specific items by index or path.
- **Order Matters**: The array order determines the display order of images
- **Object Structure**: Each slider image is an object with `image`, `title`, `subtitle`, and `hyperlink` fields

### URL Format
- Images are stored as paths (e.g., `sliders/abc123.jpg`)
- API responses return full URLs for images (e.g., `http://localhost:8000/storage/sliders/abc123.jpg`)
- Hyperlinks are returned as provided (must be valid URLs)
- Use the full URLs in your frontend application

### Slider Image Structure
Each slider image in the response contains:
- `image`: Full URL to the image file
- `title`: Title text (or `null` if not provided)
- `subtitle`: Subtitle text (or `null` if not provided)
- `hyperlink`: Clickable URL (or `null` if not provided)

**Example Response Object:**
```json
{
  "image": "http://localhost:8000/storage/sliders/abc123.jpg",
  "title": "Welcome to Our Store",
  "subtitle": "Discover amazing products",
  "hyperlink": "https://example.com/products"
}
```

### Best Practices
1. **Optimize Images**: Compress images before uploading to reduce file size
2. **Consistent Dimensions**: Use consistent aspect ratios for better slider appearance
3. **Reasonable Count**: Limit the number of slider images (recommended: 3-10 images)
4. **Regular Updates**: Update slider images periodically to keep content fresh
5. **Backup**: Keep backups of important slider images

### Integration Tips
- Use the public endpoint (`/api/site-settings/public`) for frontend display
- Cache slider images on the frontend to reduce API calls
- Implement lazy loading for better performance
- Consider using a CDN for faster image delivery

---

## Support

For issues or questions about slider images, please refer to the main Site Settings API documentation or contact the development team.

