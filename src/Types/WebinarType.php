<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Carbon\Carbon;
use Rentalhost\Vanilla\Type\Type;

/**
 * @property int                 $id
 *
 * @property string              $title
 * @property string              $slug
 * @property string              $description
 *
 * @property Carbon              $start_time
 * @property Carbon              $date_added
 *
 * @property bool                $is_public
 * @property bool                $is_group_only
 * @property bool                $is_draft
 *
 * @property int|null            $subscription_form_id
 *
 * @property string              $state
 * @property string|null         $access_code
 *
 * @property int|null            $total_invited
 * @property int|null            $total_subscribed
 *
 * @property float               $ticket_price
 *
 * @property bool                $embed_enabled
 *
 * @property string              $playback_stream_name
 * @property string              $publish_stream_name
 *
 * @property bool                $live_scheduled
 * @property bool                $block_shared_subscriptions
 * @property bool                $subscription_required
 *
 * @property string              $url
 * @property string              $cover
 *
 * @property string              $stream_key
 * @property string              $stream_url
 *
 * @property WebinarOwnerType    $owner
 * @property WebinarTimezoneType $timezone
 * @property WebinarCategoryType $category
 * @property WebinarStreamType   $stream
 */
class WebinarType
    extends Type
{
    public const
        STATE_UPCOMING = 'upcoming',
        STATE_LIVE = 'live',
        STATE_LIVE_PAUSED = 'live-paused',
        STATE_RECORDED = 'recorded';

    protected static ?array $casts = [
        'start_time' => Carbon::class,
        'date_added' => Carbon::class,
        'owner'      => WebinarOwnerType::class,
        'timezone'   => WebinarTimezoneType::class,
        'category'   => WebinarCategoryType::class,
        'stream'     => WebinarStreamType::class
    ];
}
