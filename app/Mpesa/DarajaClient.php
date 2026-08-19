<?php

namespace App\Mpesa;

use App\Models\MpesaIntegration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DarajaClient
{
    /**
     * @return array<string, mixed>
     */
    public function testConnection(MpesaIntegration $integration): array
    {
        try {
            $this->accessToken($integration, forceRefresh: true);

            $integration->update([
                'connection_status' => 'verified',
                'last_tested_at' => now(),
                'last_error' => null,
            ]);

            return ['ok' => true, 'message' => 'M-Pesa connection verified.'];
        } catch (\Throwable) {
            $integration->update([
                'connection_status' => 'failed',
                'last_tested_at' => now(),
                'last_error' => 'Connection failed. Check your Consumer Key and Consumer Secret.',
                'is_active' => false,
            ]);

            return ['ok' => false, 'message' => 'Connection failed. Check your Consumer Key and Consumer Secret.'];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws ConnectionException
     */
    public function stkPush(MpesaIntegration $integration, array $payload): array
    {
        $response = Http::withToken($this->accessToken($integration))
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->connectTimeout(5)
            ->post($this->baseUrl($integration).'/mpesa/stkpush/v1/processrequest', $payload);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->json();
    }

    public function accessToken(MpesaIntegration $integration, bool $forceRefresh = false): string
    {
        $cacheKey = "mpesa_token:{$integration->id}:{$integration->environment}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($integration): string {
            $response = Http::withBasicAuth($integration->consumer_key, $integration->consumer_secret)
                ->acceptJson()
                ->timeout(10)
                ->connectTimeout(5)
                ->get($this->baseUrl($integration).'/oauth/v1/generate', [
                    'grant_type' => 'client_credentials',
                ]);

            if ($response->failed()) {
                $response->throw();
            }

            return (string) $response->json('access_token');
        });
    }

    public function baseUrl(MpesaIntegration $integration): string
    {
        return $integration->environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }
}
