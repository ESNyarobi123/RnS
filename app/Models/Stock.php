<?php

namespace App\Models;

use App\Enums\StockStatus;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'name', 'description', 'quantity', 'unit_price', 'reorder_level', 'status'])]
class Stock extends Model
{
    /** @use HasFactory<StockFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'reorder_level' => 'integer',
            'status' => StockStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Stock $stock) {
            if ($stock->isDirty('quantity')) {
                $stock->status = match (true) {
                    $stock->quantity <= 0 => StockStatus::OutOfStock,
                    $stock->quantity <= $stock->reorder_level => StockStatus::LowStock,
                    default => StockStatus::InStock,
                };
            }
        });
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function updateStockStatus(): void
    {
        $status = match (true) {
            $this->quantity <= 0 => StockStatus::OutOfStock,
            $this->quantity <= $this->reorder_level => StockStatus::LowStock,
            default => StockStatus::InStock,
        };

        $this->update(['status' => $status]);
    }
}
