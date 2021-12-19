<?php

namespace App\Link\DTO;

use App\Entity\Link as LinkEntity;
use Symfony\Component\Validator\Constraints as Assert;

class Link implements \JsonSerializable
{
    private $id;

    /**
     * @Assert\NotBlank
     */
    private $url = '';

    private $shortUrl = '';

    private $title = '';

    private $tags = [];

    public static function fromEntity(?LinkEntity $entity): Link
    {
        $dto = new self();
        if (!$entity) {
            return $dto;
        }

        $dto->setId($entity->getId());
        $dto->setTags($entity->getTags());
        $dto->setTitle($entity->getTitle());
        $dto->setUrl($entity->getUrl());
        $dto->setShortUrl($entity->getShortUrl());

        return $dto;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getShortUrl(): ?string
    {
        return $this->shortUrl;
    }

    public function setShortUrl(string $shortUrl): self
    {
        $this->shortUrl = $shortUrl;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'url'      => $this->url,
            'shortUrl' => $this->shortUrl,
            'title'    => $this->title,
            'tags'     => $this->tags,
        ];
    }
}
