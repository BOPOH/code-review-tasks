<?php

namespace App\Link\Command;

use App\Link\DTO\Link;
use App\Link\Exception\UnreachableUrlException;

class BatchOperation
{
    private Add $addCommand;
    private Update $updateCommand;

    public function __construct(Add $addCommand, Update $updateCommand)
    {
        $this->addCommand = $addCommand;
        $this->updateCommand = $updateCommand;
    }

    /**
     * @throws UnreachableUrlException
     */
    public function process(array $links): array
    {
        $result = [];
        foreach ($links as $linkData) {
            $dto = $this->populateLinkDTO((array)$linkData);
            if ($dto->getId()) {
                $commandResult = $this->updateCommand->update($dto);
            } else {
                $commandResult = $this->addCommand->add($dto);
            }

            if ($commandResult) {
                $result[] = $commandResult->getId();
            }
        }

        return $result;
    }

    private function populateLinkDTO(array $linkData): Link
    {
        $linkData += [
            'id'       => null,
            'long_url' => '',
            'title'    => '',
            'tags'     => [],
        ];

        $dto = new Link();
        $dto->setId($linkData['id']);
        $dto->setUrl($linkData['long_url']);
        $dto->setTitle($linkData['title']);
        $dto->setTags((array)$linkData['tags']);

        return $dto;
    }
}
