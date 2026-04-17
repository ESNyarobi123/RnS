<?php

namespace App\Models;

use Database\Factories\BotSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['phone_number', 'secret_key', 'is_active', 'metadata'])]
class BotSetting extends Model
{
    /** @use HasFactory<BotSettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'secret_key' => 'encrypted',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public static function current(): ?self
    {
        return self::query()
            ->latest('id')
            ->first();
    }

    public function normalizedPhoneNumber(): string
    {
        return preg_replace('/\D+/', '', (string) $this->phone_number) ?: (string) $this->phone_number;
    }

    public function whatsappUrl(string $message): string
    {
        return 'https://wa.me/'.Str::of($this->normalizedPhoneNumber())->trim().'?text='.urlencode($message);
    }
}
