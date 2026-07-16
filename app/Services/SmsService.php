<?php

declare(strict_types=1);

namespace App\Services;

/**
 * SMS sending service using configurable provider (Twilio pattern).
 */
class SmsService
{
    private string $provider;
    private string $apiUrl;
    private string $accountSid;
    private string $authToken;
    private string $fromNumber;

    public function __construct()
    {
        $this->provider    = $_ENV['SMS_PROVIDER'] ?? 'twilio';
        $this->accountSid  = $_ENV['SMS_ACCOUNT_SID'] ?? '';
        $this->authToken   = $_ENV['SMS_AUTH_TOKEN'] ?? '';
        $this->fromNumber  = $_ENV['SMS_FROM_NUMBER'] ?? '';
        $this->apiUrl      = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
    }

    /**
     * Send an SMS message.
     */
    public function send(string $phone, string $message): array
    {
        if (empty($this->accountSid) || empty($this->authToken)) {
            error_log("[SmsService] SMS credentials not configured");
            return ['success' => false, 'error' => 'SMS provider not configured'];
        }

        $phone = $this->sanitizePhone($phone);

        $data = http_build_query([
            'From' => $this->fromNumber,
            'To'   => $phone,
            'Body' => $message,
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_USERPWD => "{$this->accountSid}:{$this->authToken}",
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("[SmsService] cURL error: {$error}");
            return ['success' => false, 'error' => $error];
        }

        $result = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success'    => true,
                'message_id' => $result['sid'] ?? null,
                'status'     => $result['status'] ?? 'queued',
            ];
        }

        error_log("[SmsService] API error ({$httpCode}): {$response}");

        return [
            'success' => false,
            'error'   => $result['message'] ?? "HTTP {$httpCode}",
        ];
    }

    /**
     * Send an OTP code via SMS.
     */
    public function sendOtp(string $phone, string $code): array
    {
        $appName = $_ENV['APP_NAME'] ?? 'YouTube Clone';
        $message = "Your {$appName} verification code is: {$code}. Valid for 10 minutes. Do not share this code.";

        return $this->send($phone, $message);
    }

    /**
     * Generate a random OTP code.
     */
    public function generateOtp(int $length = 6): string
    {
        $min = (int) str_repeat('1', $length);
        $max = (int) str_repeat('9', $length);
        return (string) random_int($min, $max);
    }

    /**
     * Sanitize a phone number to E.164 format.
     */
    private function sanitizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }
}
