<?php

namespace App\Models;

use App\Concerns\HasImage;
use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use Database\Factories\BusinessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'type', 'description', 'address', 'phone', 'logo', 'status', 'bot_code', 'menu_image', 'qr_code', 'qr_image_path'])]
class Business extends Model
{
    /** @use HasFactory<BusinessFactory> */
    use HasFactory, HasImage, SoftDeletes;

    public function imageColumn(): string
    {
        return 'logo';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BusinessType::class,
            'status' => BusinessStatus::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return HasMany<Category, $this> */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<BusinessWorker, $this> */
    public function workerLinks(): HasMany
    {
        return $this->hasMany(BusinessWorker::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<Payroll, $this> */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /** @return HasMany<PaymentSetting, $this> */
    public function paymentSettings(): HasMany
    {
        return $this->hasMany(PaymentSetting::class);
    }

    /** @return HasMany<Feedback, $this> */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /** @return HasMany<Stock, $this> */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function activeWorkerLinks(): HasMany
    {
        return $this->workerLinks()->where('is_active', true);
    }

    /** @return HasMany<Category, $this> */
    public function activeCategories(): HasMany
    {
        return $this->categories()->where('is_active', true)->orderBy('sort_order');
    }

    /** @return HasMany<Product, $this> */
    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true);
    }

    public function isRestaurant(): bool
    {
        return $this->type === BusinessType::Restaurant;
    }

    public function isSalon(): bool
    {
        return $this->type === BusinessType::Salon;
    }

    public function isActive(): bool
    {
        return $this->status === BusinessStatus::Active;
    }

    /** @return HasMany<ServiceTable, $this> */
    public function serviceTables(): HasMany
    {
        return $this->hasMany(ServiceTable::class);
    }

    /** @return HasMany<Tip, $this> */
    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class);
    }

    /** @return HasMany<WaiterCall, $this> */
    public function waiterCalls(): HasMany
    {
        return $this->hasMany(WaiterCall::class);
    }

    public function generateBotCode(): string
    {
        do {
            $code = 'BIZ-'.Str::upper(Str::random(6));
        } while (self::where('bot_code', $code)->exists());

        $this->update(['bot_code' => $code]);

        return $code;
    }

    public function tableLabel(): string
    {
        return $this->isRestaurant() ? 'Table' : 'Station';
    }

    public function tableLabelPlural(): string
    {
        return $this->isRestaurant() ? 'Tables' : 'Stations';
    }

    public function hasMenuImage(): bool
    {
        return ! empty($this->menu_image);
    }

    public function menuImageUrl(): ?string
    {
        if (! $this->menu_image) {
            return null;
        }

        return Storage::disk('public')->url($this->menu_image);
    }
}
