<?php

namespace App\Service\Link;

use App\Link\DTO\Link;
use App\Link\TitleExtractorInterface;

class TitleExtractor implements TitleExtractorInterface
{
    public function extractTitle(Link $link): string
    {
        if ($link->getTitle()) {
            return $link->getTitle();
        }

        return 'Short link for ' . $link->getUrl();
    }
}
