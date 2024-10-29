<?php

namespace App\Controller;


use App\Repository\ParticipantRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, ParticipantRepository $repository, Request $request): Response
    {
        if ($this->getUser()) {
            $this->addFlash("success", 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('main_accueil'); // Redirigez vers la route appropriée
        }
        // Récupère l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        // Dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si pas d'erreur et qu'un nom d'utilisateur est fourni, l'utilisateur s'est connecté avec succès
        if (!$error && $lastUsername) {
            $this->addFlash("success", 'Vous êtes connecté.');
            return $this->redirectToRoute('main_accueil'); // Redirigez après la connexion réussie
        }

        // Affiche le formulaire de connexion
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,

            'error' => $error
        ]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
