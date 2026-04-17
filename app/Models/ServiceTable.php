<?php

namespace App\Models;

use Database\Factories\ServiceTableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['business_id', 'name', 'code', 'sort_order', 'is_active'])]
class ServiceTable extends Model
{
    /** @use HasFactory<ServiceTableFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceTable $table) {
            if (empty($table->code)) {
                $table->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TBL-'.Str::upper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
