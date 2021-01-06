<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\EventialsDriver\Services;

class CustomDomainService
{
    public static function applyCustomDomainToUrl(string $domain, string $url): string
    {
        return preg_replace('<^(https://)www.eventials.com(/)>', '$1' . $domain . '$2', $url);
    }
}
