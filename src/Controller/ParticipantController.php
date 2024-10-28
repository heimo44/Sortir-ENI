<?php

namespace App\Controller;

use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: 'participant_index')]
    public function index(): Response
    {
        return $this->render('participant/index.html.twig', [
            'controller_name' => 'ParticipantController',
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'participant_show', requirements: ['id' => '\d+'], methods:['GET'])]
    public function show(ParticipantRepository $participantRepository, $id): Response
    {
        $participant = $participantRepository->find($id);

        if(!$participant){
            // A décommenter s'il y a un flash display sur la page main_accueil
            // $this->addFlash('danger', "Le participant n'existe pas.");
            return $this->redirectToRoute('main_accueil'); // Change to your desired route
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/{id}/edit', name: 'participant_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(ParticipantRepository $participantRepository, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, $id): Response
    {
        // Renvoyer l'utilisateur sur la page d'accueil si l'ID ne correspond à aucun participant
        $participant = $participantRepository->find($id);

        if (!$participant) {
            // A décommenter s'il y a un flash display sur la page cible
            // $this->addFlash('danger', "Le participant n'existe pas.");

            return $this->redirectToRoute('main_accueil');
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        $user = $this->getUser();

        // Pour limiter l'accès à la page d'édition seulement à l'utilisateur qui édite son propre profil
        if(!$user || $participantForm->get('email')->getData() != $user->getUserIdentifier()){
            return $this->redirectToRoute('app_login');
        }

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $password = $participantForm->get('password')->getData();
            $confirmPassword = $request->request->get('confirm_password');

            if($password !== $confirmPassword) {
                $this->addFlash("danger", "Les mots de passe ne correspondent pas");
            }

            if($password === $confirmPassword) {
                // Pour crypter le mdp
                $hashedPassword = $userPasswordHasher->hashPassword($participant, $password);
                $participant->setPassword($hashedPassword);

                $em->persist($participant);
                $em->flush();

                // A décommenter s'il y a un flash display sur la page cible
                // $this->addFlash("success", "Utilisateur mis à jour, veuillez vous reconnecter");

                return $this->redirectToRoute('app_logout', [
                    'id' => $participant->getId()
                ]);
            }
        }

        return $this->render('participant/edit.html.twig', [
            "participant" => $participant,
            "participantForm" => $participantForm->createView(),
        ]);
    }
}
