<?php

namespace App\Models;

use Database\Factories\WaiterCallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'service_table_id', 'table_id', 'customer_phone', 'customer_name', 'notes', 'status', 'responded_at'])]
class WaiterCall extends Model
{
    /** @use HasFactory<WaiterCallFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** @return BelongsTo<ServiceTable, $this> */
    public function serviceTable(): BelongsTo
    {
        return $this->belongsTo(ServiceTable::class);
    }

    /** @return BelongsTo<Table, $this> */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function markResponded(): void
    {
        $this->update([
            'status' => 'responded',
            'responded_at' => now(),
        ]);
    }
}
