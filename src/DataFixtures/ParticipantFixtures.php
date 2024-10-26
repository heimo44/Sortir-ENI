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
        $faker= \Faker\Factory::create("fr_FR");

        $user= new Participant();
        $user->setEmail("admin@sortir.fr");
        $user->setLastname("Lebreton");
        $user->setFirstName("Lionel");
        $user->setTelephone("0606060606");
        $user->setPassword($this->passwordHarsher->hashPassword($user, "123456123456!!"));

        $user->setActif(true);
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        for($i = 1 ; $i <= 10 ; $i++) {
            $user = new Participant();

            $Lastname = $faker->Lastname;
            $Firstname = $faker->Firstname;
            $user->setLastname($Lastname);
            $user->setFirstName($Firstname);
            $normalized_firstname = iconv('UTF-8', 'ASCII//TRANSLIT', $Firstname);
            $normalized_lastname = iconv('UTF-8', 'ASCII//TRANSLIT', $Lastname);

            $email = strtolower(
                preg_replace(
                    '/[^a-zA-Z0-9.]/',
                    '',
                    $normalized_firstname . '.' . $normalized_lastname
                ) . '@sortir.fr'
            );

            $user->setEmail($email);
            $user->setEmail(strtolower($Firstname . '.' . $Lastname . '@sortir.fr'));
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