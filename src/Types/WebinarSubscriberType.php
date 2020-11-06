<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property string        $email
 * @property string[]|null $fields
 */
class WebinarSubscriberType
    extends Type
{
    public static function create(string $email, ?array $fields = null): WebinarSubscriberType
    {
        $self        = new self;
        $self->email = $email;

        if ($fields) {
            $self->fields = $fields;
        }

        return $self;
    }
}
