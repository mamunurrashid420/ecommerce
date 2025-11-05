# Site Settings Public API Documentation

## Overview
The Site Settings Public API provides essential configuration data for customer-facing websites. This API returns all the necessary information to configure and customize the frontend of an e-commerce site, including branding, contact information, business hours, currency settings, and more.

## Base URL
```
http://localhost:8000/api
```

## Authentication
**No authentication required** - This is a public endpoint designed for frontend consumption.

---

## 📋 Public Site Settings API

### Get Public Site Settings
**GET** `/api/site-settings/public`

**Description:** Retrieve public site configuration data for frontend display and functionality.

**Headers:**
```
Accept: application/json
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "title": "My Store",
        "tagline": "Some beautiful store",
        "description": "Very good system for e-commerce with modern features and user-friendly interface",
        "contact_number": "+1-234-567-8900",
        "email": "contact@mystore.com",
        "address": "123 Business Street, City, State 12345, Country",
        "business_name": "My Store LLC",
        "header_logo": "http://localhost:8000/storage/logos/header-logo.png",
        "footer_logo": "http://localhost:8000/storage/logos/footer-logo.png",
        "favicon": "http://localhost:8000/storage/logos/favicon.ico",
        "social_links": {
            "facebook": "https://facebook.com/mystore",
            "twitter": "https://twitter.com/mystore",
            "instagram": "https://instagram.com/mystore",
            "linkedin": "https://linkedin.com/company/mystore",
            "youtube": "https://youtube.com/c/mystore",
            "tiktok": "https://tiktok.com/@mystore",
            "whatsapp": "+1234567890"
        },
        "meta_title": "My Store - Best Online Shopping Experience",
        "meta_description": "Shop the latest products with great deals, fast shipping, and excellent customer service at My Store.",
        "meta_keywords": "online shopping, e-commerce, deals, fast shipping, quality products",
        "currency": "USD",
        "currency_symbol": "$",
        "currency_position": "before",
        "formatted_currency": "$ ",
        "store_enabled": true,
        "store_mode": "live",
        "maintenance_message": null,
        "business_hours": {
            "monday": {
                "open": "09:00",
                "close": "17:00",
                "closed": false
            },
            "tuesday": {
                "open": "09:00",
                "close": "17:00",
                "closed": false
            },
            "wednesday": {
                "open": "09:00",
                "close": "17:00",
                "closed": false
            },
            "thursday": {
                "open": "09:00",
                "close": "17:00",
                "closed": false
            },
            "friday": {
                "open": "09:00",
                "close": "17:00",
                "closed": false
            },
            "saturday": {
                "open": "10:00",
                "close": "16:00",
                "closed": false
            },
            "sunday": {
                "open": "10:00",
                "close": "16:00",
                "closed": true
            }
        },
        "google_analytics_id": "GA-XXXXXXXXX",
        "facebook_pixel_id": "123456789012345"
    }
}
```

---

## 🎯 Data Fields Explanation

### **Basic Information**
- **`title`**: Site/store name for display in headers and titles
- **`tagline`**: Short promotional text or slogan
- **`description`**: Detailed description of the business/store
- **`business_name`**: Official business name for legal/formal display

### **Contact Information**
- **`contact_number`**: Primary phone number for customer contact
- **`email`**: Primary email address for customer inquiries
- **`address`**: Physical business address for display

### **Branding Assets**
- **`header_logo`**: Full URL to header logo image
- **`footer_logo`**: Full URL to footer logo image  
- **`favicon`**: Full URL to favicon file

### **Social Media Links**
- **`social_links`**: Object containing social media URLs
  - `facebook`: Facebook page URL
  - `twitter`: Twitter profile URL
  - `instagram`: Instagram profile URL
  - `linkedin`: LinkedIn company page URL
  - `youtube`: YouTube channel URL
  - `tiktok`: TikTok profile URL
  - `whatsapp`: WhatsApp number for contact

### **SEO Configuration**
- **`meta_title`**: Default meta title for SEO
- **`meta_description`**: Default meta description for SEO
- **`meta_keywords`**: Default meta keywords for SEO

### **Currency Settings**
- **`currency`**: Currency code (e.g., "USD", "EUR", "GBP")
- **`currency_symbol`**: Currency symbol (e.g., "$", "€", "£")
- **`currency_position`**: Position of symbol ("before" or "after")
- **`formatted_currency`**: Ready-to-use formatted currency string

