<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Services;

use Psr\Http\Message\ResponseInterface;
use Rentalhost\Vanilla\Type\Type;
use Rentalhost\Vanilla\Type\TypeArray;

class TypeService
{
    private static function jsonFromGuzzleResponse(ResponseInterface $response): array
    {
        $stream = $response->getBody();

        assert($stream !== null);

        return json_decode($stream->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public static function arrayFromGuzzleResponse(ResponseInterface $response, string $typeArrayClass): TypeArray
    {
        assert(is_a($typeArrayClass, TypeArray::class, true));

        return new $typeArrayClass(self::jsonFromGuzzleResponse($response));
    }

    public static function fromGuzzleResponse(ResponseInterface $response, string $typeClass): Type
    {
        assert(is_a($typeClass, Type::class, true));

        return new $typeClass(self::jsonFromGuzzleResponse($response));
    }
}
