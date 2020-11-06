<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver;

use GuzzleHttp\Client as GuzzleClient;
use Rentalhost\Vanilla\EventialsDriver\Types\CredentialsType;

class DriverConnector
{
    private const EVENTIALS_API_ENDPOINT = 'https://api.eventials.com/v1/';

    public static function withCredentials(CredentialsType $credentials): DriverHandler
    {
        return new DriverHandler(self::getGuzzleClientAuthenticated($credentials));
    }

    public static function getGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([ 'base_uri' => self::EVENTIALS_API_ENDPOINT ]);
    }

    public static function getGuzzleClientAuthenticated(CredentialsType $credentials): GuzzleClient
    {
        return new GuzzleClient([
            'base_uri' => self::EVENTIALS_API_ENDPOINT,
            'headers'  => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $credentials->getAccessToken()->access_token
            ]
        ]);
    }
}
