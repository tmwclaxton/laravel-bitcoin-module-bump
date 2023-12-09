<?php

namespace Mollsoft\LaravelBitcoinModule\Enums;

enum TransactionCategory: string
{
    case Receive = 'receive';
    case Send = 'send';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
