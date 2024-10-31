<?php

namespace App\Security;

use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;
    public const LOGIN_ROUTE = 'app_login';
    private ParticipantRepository $participantRepository;

    public function __construct(ParticipantRepository $participantRepository, private UrlGeneratorInterface $urlGenerator)
    {
        $this->participantRepository = $participantRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $payload = $request->getPayload();
        $email = $payload->getString('email');
        $password = $payload->getString('password');
        $csrfToken = $payload->getString('_csrf_token');

        // Récupération de l'utilisateur à partir du repository
        $user = $this->participantRepository->findOneBy(['email' => $email]);

        // Vérification si l'utilisateur existe et s'il est actif
        if ($user === null || !$this->participantRepository->participantCheckIsActif($email)) {
            // Gérer le cas où l'utilisateur n'existe pas ou est inactif
            $request->getSession()->getFlashBag()->add('error', 'Votre compte est inactif. Veuillez contacter l\'administrateur.');

            // Vous pouvez retourner null ou une réponse de redirection pour terminer le processus
           throw new AuthenticationException('Votre compte est inactif.'); // Ceci arrêtera le processus d'authentification
        }

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }



    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Rediriger vers la route souhaitée après la connexion
        return new RedirectResponse($this->urlGenerator->generate('app_login')); // Remplacez 'app_home' par votre route
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}