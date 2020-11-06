<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Types;

use Rentalhost\Vanilla\EventialsDriver\DriverConnector;
use Rentalhost\Vanilla\Type\Type;

/**
 * @property string $client_id
 * @property string $client_secret
 */
class CredentialsType
    extends Type
{
    private ?AccessTokenType $accessToken = null;

    public static function create(string $clientId, string $clientSecret): self
    {
        return new self([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret
        ]);
    }

    public function getAccessToken(): AccessTokenType
    {
        if (!$this->accessToken) {
            $accessTokenRequest = new AccessTokenRequestType([
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret
            ]);

            $response = DriverConnector::getGuzzleClient()->post('oauth/token', [ 'form_params' => $accessTokenRequest->toArray() ]);

            $this->accessToken = new AccessTokenType(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
        }

        return $this->accessToken;
    }
}
