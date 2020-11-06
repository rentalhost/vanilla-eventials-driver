<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property int|null    $id
 *
 * @property string|null $username
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string      $email
 */
class UserType
    extends Type
{
    public function __construct(?array $attributes = null)
    {
        parent::__construct($attributes);

        $this->id         = $this->id ?: null;
        $this->username   = $this->username ?: null;
        $this->first_name = $this->first_name ?: null;
        $this->last_name  = $this->last_name ?: null;
    }
}
