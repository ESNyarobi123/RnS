<?php

namespace App\Models;

use App\Enums\LinkType;
use Database\Factories\BusinessWorkerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'worker_id', 'link_type', 'linked_at', 'unlinked_at', 'expires_at', 'is_active', 'qr_code', 'qr_image_path'])]
class BusinessWorker extends Model
{
    /** @use HasFactory<BusinessWorkerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'link_type' => LinkType::class,
            'linked_at' => 'datetime',
            'unlinked_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BusinessWorker $link) {
            if ($link->is_active) {
                $existingActive = self::where('business_id', $link->business_id)
                    ->where('worker_id', $link->worker_id)
                    ->where('is_active', true)
                    ->exists();

                if ($existingActive) {
                    throw new \RuntimeException('Worker is already actively linked to this business.');
                }
            }
        });
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

    public function isExpired(): bool
    {
        if ($this->link_type === LinkType::Permanent) {
            return false;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function unlink(): void
    {
        $this->update([
            'is_active' => false,
            'unlinked_at' => now(),
        ]);
    }
}
