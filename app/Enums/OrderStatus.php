<?php
namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';   // 🆕 В обробці
    case Confirmed  = 'confirmed';
    case Packed     = 'packed';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';
    case Returned   = 'returned';
    case Refunded   = 'refunded';

    public static function labels(): array
    {
        return [
            self::Pending->value    => 'Очікує підтвердження',
            self::Processing->value => 'В обробці',            // 🆕
            self::Confirmed->value  => 'Підтверджене',
            self::Packed->value     => 'Упаковане',
            self::Shipped->value    => 'Відправлене',
            self::Delivered->value  => 'Доставлене',
            self::Cancelled->value  => 'Скасоване',
            self::Returned->value   => 'Повернене',
            self::Refunded->value   => 'Повернення коштів',
        ];
    }
}
