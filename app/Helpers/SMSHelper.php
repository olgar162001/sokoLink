<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SMSHelper
{
    public static function sendSMS($mobile, $message)
    {
        try {
            $sms_status = env('SMS_API_STATUS', false);
            $url = 'http://192.168.119.2/app/smsapi/index.php'; // keep same or change if needed

            $number = self::formatTanzanianNumber($mobile);
            if (!$number || strlen($number) !== 12) {
                Log::error("Invalid mobile number format: {$mobile} -> {$number}");
                return ['status' => false, 'message' => 'Invalid mobile number'];
            }

            $api_key = '266EC2313BFF5F'; 
            $from = 'MyNewSender'; 
            $params = [
                'key' => $api_key,
                'campaign' => '1132',
                'routeid' => '8',
                'type' => 'text',
                'contacts' => $number,
                'senderid' => $from,
                'msg' => $message,
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || !empty($error)) {
                Log::error("SMS API request failed: URL={$url}, Error={$error}, Mobile={$number}");
                return ['status' => false, 'message' => 'Failed to connect to SMS API', 'error' => $error];
            }

            if ($http_code !== 200) {
                Log::error("SMS API returned non-200 status: HTTP={$http_code}, Response={$response}, Mobile={$number}");
                return ['status' => false, 'message' => 'SMS API error', 'response' => $response];
            }

            Log::info("SMS API response: URL={$url}, Mobile={$number}, Response={$response}");
            $result = json_decode($response, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($result['status']) && $result['status'] === 'success') {
                return ['status' => true, 'message' => 'SMS sent successfully', 'response' => $response];
            }

            Log::error("SMS API failed: URL={$url}, Mobile={$number}, Response={$response}");
            return ['status' => false, 'message' => 'SMS API rejected the request', 'response' => $response];

        } catch (\Exception $e) {
            Log::error("AnotherSMSHelper exception: Mobile={$mobile}, Error={$e->getMessage()}");
            return ['status' => false, 'message' => 'Internal error', 'error' => $e->getMessage()];
        }
    }

    private static function formatTanzanianNumber($mobile)
    {
        $mobile = preg_replace('/\D/', '', $mobile);
        if (empty($mobile)) return null;

        if (strpos($mobile, '0') === 0) return '255' . substr($mobile, 1);
        if (strpos($mobile, '255') === 0) return $mobile;
        if (strpos($mobile, '7') === 0 || strpos($mobile, '6') === 0) return '255' . $mobile;
        if (strpos($mobile, '+255') === 0) return substr($mobile, 1);

        return null;
    }
}
