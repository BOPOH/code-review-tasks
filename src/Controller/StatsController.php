<?php

namespace App\Controller;

use App\Entity\Link as LinkEntity;
use App\Entity\LinkVisit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function random_int;

/**
 * @Route("/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function indexAction(Request $request, EntityManagerInterface $em): Response
    {
        $result = $em->getRepository(LinkVisit::class)
            ->createQueryBuilder('link_visit')
            ->join('link_visit.link', 'link')
            ->select('COUNT(1) AS total_views')
            ->addSelect('COUNT(DISTINCT CONCAT(link_visit.ip, link_visit.userAgent)) AS unique_views')
            ->addSelect('link.id')
            ->addGroupBy('link')
            ->addOrderBy('unique_views', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return $this->json($result);
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function linkAction(?LinkEntity $link, Request $request, EntityManagerInterface $em): Response
    {
        if (!$link) {
            throw $this->createNotFoundException('Link not found.');
        }

        $result = $em->getRepository(LinkVisit::class)
            ->createQueryBuilder('link_visit')
            ->select('COUNT(1) AS total_views')
            ->addSelect('COUNT(DISTINCT CONCAT(link_visit.ip, link_visit.userAgent)) AS unique_views')
            ->addSelect('DATE(link_visit.date) AS visit_date')
            ->addGroupBy('visit_date')
            ->addOrderBy('visit_date', 'DESC')
            ->andWhere('link_visit.link = :link')
            ->setParameter('link', $link)
            ->getQuery()
            ->getArrayResult();

        return $this->json($result);
    }
}
