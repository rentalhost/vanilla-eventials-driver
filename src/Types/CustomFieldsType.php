<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

class CustomFieldsType
    extends Type
{
    public function __construct(?array $attributes = null)
    {
        parent::__construct(array_map(static function (string $attribute) {
            return $attribute === '' ? null : $attribute;
        }, $attributes));
    }
}
