<?php

namespace App\Link\Command;

use App\Entity\Link as LinkEntity;
use App\Link\DTO\Link as LinkDTO;
use App\Link\Exception\UnreachableUrlException;
use App\Link\TitleExtractorInterface;
use App\Link\UrlValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class Update
{
    private EntityManagerInterface $em;
    private TitleExtractorInterface $titleExtractor;
    private UrlValidatorInterface $urlValidator;

    public function __construct(EntityManagerInterface $em, TitleExtractorInterface $titleExtractor, UrlValidatorInterface $urlValidator)
    {
        $this->em = $em;
        $this->titleExtractor = $titleExtractor;
        $this->urlValidator = $urlValidator;
    }

    /**
     * @throws UnreachableUrlException
     */
    public function update(LinkDTO $dto): ?LinkEntity
    {
        if (!$dto->getId()) {
            return null;
        }

        $entity = $this->em->find(LinkEntity::class, $dto->getId());
        if (!$entity) {
            return null;
        }

        if (!$this->urlValidator->validate($dto->getUrl())) {
            throw new UnreachableUrlException('Unreachable url');
        }

        $entity->setUrl($dto->getUrl());
        $entity->setTags($dto->getTags());
        $entity->setTitle($this->titleExtractor->extractTitle($dto));

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
