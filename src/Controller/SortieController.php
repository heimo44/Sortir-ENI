<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('main/accueil.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $user = $this->getUser();
        if($user){
            $currentUser = $this->participantRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
            $campus = $currentUser->getCampus();
            $sortie = new Sortie();
            $sortie->setOrganisateur($currentUser);
            $sortie->setCampus($campus);
            $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
            if ($request->isMethod('POST')) {
                dump($request->getContent());
                if ($request->request->get('action') === 'publier') {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                    $sortie->setEtat($etat);
                }
            }
            $entityManager->persist($etat);
            $entityManager->flush();

            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Votre sortie a bien été créée');
                return $this->redirectToRoute('main_accueil');
            }

            return $this->render('sortie/new.html.twig', [
                'sortie' => $sortie,
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        $user = $this->getUser();
        if($user){
            return $this->render('sortie/show.html.twig', [
                'sortie' => $sortie,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        $user = $this->getUser();
        if($user){
            if($user->getId() !== $sortie->getOrganisateur()->getId()){
                $this->addFlash('danger','Vous ne pouvez pas modifier cette sortie' );
                return $this->redirectToRoute('main_accueil');
            }
            if ($request->request->get('annulerSortie')) {
                $etatCancel = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($etatCancel);
                $entityManager->flush();
                return $this->redirectToRoute('main_accueil');
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $action = $request->request->get('action');
                if ($action === 'publier') {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                    $sortie->setEtat($etat);
                }
                $entityManager->persist($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('main_accueil', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('sortie/edit.html.twig', [
                'sortie' => $sortie,
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/add/participant/{id}', name: 'app_sortie_add_participant', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function addParticipant(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if($user){
            if($sortie->getDateLimiteInscription() > new \DateTime('today')){
                $this->addFlash('danger', 'Vous ne pouvez pas vous inscrire à cette sortie');
                return $this->redirectToRoute('main_accueil');
            } elseif($user and $sortie->getDateLimiteInscription() <= new \DateTime('today')){
                if($sortie->getNbInscriptionsMax() <= $sortie->getParticipants()->count()){
                    $this->addFlash('danger', 'Il ne reste plus de places disponibles');
                    return $this->redirectToRoute('main_accueil');
                }
                $sortie->addParticipant($user);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Participation enregistrée avec succès');
                return $this->redirectToRoute('main_accueil');
            }

            return $this->render('main/accueil.html.twig', [
                'sortie' => $sortie,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/remove/participant/{id}', name: 'app_sortie_remove_participant', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function removeParticipant(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $user = $this->getUser();
        if($user){
            if($sortie->getDateHeureDebut() < new \DateTime('now')){
                $this->addFlash('danger', "Vous ne pouvez pas vous désister d'une sortie qui a déjà débuté");
                return $this->redirectToRoute('main_accueil');
            }
            if($user and $sortie->getDateHeureDebut() > new \DateTime('now')){
                $sortie->removeParticipant($user);
                $entityManager->persist($sortie);

                $entityManager->flush();
                $this->addFlash('danger', 'Vous ne participez plus à la sortie');
                return $this->redirectToRoute('main_accueil');
            }

            return $this->render('main/accueil.html.twig', [
                'sortie' => $sortie,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/publish/{id}', name: 'app_sortie_publish', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function publishSortie(Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {

        $user = $this->getUser();
        if($user){
            if($user and $sortie->getOrganisateur()->getId() == $user->getId()){
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
                $entityManager->persist($sortie);
                $entityManager->flush();
                $this->addFlash('success', 'Votre sortie a été publiée avec succès');
                return $this->redirectToRoute('main_accueil');
            } else {
                $this->addFlash('danger', "Vous ne pouvez pas publier une sortie dont vous n'êtes pas l'organisateur");
                return $this->redirectToRoute('main_accueil');
            }
        }
        return $this->redirectToRoute('app_login');
    }

    #[Route('/{id}/cancel', name: 'app_sortie_cancel', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function cancel(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {
        if($this->getUser()){
            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);
            $sortie = $sortieRepository->findOneBy(['id' => $sortie->getId()]);
            $currentDateTime = new \DateTime();
            if ($sortie->getDateHeureDebut() < $currentDateTime) {
                $this->addFlash('error', 'Vous ne pouvez pas annuler une sortie dont la date est déjà passée.');
                return $this->redirectToRoute('main_accueil');
            }

            if($this->getUser()->getId() != $sortie->getOrganisateur()->getId()){
                $this->addFlash('danger','Vous ne pouvez pas annuler cette sortie' );
                return $this->redirectToRoute('main_accueil');
            }

            if ($request->isMethod('POST')) {
                $motif = $request->request->get('motif');
                if ($request->request->get('action') === 'save') {
                    $this->addFlash('success','Motif enregistré' );
                    $sortie->setMotif($motif);
                    $entityManager->flush();
                    return $this->redirectToRoute('app_sortie_cancel', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
                } elseif ($request->request->get('action') === 'cancel') {
                    $etatCancel = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                    $sortie->setEtat($etatCancel);
                    $sortie->setMotif($motif);
                    $entityManager->flush();
                    return $this->redirectToRoute('main_accueil', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
                }
            }

            return $this->render('sortie/cancel.html.twig', [
                'sortie' => $sortie,
                'form' => $form,
            ]);
        }
        return $this->redirectToRoute('app_login');
    }
}
