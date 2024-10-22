<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etatLibelle = ["Créée", "Ouverte", "Clôturée", "Activité en cours", "passée", "Annulée"];
        foreach ($etatLibelle as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
        }

        $manager->flush();
    }
}