### **Store Status**
- **`store_enabled`**: Whether the store is operational
- **`store_mode`**: Current store mode
  - `"live"`: Store is fully operational
  - `"maintenance"`: Store is in maintenance mode
  - `"coming_soon"`: Store is in coming soon mode
- **`maintenance_message`**: Message to display during maintenance

### **Business Hours**
- **`business_hours`**: Object with days of the week
  - Each day contains:
    - `open`: Opening time (24-hour format)
    - `close`: Closing time (24-hour format)
    - `closed`: Boolean indicating if closed all day

### **Analytics & Tracking**
- **`google_analytics_id`**: Google Analytics tracking ID
- **`facebook_pixel_id`**: Facebook Pixel ID for tracking

---

## 💻 Usage Examples

### **JavaScript/Fetch**
```javascript
// Get public site settings
const getSiteSettings = async () => {
    try {
        const response = await fetch('/api/site-settings/public', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error('Failed to fetch site settings');
        }
    } catch (error) {
        console.error('Error fetching site settings:', error);
        return null;
    }
};

// Usage example
const configureSite = async () => {
    const settings = await getSiteSettings();
    
    if (settings) {
        // Configure site title
        document.title = settings.meta_title || settings.title;
        
        // Set favicon
        if (settings.favicon) {
            const favicon = document.querySelector('link[rel="icon"]');
            if (favicon) favicon.href = settings.favicon;
        }
        
        // Configure meta tags
        if (settings.meta_description) {
            const metaDesc = document.querySelector('meta[name="description"]');
            if (metaDesc) metaDesc.content = settings.meta_description;
        }
        
        // Set header logo
        if (settings.header_logo) {
            const headerLogo = document.querySelector('#header-logo');
            if (headerLogo) headerLogo.src = settings.header_logo;
        }
        
        // Configure currency display
        const currencyFormatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: settings.currency
        });
        
        // Check store status
        if (!settings.store_enabled || settings.store_mode !== 'live') {
            // Handle maintenance mode or store disabled
            if (settings.store_mode === 'maintenance') {
                showMaintenanceMessage(settings.maintenance_message);
            }
        }
    }
};
```

### **React Hook Example**
```javascript
import { useState, useEffect } from 'react';

const useSiteSettings = () => {
    const [settings, setSettings] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchSettings = async () => {
            try {
                const response = await fetch('/api/site-settings/public');
                const result = await response.json();
                
                if (result.success) {
                    setSettings(result.data);
                } else {
                    setError('Failed to fetch site settings');
                }
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchSettings();
    }, []);

    return { settings, loading, error };
};

// Usage in component
const Header = () => {
    const { settings, loading } = useSiteSettings();

    if (loading) return <div>Loading...</div>;

    return (
        <header>
            <img src={settings?.header_logo} alt={settings?.title} />
            <h1>{settings?.title}</h1>
            <p>{settings?.tagline}</p>
        </header>
    );
};
```

### **Vue.js Composition API Example**
```javascript
import { ref, onMounted } from 'vue';

export const useSiteSettings = () => {
    const settings = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const fetchSettings = async () => {
        try {
            const response = await fetch('/api/site-settings/public');
            const result = await response.json();
            
            if (result.success) {
                settings.value = result.data;
            } else {
                error.value = 'Failed to fetch site settings';
            }
        } catch (err) {
            error.value = err.message;
        } finally {
            loading.value = false;
        }
    };

    onMounted(fetchSettings);

    return { settings, loading, error, refetch: fetchSettings };
};
```

### **cURL Example**
```bash
# Get public site settings
curl -X GET "http://localhost:8000/api/site-settings/public" \
  -H "Accept: application/json"
```

---

## 🎨 Frontend Integration Use Cases

### **1. Site Branding**
```javascript
// Set site title and favicon
document.title = settings.meta_title || settings.title;
document.querySelector('link[rel="icon"]').href = settings.favicon;

// Display logos
document.querySelector('#header-logo').src = settings.header_logo;
document.querySelector('#footer-logo').src = settings.footer_logo;
```

### **2. Contact Information Display**
```javascript
// Display contact info in footer
const contactInfo = `
    <div class="contact-info">
        <p>📞 ${settings.contact_number}</p>
        <p>✉️ ${settings.email}</p>
        <p>📍 ${settings.address}</p>
    </div>
