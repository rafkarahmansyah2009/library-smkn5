<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Fine;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Handle incoming Midtrans Webhook Notification.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Midtrans webhook notification received', $payload);

        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');
        $transactionStatus = (string) $request->input('transaction_status');
        $fraudStatus = (string) $request->input('fraud_status');
        $paymentType = (string) $request->input('payment_type');

        // 1. Verify Signature Key
        if (! $this->midtransService->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans webhook: invalid signature key', [
                'order_id' => $orderId,
                'signature_key' => $signatureKey,
            ]);

            return response()->json(['message' => 'Invalid signature key'], 403);
        }

        // 2. Find Fine by Midtrans Order ID
        $fine = Fine::where('midtrans_order_id', $orderId)->first();
        if (! $fine) {
            Log::warning('Midtrans webhook: fine not found for order', ['order_id' => $orderId]);

            return response()->json(['message' => 'Fine record not found'], 404);
        }

        // 3. Idempotency Check: if already paid, return 200 immediately
        if ($fine->isPaid()) {
            Log::info('Midtrans webhook: fine already paid, duplicate notification ignored', [
                'order_id' => $orderId,
                'fine_id' => $fine->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Fine is already marked as paid.',
            ], 200);
        }

        // 4. Handle Transaction Status
        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            $now = Carbon::now();
            $detailNote = 'Pembayaran Midtrans online ('.($paymentType ?: 'Snap').") [Order: {$orderId}]";
            $notes = $fine->notes ? $fine->notes.' | '.$detailNote : $detailNote;

            $fine->update([
                'status' => 'paid',
                'payment_method' => 'midtrans',
                'paid_at' => $now,
                'payment_date' => $now,
                'notes' => $notes,
            ]);

            ActivityLog::record(
                'fine_paid',
                'Pembayaran denda sebesar Rp '.number_format((float) $fine->amount, 0, ',', '.')." untuk anggota {$fine->user->name} berhasil diverifikasi melalui Midtrans (Order: {$orderId}).",
                $fine->user_id
            );

            Log::info('Midtrans webhook: fine settled successfully', [
                'order_id' => $orderId,
                'fine_id' => $fine->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Fine payment settled successfully.',
            ], 200);
        }

        if ($transactionStatus === 'pending') {
            Log::info('Midtrans webhook: payment pending', [
                'order_id' => $orderId,
                'fine_id' => $fine->id,
            ]);

            return response()->json([
                'status' => 'pending',
                'message' => 'Payment status is pending.',
            ], 200);
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
            // Reset snap token so the user can re-attempt payment with a new token
            $fine->update([
                'snap_token' => null,
            ]);

            Log::info("Midtrans webhook: payment {$transactionStatus}, snap_token cleared for retry", [
                'order_id' => $orderId,
                'fine_id' => $fine->id,
            ]);

            return response()->json([
                'status' => $transactionStatus,
                'message' => "Payment {$transactionStatus}. Snap token cleared for retry.",
            ], 200);
        }

        return response()->json([
            'status' => $transactionStatus,
            'message' => 'Notification received.',
        ], 200);
    }
}
