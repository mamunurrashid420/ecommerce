<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsTestController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Test SMS sending - Public API for testing
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testSend(Request $request)
    {
        try {
            // Validate phone number
            $request->validate([
                'phone' => 'required|string|min:10|max:20',
            ]);

            $phoneNumber = $request->input('phone');
            
            // Format the phone number
            $formattedPhone = $this->smsService->formatMobile($phoneNumber);
            
            // Generate test message with timestamp
            $timestamp = now()->format('Y-m-d H:i:s');
            $testMessage = "Test SMS from e3shopbd server. Time: {$timestamp}. If you receive this, SMS is working!";
            
            // Log the test attempt
            Log::info('SMS Test API Called', [
                'original_phone' => $phoneNumber,
                'formatted_phone' => $formattedPhone,
                'timestamp' => $timestamp,
                'ip_address' => $request->ip(),
            ]);
            
            // Send SMS
            $result = $this->smsService->sendSms($formattedPhone, $testMessage);
            
            // Prepare response
            $response = [
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? 'SMS sent successfully! Check your phone.' 
                    : 'SMS sending failed. Check the error details.',
                'data' => [
                    'original_phone' => $phoneNumber,
                    'formatted_phone' => $formattedPhone,
                    'timestamp' => $timestamp,
                    'http_code' => $result['http_code'] ?? null,
                    'api_response' => $result['response'] ?? null,
                ],
            ];
            
            // Add error details if failed
            if (!$result['success']) {
                $response['data']['error'] = $result['error'] ?? 'Unknown error';
                $response['data']['raw_response'] = $result['raw_response'] ?? null;
            }
            
            // Add debugging info
            $response['debug'] = [
                'api_url' => config('services.bdbulksms.url'),
                'token_configured' => config('services.bdbulksms.token') ? 'Yes' : 'No',
                'token_length' => config('services.bdbulksms.token') ? strlen(config('services.bdbulksms.token')) : 0,
            ];
            
            // Add troubleshooting tips if SMS failed
            if (!$result['success']) {
                $response['troubleshooting'] = [
                    'Check your BD Bulk SMS account balance',
                    'Verify your account is active',
                    'Ensure the phone number is correct and active',
                    'Check if your carrier is blocking promotional SMS',
                    'Verify sender ID is approved (if required)',
                    'Check if the number is on DND (Do Not Disturb) list',
                ];
            } else {
                $response['note'] = 'API returned success. If you don\'t receive SMS, check: 1) Account balance, 2) Sender ID approval, 3) Phone number validity, 4) Network delays';
            }
            
            return response()->json($response, $result['success'] ? 200 : 500);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('SMS Test API Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while testing SMS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SMS service status and configuration
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        try {
            $config = [
                'api_url' => config('services.bdbulksms.url'),
                'token_configured' => config('services.bdbulksms.token') ? true : false,
                'token_length' => config('services.bdbulksms.token') ? strlen(config('services.bdbulksms.token')) : 0,
            ];
            
            return response()->json([
                'success' => true,
                'message' => 'SMS service configuration',
                'data' => $config,
                'status' => $config['token_configured'] ? 'Configured' : 'Not Configured',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving SMS service status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

