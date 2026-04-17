<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['business_id', 'worker_id', 'order_number', 'customer_name', 'customer_phone', 'subtotal', 'tax', 'total', 'status', 'notes', 'completed_at'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => OrderStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $date = now()->format('Ymd');

        do {
            $number = $prefix.$date.'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<User, $this> */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<Feedback, $this> */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function recalculateTotal(): void
    {
        $subtotal = $this->items()->sum('total_price');
        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total' => $subtotal + $this->tax,
        ]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => OrderStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
