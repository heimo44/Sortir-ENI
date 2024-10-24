<?php

namespace App\DataFixtures;

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
        $user= new Participant();
        $user->setEmail("admin@sortir.fr");
        $user->setLastname("admin");
        $user->setFirstName("admin");
        $user->setTelephone("0606060606");
        $user->setPassword($this->passwordHarsher->hashPassword($user, "123456"));

        $user->setActif(true);
        $user->setRoles(['ROLE_ADMIN']);

        $manager->persist($user);

        for($i = 1 ; $i <= 5 ; $i++) {
            $user = new Participant();
            $user->setEmail("participant$i@sortir.fr");
            $user->setLastname("participant$i");
            $user->setFirstName("participant$i");
            $user->setTelephone("0606060606");
            $user->setPassword($this->passwordHarsher->hashPassword($user, "123456"));

            $user->setActif(true);
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $this->addReference("user$i", $user);
        }

        $manager->flush();
    }
}