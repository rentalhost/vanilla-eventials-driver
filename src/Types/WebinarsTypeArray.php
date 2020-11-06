<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\TypeArray;

class WebinarsTypeArray
    extends TypeArray
{
    public static string $castTo = WebinarType::class;
}
