<?php

namespace App\Link;

use App\Link\DTO\Link;

interface TitleExtractorInterface
{
    public function extractTitle(Link $link): string;
}
