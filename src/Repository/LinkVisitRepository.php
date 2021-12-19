<?php

namespace App\Repository;

use App\Entity\LinkVisit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LinkVisit|null find($id, $lockMode = null, $lockVersion = null)
 * @method LinkVisit|null findOneBy(array $criteria, array $orderBy = null)
 * @method LinkVisit[]    findAll()
 * @method LinkVisit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkVisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkVisit::class);
    }
}
