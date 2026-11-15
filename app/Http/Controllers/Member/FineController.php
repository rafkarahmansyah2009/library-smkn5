<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Services\MidtransService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class FineController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService
    ) {}

    /**
     * Display member's fines list.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $fines = Fine::with(['borrowing.book'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalUnpaid = Fine::where('user_id', $userId)
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalPaid = Fine::where('user_id', $userId)
            ->where('status', 'paid')
            ->sum('amount');

        return view('member.fines.index', compact('fines', 'totalUnpaid', 'totalPaid'));
    }

    /**
     * Generate Midtrans Snap token for online payment.
     */
    public function pay(Request $request, Fine $fine): JsonResponse
    {
        // 1. Authorization: user only can pay their own fine
        if ((int) $fine->user_id !== (int) auth()->id()) {
            return response()->json([
                'message' => 'Anda tidak memiliki hak akses untuk membayar denda ini.',
            ], 403);
        }

        // 2. Prevent paying already paid fine
        if ($fine->isPaid()) {
            return response()->json([
                'message' => 'Denda ini sudah lunas.',
            ], 400);
        }

        // 3. Generate token with error handling
        try {
            $snapToken = $this->midtransService->createSnapToken($fine, auth()->user());

            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'order_id' => $fine->midtrans_order_id,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Gagal generate Snap Token Midtrans: '.$e->getMessage(), [
                'fine_id' => $fine->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Layanan pembayaran online Midtrans sedang mengalami gangguan. Silakan coba beberapa saat lagi atau hubungi staf perpustakaan.',
            ], 500);
        }
    }
}
