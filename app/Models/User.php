<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasImage;
use App\Enums\BusinessType;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'global_number', 'avatar'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasImage, Notifiable, TwoFactorAuthenticatable;

    public function imageColumn(): string
    {
        return 'avatar';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->role === UserRole::Worker && empty($user->global_number)) {
                $user->global_number = self::generateGlobalNumber();
            }
        });
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public static function generateGlobalNumber(): string
    {
        do {
            $number = 'TIP-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('global_number', $number)->exists());

        return $number;
    }

    /** @return HasMany<Business, $this> */
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }

    /** @return HasMany<BusinessWorker, $this> */
    public function businessLinks(): HasMany
    {
        return $this->hasMany(BusinessWorker::class, 'worker_id');
    }

    /** @return HasMany<Order, $this> */
    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'worker_id');
    }

    /** @return HasMany<Payroll, $this> */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'worker_id');
    }

    /** @return HasMany<Feedback, $this> */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'worker_id');
    }

    /** @return HasMany<Tip, $this> */
    public function tips(): HasMany
    {
        return $this->hasMany(Tip::class, 'worker_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::Manager;
    }

    public function isWorker(): bool
    {
        return $this->role === UserRole::Worker;
    }

    public function activeBusinessLink(): ?BusinessWorker
    {
        return $this->businessLinks()->where('is_active', true)->first();
    }

    public function activeBusiness(): ?Business
    {
        return $this->activeBusinessLink()?->business;
    }

    public function isLinkedToBusiness(): bool
    {
        return $this->businessLinks()->where('is_active', true)->exists();
    }

    public function workerTitle(): string
    {
        $business = $this->activeBusiness();

        if (! $business) {
            return 'Worker';
        }

        return $business->type->workerTitle();
    }
}
