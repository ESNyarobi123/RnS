<?php

namespace App\Enums;

enum LinkType: string
{
    case Permanent = 'permanent';
    case Temporary = 'temporary';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent',
            self::Temporary => 'Temporary',
        };
    }
}
