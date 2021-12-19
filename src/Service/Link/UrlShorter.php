<?php

namespace App\Service\Link;

use App\Link\UrlShorterInterface;

class UrlShorter implements UrlShorterInterface
{
    public function getShortUrl(string $url): string
    {
        return \md5($url);
    }
}
