<?php

namespace App\Enums;

enum BusinessType: string
{
    case Restaurant = 'restaurant';
    case Salon = 'salon';

    public function label(): string
    {
        return match ($this) {
            self::Restaurant => 'Restaurant',
            self::Salon => 'Salon',
        };
    }

    public function workerTitle(): string
    {
        return match ($this) {
            self::Restaurant => 'Waiter',
            self::Salon => 'Stylist',
        };
    }

    public function workerTitlePlural(): string
    {
        return match ($this) {
            self::Restaurant => 'Waiters',
            self::Salon => 'Stylists',
        };
    }

    public function itemLabel(): string
    {
        return match ($this) {
            self::Restaurant => 'Menu Item',
            self::Salon => 'Service',
        };
    }

    public function itemLabelPlural(): string
    {
        return match ($this) {
            self::Restaurant => 'Menu Items',
            self::Salon => 'Services',
        };
    }

    public function categoryLabel(): string
    {
        return match ($this) {
            self::Restaurant => 'Food Category',
            self::Salon => 'Service Category',
        };
    }
}
