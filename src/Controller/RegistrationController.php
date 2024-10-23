<?php

namespace App\Controller;

use App\Entity\Participant;

use App\Form\RegistrationType;

use App\Security\AppAuthenticator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(RegistrationType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $participant->setRoles(["ROLES_USER"]);
            // encode the plain password
            $participant->setPassword($userPasswordHasher->hashPassword($participant, $plainPassword));

            $entityManager->persist($participant);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $security->login($participant, AppAuthenticator::class, 'main');
        }

        return $this->render('security/login.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
