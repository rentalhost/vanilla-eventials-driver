<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\TypeArray;

class WebinarFormTypeArray
    extends TypeArray
{
    public static string $castTo = WebinarFormType::class;

    public function getByName(string $formName): ?WebinarFormType
    {
        /** @var WebinarFormType $item */
        foreach ($this as $item) {
            if ($item->name === $formName) {
                return $item;
            }
        }

        return null;
    }

    public function hasName(string $formName): bool
    {
        return $this->getByName($formName) !== null;
    }
}
