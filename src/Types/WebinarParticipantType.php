<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Carbon\Carbon;
use Rentalhost\Vanilla\Type\Type;

/**
 * @property int         $id
 *
 * @property string      $email
 *
 * @property string|null $access_code
 * @property string      $access_url
 *
 * @property bool        $send_invitation
 * @property bool        $invitation_sent
 *
 * @property Carbon      $date_added
 *
 * @property array       $additional_data
 */
class WebinarParticipantType
    extends Type
{
    protected static ?array $casts = [
        'date_added' => Carbon::class
    ];

    public function __construct(?array $attributes = null)
    {
        parent::__construct($attributes);

        $this->access_code     = $this->access_code ?: null;
        $this->additional_data = json_decode($this->get('additional_data'), true, 512, JSON_THROW_ON_ERROR);
    }
}
