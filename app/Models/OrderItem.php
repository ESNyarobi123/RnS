<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'product_id', 'quantity', 'unit_price', 'total_price', 'notes'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderItem $item) {
            $item->total_price = $item->unit_price * $item->quantity;
        });

        static::updating(function (OrderItem $item) {
            if ($item->isDirty(['unit_price', 'quantity'])) {
                $item->total_price = $item->unit_price * $item->quantity;
            }
        });

        static::saved(function (OrderItem $item) {
            $item->order->recalculateTotal();
        });

        static::deleted(function (OrderItem $item) {
            $item->order->recalculateTotal();
        });
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
