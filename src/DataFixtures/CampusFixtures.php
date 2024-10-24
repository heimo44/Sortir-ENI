<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $campus = ["NANTES", "RENNES", "QUIMPER", "NIORT"];
        foreach ($campus as $ecole) {
            $campus = new Campus();
            $campus->setNom($ecole);
            $manager->persist($campus);

            // On enregistre une référence pour pouvoir récupérer cet objet dans d'autres fixtures
            $this->addReference('campus_' . $ecole, $campus);
        }

        $manager->flush();
    }
}