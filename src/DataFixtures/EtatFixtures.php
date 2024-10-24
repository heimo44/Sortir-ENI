<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etatLibelle = ["Créée", "Ouverte", "Clôturée", "Activité en cours", "passée", "Annulée"];
        foreach ($etatLibelle as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);

            // On enregistre une référence pour pouvoir récupérer cet objet dans d'autres fixtures
            $this->addReference('etat_' . $libelle, $etat);
        }

        $manager->flush();
    }
}