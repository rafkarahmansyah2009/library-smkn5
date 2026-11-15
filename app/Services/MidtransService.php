<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Set Midtrans configuration from app config.
     */
    protected function configure(): void
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$clientKey = (string) config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }

    /**
     * Generate Snap Token for a fine.
     *
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function createSnapToken(Fine $fine, User $user): string
    {
        if ($fine->isPaid()) {
            throw new InvalidArgumentException('Denda ini sudah lunas.');
        }

        if ((int) $fine->user_id !== (int) $user->id) {
            throw new InvalidArgumentException('Anda tidak memiliki akses ke denda ini.');
        }

        $this->configure();

        $orderId = 'DENDA-'.$fine->id.'-'.time();
        $grossAmount = (int) round((float) $fine->amount);

        if ($grossAmount <= 0) {
            throw new InvalidArgumentException('Nominal denda tidak valid.');
        }

        $bookTitle = $fine->borrowing?->book?->title ?? 'Koleksi Perpustakaan';

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => 'FINE-'.$fine->id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => Str::limit('Denda: '.$bookTitle, 45, '...'),
                ],
            ],
        ];

        $snapToken = Snap::getSnapToken($params);

        $fine->update([
            'midtrans_order_id' => $orderId,
            'snap_token' => $snapToken,
        ]);

        return $snapToken;
    }

    /**
     * Verify Midtrans notification signature key.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $serverKey = (string) config('midtrans.server_key');
        $payload = $orderId.$statusCode.$grossAmount.$serverKey;
        $expectedSignature = hash('sha512', $payload);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
