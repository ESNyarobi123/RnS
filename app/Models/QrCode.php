<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'type', 'business_id', 'worker_id', 'table_id', 'name', 'qr_image_path', 'is_active', 'metadata'])]
class QrCode extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(BusinessWorker::class, 'worker_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getQrUrlAttribute(): string
    {
        return route('qr.scan', $this->code);
    }
}
