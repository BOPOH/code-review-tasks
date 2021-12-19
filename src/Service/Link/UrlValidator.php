<?php

namespace App\Service\Link;

use App\Link\UrlValidatorInterface;

class UrlValidator implements UrlValidatorInterface
{
    public function validate(string $url): bool
    {
        return @\file_get_contents($url) !== false;
    }
}
