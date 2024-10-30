<?php

namespace App\DataFixtures;
use App\Entity\Campus;
use Faker\Factory;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private $campusList = ["NANTES", "RENNES", "QUIMPER", "NIORT"];

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHarsher
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $faker= \Faker\Factory::create("fr_FR");
        // Création de l'admin
        $user = new Participant();
        $user->setEmail("admin@sortir.fr");
        $user->setLastname("Lebreton");
        $user->setFirstName("Lionel");
        $user->setTelephone("0606060606");
        $user->setPassword($this->passwordHarsher->hashPassword($user, "123456123456!!"));
        $user->setActif(true);
        $user->setRoles(['ROLE_ADMIN']);
        // Attribuer un campus aléatoire à l'admin
        $randomCampus = $this->getReference('campus_' . $this->campusList[array_rand($this->campusList)]);
        $user->setCampus($randomCampus);
        $manager->persist($user);
        // Création des utilisateurs
        for($i = 1 ; $i <= 50 ; $i++) {
            $user = new Participant();
                $lastname = $faker->lastName;
                $firstname = $faker->firstName;
                $user->setLastname($lastname);
                $user->setFirstName($firstname);

                // Supprimer les accents pour l'adresse e-mail
                $normalizedFirstname = iconv('UTF-8', 'ASCII//TRANSLIT', $firstname);
                $normalizedLastname = iconv('UTF-8', 'ASCII//TRANSLIT', $lastname);
                $email = strtolower(
                    preg_replace('/[^a-zA-Z0-9.]/', '', $normalizedFirstname . '.' . $normalizedLastname) . '@sortir.fr'
                );

                $user->setEmail($email);
                $user->setTelephone($faker->numerify('06########'));
                $user->setPassword($this->passwordHarsher->hashPassword($user, "123456"));
                $user->setActif(true);
                $user->setRoles(['ROLE_USER']);

                // Attribuer un campus aléatoire à l'utilisateur
                $randomCampus = $this->getReference('campus_' . $this->campusList[array_rand($this->campusList)]);
                $user->setCampus($randomCampus);

                $manager->persist($user);
                $this->addReference("user$i", $user);
            }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            CampusFixtures::class, // doit être chargé avant
        ];
    }
}
