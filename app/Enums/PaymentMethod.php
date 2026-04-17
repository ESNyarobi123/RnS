<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MobileMoney => 'Mobile Money',
            self::Card => 'Card',
            self::BankTransfer => 'Bank Transfer',
        };
    }
}
