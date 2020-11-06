<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Carbon\Carbon;
use Rentalhost\Vanilla\Type\Type;

/**
 * @property int              $id
 *
 * @property Carbon           $date_added
 * @property bool             $share_info
 *
 * @property CustomFieldsType $custom_fields
 *
 * @property UserType         $user
 */
class SubscriptionUserType
    extends Type
{
    protected static ?array $casts = [
        'date_added'    => Carbon::class,
        'custom_fields' => CustomFieldsType::class,
        'user'          => UserType::class
    ];
}
