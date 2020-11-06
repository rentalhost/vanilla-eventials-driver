<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Carbon\Carbon;
use Rentalhost\Vanilla\Type\Type;

/**
 * @property string     $title
 * @property string     $description
 *
 * @property Carbon     $start_time   in Zulu ISO-8601 format.
 * @property float|null $duration     as floating hours (eg. 1.5 = 1 hour and 30 minutes).
 *
 * @property int        $timezone_id  a positive integer from available timezones ids.
 * @property int        $category_id  a positive integer from available categories ids.
 *
 * @property bool|null  $is_public
 * @property bool|null  $is_draft
 *
 * @property bool|null  $embed_enabled
 *
 * @property float|null $ticket_price a positive float, zero or null.
 *
 * @property bool|null  $subscription_required
 * @property int|null   $subscription_form_id
 *
 * @property array|null $metadata
 */
class WebinarRequestType
    extends Type
{
    protected static ?array $casts = [
        'start_time' => Carbon::class
    ];

    public static function create(string $title, Carbon $startTime, ?string $description = null, ?int $subscriptionFormId = null, ?int $categoryId = null, ?int $timezoneId = null): WebinarRequestType
    {
        $request                       = new self;
        $request->title                = $title;
        $request->description          = $description ?? $title;
        $request->start_time           = $startTime->toIso8601ZuluString('microsecond');
        $request->subscription_form_id = $subscriptionFormId;
        $request->category_id          = $categoryId ?? 23;
        $request->timezone_id          = $timezoneId ?? 69;

        return $request;
    }
}
