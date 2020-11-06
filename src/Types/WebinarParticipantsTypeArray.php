<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\TypeArray;

class WebinarParticipantsTypeArray
    extends TypeArray
{
    public static string $castTo = WebinarParticipantType::class;

    public function getByEmail(string $participantEmail): ?WebinarParticipantType
    {
        /** @var WebinarParticipantType $item */
        foreach ($this as $item) {
            if ($item->email === $participantEmail) {
                return $item;
            }
        }

        return null;
    }
}
