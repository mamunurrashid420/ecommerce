<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private $token;
    private $apiUrl;

    public function __construct()
    {
        $this->token = config('services.bdbulksms.token');
        $this->apiUrl = config('services.bdbulksms.url', 'https://api.bdbulksms.net/api.php?json');
    }

    /**
     * Send SMS using BD Bulk SMS API
     *
     * @param string $to Phone number(s) - can be comma separated
     * @param string $message SMS message content
     * @return array Response from SMS API
     */
    public function sendSms($to, $message)
    {
        try {
            // Prepare data for API
            $data = [
                'to' => $to,
                'message' => $message,
                'token' => $this->token
            ];

            // Send SMS using cURL (as per your original code)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            // Log the SMS attempt
            Log::info('SMS API Request', [
                'to' => $to,
                'message' => $message,
                'response' => $response,
                'http_code' => $httpCode,
                'error' => $error
            ]);

            if ($error) {
                throw new \Exception('cURL Error: ' . $error);
            }

            // Parse response
            $responseData = json_decode($response, true);
            
            return [
                'success' => $httpCode === 200,
                'response' => $responseData ?: $response,
                'http_code' => $httpCode,
                'raw_response' => $response
            ];

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $to,
                'message' => $message,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response' => null
            ];
        }
    }

    /**
     * Send OTP SMS
     *
     * @param string $mobile Phone number
     * @param string $otp OTP code
     * @return array
     */
    public function sendOtp($mobile, $otp)
    {
        $message = "e3shopbd - Your OTP code is: {$otp}. This code will expire in 10 minutes. Do not share this code with anyone.";
        
        return $this->sendSms($mobile, $message);
    }

    /**
     * Format mobile number for BD SMS gateway
     *
     * @param string $mobile
     * @return string
     */
    public function formatMobile($mobile)
    {
        // Remove any spaces, dashes, or special characters
        $mobile = preg_replace('/[^0-9+]/', '', $mobile);
        
        // If starts with +88, keep as is
        if (strpos($mobile, '+88') === 0) {
            return $mobile;
        }
        
        // If starts with 88, add +
        if (strpos($mobile, '88') === 0) {
            return '+' . $mobile;
        }
        
        // If starts with 01, add +88
        if (strpos($mobile, '01') === 0) {
            return '+88' . $mobile;
        }
        
        // If starts with 1 and length is 10, add +880
        if (strpos($mobile, '1') === 0 && strlen($mobile) === 10) {
            return '+880' . $mobile;
        }
        
        return $mobile;
    }
}