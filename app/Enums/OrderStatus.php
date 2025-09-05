<?php
namespace App\Enums;

enum OrderStatus:string {
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Packed    = 'packed';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned  = 'returned';
    case Refunded  = 'refunded';

    public static function labels(): array {
        return [
            self::Pending->value   => 'Очікує підтвердження',
            self::Confirmed->value => 'Підтверджене',
            self::Packed->value    => 'Упаковане',
            self::Shipped->value   => 'Відправлене',
            self::Delivered->value => 'Доставлене',
            self::Cancelled->value => 'Скасоване',
            self::Returned->value  => 'Повернене',
            self::Refunded->value  => 'Повернення коштів',
        ];
    }
}
