<?php

namespace App\Enums;

enum PurchaseStatusEnum: string
{
    case PENDING = 'PENDING';
    case SUCCESSFUL = 'SUCCESSFUL';
    case FAILED = 'FAILED';

    public static function toArray(): array
    {
        return array_column(PurchaseStatusEnum::cases(), 'value');
    }
}
