<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property string $access_token
 * @property string $refresh_token
 *
 * @property string $token_type
 *
 * @property int    $expires_in
 */
class AccessTokenType
    extends Type
{
}
