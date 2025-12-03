# Dropship Client-Side Integration Documentation

## Overview

This documentation covers client-side (frontend) integration for displaying dropship-sourced products to customers. The client side focuses on displaying imported products that have been processed and stored in your local database.

---

## Important Notes

⚠️ **Security**: Client-side should NOT directly access the Dropship sourcing API. All sourcing operations should be done through the admin panel.

✅ **Client Access**: Clients access products through your standard product API after products have been imported by administrators.

---

## Product Display Flow

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Admin Panel   │────▶│  Import Product │────▶│  Local Database │
│  (Dropship API) │     │   (Transform)   │     │   (Products)    │
└─────────────────┘     └─────────────────┘     └─────────────────┘
                                                        │
                                                        ▼
                                               ┌─────────────────┐
                                               │   Client App    │
                                               │ (Standard API)  │
                                               └─────────────────┘
```

---

## Client API Endpoints

### 1. Get Products (Imported from Dropship)

**Endpoint:** `GET /api/products`

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| category_id | integer | No | Filter by category |
| search | string | No | Search keyword |
| page | integer | No | Page number |
| per_page | integer | No | Items per page |
| sort | string | No | `price_asc`, `price_desc`, `newest` |

**Example Request:**
```javascript
// Client-side - React/Next.js
const getProducts = async (filters = {}) => {
  const params = new URLSearchParams({
    page: filters.page || 1,
    per_page: filters.perPage || 20,
    ...(filters.category && { category_id: filters.category }),
    ...(filters.search && { search: filters.search }),
    ...(filters.sort && { sort: filters.sort }),
  });

  const response = await fetch(`/api/products?${params}`, {
    headers: { 'Accept': 'application/json' },
  });
  return response.json();
};
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Ceramic Mug with Lid",
      "slug": "ceramic-mug-with-lid",
      "description": "High quality ceramic mug...",
      "price": "15.99",
      "compare_at_price": "19.99",
      "currency": "USD",
      "stock": 150,
      "images": [
        {"id": 1, "url": "https://your-cdn.com/products/mug-1.jpg", "alt": "Mug front"},
        {"id": 2, "url": "https://your-cdn.com/products/mug-2.jpg", "alt": "Mug side"}
      ],
      "variants": [
        {"id": 1, "name": "Green - Large", "price": "15.99", "stock": 80},
        {"id": 2, "name": "Blue - Medium", "price": "13.99", "stock": 70}
      ],
      "category": {"id": 5, "name": "Kitchen"},
      "rating": 4.5,
      "reviews_count": 23
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  }
}
```

---

### 2. Get Single Product

**Endpoint:** `GET /api/products/{slug}`

**Example Request:**
```javascript
const getProduct = async (slug) => {
  const response = await fetch(`/api/products/${slug}`, {
    headers: { 'Accept': 'application/json' },
  });
  return response.json();
};
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Ceramic Mug with Lid",
    "slug": "ceramic-mug-with-lid",
    "description": "High quality ceramic mug with bamboo lid...",
    "short_description": "Eco-friendly ceramic mug",
    "price": "15.99",
    "compare_at_price": "19.99",
    "currency": "USD",
    "stock": 150,
    "sku": "MUG-001",
    "images": [
      {"id": 1, "url": "https://...", "alt": "Front view", "is_primary": true}
    ],
    "video_url": "https://...",
    "variants": [
      {
        "id": 1,
        "sku": "MUG-001-GRN-L",
        "name": "Green - Large",
        "price": "15.99",
        "stock": 80,
        "image": "https://...",
        "options": {"color": "Green", "size": "Large"}
      }
    ],
    "attributes": [
      {"name": "Material", "value": "Ceramic"},
      {"name": "Capacity", "value": "350ml"}
    ],
    "category": {"id": 5, "name": "Kitchen", "slug": "kitchen"},
    "related_products": [
      {"id": 2, "name": "Bamboo Coaster", "price": "5.99", "image": "https://..."}
    ]
  }
}
```

---

### 3. Get Product Variants

**Endpoint:** `GET /api/products/{slug}/variants`

**Example Request:**
```javascript
const getVariants = async (slug) => {
  const response = await fetch(`/api/products/${slug}/variants`);
  return response.json();
};
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "sku": "MUG-001-GRN-L",
      "price": "15.99",
      "compare_at_price": "19.99",
      "stock": 80,
      "image": "https://...",
      "options": {
        "color": "Green",
        "size": "Large"
      },
      "is_available": true
    }
  ]
}
```

---

### 4. Add to Cart

**Endpoint:** `POST /api/cart/items`

**Request Body:**
```json
{
  "product_id": 1,
  "variant_id": 1,
  "quantity": 2
}
```

**Example Request:**
```javascript
const addToCart = async (productId, variantId, quantity = 1) => {
  const response = await fetch('/api/cart/items', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      product_id: productId,
      variant_id: variantId,
      quantity: quantity,
    }),
  });
  return response.json();
};
```

---

## React/Next.js Integration Examples

### Product List Component

```jsx
import { useState, useEffect } from 'react';

