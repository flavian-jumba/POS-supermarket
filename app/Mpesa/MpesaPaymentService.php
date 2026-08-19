<?php

namespace App\Mpesa;

use App\Models\MpesaIntegration;
use App\Models\MpesaTransaction;
use App\Models\Sale;
use App\Pos\CheckoutException;
use App\Pos\CheckoutService;
use Illuminate\Support\Facades\DB;

class MpesaPaymentService
{
    public function __construct(
        private readonly DarajaClient $daraja,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * @param  array{organizationId:?int,branchId:?int,registerId:?int,registerSessionId:?int,cashierId:?int}  $context
     * @param  array<int, array{id:int,qty:int}>  $cartItems
     */
    public function sendStkPush(array $context, array $cartItems, string $phone): MpesaTransaction
    {
        $integration = MpesaIntegration::query()
            ->where('organization_id', $context['organizationId'])
            ->first();

        if (! $integration?->isUsable()) {
            throw new CheckoutException('M-Pesa is not configured for this supermarket.');
        }

        $normalizedPhone = $this->normalizePhone($phone);

        [$sale, $payment, $transaction] = $this->checkout->createPendingMpesaPayment($context, $cartItems, $normalizedPhone);

        $payload = $this->stkPayload($integration, $sale, $normalizedPhone);

        try {
            $response = $this->daraja->stkPush($integration, $payload);
        } catch (\Throwable) {
            $transaction->update([
                'status' => 'failed',
                'result_description' => 'Could not send M-Pesa STK request.',
                'request_payload' => $this->safeRequestPayload($payload),
            ]);
            $payment->update(['status' => 'failed']);

            throw new CheckoutException('Could not send M-Pesa STK request.');
        }

        $transaction->update([
            'merchant_request_id' => $response['MerchantRequestID'] ?? null,
            'checkout_request_id' => $response['CheckoutRequestID'] ?? null,
            'status' => 'processing',
            'result_code' => isset($response['ResponseCode']) ? (int) $response['ResponseCode'] : null,
            'result_description' => $response['CustomerMessage'] ?? $response['ResponseDescription'] ?? null,
            'request_payload' => $this->safeRequestPayload($payload),
        ]);

        return $transaction->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleCallback(array $payload): ?MpesaTransaction
    {
        $callback = data_get($payload, 'Body.stkCallback', []);
        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;

        if (! $checkoutRequestId) {
            return null;
        }

        return DB::transaction(function () use ($payload, $callback, $checkoutRequestId): ?MpesaTransaction {
            $transaction = MpesaTransaction::query()
                ->where('checkout_request_id', $checkoutRequestId)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return null;
            }

            if ($transaction->isTerminal()) {
                return $transaction;
            }

            $resultCode = (int) ($callback['ResultCode'] ?? -1);
            $resultDescription = $callback['ResultDesc'] ?? null;
            $metadata = collect($callback['CallbackMetadata']['Item'] ?? [])->pluck('Value', 'Name');

            if ($resultCode === 0) {
                $transaction->update([
                    'status' => 'successful',
                    'result_code' => $resultCode,
                    'result_description' => $resultDescription,
                    'mpesa_receipt_number' => $metadata->get('MpesaReceiptNumber'),
                    'completed_at' => now(),
                    'callback_payload' => $this->sanitizeCallback($payload),
                ]);

                $this->checkout->completePendingMpesaPayment($transaction->fresh());

                return $transaction->fresh();
            }

            $status = $this->failureStatus($resultCode);

            $transaction->payment()->update([
                'status' => $status === 'cancelled' ? 'cancelled' : 'failed',
            ]);

            $transaction->update([
                'status' => $status,
                'result_code' => $resultCode,
                'result_description' => $resultDescription,
                'completed_at' => now(),
                'callback_payload' => $this->sanitizeCallback($payload),
            ]);

            return $transaction->fresh();
        });
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0')) {
            return '254'.substr($digits, 1);
        }

        if (str_starts_with($digits, '7') || str_starts_with($digits, '1')) {
            return '254'.$digits;
        }

        return $digits;
    }

    /**
     * @return array<string, mixed>
     */
    private function stkPayload(MpesaIntegration $integration, Sale $sale, string $phone): array
    {
        $timestamp = now()->format('YmdHis');

        return [
            'BusinessShortCode' => $integration->shortcode,
            'Password' => base64_encode($integration->shortcode.$integration->passkey.$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => $integration->transaction_type,
            'Amount' => (int) ceil((float) $sale->total),
            'PartyA' => $phone,
            'PartyB' => $integration->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => route('mpesa.callback'),
            'AccountReference' => $sale->sale_number,
            'TransactionDesc' => 'POS sale '.$sale->sale_number,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function safeRequestPayload(array $payload): array
    {
        unset($payload['Password']);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function sanitizeCallback(array $payload): array
    {
        return $payload;
    }

    private function failureStatus(int $resultCode): string
    {
        return match ($resultCode) {
            1032 => 'cancelled',
            1037, 1025 => 'timeout',
            default => 'failed',
        };
    }
}
