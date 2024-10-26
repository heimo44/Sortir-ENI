<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Liste de villes avec leurs noms et codes postaux
        $villes = [
            ['nom' => 'Saint_Herblain', 'codePostal' => '44162'],
            ['nom' => 'Vertou', 'codePostal' => '44120'],
            ['nom' => 'Nantes', 'codePostal' => '44000'],
            ['nom' => 'Orvault', 'codePostal' => '44700'],
            ['nom' => 'Quimper', 'codePostal' => '29000'],
            ['nom' => 'Plomelin', 'codePostal' => '29700'],
            ['nom' => 'Niort', 'codePostal' => '79000'],
            ['nom' => 'Bessines', 'codePostal' => '79000'],
        ];

        foreach ($villes as $data) {
            $ville = new Ville();
            $ville->setNom($data['nom']);
            $ville->setCodePostal($data['codePostal']);
            $manager->persist($ville);

            // Ajouter une référence pour chaque ville
            $this->addReference('ville_' . strtolower(str_replace([' ', '-'], '_', $data['nom'])), $ville);
        }

        $manager->flush();
    }

}