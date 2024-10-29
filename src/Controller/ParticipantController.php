<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
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

        $participantForm = $this->createForm(ParticipantType::class, $participant, [
            'user_edition' => true,
        ]);
        $participantForm->handleRequest($request);

        $user = $this->getUser();

        // Pour limiter l'accès à la page d'édition seulement à l'utilisateur qui édite son propre profil
        if(!$user || $participantForm->get('email')->getData() != $user->getUserIdentifier()){
            return $this->redirectToRoute('app_login');
        }

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $newPassword = $participantForm->get('newPassword')->getData();
            $confirmNewPassword = $participantForm->get('newPasswordConfirmation')->getData();

            if($newPassword === '' && $newPassword !== $confirmNewPassword) {
                $this->addFlash("danger", "Les mots de passe ne correspondent pas");
            }

            if($newPassword === $confirmNewPassword) {
                if($newPassword !== ''){
                    // Pour crypter le nouveau mot de passe
                    $hashedPassword = $userPasswordHasher->hashPassword($participant, $newPassword);
                    $participant->setPassword($hashedPassword);
                }

                // Pour purger les mots de passes de la base de données (données non cryptées)
                $participant->setNewPassword(null);
                $participant->setNewPasswordConfirmation(null);

                $em->persist($participant);
                $em->flush();

                // Si changement de mot de passe, reconnexion obligatoire
                if($newPassword !== ''){
                    // A décommenter s'il y a un flash display sur la page login
                    // $this->addFlash("success", "Utilisateur mis à jour, veuillez vous reconnecter !");

                    return $this->redirectToRoute('app_logout', [
                        'id' => $participant->getId()
                    ]);
                // Sinon, retourne sur la page d'affichage du profil
                } else {
                    $this->addFlash("success", "Utilisateur mis à jour !");

                    return $this->redirectToRoute('participant_show', [
                        'id' => $participant->getId(),
                    ]);
                }
            }
        }

        return $this->render('participant/edit.html.twig', [
            "participant" => $participant,
            "participantForm" => $participantForm->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/create', name: 'participant_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Création d'un nouveau participant avec des valeurs par défaut
        $participant = new Participant();
        $participant->setPassword($userPasswordHasher->hashPassword($participant, '123456'));
        $participant->setActif(true);

        // Création du formulaire
        $participantForm = $this->createForm(ParticipantType::class, $participant, [
            'user_creation' => true,
        ]);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            // Pour crypter le nouveau mot de passe
            $newPassword = $participantForm->get('newPassword')->getData();
            if ($newPassword !== '') {
                $hashedPassword = $userPasswordHasher->hashPassword($participant, $newPassword);
                $participant->setPassword($hashedPassword);
            }

            // Pour ajouter un nouveau rôle selon la checkbox
            $role = $participantForm->get('roles')->getData();
            if($role == ['ROLE_ADMIN']){
                $participant->setRoles(['ROLE_ADMIN']);
            } else {
                $participant->setRoles(['ROLE_USER']);
            }

            // Pour purger le mot de passe de la base de données (données non cryptées)
            $participant->setNewPassword(null);

            // Persister l'entité
            $em->persist($participant);
            $em->flush();

            $this->addFlash("success", "Compte créé avec succès !");
            return $this->redirectToRoute('participant_index');
        }

        return $this->render('participant/create.html.twig', [
            'participant' => $participant,
            "participantForm" => $participantForm->createView(),
        ]);
    }
}
