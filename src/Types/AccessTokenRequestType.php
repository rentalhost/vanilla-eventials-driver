<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property string $grant_type
 *
 * @property string $client_id
 * @property string $client_secret
 *
 * @property string $response_type
 */
class AccessTokenRequestType
    extends Type
{
    protected array $attributes = [
        'grant_type'    => 'client_credentials',
        'response_type' => 'token'
    ];
}
