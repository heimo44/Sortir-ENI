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

    public function findByFilters(
        $campus,
        $isOrganizer,
        $isInscrit,
        $isNonInscrit,
        $isPassed,
        $user,
        ?string $searchTerm = null,
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFin = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.organisateur', 'o')
            ->addSelect('o')
            ->leftJoin('s.etat', 'e')
            ->addSelect('e')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $campus);

        // Filtre sur le nom de la sortie
        if ($searchTerm) {
            $qb->andWhere('s.nom LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Filtre sur la période
        if ($dateDebut && $dateFin) {
            $qb->andWhere('s.dateHeureDebut BETWEEN :dateDebut AND :dateFin')
                ->setParameter('dateDebut', $dateDebut)
                ->setParameter('dateFin', $dateFin);
        }

        // Construction de la condition OR pour les différents filtres
        $conditions = [];
        $parameters = [];

        // Sorties dont je suis l'organisateur
        if ($isOrganizer) {
            $conditions[] = 's.organisateur = :userId';
            $parameters['userId'] = $user->getId();
        }

        // Sorties auxquelles je suis inscrit
        if ($isInscrit) {
            $conditions[] = ':user MEMBER OF s.participants';
            $parameters['user'] = $user;
        }

        // Sorties auxquelles je ne suis pas inscrit
        if ($isNonInscrit) {
            $conditions[] = ':user NOT MEMBER OF s.participants';
            $parameters['user'] = $user;
        }

        // Sorties passées
        if ($isPassed) {
            $conditions[] = 'e.libelle = :etatPasse';
            $parameters['etatPasse'] = 'Passée';
        }

        // Si au moins une condition est présente, on l'ajoute à la requête
        if (!empty($conditions)) {
            $qb->andWhere('(' . implode(' OR ', $conditions) . ')');
            foreach ($parameters as $key => $value) {
                $qb->setParameter($key, $value);
            }
        }

        return $qb->getQuery()->getResult();
    }
}