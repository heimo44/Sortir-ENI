<?php

namespace App\Repository;

use App\Entity\Participant;
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

    public function findByFilters($campus, $organisateur, $inscrit, $nonInscrit, $passe, $nomSortie, $dateDebut, $dateFin, $userId, $user, $isOrganizer)
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('o')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $campus);

        if ($organisateur) {
            $qb->andWhere('s.organisateur = :userId')
                ->setParameter('userId', $userId);
        }

        if ($inscrit) {
            $qb->andWhere(':userId MEMBER OF s.participants')
                ->setParameter('userId', $userId);
        }

        if ($nonInscrit) {
            $qb->andWhere(':userId NOT MEMBER OF s.participants')
                ->setParameter('userId', $userId);
        }

        if ($passe) {
            $qb->andWhere('s.dateHeureDebut < :currentDate')
                ->setParameter('currentDate', new \DateTime());
        }

        if ($nomSortie) {
            $qb->andWhere('s.nom LIKE :nomSortie')
                ->setParameter('nomSortie', '%' . $nomSortie . '%');
        }

        if ($dateDebut) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin) {
            $qb->andWhere('s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);

        if ($isOrganizer) {
            $qb->andWhere('s.organisateur = :user')
                ->setParameter('user', $user);
        }

            return $qb->getQuery()->getResult();
        }
    }
}