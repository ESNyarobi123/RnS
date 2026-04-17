<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['business_id', 'name', 'qr_code', 'qr_image_path', 'capacity', 'status', 'metadata'])]
class Table extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('qr_code', $code)->exists());

        return $code;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->business?->isRestaurant() ? "Table {$this->name}" : "Station {$this->name}";
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function occupy(): void
    {
        $this->status = 'occupied';
        $this->save();
    }

    public function release(): void
    {
        $this->status = 'available';
        $this->save();
    }

    public function qrImageUrl(): ?string
    {
        if (! $this->qr_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->qr_image_path);
    }
}
