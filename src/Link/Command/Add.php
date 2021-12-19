<?php

namespace App\Link\Command;

use App\Entity\Link as LinkEntity;
use App\Link\DTO\Link as LinkDTO;
use App\Link\Exception\UnreachableUrlException;
use App\Link\TitleExtractorInterface;
use App\Link\UrlShorterInterface;
use App\Link\UrlValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class Add
{
    private EntityManagerInterface $em;
    private UrlShorterInterface $urlShorter;
    private TitleExtractorInterface $titleExtractor;
    private UrlValidatorInterface $urlValidator;

    public function __construct(
        EntityManagerInterface $em,
        UrlShorterInterface $urlShorter,
        TitleExtractorInterface $titleExtractor,
        UrlValidatorInterface $urlValidator
    )
    {
        $this->em = $em;
        $this->urlShorter = $urlShorter;
        $this->titleExtractor = $titleExtractor;
        $this->urlValidator = $urlValidator;
    }

    /**
     * @throws UnreachableUrlException
     */
    public function add(LinkDTO $dto): LinkEntity
    {
        if (!$this->urlValidator->validate($dto->getUrl())) {
            throw new UnreachableUrlException('Unreachable url');
        }

        $entity = new LinkEntity();
        $entity->setUrl($dto->getUrl());
        $entity->setTags($dto->getTags());

        $entity->setTitle($this->titleExtractor->extractTitle($dto));
        $entity->setShortUrl($this->urlShorter->getShortUrl($dto->getUrl()));

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
