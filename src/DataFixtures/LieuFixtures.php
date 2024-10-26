<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Liste de lieux avec leurs informations et la référence à la ville
        $lieux = [
            [
                'nom' => 'Restaurant Les Caudalies',
                'rue' => '229 Route de Vannes',
                'latitude' => 47.242162012142,
                'longitude' => -1.595495017988,
                'ville' => 'ville_saint_herblain'
            ],
            [
                'nom' => 'Salle de concert',
                'rue' => '1, rue du 11 nov. 1918',
                'latitude' => 47.169604989313,
                'longitude' => -1.475186041312,
                'ville' => 'ville_vertou'
            ],
            [
                'nom' => 'Jardinnerie Jane',
                'rue' => '10, rue Mercoeur',
                'latitude' => 47.21751800143,
                'longitude' => -1.560407030191,
                'ville' => 'ville_nantes'
            ],
            [
                'nom' => 'Cinéma',
                'rue' => '33, avenue de la Ferrière',
                'latitude' => 47.248567001544,
                'longitude' => -1.587484014028,
                'ville' => 'ville_orvault'
            ],
            [
                'nom' => 'Médiathèque',
                'rue' => 'Esplanade Julien Gracq',
                'latitude' => 47.995952529963,
                'longitude' => -4.108731625264,
                'ville' => 'ville_quimper'
            ],
            [
                'nom' => 'Théâtre',
                'rue' => '20, rue Menez Cloeder',
                'latitude' => 47.934334518357,
                'longitude' => -4.149074998158,
                'ville' => 'ville_plomelin'
            ],
            [
                'nom' => 'Piscine',
                'rue' => 'Rue de Bessac',
                'latitude' => 46.330987062279,
                'longitude' => -0.465029118911,
                'ville' => 'ville_niort'
            ],
            [
                'nom' => 'Atelier cours de cuisine',
                'rue' => 'Place du marché',
                'latitude' => 46.108735401345,
                'longitude' => 1.358337648577,
                'ville' => 'ville_bessines'
            ],
        ];

        foreach ($lieux as $data) {
            $lieu = new Lieu();
            $lieu->setNom($data['nom']);
            $lieu->setRue($data['rue']);
            $lieu->setLatitude($data['latitude']);
            $lieu->setLongitude($data['longitude']);
            $lieu->setVille($this->getReference($data['ville']));

            $manager->persist($lieu);

            // Dans LieuFixtures.php
            $referenceKey = 'lieu_' . strtolower(str_replace(
                    [' ', 'é', 'è', 'ê', 'à', 'â', 'ô', 'û', 'ù', 'ï', 'ü'],
                    ['_', 'e', 'e', 'e', 'a', 'a', 'o', 'u', 'u', 'i', 'u'],
                    $data['nom']
                ));
            $this->addReference($referenceKey, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Préciser que LieuFixtures dépend de VilleFixtures
        return [
            VilleFixtures::class,
        ];
    }

}