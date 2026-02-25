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

    /**
     * Send OTP for registration/login
     */
    public function sendOtp(string $phone): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ])->post("{$this->baseUrl}/sms/otp", [
            'phone'   => $phone,
            'channel' => $this->getChannel(),
        ]);

        return $response->json();
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $phone, string $code): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Accept'        => 'application/json',
        ])->post("{$this->baseUrl}/sms/verify", [
            'phone' => $phone,
            'code'  => $code,
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
            'phone'   => $phone,
            'channel' => $this->getChannel(),
        ]);

        return $response->json();
    }
}
