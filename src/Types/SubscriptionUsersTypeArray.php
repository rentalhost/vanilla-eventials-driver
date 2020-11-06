<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\TypeArray;

class SubscriptionUsersTypeArray
    extends TypeArray
{
    public static string $castTo = SubscriptionUserType::class;

    public function getByEmail(string $email): ?SubscriptionUserType
    {
        /** @var SubscriptionUserType $item */
        foreach ($this as $item) {
            if ($item->user->email === $email) {
                return $item;
            }
        }

        return null;
    }
}
