<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Services;

class ArrayService
{
    public static function exceptNull(array $items): array
    {
        return array_filter($items, static function ($item) {
            return $item !== null;
        });
    }
}
