<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[IsGranted('ROLE_USER')]
#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/', name: 'participant_index', methods:['GET', 'POST'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        $listParticipants = $participantRepository->findAll();

        return $this->render('participant/index.html.twig', [
            'participants' => $listParticipants,
        ]);
    }

    #[Route('/{id}', name: 'participant_show', requirements: ['id' => '\d+'], methods:['GET', 'POST'])]
    public function show(ParticipantRepository $participantRepository, Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $participant = $participantRepository->find($id);

        if(!$participant){
            $this->addFlash('danger', "Le participant n'existe pas.");
            return $this->redirectToRoute('main_accueil');
        }

        if ($request->isMethod('POST')) {
            $actif = $request->request->get('actif');
            $participant->setIsActif($actif === '1');
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', "Le statut du participant a été mis à jour avec succès.");
            return $this->redirectToRoute('participant_show', ['id' => $participant->getId()]);
        }

        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'participant_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(ParticipantRepository $participantRepository, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher, $id): Response
    {
        $participant = $participantRepository->find($id);

        if (!$participant) {
            $this->addFlash('danger', "Le participant n'existe pas.");
            return $this->redirectToRoute('main_accueil');
        }

        $user = $this->getUser();
        if(!$user || $participant->getEmail() !== $user->getUserIdentifier()){
            return $this->redirectToRoute('app_login');
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant, [
            'user_edition' => true,
        ]);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $this->handleProfileImageUpload($participant, $participantForm);

            $newPassword = $participantForm->get('newPassword')->getData();
            $confirmNewPassword = $participantForm->get('newPasswordConfirmation')->getData();

            if($newPassword !== '' && $newPassword === $confirmNewPassword) {
                $hashedPassword = $userPasswordHasher->hashPassword($participant, $newPassword);
                $participant->setPassword($hashedPassword);
            } elseif ($newPassword !== '' && $newPassword !== $confirmNewPassword) {
                $this->addFlash("danger", "Les mots de passe ne correspondent pas");
                return $this->redirectToRoute('participant_edit', ['id' => $participant->getId()]);
            }

            $participant->setNewPassword(null);
            $participant->setNewPasswordConfirmation(null);

            $em->persist($participant);
            $em->flush();

            if($newPassword !== ''){
                $this->addFlash("success", "Utilisateur mis à jour, veuillez vous reconnecter !");
                return $this->redirectToRoute('app_logout', ['id' => $participant->getId()]);
            } else {
                $this->addFlash("success", "Utilisateur mis à jour !");
                return $this->redirectToRoute('participant_show', ['id' => $participant->getId()]);
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
        $participant = new Participant();
        $participant->setPassword($userPasswordHasher->hashPassword($participant, '123456'));
        $participant->setIsActif(true);

        $participantForm = $this->createForm(ParticipantType::class, $participant, [
            'user_creation' => true,
        ]);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $newPassword = $participantForm->get('newPassword')->getData();
            if ($newPassword !== '') {
                $hashedPassword = $userPasswordHasher->hashPassword($participant, $newPassword);
                $participant->setPassword($hashedPassword);
            }

            $role = $participantForm->get('roles')->getData();
            if($role){
                $role = 'ROLE_ADMIN';
            } else {
                $role = 'ROLE_USER';
            }
            $participant->setRoles([$role]);

            $participant->setNewPassword(null);

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

    private function handleProfileImageUpload(Participant $participant, $form): void
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form->get('profileImageFilename')->getData();

        if ($uploadedFile) {
            $slugger = new AsciiSlugger();
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename .'-'.uniqid().'.'. $uploadedFile->guessExtension();

            try {
                $uploadedFile->move(
                    $this->getParameter('assets_uploads_images'),
                    $newFilename
                );

                $oldFilePath = $this->getParameter('assets_uploads_images') . '/' . $participant->getProfileImageFilename();

                if ($participant->getProfileImageFilename() && file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }

                $participant->setProfileImageFilename($newFilename);
            } catch (FileException $exception) {
                $this->addFlash('danger', 'There was an error uploading your profile image');
            }
        }
    }
}