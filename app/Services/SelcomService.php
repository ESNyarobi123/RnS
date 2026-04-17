<?php

namespace App\Services;

use App\Models\PaymentSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SelcomService
{
    private string $apiKey;

    private string $apiSecret;

    private string $vendor;

    private string $baseUrl;

    public function __construct(PaymentSetting $setting)
    {
        $this->apiKey = $setting->api_key;
        $this->apiSecret = $setting->api_secret;
        $config = $setting->config ?? [];
        $this->vendor = $config['vendor'] ?? '';
        $environment = $config['environment'] ?? 'sandbox';
        $this->baseUrl = $environment === 'production'
            ? 'https://apigw.selcommobile.com'
            : 'https://apigw.selcommobile.com/sandbox';
    }

    public static function forBusiness(\App\Models\Business $business): ?self
    {
        $setting = $business->paymentSettings()
            ->where('provider', 'selcom')
            ->where('is_active', true)
            ->first();

        if (! $setting || ! $setting->api_key || ! $setting->api_secret) {
            return null;
        }

        return new self($setting);
    }

    /**
     * Create a minimal order (non-card) on Selcom.
     *
     * @param  array{order_id: string, amount: float, buyer_name: string, buyer_phone: string, buyer_email: string|null}  $data
     * @return array{success: bool, data: array|null, error: string|null}
     */
    public function createOrderMinimal(array $data): array
    {
        $transid = 'T'.now()->format('YmdHis').Str::random(6);

        $payload = [
            'vendor' => $this->vendor,
            'order_id' => $data['order_id'],
            'buyer_email' => $data['buyer_email'] ?? 'customer@tipta.co.tz',
            'buyer_name' => $data['buyer_name'],
            'buyer_phone' => $data['buyer_phone'],
            'amount' => (int) $data['amount'],
            'currency' => 'TZS',
            'buyer_remarks' => $data['remarks'] ?? 'Payment for order',
            'merchant_remarks' => $data['remarks'] ?? 'Order payment',
            'no_of_items' => 1,
        ];

        $signedFields = array_keys($payload);

        return $this->request('POST', '/v1/checkout/create-order-minimal', $payload, $signedFields, $transid);
    }

    /**
     * Push wallet payment (USSD prompt to customer phone).
     *
     * @param  array{order_id: string, msisdn: string}  $data
     * @return array{success: bool, data: array|null, error: string|null}
     */
    public function walletPayment(array $data): array
    {
        $transid = 'T'.now()->format('YmdHis').Str::random(6);

        $payload = [
            'transid' => $transid,
            'order_id' => $data['order_id'],
            'msisdn' => $data['msisdn'],
        ];

        $signedFields = array_keys($payload);

        return $this->request('POST', '/v1/checkout/wallet-payment', $payload, $signedFields, $transid);
    }

    /**
     * Check order payment status.
     *
     * @return array{success: bool, data: array|null, error: string|null}
     */
    public function orderStatus(string $orderId): array
    {
        return $this->request('GET', '/v1/checkout/order-status', ['order_id' => $orderId], ['order_id']);
    }

    /**
     * Build auth headers and execute request.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string>  $signedFields
     * @return array{success: bool, data: array|null, error: string|null}
     */
    private function request(string $method, string $path, array $payload, array $signedFields, ?string $transid = null): array
    {
        try {
            $timestamp = now()->toIso8601String();
            $headers = $this->buildHeaders($timestamp, $payload, $signedFields);

            $url = $this->baseUrl.$path;

            $response = match ($method) {
                'POST' => Http::withHeaders($headers)->post($url, $payload),
                'GET' => Http::withHeaders($headers)->get($url, $payload),
                'DELETE' => Http::withHeaders($headers)->delete($url, $payload),
            };

            $body = $response->json();

            Log::channel('daily')->info('Selcom API response', [
                'path' => $path,
                'status' => $response->status(),
                'resultcode' => $body['resultcode'] ?? null,
                'result' => $body['result'] ?? null,
            ]);

            if (($body['resultcode'] ?? '') === '000' || ($body['result'] ?? '') === 'SUCCESS') {
                return [
                    'success' => true,
                    'data' => $body['data'] ?? $body,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'data' => $body,
                'error' => $body['message'] ?? ($body['result'] ?? 'Unknown error'),
            ];
        } catch (\Throwable $e) {
            Log::channel('daily')->error('Selcom API error', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build Selcom authentication headers.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string>  $signedFields
     * @return array<string, string>
     */
    private function buildHeaders(string $timestamp, array $payload, array $signedFields): array
    {
        $signInput = 'timestamp='.$timestamp;
        foreach ($signedFields as $field) {
            $signInput .= '&'.$field.'='.($payload[$field] ?? '');
        }

        $digest = base64_encode(hash_hmac('sha256', $signInput, $this->apiSecret, true));
        $authorization = 'SELCOM '.base64_encode($this->apiKey);

        return [
            'Authorization' => $authorization,
            'Timestamp' => $timestamp,
            'Digest-Method' => 'HS256',
            'Digest' => $digest,
            'Signed-Fields' => implode(',', $signedFields),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
