<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Fine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'borrowing_id',
        'user_id',
        'amount',
        'days_overdue',
        'status',
        'payment_method',
        'midtrans_order_id',
        'snap_token',
        'payment_date',
        'paid_at',
        'paid_to_user_id',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'days_overdue' => 'integer',
            'payment_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get formatted amount in Rupiah.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp '.number_format((float) $this->amount, 0, ',', '.');
    }

    /**
     * Get the borrowing related to this fine.
     */
    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    /**
     * Get the member user who owes the fine.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Get the staff user who processed the payment.
     */
    public function paidTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_to_user_id')->withTrashed();
    }

    /**
     * Check if the fine is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark fine as paid safely by staff (cash/manual).
     *
     * @throws InvalidArgumentException
     */
    public function markAsPaid(User $cashier, ?string $notes = null, string $paymentMethod = 'tunai'): void
    {
        if ($this->isPaid()) {
            throw new InvalidArgumentException('Denda ini sudah pernah dibayar.');
        }

        $now = Carbon::now();

        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_date' => $now,
            'paid_at' => $now,
            'paid_to_user_id' => $cashier->id,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Mark fine as paid via Midtrans online payment.
     */
    public function markAsPaidMidtrans(string $orderId, ?string $notes = null): void
    {
        if ($this->isPaid()) {
            return;
        }

        $now = Carbon::now();

        $this->update([
            'status' => 'paid',
            'payment_method' => 'midtrans',
            'payment_date' => $now,
            'paid_at' => $now,
            'midtrans_order_id' => $orderId,
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