function ProductList({ categoryId }) {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);

  useEffect(() => {
    fetchProducts();
  }, [categoryId, page]);

  const fetchProducts = async () => {
    setLoading(true);
    try {
      const data = await getProducts({
        category: categoryId,
        page,
        perPage: 20
      });
      setProducts(data.data);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <ProductSkeleton count={8} />;

  return (
    <div className="product-grid">
      {products.map((product) => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
}
```

### Product Detail Component

```jsx
function ProductDetail({ slug }) {
  const [product, setProduct] = useState(null);
  const [selectedVariant, setSelectedVariant] = useState(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    getProduct(slug).then((data) => {
      setProduct(data.data);
      setSelectedVariant(data.data.variants[0]);
    });
  }, [slug]);

  const handleAddToCart = async () => {
    await addToCart(product.id, selectedVariant.id, quantity);
    // Show success notification
  };

  if (!product) return <LoadingSpinner />;

  return (
    <div className="product-detail">
      <ProductGallery images={product.images} video={product.video_url} />

      <div className="product-info">
        <h1>{product.name}</h1>
        <div className="price">
          <span className="current">${selectedVariant?.price || product.price}</span>
          {product.compare_at_price && (
            <span className="compare">${product.compare_at_price}</span>
          )}
        </div>

        <VariantSelector
          variants={product.variants}
          selected={selectedVariant}
          onSelect={setSelectedVariant}
        />

        <QuantitySelector value={quantity} onChange={setQuantity} />

        <button
          onClick={handleAddToCart}
          disabled={!selectedVariant?.is_available}
        >
          Add to Cart
        </button>

        <div className="description">{product.description}</div>

        <ProductAttributes attributes={product.attributes} />
      </div>
    </div>
  );
}
```

### Variant Selector Component

```jsx
function VariantSelector({ variants, selected, onSelect }) {
  // Group variants by option type
  const options = useMemo(() => {
    const grouped = {};
    variants.forEach((variant) => {
      Object.entries(variant.options).forEach(([key, value]) => {
        if (!grouped[key]) grouped[key] = new Set();
        grouped[key].add(value);
      });
    });
    return Object.entries(grouped).map(([name, values]) => ({
      name,
      values: Array.from(values),
    }));
  }, [variants]);

  return (
    <div className="variant-selector">
      {options.map((option) => (
        <div key={option.name} className="option-group">
          <label>{option.name}</label>
          <div className="option-values">
            {option.values.map((value) => (
              <button
                key={value}
                className={selected?.options[option.name] === value ? 'active' : ''}
                onClick={() => {
                  const newVariant = variants.find(
                    (v) => v.options[option.name] === value
                  );
                  if (newVariant) onSelect(newVariant);
                }}
              >
                {value}
              </button>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}
```

---

## Vue.js Integration Examples

### Product List (Vue 3 Composition API)

```vue
<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps(['categoryId']);
const products = ref([]);
const loading = ref(true);
const page = ref(1);

const fetchProducts = async () => {
  loading.value = true;
  try {
    const response = await fetch(
      `/api/products?category_id=${props.categoryId}&page=${page.value}`
    );
    const data = await response.json();
    products.value = data.data;
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProducts);
watch([() => props.categoryId, page], fetchProducts);
</script>

<template>
  <div class="product-grid">
    <ProductCard
      v-for="product in products"
      :key="product.id"
      :product="product"
    />
  </div>
</template>
```

---

## API Client Service

Create a centralized API service for clean code organization:

```javascript
// services/api.js
const API_BASE = process.env.NEXT_PUBLIC_API_URL || '/api';

class ApiService {
  async request(endpoint, options = {}) {
    const response = await fetch(`${API_BASE}${endpoint}`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error(`API Error: ${response.status}`);
    }

    return response.json();
  }

  // Products
  getProducts(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/products?${query}`);
  }

  getProduct(slug) {
    return this.request(`/products/${slug}`);
  }

  // Cart
  getCart() {
    return this.request('/cart');
  }

  addToCart(productId, variantId, quantity) {
    return this.request('/cart/items', {
      method: 'POST',
      body: JSON.stringify({ product_id: productId, variant_id: variantId, quantity }),
    });
  }
}

export const api = new ApiService();
```

---

## Error Handling

```javascript
// Handle API errors gracefully
const fetchWithErrorHandling = async (fetcher) => {
  try {
    return await fetcher();
  } catch (error) {
    if (error.message.includes('404')) {
      // Product not found
      return null;
    }
    if (error.message.includes('401')) {
      // Redirect to login
      window.location.href = '/login';
    }
    // Show error notification
    toast.error('Something went wrong. Please try again.');
    throw error;
  }
};
```

---

## SEO Considerations

For dropship products, ensure proper SEO:

```jsx
// Next.js Head component
import Head from 'next/head';

function ProductHead({ product }) {
  return (
    <Head>
      <title>{product.name} | Your Store</title>
      <meta name="description" content={product.short_description} />
      <meta property="og:title" content={product.name} />
      <meta property="og:image" content={product.images[0]?.url} />
      <meta property="og:type" content="product" />
      <meta property="product:price:amount" content={product.price} />
      <meta property="product:price:currency" content={product.currency} />
    </Head>
  );
}
```

---

## Performance Tips

1. **Image Optimization**: Use CDN-hosted images with proper sizing
2. **Lazy Loading**: Load product images lazily for better performance
3. **Caching**: Implement proper caching for product data
4. **Pagination**: Use cursor-based pagination for large catalogs
5. **Skeleton Loading**: Show skeleton UI while loading products
```

