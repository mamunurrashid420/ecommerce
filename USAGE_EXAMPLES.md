# Site Settings Usage Examples

## Using the Helper Class

```php
<?php

use App\Helpers\SiteSettingsHelper;

// Get site title
$title = SiteSettingsHelper::title();

// Get formatted price
$price = SiteSettingsHelper::formatPrice(29.99); // Returns "$29.99"

// Check if store is open
if (SiteSettingsHelper::isOpenToday()) {
    echo "We're open!";
}

// Get social media links
$socialLinks = SiteSettingsHelper::socialLinks();
$facebookUrl = SiteSettingsHelper::socialLink('facebook');

// Check free shipping eligibility
$orderTotal = 75.00;
if (SiteSettingsHelper::qualifiesForFreeShipping($orderTotal)) {
    echo "Free shipping available!";
}

// Calculate tax
$taxAmount = SiteSettingsHelper::calculateTax(100.00);
```

## Using the Facade (after registering in config/app.php)

```php
<?php

use App\Facades\SiteSettings;

// Same methods as helper, but cleaner syntax
$title = SiteSettings::title();
$email = SiteSettings::email();
$logo = SiteSettings::headerLogo();
```

## In Blade Templates

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ SiteSettingsHelper::metaTitle() }}</title>
    <meta name="description" content="{{ SiteSettingsHelper::metaDescription() }}">
    <link rel="icon" href="{{ SiteSettingsHelper::favicon() }}">
    
    @if(SiteSettingsHelper::googleAnalyticsId())
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ SiteSettingsHelper::googleAnalyticsId() }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ SiteSettingsHelper::googleAnalyticsId() }}');
        </script>
    @endif
</head>
<body>
    <header>
        @if(SiteSettingsHelper::headerLogo())
            <img src="{{ SiteSettingsHelper::headerLogo() }}" alt="{{ SiteSettingsHelper::title() }}">
        @else
            <h1>{{ SiteSettingsHelper::title() }}</h1>
        @endif
        
        @if(SiteSettingsHelper::tagline())
            <p>{{ SiteSettingsHelper::tagline() }}</p>
        @endif
    </header>

    <main>
        @if(!SiteSettingsHelper::isStoreEnabled() || SiteSettingsHelper::isMaintenanceMode())
            <div class="maintenance-notice">
                {{ SiteSettingsHelper::maintenanceMessage() }}
            </div>
        @else
            <!-- Your store content -->
        @endif
    </main>

    <footer>
        <div class="contact-info">
            <p>Email: {{ SiteSettingsHelper::email() }}</p>
            <p>Phone: {{ SiteSettingsHelper::phone() }}</p>
            <p>Address: {{ SiteSettingsHelper::address() }}</p>
        </div>

        <div class="social-links">
            @foreach(SiteSettingsHelper::socialLinks() as $platform => $url)
                @if($url)
                    <a href="{{ $url }}" target="_blank">{{ ucfirst($platform) }}</a>
                @endif
            @endforeach
        </div>

        @if(SiteSettingsHelper::footerLogo())
            <img src="{{ SiteSettingsHelper::footerLogo() }}" alt="{{ SiteSettingsHelper::title() }}">
        @endif
    </footer>
</body>
</html>
```

## In Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Helpers\SiteSettingsHelper;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function calculateShipping(Request $request)
    {
        $orderTotal = $request->input('total');
        
        if (SiteSettingsHelper::qualifiesForFreeShipping($orderTotal)) {
            $shippingCost = 0;
        } else {
            $shippingCost = SiteSettingsHelper::shippingCost();
        }

        $taxAmount = SiteSettingsHelper::calculateTax($orderTotal);
        
        return response()->json([
            'subtotal' => $orderTotal,
            'shipping' => $shippingCost,
            'tax' => $taxAmount,
            'total' => $orderTotal + $shippingCost + $taxAmount,
            'currency' => SiteSettingsHelper::currency(),
            'currency_symbol' => SiteSettingsHelper::currencySymbol(),
        ]);
    }

    public function getPaymentMethods()
    {
        return response()->json([
            'payment_methods' => SiteSettingsHelper::enabledPaymentMethods(),
            'shipping_methods' => SiteSettingsHelper::enabledShippingMethods(),
        ]);
    }
}
```

