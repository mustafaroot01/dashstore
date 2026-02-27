<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class OtpService
{
    private string $baseUrl   = 'https://otp.arqam.tech/api';
    private string $apiKey    = 'otplive_RXGXrl5hgWr5S8CwnQNjKAEYCdATHUKa';

    private function getChannel(): string
    {
        return Setting::get('otp_channel', 'whatsapp');
    }

    private function formatPhone(string $phone): string
    {
        // Remove leading 0 if present, and add +964
        $phone = preg_replace('/^0/', '', $phone);
        if (!str_starts_with($phone, '+')) {
            if (!str_starts_with($phone, '964')) {
                $phone = '+964' . $phone;
            } else {
                $phone = '+' . $phone;
            }
        }
        return $phone;
    }

    /**
     * Send OTP for registration/login
     */
    public function sendOtp(string $phone): array
    {
        $phone = $this->formatPhone($phone);
        $channel = $this->getChannel();

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ])->post("{$this->baseUrl}/sms/otp", [
            'phoneNumber' => $phone,
            'channel'     => $channel,
        ]);

        $result = $response->json();

        // Fallback to SMS if WhatsApp fails
        if (! ($result['success'] ?? false) && $channel === 'whatsapp') {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Accept'        => 'application/json',
            ])->post("{$this->baseUrl}/sms/otp", [
                'phoneNumber' => $phone,
                'channel'     => 'sms',
            ]);
            return $response->json();
        }

        return $result;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $messageId, string $code): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ])->post("{$this->baseUrl}/sms/verify", [
            'messageId' => $messageId,
            'code'      => $code,
        ]);

        return $response->json();
    }

    /**
     * Send password reset OTP
     */
    public function sendResetOtp(string $phone): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ])->post("{$this->baseUrl}/sms/reset-password", [
            'phoneNumber' => $this->formatPhone($phone),
            'channel'     => $this->getChannel(),
        ]);

        return $response->json();
    }
}
