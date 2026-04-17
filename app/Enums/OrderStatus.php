<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Preparing = 'preparing';
    case InProgress = 'in_progress';
    case Served = 'served';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => 'Preparing',
            self::InProgress => 'In Progress',
            self::Served => 'Served',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function liveLabel(?BusinessType $type = null): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Preparing => $type === BusinessType::Salon ? 'In Progress' : 'Preparing',
            self::InProgress => 'In Progress',
            self::Served => $type === BusinessType::Salon ? 'Ready' : 'Served',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Confirmed => 'blue',
            self::Preparing => 'indigo',
            self::InProgress => 'indigo',
            self::Served => 'cyan',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }

    /** @return array<self> */
    public static function liveStatuses(): array
    {
        return [self::Pending, self::Preparing, self::Served, self::Completed];
    }
}