## Middleware for Store Status

```php
<?php

namespace App\Http\Middleware;

use App\Helpers\SiteSettingsHelper;
use Closure;
use Illuminate\Http\Request;

class CheckStoreStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (!SiteSettingsHelper::isStoreEnabled()) {
            return response()->json([
                'message' => 'Store is currently disabled'
            ], 503);
        }

        if (SiteSettingsHelper::isMaintenanceMode()) {
            return response()->json([
                'message' => SiteSettingsHelper::maintenanceMessage()
            ], 503);
        }

        return $next($request);
    }
}
```

## Vue.js Component Example

```vue
<template>
  <div class="site-header">
    <img v-if="settings.header_logo" :src="settings.header_logo" :alt="settings.title">
    <h1 v-else>{{ settings.title }}</h1>
    <p v-if="settings.tagline">{{ settings.tagline }}</p>
    
    <div class="store-status" v-if="!settings.store_enabled || settings.store_mode !== 'live'">
      {{ settings.maintenance_message }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'SiteHeader',
  data() {
    return {
      settings: {}
    }
  },
  async mounted() {
    try {
      const response = await fetch('/api/site-settings/public');
      const data = await response.json();
      this.settings = data.data;
    } catch (error) {
      console.error('Failed to load site settings:', error);
    }
  }
}
</script>
```

## React Component Example

```jsx
import React, { useState, useEffect } from 'react';

const SiteHeader = () => {
  const [settings, setSettings] = useState({});
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchSettings = async () => {
      try {
        const response = await fetch('/api/site-settings/public');
        const data = await response.json();
        setSettings(data.data);
      } catch (error) {
        console.error('Failed to load site settings:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchSettings();
  }, []);

  if (loading) return <div>Loading...</div>;

  return (
    <header className="site-header">
      {settings.header_logo ? (
        <img src={settings.header_logo} alt={settings.title} />
      ) : (
        <h1>{settings.title}</h1>
      )}
      
      {settings.tagline && <p>{settings.tagline}</p>}
      
      {(!settings.store_enabled || settings.store_mode !== 'live') && (
        <div className="store-status">
          {settings.maintenance_message}
        </div>
      )}
    </header>
  );
};

export default SiteHeader;
```

## Admin Panel Integration

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::getInstance();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = SiteSetting::getInstance();
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'email' => 'required|email',
            'contact_number' => 'nullable|string|max:20',
            // ... other validation rules
        ]);

        $settings->update($validated);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}
```

## Testing

```php
<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Helpers\SiteSettingsHelper;
use Tests\TestCase;

class SiteSettingsTest extends TestCase
{
    public function test_can_get_site_settings()
    {
        $settings = SiteSetting::factory()->create([
            'title' => 'Test Store',
            'currency' => 'USD',
            'currency_symbol' => '$',
        ]);

        $this->assertEquals('Test Store', SiteSettingsHelper::title());
        $this->assertEquals('USD', SiteSettingsHelper::currency());
        $this->assertEquals('$29.99', SiteSettingsHelper::formatPrice(29.99));
    }

    public function test_public_api_endpoint()
    {
        $response = $this->get('/api/site-settings/public');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'title',
                        'tagline',
                        'email',
                        'social_links',
                        'currency',
                        'store_enabled',
                    ]
                ]);
    }
}
```

## Caching Considerations

The helper automatically caches settings for 1 hour and clears cache when settings are updated. You can customize this:

```php
// In SiteSettingsHelper.php, modify the cache duration
$settings = Cache::remember('site_settings', 7200, function () { // 2 hours
    return SiteSetting::getInstance();
});

// Or disable caching entirely for development
$settings = SiteSetting::getInstance(); // No caching
```

## Environment-Specific Settings

You can override certain settings based on environment:

```php
// In AppServiceProvider or a custom provider
public function boot()
{
    if (app()->environment('local')) {
        // Override settings for local development
        config(['app.name' => SiteSettingsHelper::title()]);
    }
}
```