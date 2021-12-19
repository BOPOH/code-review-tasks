<?php

namespace App\Link;

interface UrlValidatorInterface
{
    public function validate(string $url): bool;
}
