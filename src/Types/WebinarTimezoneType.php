<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\Type\Type;

/**
 * @property int    $id
 *
 * @property string $country_isocode
 * @property string $code
 * @property string $name
 *
 * @property bool   $is_default
 *
 * @property int    $utc_offset
 */
class WebinarTimezoneType
    extends Type
{
}