`;
```

### **3. Social Media Links**
```javascript
// Generate social media links
const socialLinks = Object.entries(settings.social_links)
    .filter(([platform, url]) => url)
    .map(([platform, url]) => `
        <a href="${url}" target="_blank" class="social-link ${platform}">
            <i class="fab fa-${platform}"></i>
        </a>
    `).join('');
```

### **4. Business Hours Display**
```javascript
// Display business hours
const formatBusinessHours = (hours) => {
    return Object.entries(hours).map(([day, schedule]) => {
        const dayName = day.charAt(0).toUpperCase() + day.slice(1);
        if (schedule.closed) {
            return `${dayName}: Closed`;
        }
        return `${dayName}: ${schedule.open} - ${schedule.close}`;
    }).join('<br>');
};
```

### **5. Currency Formatting**
```javascript
// Format prices with site currency
const formatPrice = (amount) => {
    if (settings.currency_position === 'before') {
        return `${settings.currency_symbol}${amount}`;
    } else {
        return `${amount}${settings.currency_symbol}`;
    }
};
```

### **6. Store Status Check**
```javascript
// Check if store is operational
const isStoreOpen = () => {
    if (!settings.store_enabled) return false;
    if (settings.store_mode !== 'live') return false;
    return true;
};

// Handle maintenance mode
if (settings.store_mode === 'maintenance') {
    showMaintenancePage(settings.maintenance_message);
}
```

### **7. SEO Meta Tags**
```javascript
// Set SEO meta tags
const setMetaTags = () => {
    // Title
    document.title = settings.meta_title;
    
    // Description
    let metaDesc = document.querySelector('meta[name="description"]');
    if (!metaDesc) {
        metaDesc = document.createElement('meta');
        metaDesc.name = 'description';
        document.head.appendChild(metaDesc);
    }
    metaDesc.content = settings.meta_description;
    
    // Keywords
    let metaKeywords = document.querySelector('meta[name="keywords"]');
    if (!metaKeywords) {
        metaKeywords = document.createElement('meta');
        metaKeywords.name = 'keywords';
        document.head.appendChild(metaKeywords);
    }
    metaKeywords.content = settings.meta_keywords;
};
```

### **8. Analytics Integration**
```javascript
// Google Analytics
if (settings.google_analytics_id) {
    gtag('config', settings.google_analytics_id);
}

// Facebook Pixel
if (settings.facebook_pixel_id) {
    fbq('init', settings.facebook_pixel_id);
}
```

---

## 🔄 Caching Recommendations

### **Frontend Caching**
```javascript
// Cache settings in localStorage for better performance
const CACHE_KEY = 'site_settings';
const CACHE_DURATION = 30 * 60 * 1000; // 30 minutes

const getCachedSettings = () => {
    const cached = localStorage.getItem(CACHE_KEY);
    if (cached) {
        const { data, timestamp } = JSON.parse(cached);
        if (Date.now() - timestamp < CACHE_DURATION) {
            return data;
        }
    }
    return null;
};

const setCachedSettings = (settings) => {
    localStorage.setItem(CACHE_KEY, JSON.stringify({
        data: settings,
        timestamp: Date.now()
    }));
};
```

---

## ⚠️ Error Handling

### **Error Response (500)**
```json
{
    "success": false,
    "message": "Failed to retrieve public site settings",
    "error": "Database connection failed"
}
```

### **Graceful Fallbacks**
```javascript
const getSettingsWithFallback = async () => {
    try {
        const settings = await getSiteSettings();
        return settings || getDefaultSettings();
    } catch (error) {
        console.error('Failed to load site settings:', error);
        return getDefaultSettings();
    }
};

const getDefaultSettings = () => ({
    title: 'My Store',
    currency: 'USD',
    currency_symbol: '$',
    currency_position: 'before',
    store_enabled: true,
    store_mode: 'live'
});
```

---

## 🚀 Performance Tips

1. **Cache the response** in localStorage/sessionStorage
2. **Load settings early** in your app initialization
3. **Use fallback values** for critical settings
4. **Implement retry logic** for failed requests
5. **Consider CDN caching** for static assets (logos, favicon)

This public API provides everything needed to configure and customize your customer-facing e-commerce site with dynamic, admin-controlled settings!