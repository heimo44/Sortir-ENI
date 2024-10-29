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

    public function findByFilters($campus, $organisateur, $inscrit, $nonInscrit, $passe, $nomSortie, $dateDebut, $dateFin, $userId)
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
        }

        return $qb->getQuery()->getResult();
    }
}


//    public function findByFilters(
//        ?Campus $campus = null,
//        ?string $searchTerm = null,
//        ?\DateTime $dateDebut = null,
//        ?\DateTime $dateFin = null,
//        ?bool $isOrganisateur = false,
//        ?bool $isInscrit = false,
//        ?bool $isNonInscrit = false,
//        ?bool $isSortiePassee = false,
//        ?Participant $user = null
//    ): array {
//        $qb = $this->createQueryBuilder('s')
//            ->leftJoin('s.campus', 'c')
//            ->leftJoin('s.participant', 'p')
//            ->leftJoin('s.participants', 'inscrits');
//
//        // Filtre par campus
//        if ($campus) {
//            $qb->andWhere('s.campus = :campus')
//                ->setParameter('campus', $campus);
//        }
//
//        // Filtre par nom de sortie
//        if ($searchTerm) {
//            $qb->andWhere('s.nom LIKE :searchTerm')
//                ->setParameter('searchTerm', '%' . $searchTerm . '%');
//        }
//
//        // Filtre par date
//        if ($dateDebut) {
//            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
//                ->setParameter('dateDebut', $dateDebut);
//        }
//        if ($dateFin) {
//            $qb->andWhere('s.dateHeureDebut <= :dateFin')
//                ->setParameter('dateFin', $dateFin);
//        }
//
//        // Filtre organisateur
//        if ($isOrganisateur && $user) {
//            $qb->andWhere('s.participant = :organisateur')
//                ->setParameter('organisateur', $user);
//        }
//
//        // Filtre inscrit
//        if ($isInscrit && $user) {
//            $qb->andWhere(':user MEMBER OF s.participants')
//                ->setParameter('user', $user);
//        }
//
//        // Filtre non inscrit
//        if ($isNonInscrit && $user) {
//            $qb->andWhere(':user NOT MEMBER OF s.participants')
//                ->setParameter('user', $user);
//        }
//
//        // Filtre sorties passées
//        if ($isSortiePassee) {
//            $qb->andWhere('s.dateHeureDebut < :now')
//                ->setParameter('now', new \DateTime());
//        }
//
//        return $qb->getQuery()->getResult();
//    }

//}
