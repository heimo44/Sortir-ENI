<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters($campus, $isOrganizer, $user)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('o')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $campus);

        if ($isOrganizer) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

            return $qb->getQuery()->getResult();
    }

}