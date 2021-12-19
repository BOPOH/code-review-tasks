<?php

namespace App\Link;

interface UrlShorterInterface
{
    public function getShortUrl(string $url): string;
}
