<?php

namespace App\Link\Command;

use App\Entity\Link as LinkEntity;
use App\Entity\LinkVisit as LinkVisitEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class Visit
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function visit(LinkEntity $entity, Request $request): LinkVisitEntity
    {
        $ip = (string)$request->getClientIp();
        $userAgent = (string)$request->headers->get('User-Agent');
        $date = new \DateTime((string)$request->headers->get('Date'));

        $linkVisit = new LinkVisitEntity();
        $linkVisit->setLink($entity);
        $linkVisit->setIp($ip);
        $linkVisit->setUserAgent($userAgent);
        $linkVisit->setDate($date);

        $entity->addLinkVisit($linkVisit);

        $this->em->persist($linkVisit);
        $this->em->flush();

        return $linkVisit;
    }
}
