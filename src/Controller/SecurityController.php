<?php
namespace App\Controller;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private const MAX_ATTEMPTS = 3;
    private const LOCKOUT_DURATION = 600; // 10 minutes en secondes

    #[Route(path: '/', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        ParticipantRepository $repository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Redirection si déjà connecté
        if ($this->getUser()) {
            $this->addFlash("success", 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('main_accueil');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($lastUsername) {
            $user = $repository->findOneBy(['email' => $lastUsername]);

            if ($user) {
                $isActif = $repository->participantCheckIsActif($lastUsername);
                if (!$isActif || !$user->getIsActif()) {
                    $this->addFlash("error", 'Votre compte est inactif. Veuillez contacter l\'administrateur.');
                    return $this->redirectToRoute('app_login');  // Si inactif, arrêt ici
                }

                if ($this->isUserLocked($user)) {
                    $remainingTime = $this->getRemainingLockoutTime($user);
                    $this->addFlash("error", sprintf('Compte temporairement bloqué. Réessayez dans %d minutes.', ceil($remainingTime / 60)));
                    return $this->redirectToRoute('app_login');  // Si verrouillé, arrêt ici
                }

                if ($error) {
                    $failedAttempts = $user->getFailedAttempts() ?? 0;
                    $user->setFailedAttempts($failedAttempts + 1);
                    if ($failedAttempts + 1 >= self::MAX_ATTEMPTS) {
                        $user->setLockedUntil(new \DateTime('+10 minutes'));
                        $this->addFlash("danger", "Compte bloqué pour 10 minutes suite à trop de tentatives.");
                    }
                    $entityManager->persist($user);
                    $entityManager->flush();
                } else {
                    //Réinitialisation des tentatives si connexion réussie
                    $user->setFailedAttempts(0);
                    $user->setLockedUntil(null);
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash("success", 'Vous êtes connecté.');
                    return $this->redirectToRoute('main_accueil');  // Connexion réussie
                }
            } else {
                $this->addFlash("error", "Nom d'utilisateur ou mot de passe incorrect.");
            }
        }

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

    private function isUserLocked($user): bool
    {
        $lockedUntil = $user->getLockedUntil();
        if (!$lockedUntil) {
            return false;
        }
        return $lockedUntil > new \DateTime();
    }

    private function getRemainingLockoutTime($user): int
    {
        if (!$user->getLockedUntil()) {
            return 0;
        }
        $now = new \DateTime();
        $lockoutEnd = $user->getLockedUntil();
        $remaining = $lockoutEnd->getTimestamp() - $now->getTimestamp();
        return max(0, $remaining);
    }
}
