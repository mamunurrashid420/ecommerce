<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * Display the site settings.
     */
    public function show(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'message' => 'Site settings retrieved successfully',
                'data' => [
                    'id' => $settings->id,
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'contact_number' => $settings->contact_number,
                    'email' => $settings->email,
                    'support_email' => $settings->support_email,
                    'address' => $settings->address,
                    'business_name' => $settings->business_name,
                    'business_registration_number' => $settings->business_registration_number,
                    'tax_number' => $settings->tax_number,
                    'header_logo' => $settings->header_logo_url,
                    'footer_logo' => $settings->footer_logo_url,
                    'favicon' => $settings->favicon_url,
                    'slider_images' => $settings->slider_images_urls,
                    'offer' => $settings->offer_with_url,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'price_margin' => $settings->price_margin,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_rate' => $settings->currency_rate,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'min_product_quantity' => $settings->min_product_quantity,
                    'min_order_amount' => $settings->min_order_amount,
                    'shipping_cost' => $settings->shipping_cost,
                    'shipping_cost_by_ship' => $settings->shipping_cost_by_ship,
                    'shipping_duration_by_ship' => $settings->shipping_duration_by_ship,
                    'shipping_cost_by_air' => $settings->shipping_cost_by_air,
                    'shipping_duration_by_air' => $settings->shipping_duration_by_air,
                    'free_shipping_threshold' => $settings->free_shipping_threshold,
                    'tax_rate' => $settings->tax_rate,
                    'tax_inclusive' => $settings->tax_inclusive,
                    'store_enabled' => $settings->store_enabled,
                    'store_mode' => $settings->store_mode,
                    'maintenance_message' => $settings->maintenance_message,
                    'business_hours' => $settings->business_hours_with_defaults,
                    'payment_methods' => $settings->payment_methods,
                    'shipping_methods' => $settings->shipping_methods,
                    'accepted_countries' => $settings->accepted_countries,
                    'email_notifications' => $settings->email_notifications,
                    'sms_notifications' => $settings->sms_notifications,
                    'notification_email' => $settings->notification_email,
                    'google_analytics_id' => $settings->google_analytics_id,
                    'facebook_pixel_id' => $settings->facebook_pixel_id,
                    'custom_scripts' => $settings->custom_scripts,
                    'terms_of_service' => $settings->terms_of_service,
                    'privacy_policy' => $settings->privacy_policy,
                    'return_policy' => $settings->return_policy,
                    'shipping_policy' => $settings->shipping_policy,
                    'additional_settings' => $settings->additional_settings,
                    'created_at' => $settings->created_at,
                    'updated_at' => $settings->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve site settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create or update site settings.
     */
    public function createOrUpdate(Request $request): JsonResponse
    {
        try {
            // Prepare validation rules
            $rules = [
                'title' => 'nullable|string|max:255',
                'tagline' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'contact_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'support_email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'business_name' => 'nullable|string|max:255',
                'business_registration_number' => 'nullable|string|max:100',
                'tax_number' => 'nullable|string|max:100',
                'social_links' => 'nullable|array',
                'social_links.facebook' => 'nullable|url',
                'social_links.twitter' => 'nullable|url',
                'social_links.instagram' => 'nullable|url',
                'social_links.linkedin' => 'nullable|url',
                'social_links.youtube' => 'nullable|url',
                'social_links.tiktok' => 'nullable|url',
                'social_links.whatsapp' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string',
                'currency' => 'nullable|string|size:3',
                'currency_symbol' => 'nullable|string|max:10',
                'currency_rate' => 'nullable|numeric|min:0',
                'currency_position' => 'nullable|in:before,after',
                'price_margin' => 'nullable|numeric|min:0',
                'min_product_quantity' => 'nullable|integer|min:1',
                'min_order_amount' => 'nullable|numeric|min:0',
                'shipping_cost' => 'nullable|numeric|min:0',
                'shipping_cost_by_ship' => 'nullable|numeric|min:0',
                'shipping_duration_by_ship' => 'nullable|string|max:255',
                'shipping_cost_by_air' => 'nullable|numeric|min:0',
                'shipping_duration_by_air' => 'nullable|string|max:255',
                'free_shipping_threshold' => 'nullable|numeric|min:0',
                'tax_rate' => 'nullable|numeric|min:0|max:100',
                'tax_inclusive' => 'nullable|boolean',
                'store_enabled' => 'nullable|boolean',
                'store_mode' => 'nullable|in:live,maintenance,coming_soon',
                'maintenance_message' => 'nullable|string',
                'business_hours' => 'nullable|array',
                'payment_methods' => 'nullable|array',
                'shipping_methods' => 'nullable|array',
                'accepted_countries' => 'nullable|array',
                'email_notifications' => 'nullable|boolean',
                'sms_notifications' => 'nullable|boolean',
                'notification_email' => 'nullable|email|max:255',
                'google_analytics_id' => 'nullable|string|max:50',
                'facebook_pixel_id' => 'nullable|string|max:50',
                'custom_scripts' => 'nullable|string',
                'terms_of_service' => 'nullable|string',
                'privacy_policy' => 'nullable|string',
                'return_policy' => 'nullable|string',
                'shipping_policy' => 'nullable|string',
                'additional_settings' => 'nullable|array',
                'offer' => 'nullable|array',
                'offer.offer_name' => 'nullable|string|max:255',
                'offer.description' => 'nullable|string',
                'offer.amount' => 'nullable|numeric|min:0',
                'offer.promotional_image' => 'nullable|string',
                'offer.start_date' => 'nullable|date',
                'offer.end_date' => 'nullable|date|after:offer.start_date',
            ];

            // Add conditional validation for logo fields
            // Only validate as image if it's actually a file upload, ignore if it's a string (existing path)
            if ($request->hasFile('header_logo')) {
                $rules['header_logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            } elseif ($request->has('header_logo') && is_string($request->header_logo)) {
                $rules['header_logo'] = 'nullable|string';
            }

            if ($request->hasFile('footer_logo')) {
                $rules['footer_logo'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            } elseif ($request->has('footer_logo') && is_string($request->footer_logo)) {
                $rules['footer_logo'] = 'nullable|string';
            }

            if ($request->hasFile('favicon')) {
                $rules['favicon'] = 'nullable|image|mimes:ico,png|max:1024';
            } elseif ($request->has('favicon') && is_string($request->favicon)) {
                $rules['favicon'] = 'nullable|string';
            }

            // Validation for promotional image upload
            if ($request->hasFile('promotional_image')) {
                $rules['promotional_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            }

            // Validation for slider images
            if ($request->hasFile('slider_images')) {
                $rules['slider_images'] = 'nullable|array';
                $rules['slider_images.*'] = 'image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                $rules['slider_titles'] = 'nullable|array';
                $rules['slider_titles.*'] = 'nullable|string|max:255';
                $rules['slider_subtitles'] = 'nullable|array';
                $rules['slider_subtitles.*'] = 'nullable|string|max:500';
                $rules['slider_hyperlinks'] = 'nullable|array';
                $rules['slider_hyperlinks.*'] = 'nullable|url|max:500';
            } elseif ($request->has('slider_images')) {
                // Normalize slider_images to ensure it's an array
                $sliderImages = $request->input('slider_images');
                
                // If it's a JSON string, decode it
                if (is_string($sliderImages)) {
                    $decoded = json_decode($sliderImages, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $request->merge(['slider_images' => $decoded]);
                        $sliderImages = $decoded;
                    } else {
                        // If it's not valid JSON, treat as empty array
                        $request->merge(['slider_images' => []]);
                        $sliderImages = [];
                    }
                }
                
                // Ensure it's an array
                if (!is_array($sliderImages)) {
                    $request->merge(['slider_images' => []]);
                    $sliderImages = [];
                }
                
                $rules['slider_images'] = 'nullable|array';
                
                // Only add nested validation rules if array is not empty
                if (!empty($sliderImages)) {
                    $rules['slider_images.*.image'] = 'nullable|string';
                    $rules['slider_images.*.title'] = 'nullable|string|max:255';
                    $rules['slider_images.*.subtitle'] = 'nullable|string|max:500';
                    $rules['slider_images.*.hyperlink'] = 'nullable|url|max:500';
                }
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = SiteSetting::getInstance();
            $data = $request->except(['header_logo', 'footer_logo', 'favicon', 'slider_images', 'promotional_image']);

            // Handle file uploads
            if ($request->hasFile('header_logo')) {
                if ($settings->header_logo) {
                    Storage::delete($settings->header_logo);
                }
                $data['header_logo'] = $request->file('header_logo')->store('logos', 'public');
            }

            if ($request->hasFile('footer_logo')) {
                if ($settings->footer_logo) {
                    Storage::delete($settings->footer_logo);
                }
                $data['footer_logo'] = $request->file('footer_logo')->store('logos', 'public');
            }

            if ($request->hasFile('favicon')) {
                if ($settings->favicon) {
                    Storage::delete($settings->favicon);
                }
                $data['favicon'] = $request->file('favicon')->store('logos', 'public');
            }

            // Handle promotional image upload for offer
            if ($request->hasFile('promotional_image')) {
                $existingOffer = $settings->offer ?? [];
                if (isset($existingOffer['promotional_image'])) {
                    Storage::delete($existingOffer['promotional_image']);
                }
                $promotionalImagePath = $request->file('promotional_image')->store('offers', 'public');
                
                // Update or create offer data with new promotional image
                $offerData = $request->input('offer', []);
                $offerData['promotional_image'] = $promotionalImagePath;
                $data['offer'] = $offerData;
            }

            // Handle slider images uploads
            if ($request->hasFile('slider_images')) {
                // Get existing slider images (don't delete them)
                $existingImages = $settings->slider_images ?? [];
                if (!is_array($existingImages)) {
                    $existingImages = [];
                }
                
                // Upload new slider images with titles, subtitles, and hyperlinks
                $uploadedImages = [];
                $files = $request->file('slider_images');
                $titles = $request->input('slider_titles', []);
                $subtitles = $request->input('slider_subtitles', []);
                $hyperlinks = $request->input('slider_hyperlinks', []);
                
                foreach ($files as $index => $image) {
                    $imagePath = $image->store('sliders', 'public');
                    $uploadedImages[] = [
                        'image' => $imagePath,
                        'title' => $titles[$index] ?? null,
                        'subtitle' => $subtitles[$index] ?? null,
                        'hyperlink' => $hyperlinks[$index] ?? null,
                    ];
                }
                
                // Append new images to existing ones instead of replacing
                $data['slider_images'] = array_merge($existingImages, $uploadedImages);
            } elseif ($request->has('slider_images')) {
                // If slider_images is provided as an array (for updating/reordering/deleting)
                $newImages = $request->input('slider_images', []);
                
                // Normalize to ensure it's an array
                if (is_string($newImages)) {
                    $decoded = json_decode($newImages, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $newImages = $decoded;
                    } else {
                        $newImages = [];
                    }
                }
                
                if (!is_array($newImages)) {
                    $newImages = [];
                }
                
                $existingImages = $settings->slider_images ?? [];
                if (!is_array($existingImages)) {
                    $existingImages = [];
                }
                
                // Find images that were removed and delete them
                if (!empty($existingImages) && !empty($newImages)) {
                    $existingPaths = array_map(function ($item) {
                        return is_array($item) ? ($item['image'] ?? null) : $item;
                    }, $existingImages);
                    
                    $newPaths = array_map(function ($item) {
                        return is_array($item) ? ($item['image'] ?? null) : $item;
                    }, $newImages);
                    
                    $removedPaths = array_diff($existingPaths, $newPaths);
                    foreach ($removedPaths as $removedPath) {
                        if ($removedPath) {
                            Storage::delete($removedPath);
                        }
                    }
                } elseif (empty($newImages) && !empty($existingImages)) {
                    // If new images is empty but existing images exist, delete all existing images
                    foreach ($existingImages as $item) {
                        $imagePath = is_array($item) ? ($item['image'] ?? null) : $item;
                        if ($imagePath) {
                            Storage::delete($imagePath);
                        }
                    }
                }
                
                // Ensure all items are in the correct format
                $formattedImages = [];
                if (!empty($newImages)) {
                    $formattedImages = array_map(function ($item) {
                        if (is_string($item)) {
                            // Legacy format: convert to new format
                            return [
                                'image' => $item,
                                'title' => null,
                                'subtitle' => null,
                                'hyperlink' => null,
                            ];
                        }
                        // New format: ensure all fields are present
                        return [
                            'image' => $item['image'] ?? null,
                            'title' => $item['title'] ?? null,
                            'subtitle' => $item['subtitle'] ?? null,
                            'hyperlink' => $item['hyperlink'] ?? null,
                        ];
                    }, $newImages);
                }
                
                $data['slider_images'] = $formattedImages;
            }

            $settings->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Site settings updated successfully',
                'data' => [
                    'id' => $settings->id,
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'contact_number' => $settings->contact_number,
                    'email' => $settings->email,
                    'support_email' => $settings->support_email,
                    'address' => $settings->address,
                    'business_name' => $settings->business_name,
                    'business_registration_number' => $settings->business_registration_number,
                    'tax_number' => $settings->tax_number,
                    'header_logo' => $settings->header_logo_url,
                    'footer_logo' => $settings->footer_logo_url,
                    'favicon' => $settings->favicon_url,
                    'slider_images' => $settings->slider_images_urls,
                    'offer' => $settings->offer_with_url,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'price_margin' => $settings->price_margin,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_rate' => $settings->currency_rate,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'min_product_quantity' => $settings->min_product_quantity,
                    'min_order_amount' => $settings->min_order_amount,
                    'shipping_cost' => $settings->shipping_cost,
                    'shipping_cost_by_ship' => $settings->shipping_cost_by_ship,
                    'shipping_duration_by_ship' => $settings->shipping_duration_by_ship,
                    'shipping_cost_by_air' => $settings->shipping_cost_by_air,
                    'shipping_duration_by_air' => $settings->shipping_duration_by_air,
                    'free_shipping_threshold' => $settings->free_shipping_threshold,
                    'tax_rate' => $settings->tax_rate,
                    'tax_inclusive' => $settings->tax_inclusive,
                    'store_enabled' => $settings->store_enabled,
                    'store_mode' => $settings->store_mode,
                    'maintenance_message' => $settings->maintenance_message,
                    'business_hours' => $settings->business_hours_with_defaults,
                    'payment_methods' => $settings->payment_methods,
                    'shipping_methods' => $settings->shipping_methods,
                    'accepted_countries' => $settings->accepted_countries,
                    'email_notifications' => $settings->email_notifications,
                    'sms_notifications' => $settings->sms_notifications,
                    'notification_email' => $settings->notification_email,
                    'google_analytics_id' => $settings->google_analytics_id,
                    'facebook_pixel_id' => $settings->facebook_pixel_id,
                    'custom_scripts' => $settings->custom_scripts,
                    'terms_of_service' => $settings->terms_of_service,
                    'privacy_policy' => $settings->privacy_policy,
                    'return_policy' => $settings->return_policy,
                    'shipping_policy' => $settings->shipping_policy,
                    'additional_settings' => $settings->additional_settings,
                    'updated_at' => $settings->updated_at,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update site settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get public site settings (for frontend display)
     */
    public function public(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();

            // Get featured categories
            $featuredCategories = Category::active()
                ->featured()
                ->select('id', 'name', 'description', 'slug', 'image_url', 'icon', 'sort_order')
                ->withCount(['products as active_products_count' => function ($query) {
                    $query->where('is_active', true);
                }])
                ->orderBy('sort_order')
                ->limit(12)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'slug' => $category->slug,
                        'image_url' => $category->full_image_url,
                        'icon' => $category->icon,
                        'sort_order' => $category->sort_order,
                        'active_products_count' => $category->active_products_count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'title' => $settings->title,
                    'tagline' => $settings->tagline,
                    'description' => $settings->description,
                    'contact_number' => $settings->contact_number,
                    'email' => $settings->email,
                    'address' => $settings->address,
                    'business_name' => $settings->business_name,
                    'header_logo' => $settings->header_logo_url,
                    'footer_logo' => $settings->footer_logo_url,
                    'favicon' => $settings->favicon_url,
                    'slider_images' => $settings->slider_images_urls,
                    'offer' => $settings->offer_with_url,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_rate' => $settings->currency_rate,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'min_product_quantity' => $settings->min_product_quantity,
                    'min_order_amount' => $settings->min_order_amount,
                    'shipping_cost' => $settings->shipping_cost,
                    'shipping_cost_by_ship' => $settings->shipping_cost_by_ship,
                    'shipping_duration_by_ship' => $settings->shipping_duration_by_ship,
                    'shipping_cost_by_air' => $settings->shipping_cost_by_air,
                    'shipping_duration_by_air' => $settings->shipping_duration_by_air,
                    'free_shipping_threshold' => $settings->free_shipping_threshold,
                    'tax_rate' => $settings->tax_rate,
                    'tax_inclusive' => $settings->tax_inclusive,
                    'store_enabled' => $settings->store_enabled,
                    'store_mode' => $settings->store_mode,
                    'maintenance_message' => $settings->maintenance_message,
                    'business_hours' => $settings->business_hours_with_defaults,
                    'google_analytics_id' => $settings->google_analytics_id,
                    'facebook_pixel_id' => $settings->facebook_pixel_id,
                    'price_margin' => $settings->price_margin,
                    'terms_of_service' => $settings->terms_of_service,
                    'privacy_policy' => $settings->privacy_policy,
                    'shipping_policy' => $settings->shipping_policy,
                    'return_policy' => $settings->return_policy,
                    'featured_categories' => $featuredCategories,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve public site settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Terms of Service (Public)
     */
    public function getTermsOfService(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'terms_of_service' => $settings->terms_of_service,
                    'last_updated' => $settings->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve terms of service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Privacy Policy (Public)
     */
    public function getPrivacyPolicy(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'privacy_policy' => $settings->privacy_policy,
                    'last_updated' => $settings->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve privacy policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Return Policy (Public)
     */
    public function getReturnPolicy(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'return_policy' => $settings->return_policy,
                    'last_updated' => $settings->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve return policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Shipping Policy (Public)
     */
    public function getShippingPolicy(): JsonResponse
    {
        try {
            $settings = SiteSetting::getInstance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'shipping_policy' => $settings->shipping_policy,
                    'last_updated' => $settings->updated_at,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shipping policy',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove slider items by image paths or indices
     */
    public function removeSliderItems(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'slider_indices' => 'nullable|array',
                'slider_indices.*' => 'integer|min:0',
                'slider_paths' => 'nullable|array',
                'slider_paths.*' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = SiteSetting::getInstance();
            $existingImages = $settings->slider_images ?? [];
            
            if (!is_array($existingImages) || empty($existingImages)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No slider images found to remove'
                ], 404);
            }

            $indicesToRemove = $request->input('slider_indices', []);
            $pathsToRemove = $request->input('slider_paths', []);

            // If neither indices nor paths are provided, return error
            if (empty($indicesToRemove) && empty($pathsToRemove)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either slider_indices or slider_paths to remove items'
                ], 422);
            }

            $removedCount = 0;
            $remainingImages = [];
            $imagesToDelete = [];

            // Process removal by indices
            if (!empty($indicesToRemove)) {
                foreach ($existingImages as $index => $item) {
                    if (in_array($index, $indicesToRemove)) {
                        // Mark for deletion
                        $imagePath = is_array($item) ? ($item['image'] ?? null) : $item;
                        if ($imagePath) {
                            $imagesToDelete[] = $imagePath;
                        }
                        $removedCount++;
                    } else {
                        $remainingImages[] = $item;
                    }
                }
            } else {
                // Process removal by paths
                $remainingImages = $existingImages;
                
                foreach ($pathsToRemove as $pathToRemove) {
                    // Remove leading /storage/ or storage/ if present
                    $normalizedPath = preg_replace('#^/?storage/#', '', $pathToRemove);
                    // Also handle full URLs
                    $normalizedPath = preg_replace('#^https?://[^/]+/storage/#', '', $normalizedPath);
                    
                    foreach ($remainingImages as $key => $item) {
                        $imagePath = is_array($item) ? ($item['image'] ?? null) : $item;
                        if ($imagePath) {
                            // Normalize existing path for comparison
                            $normalizedExistingPath = preg_replace('#^/?storage/#', '', $imagePath);
                            
                            if ($normalizedPath === $normalizedExistingPath || 
                                $imagePath === $pathToRemove || 
                                $imagePath === $normalizedPath) {
                                $imagesToDelete[] = $imagePath;
                                unset($remainingImages[$key]);
                                $removedCount++;
                                break;
                            }
                        }
                    }
                }
                
                // Re-index array after removal
                $remainingImages = array_values($remainingImages);
            }

            // Delete image files from storage
            foreach ($imagesToDelete as $imagePath) {
                if ($imagePath) {
                    Storage::delete($imagePath);
                }
            }

            // Update settings with remaining images
            $settings->slider_images = $remainingImages;
            $settings->save();

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$removedCount} slider item(s)",
                'data' => [
                    'removed_count' => $removedCount,
                    'slider_images' => $settings->slider_images_urls,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove slider items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
