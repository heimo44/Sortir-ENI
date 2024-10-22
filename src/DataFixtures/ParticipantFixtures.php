<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {

    }

    public function load(ObjectManager $manager): void
    {
        $participant= new Participant();
        $participant->setMail("admin@sortir.fr");
        $participant->setNom("admin");
        $participant->setPrenom("admin");
        $participant->setTelephone("0606060606");
        $participant->setPassword($this->passwordHasher->hashPassword($participant, "123456789101112"));
        $participant->setAdministrateur(true);
        $participant->setActif(true);
        $participant->setRoles(['ROLE_ADMIN']);

        $manager->persist($participant);

        for($i = 1 ; $i <= 5 ; $i++) {
            $participant = new Participant();
            $participant->setMail("participant$i@sortir.fr");
            $participant->setNom("participant$i");
            $participant->setPrenom("participant$i");
            $participant->setTelephone("0606060606");
            $participant->setPassword($this->passwordHasher->hashPassword($participant, "123456789101112"));
            $participant->setAdministrateur(false);
            $participant->setActif(true);
            $participant->setRoles(['ROLE_USER']);
            $manager->persist($participant);
            $this->addReference("user$i", $participant);
        }

        $manager->flush();
    }
}