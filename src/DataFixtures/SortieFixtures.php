<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            // Création d'une instance vide
            $sortie = new Sortie();

            // On hydrate l'instance avec des données factices
            $sortie->setNom($faker->sentence(3));

            // Génère une date avec une heure aléatoire entre maintenant et 6 mois dans le futur
            $dateSortie = $faker->dateTimeBetween('now', '+6 months');

            // Vérifie si l'objet DateTime est correctement créé
            if ($dateSortie instanceof \DateTime) {
                // Affiche la date avant modification
                echo "Avant modification: " . $dateSortie->format('Y-m-d H:i:s') . "\n";

                // Définit l'heure et les minutes
                $dateSortie->setTime($faker->numberBetween(0, 23), $faker->numberBetween(0, 59));

                // Affiche la date après modification
                echo "Après modification: " . $dateSortie->format('Y-m-d H:i:s') . "\n";

                $sortie->setDateHeureDebut($dateSortie);

                // Clôture : clone la date de sortie pour éviter de la modifier directement
                $clotureDate = clone $dateSortie;
                $clotureDate->modify('-1 day');

                // Attribue la date de clôture à l'entité
                $sortie->setDateLimiteInscription($clotureDate);

                $duree = $faker->numberBetween(30, 300);
                $sortie->setDuree($duree);

                $sortie->setNbInscriptionsMax($faker->numberBetween(10, 30));

                $sortie->setInfosSortie($faker->sentence(20));

                // Attribue un état aléatoire à la sortie
                $etat = $this->getReference('etat_' . $faker->randomElement(["Créée", "Ouverte", "Clôturée", "Activité en cours", "passée", "Annulée"]));
                $sortie->setEtat($etat);

                // Persiste l'entité dans la base de données
                $manager->persist($sortie);
            }
        }

            $manager->flush();
    }

    public function getDependencies()
    {
        return [
            EtatFixtures::class, // Indique que EtatFixture doit être chargé avant
        ];
    }
}