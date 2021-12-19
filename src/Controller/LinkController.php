<?php

namespace App\Controller;

use App\Entity\Link as LinkEntity;
use App\Link\Command\BatchOperation;
use App\Link\Command\Visit;
use App\Link\DTO\Link as LinkDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/links")
 */
class LinkController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function indexAction(Request $request, EntityManagerInterface $em): Response
    {
        $qb = $em->getRepository(LinkEntity::class)
            ->createQueryBuilder('link');

        if ($title = $request->get('title')) {
            $qb
                ->andWhere($qb->expr()->like('link.title', ':title'))
                ->setParameter('title', "%{$title}%");
        }

        if ($tag = $request->get('tag')) {
            $qb
                ->andWhere(
                    $qb->expr()->like('CONCAT(\',\', link.tags, \',\')', ':tag')
                )
                ->setParameter('tag', "%,{$tag},%");
        }

        $links = $qb
            ->getQuery()
            ->getResult();

        $dtoList = [];
        foreach ($links as $link) {
            $dtoList[] = LinkDTO::fromEntity($link);
        }

        return $this->json($dtoList);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function createAction(Request $request, BatchOperation $batchOperation): Response
    {
        return $this->submitLink($request, $batchOperation);
    }

    /**
     * @Route("/{id}", methods={"PATCH"})
     */
    public function updateAction(?LinkEntity $link, Request $request, BatchOperation $batchOperation): Response
    {
        if (!$link) {
            throw $this->createNotFoundException('Link not found.');
        }

        return $this->submitLink($request, $batchOperation, $link);
    }

    /**
     * @Route("/{id}", methods={"DELETE"})
     */
    public function deleteAction(?LinkEntity $link, EntityManagerInterface $em): Response
    {
        if ($link) {
            $em->remove($link);
            $em->flush();
        }

        return $this->json(['success' => true]);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function detailsAction(?LinkEntity $link, Request $request): Response
    {
        if (!$link) {
            throw $this->createNotFoundException('Link not found.');
        }

        $dto = LinkDTO::fromEntity($link);
        return $this->json($dto);
    }

    /**
     * @Route("/visit/{shortId}", methods={"GET"})
     */
    public function visitAction(?string $shortId, Request $request, EntityManagerInterface $em, Visit $visitCommand): Response
    {
        if (!$shortId) {
            throw $this->createNotFoundException('Link not found.');
        }

        /** @var ?LinkEntity $link */
        $link = $em->getRepository(LinkEntity::class)
            ->findOneBy([
                'shortUrl' => $shortId,
            ]);
        if (!$link) {
            throw $this->createNotFoundException('Link not found.');
        }

        $visitCommand->visit($link, $request);

        return $this->redirect($link->getUrl());
    }

    private function submitLink(Request $request, BatchOperation $batchOperation, LinkEntity $link = null): Response
    {
        // TODO: add request validation
        $rawData = \json_decode($request->getContent(), true);
        if (isset($rawData['long_url'])) {
            if ($link) {
                $rawData += ['id' => $link->getId()];
            }
            $rawData = [$rawData];
        }

        $result = $batchOperation->process($rawData);

        return $this->json(['success' => true, 'processedLinks' => $result]);
    }
}
