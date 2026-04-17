<?php

namespace App\Models;

use App\Enums\PayrollStatus;
use Database\Factories\PayrollFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'worker_id', 'amount', 'period_start', 'period_end', 'status', 'paid_at', 'notes'])]
class Payroll extends Model
{
    /** @use HasFactory<PayrollFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'period_start' => 'date',
            'period_end' => 'date',
            'status' => PayrollStatus::class,
            'paid_at' => 'datetime',
        ];
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

    public function markPaid(): void
    {
        $this->update([
            'status' => PayrollStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
