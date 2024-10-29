<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHarsher
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create("fr_FR");

        $user = new Participant();
        $user->setEmail("admin@sortir.fr");
        $user->setLastname("Lebreton");
        $user->setFirstName("Lionel");
        $user->setTelephone("0606060606");
        $user->setPassword($this->passwordHarsher->hashPassword($user, "123456123456!!"));
        $user->setActif(true);
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        for ($i = 1; $i <= 10; $i++) {
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
            $manager->persist($user);

            $this->addReference("user$i", $user);
        }

        $manager->flush();
    }
}