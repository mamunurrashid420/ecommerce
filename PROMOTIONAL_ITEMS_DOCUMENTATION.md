# Secondary Address Feature Documentation

## Overview
The secondary address feature allows you to add an additional address field to your site settings. This field is optional and can be used to store a secondary business address, warehouse address, or any other relevant address information.

## Database Changes
- Added `secondary_address` TEXT field to the `site_settings` table
- Migration file: `2026_01_19_183649_add_secondary_address_to_site_settings_table.php`

## API Endpoints

### GET /api/site-settings/public
Returns public site settings including the secondary address.

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "address": "123 Main Street, City, State 12345, Country",
    "secondary_address": "456 Secondary Street, Another City, State 67890, Country"
  }
}
```

### GET /api/site-settings
Returns admin site settings including the secondary address (requires authentication).

### POST /api/site-settings
Updates site settings including the secondary address (requires admin authentication).

## Usage Examples

### 1. Update Secondary Address
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "secondary_address": "456 Secondary Street, Another City, State 67890, Country"
  }'
```

### 2. Clear Secondary Address
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "secondary_address": null
  }'
```

## Validation Rules
- Field is optional (nullable)
- Accepts string values
- No maximum length restriction (TEXT field)

## Model Changes
- Added `secondary_address` to `$fillable` array in SiteSetting model

## Controller Changes
- Added `secondary_address` validation rule in `createOrUpdate()` method
- Added `secondary_address` to both `show()` and `public()` method responses
- Added `secondary_address` to `createOrUpdate()` method response

## Frontend Integration
The secondary address is now available in the public API response and can be used in your frontend application:

```javascript
// Fetch site settings
fetch('/api/site-settings/public')
  .then(response => response.json())
  .then(data => {
    const address = data.data.address;
    const secondaryAddress = data.data.secondary_address;
    
    // Display addresses
    if (address) {
      console.log('Primary Address:', address);
    }
    
    if (secondaryAddress) {
      console.log('Secondary Address:', secondaryAddress);
    }
  });
```

## Testing
Run the test script to verify the implementation:
```bash
php test_secondary_address.php
```

## Notes
- The secondary_address field is optional and can be null
- The field appears in both public and admin API responses
- The feature maintains backward compatibility with existing site settings
- No additional processing or URL generation is needed (unlike image fields)

---

# Promotional Items Feature Documentation

## Overview
The promotional items feature allows you to add up to 3 promotional items to your site settings. Each promotional item consists of an image and an optional URL. This feature has been successfully integrated into the existing site settings API.

## Database Changes
- Added `promotional_items` JSON field to the `site_settings` table
- Migration file: `2026_01_19_174900_add_promotional_items_to_site_settings_table.php`

## API Endpoints

### GET /api/site-settings/public
Returns public site settings including promotional items.

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "promotional_items": [
      {
        "image": "http://localhost:8000/storage/promotional/image1.jpg",
        "url": "https://example.com/promo1"
      },
      {
        "image": "http://localhost:8000/storage/promotional/image2.jpg", 
        "url": "https://example.com/promo2"
      },
      {
        "image": "http://localhost:8000/storage/promotional/image3.jpg",
        "url": null
      }
    ]
  }
}
```

### GET /api/site-settings
Returns admin site settings including promotional items (requires authentication).

### POST /api/site-settings
Updates site settings including promotional items (requires admin authentication).

## Usage Examples

### 1. Upload New Promotional Item Images
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -F "promotional_item_images[]=@/path/to/image1.jpg" \
  -F "promotional_item_images[]=@/path/to/image2.jpg" \
  -F "promotional_item_images[]=@/path/to/image3.jpg" \
  -F "promotional_item_urls[]=https://example.com/promo1" \
  -F "promotional_item_urls[]=https://example.com/promo2" \
  -F "promotional_item_urls[]="
```

### 2. Update Existing Promotional Items (JSON)
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "promotional_items": [
      {
        "image": "promotional/existing_image1.jpg",
        "url": "https://updated-url.com"
      },
      {
        "image": "promotional/existing_image2.jpg",
        "url": null
      }
    ]
  }'
```

### 3. Remove All Promotional Items
```bash
curl -X POST "http://localhost:8000/api/site-settings" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "promotional_items": []
  }'
```

## Validation Rules
- Maximum 3 promotional items allowed
- Each promotional item must have:
  - `image`: Required string (file path or uploaded file)
  - `url`: Optional URL (can be null)
- Image uploads:
  - Supported formats: jpeg, png, jpg, gif, svg
  - Maximum file size: 2MB
  - Stored in `storage/promotional/` directory

## Model Changes
- Added `promotional_items` to `$fillable` array
- Added `promotional_items` to `$casts` array as 'array'
- Added `promotionalItemsWithUrls()` accessor method
- Added `getPromotionalItemsWithUrls()` helper method

## Controller Changes
- Updated validation rules in `createOrUpdate()` method
- Added file upload handling for promotional item images
- Added promotional items to both `show()` and `public()` method responses
- Automatic cleanup of removed promotional item images

## File Storage
- Promotional item images are stored in `storage/app/public/promotional/`
- Images are automatically deleted when promotional items are removed
- Full URLs are generated automatically using the app URL

## Frontend Integration
The promotional items are now available in the public API response and can be used in your frontend application:

```javascript
// Fetch site settings
fetch('/api/site-settings/public')
  .then(response => response.json())
  .then(data => {
    const promotionalItems = data.data.promotional_items;
    
    // Render promotional items
    promotionalItems.forEach(item => {
      if (item.image) {
        const img = document.createElement('img');
        img.src = item.image;
        
        if (item.url) {
          const link = document.createElement('a');
          link.href = item.url;
          link.appendChild(img);
          document.body.appendChild(link);
        } else {
          document.body.appendChild(img);
        }
      }
    });
  });
```

## Testing
Run the test script to verify the implementation:
```bash
php test_promotional_items_api.php
```

## Notes
- The URL field is optional and can be null
- Images are automatically converted to full URLs in API responses
- The feature maintains backward compatibility with existing site settings
- Maximum of 3 promotional items to prevent performance issues
- File cleanup is handled automatically when items are removed or updated