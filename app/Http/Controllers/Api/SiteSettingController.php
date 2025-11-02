<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
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
                    'header_logo' => $settings->header_logo ? Storage::url($settings->header_logo) : null,
                    'footer_logo' => $settings->footer_logo ? Storage::url($settings->footer_logo) : null,
                    'favicon' => $settings->favicon ? Storage::url($settings->favicon) : null,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'shipping_cost' => $settings->shipping_cost,
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
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'tagline' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'contact_number' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|max:255',
                'support_email' => 'sometimes|email|max:255',
                'address' => 'sometimes|string',
                'business_name' => 'sometimes|string|max:255',
                'business_registration_number' => 'sometimes|string|max:100',
                'tax_number' => 'sometimes|string|max:100',
                'header_logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'footer_logo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'favicon' => 'sometimes|image|mimes:ico,png|max:1024',
                'social_links' => 'sometimes|array',
                'social_links.facebook' => 'sometimes|url',
                'social_links.twitter' => 'sometimes|url',
                'social_links.instagram' => 'sometimes|url',
                'social_links.linkedin' => 'sometimes|url',
                'social_links.youtube' => 'sometimes|url',
                'social_links.tiktok' => 'sometimes|url',
                'social_links.whatsapp' => 'sometimes|string',
                'meta_title' => 'sometimes|string|max:255',
                'meta_description' => 'sometimes|string|max:500',
                'meta_keywords' => 'sometimes|string',
                'currency' => 'sometimes|string|size:3',
                'currency_symbol' => 'sometimes|string|max:10',
                'currency_position' => 'sometimes|in:before,after',
                'shipping_cost' => 'sometimes|numeric|min:0',
                'free_shipping_threshold' => 'sometimes|nullable|numeric|min:0',
                'tax_rate' => 'sometimes|numeric|min:0|max:100',
                'tax_inclusive' => 'sometimes|boolean',
                'store_enabled' => 'sometimes|boolean',
                'store_mode' => 'sometimes|in:live,maintenance,coming_soon',
                'maintenance_message' => 'sometimes|string',
                'business_hours' => 'sometimes|array',
                'payment_methods' => 'sometimes|array',
                'shipping_methods' => 'sometimes|array',
                'accepted_countries' => 'sometimes|array',
                'email_notifications' => 'sometimes|boolean',
                'sms_notifications' => 'sometimes|boolean',
                'notification_email' => 'sometimes|email|max:255',
                'google_analytics_id' => 'sometimes|string|max:50',
                'facebook_pixel_id' => 'sometimes|string|max:50',
                'custom_scripts' => 'sometimes|string',
                'terms_of_service' => 'sometimes|string',
                'privacy_policy' => 'sometimes|string',
                'return_policy' => 'sometimes|string',
                'shipping_policy' => 'sometimes|string',
                'additional_settings' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = SiteSetting::getInstance();
            $data = $request->except(['header_logo', 'footer_logo', 'favicon']);

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
                    'header_logo' => $settings->header_logo ? Storage::url($settings->header_logo) : null,
                    'footer_logo' => $settings->footer_logo ? Storage::url($settings->footer_logo) : null,
                    'favicon' => $settings->favicon ? Storage::url($settings->favicon) : null,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'shipping_cost' => $settings->shipping_cost,
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
                    'header_logo' => $settings->header_logo ? Storage::url($settings->header_logo) : null,
                    'footer_logo' => $settings->footer_logo ? Storage::url($settings->footer_logo) : null,
                    'favicon' => $settings->favicon ? Storage::url($settings->favicon) : null,
                    'social_links' => $settings->social_links_with_defaults,
                    'meta_title' => $settings->meta_title,
                    'meta_description' => $settings->meta_description,
                    'meta_keywords' => $settings->meta_keywords,
                    'currency' => $settings->currency,
                    'currency_symbol' => $settings->currency_symbol,
                    'currency_position' => $settings->currency_position,
                    'formatted_currency' => $settings->formatted_currency,
                    'store_enabled' => $settings->store_enabled,
                    'store_mode' => $settings->store_mode,
                    'maintenance_message' => $settings->maintenance_message,
                    'business_hours' => $settings->business_hours_with_defaults,
                    'google_analytics_id' => $settings->google_analytics_id,
                    'facebook_pixel_id' => $settings->facebook_pixel_id,
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
}
